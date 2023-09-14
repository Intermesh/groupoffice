(function(){

	const Alerts = Ext.extend(Ext.util.Observable, {

		constructor : function() {

			this.supr().constructor.call(this);

			this.addEvents({
				"beforeshow" : true
			});
			go.Notifier.addStatusIcon('reminder', 'ic-notifications');


		},

		init : async function() {

			this.store = new go.data.Store({
				entityStore: "Alert",
				fields: ['id', 'entity', 'entityId', 'data', 'tag', 'triggerAt', 'userId', 'title', 'body'],
				filters: {
					user: {userId: go.User.id}
				},
				sortInfo: {
					field: "triggerAt",
					direction: "DESC"
				},
				baseParams: {limit: 50}
			});

			this.store.on("load", () => {
				this.onStoreLoad();
			});

			this.store.on("remove", (store, record) => {
				const id = 'core-alert-' + record.data.id

				const alert = go.Notifier.getById(id);
				if(alert) {
					// replaced is a bad name here. It won't attempt to remove the alert on
					// the server when set.
					alert.replaced = true;
					alert.destroy();
					go.Notifier.updateStatusIcons();
				}
			});

			await go.Db.store("Alert").getUpdates();
			await this.store.load();

			// re-evaluate alerts every 60s
			setInterval(() => {
				this.onStoreLoad();
			}, 60000)

		},


		onStoreLoad : function() {
			this.store.getRange().forEach((rec) => {
				this.show(rec.data);
			});

			//remove alerts that are no longer in the store
			go.Notifier.getAll().forEach((alert) => {
				if(alert.itemId.substring(0,11) == 'core-alert-') {
					const alertId = alert.itemId.substring(11);
					if(!this.store.getById(alertId)) {
						alert.replaced = true;
						alert.destroy();
					}
				}
			});
		},

		show : function(alert) {
			const now = new Date(), triggerDate = new Date (alert.triggerAt), id = 'core-alert-' + alert.id;

			if(triggerDate > now) return;

			go.Db.store(alert.entity).single(alert.entityId).then((entity) => {

				const iconCls = go.Entities.getLinkIcon(alert.entity);

				const c = {
					statusIcon: 'reminder',
					itemId: id,
					iconCls: iconCls,
					buttonAlign: "right",
					listeners: {
						destroy: (panel) => {
							if(!panel.replaced)
								go.Db.store("Alert").destroy(alert.id);
						}
					},
					handler: () => {
						go.Entities.get(alert.entity).goto(alert.entityId);
					},
					// buttons: [{
					// 	text: t("Open"),
					// 	handler: (btn) => {
					// 		go.Notifier.hideNotifications();
					// 		btn.findParentByType("panel").handler();
					// 	}
					// }, {
					// 	text: t("Dismiss"),
					// 	handler: (btn, e) => {
					// 		//needed to prevent notification area closing
					// 		e.stopEvent();
					// 		btn.findParentByType("panel").destroy();
					// 	}
					// }]
				};

				const alertConfig = {alert: alert, entity: entity, panelPromise: Promise.resolve(c)};

				//Modules can use this to cancel or modify the alert
				if(this.fireEvent('beforeshow', this, alertConfig) === false) {
					return;
				}

				alertConfig.panelPromise.then((panelCfg) => {

					if(!panelCfg.title) {
						//default title
						panelCfg.title = alert.data && alert.data.title ? alert.data.title : entity.name || entity.title || entity.description || alert.entity;
					}


					if(!panelCfg.items && !panelCfg.html) {

						//default alert body
						let body = go.util.Format.dateTime(alert.triggerAt);

						if(alert.data.body) {
							body = alert.data.body;
						}

						panelCfg.html = body;


						if(alert.data && "progress" in alert.data) {

							if(!panelCfg.items) {
								panelCfg.items = [
									{
										xtype: "box",
										html:panelCfg.html
									}
								];

								delete panelCfg.html;
							}
							panelCfg.items.push(new Ext.ProgressBar({
								text: t("Progress") + " " + alert.data.progress + "%",
								value: alert.data.progress / 100
							}))
						}
					}

					if(!("notificationBody" in c)) {
						c.notificationBody =  go.util.Format.dateTime(alert.triggerAt);

						if(alert.data) {
							c.notificationBody += ": " + JSON.stringify(alert.data, undefined, 1);
						}
					}

					go.Notifier.msg(panelCfg);
				}).catch(reason => {
					console.warn("Failed to process alert: ", reason, alert);
				});

			}).catch((reason) => {
				console.warn("Alert for unknown entity", reason, alert);
			})
		}
	})

	go.Alerts = new Alerts();

	GO.mainLayout.on('render', function () {
		go.Alerts.init();

		// go.Notifier.msg({
		// 	title: "Test 1",
		// 	description: "test desc 1",
		// 	handler: () => {
		// 		alert("test 1");
		// 	},
		// 	buttons: [{
		// 		iconCls: 'ic-delete',
		// 		text: t("Dismiss"),
		// 		handler: (btn, e) => {
		// 			//needed to stop notification area from closing
		// 			e.stopEvent();
		// 			go.Notifier.removeById("test1");
		// 		},
		// 		scope: this
		// 	}]
		// 	}, "test1");
		// go.Notifier.msg({title: "Test 2", description: "test desc 2", handler: () => {
		// 		alert("test 2");
		// 	}}, "test2");

	});


})();