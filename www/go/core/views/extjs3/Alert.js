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
				fields: ['id', 'entity', 'entityId', 'data', 'tag', 'triggerAt', 'userId', 'title', 'body'],
				filters: {
					user: {userId: go.User.id}
				},
				baseParams: {limit: 50}
			});

			this.store.on("load", () => {
				this.onStoreLoad();
			});

			this.store.load();

			// re-evaluate alerts every 60s
			setInterval(() => {
				this.onStoreLoad();
			}, 60000)

		},


		onStoreLoad : function() {
			this.store.getRange().forEach((rec) => {
				this.show(rec.data);
			});
		},

		show : function(alert) {
			const now = new Date(), id = 'core-alert-' + alert.id;

			if(new Date(alert.triggerAt) > now) {
				go.Notifier.removeById(id);
				return;
			}

			if(go.Notifier.getById(id)) {
				return;
			}

			go.Db.store(alert.entity).single(alert.entityId).then((entity) => {

				const iconCls = go.Entities.getLinkIcon(alert.entity);

				const c = {
					statusIcon: 'reminder',
					itemId: id,
					title: alert.title,
					html: alert.body,
					iconCls: iconCls,
					buttonAlign: "right",
					listeners: {
						destroy: (panel) => {
							go.Db.store("Alert").destroy(alert.id);
						}
					},
					handler: () => {
						go.Entities.get(alert.entity).goto(alert.entityId);
					},
					buttons: [{
						text: t("Open"),
						handler: (btn) => {
							btn.findParentByType("panel").handler();
						}
					}, {
						text: t("Dismiss"),
						handler: (btn, e) => {
							//needed to prevent notification area closing
							e.stopEvent();
							btn.findParentByType("panel").destroy();
						}
					}]
				};


				if(!c.notificationBody) {
					c.notificationBody =  go.util.htmlToText(alert.body);
				//	console.warn(c.notificationBody);
				}

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