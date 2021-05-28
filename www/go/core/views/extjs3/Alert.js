(function(){

	const Alerts = Ext.extend(Ext.util.Observable, {

		constructor : function() {

			this.supr().constructor.call(this);

			this.addEvents({
				"beforeshow" : true
			});
			go.Notifier.addStatusIcon('reminder', 'ic-notifications');


		},

		init : function() {
			go.Db.store("Alert").all().then((alerts) => {
				alerts.forEach((alert) => {
					this.show(alert);
				});
			});

			go.Db.store("Alert").on("changes", (store, added, changed, destroyed) => {
				destroyed.forEach((id) => {
					const panel = go.Notifier.getById("core-alert-" + id);
					//to prevent a destroy action to the server because it's already destroyed
					if(panel) {
						panel.destroyedByChanges = true;
						panel.destroy();
					}
				});

				for(let id in added) {
					this.show(added[id]);
				}

				for(let id in changed) {
					this.show(changed[id]);
				}
			});
		},

		show : function(alert) {
			const now = new Date();

			if(new Date(alert.triggerAt) > now) {
				go.Notifier.removeById('core-alert-' + alert.id);
				return;
			}

			go.Db.store(alert.entity).single(alert.entityId).then((entity) => {

				const c = {
					statusIcon: 'reminder',
					itemId: 'core-alert-' + alert.id,
					title: entity.title || entity.name || entity.description,
					html: go.util.Format.dateTime(alert.triggerAt),
					iconCls: 'entity ' + alert.entity,
					buttonAlign: "right",
					listeners: {
						destroy: (panel) => {
							if(!panel.destroyedByChanges) {
								go.Db.store("Alert").destroy(alert.id);
							}
						}
					},
					buttons: [{
						text: t("Open"),
						handler: () => {
							go.Entities.get(alert.entity).goto(alert.entityId);
							go.Notifier.hideNotifications();
						}
					}, {
						text: t("Dismiss"),
						handler: (btn) => {
							btn.findParentByType("panel").destroy();
						}
					}]
				};

				c.notificationBody = c.html;

				const alertConfig = {alert: alert, entity: entity, panelPromise: Promise.resolve(c)};

				//Modules can use this to cancel or modify the alert
				if(this.fireEvent('beforeshow', this, alertConfig) === false) {
					return;
				}

				alertConfig.panelPromise.then((panelCfg) => {
					go.Notifier.msg(panelCfg);
				});

			});
		}
	})

	go.Alerts = new Alerts();

	GO.mainLayout.on('render', function () {
		go.Alerts.init();
	});


})();