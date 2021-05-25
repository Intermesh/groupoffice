(function(){

	GO.mainLayout.on('render', function () {

		go.Notifier.addStatusIcon('reminder', 'ic-notifications');

		go.Db.store("Alert").all().then(showAlerts);

		go.Db.store("Alert").on("changes", (store, added, changed, destroyed) => {
			destroyed.forEach((id) => {
				go.Notifier.removeById("core-alert-" + id);
			});

			for(let id in added) {
				showAlert(added[id]);
			}

			for(let id in changed) {
				showAlert(changed[id]);
			}
		});
	});

	showAlerts = function(alerts) {

		go.Notifier.toggleIcon('reminder', true);

		alerts.forEach((alert) => {
			showAlert(alert);
		});
	}

	showAlert = function(alert) {
		const now = new Date();

		if(new Date(alert.triggerAt) > now) {
			go.Notifier.removeById('core-alert-' + alert.id);
			return;
		}

		go.Db.store(alert.entity).single(alert.entityId).then((entity) => {

			const pnl = {
				id: 'core-alert-' + alert.id,
				title: entity.title,
				html: go.util.Format.dateTime(alert.triggerAt),
				iconCls: 'entity ' + alert.entity,
				buttonAlign: "right",
				buttons: [{
					text: t("Open"),
					handler: () => {
						go.Entities.get(alert.entity).goto(alert.entityId);
						go.Notifier.hideNotifications();
					}
				}, {
					text: t("Dismiss"),
					handler: () => {
						go.Db.store("Alert").destroy(alert.id);
					}
				}]
			};

			go.Notifier.msg(pnl);
		});
	}
})();