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

			this.store = new go.data.Store({
				entityStore: "Alert",
				fields: ['id', 'entity', 'entityId', 'data', 'tag', 'triggerAt', 'userId'],
				filters: {
					user: {userId: go.User.id}
				}
			});

			this.store.on("load", () => {
				this.store.getRange().forEach((rec) => {
					this.show(rec.data);
				});
			});


			this.store.load();

		},

		show : function(alert) {
			const now = new Date();

			if(new Date(alert.triggerAt) > now) {
				go.Notifier.removeById('core-alert-' + alert.id);
				return;
			}

			go.Db.store(alert.entity).single(alert.entityId).then((entity) => {

				const iconCls = go.Entities.getLinkIcon(alert.entity);

				const c = {
					statusIcon: 'reminder',
					itemId: 'core-alert-' + alert.id,
					title: entity.title || entity.name || entity.description,
					html: go.util.Format.dateTime(alert.triggerAt),
					iconCls: iconCls,
					buttonAlign: "right",
					listeners: {
						destroy: (panel) => {
							go.Db.store("Alert").destroy(alert.id);
						}
					},
					handler: () => {
						go.Entities.get(alert.entity).goto(alert.entityId);
						go.Notifier.hideNotifications();
					},
					buttons: [{
						text: t("Open"),
						handler: (btn) => {
							btn.findParentByType("panel").handler();
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

			}).catch((reason) => {
				console.warn("Alert for unknown entity", reason);
			})
		}
	})

	go.Alerts = new Alerts();

	GO.mainLayout.on('render', function () {
		go.Alerts.init();
	});


})();