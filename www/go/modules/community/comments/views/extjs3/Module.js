Ext.ns('go.modules.comments');

go.Modules.register('community', 'comments', {
	title: t("Comments", "comments"),
	entities: [{
		name: "Comment",
		relations: {
			creator: {store: "User", fk: "createdBy"},
			modifier: {store: "User", fk: "modifiedBy"},
			labels: {store: "CommentLabel", fk: "labels"}
		}
	}, 
	"CommentLabel"],
	initModule: function () {
		go.Alerts.on("beforeshow", function(alerts, alertConfig) {
			const alert = alertConfig.alert;
			if(alert.data && alert.data.type == "comment") {


				//replace panel promise
				alertConfig.panelPromise = alertConfig.panelPromise.then((panelCfg) => {
					return go.Db.store("User").single(alert.data.createdBy).then((creator) =>{
						panelCfg.html += ": " + t("A comment was made by {creator}").replace("{creator}", creator.displayName);
						panelCfg.notificationBody = panelCfg.html;
						return panelCfg;
					});

				});
			}
		});
	}
});

