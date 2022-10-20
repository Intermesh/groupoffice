Ext.ns('go.modules.comments');

go.Modules.register('community', 'comments', {
	title: t("Comments", "comments"),
	entities: [{
		name: "Comment",
		relations: {
			creator: {store: "UserDisplay", fk: "createdBy"},
			modifier: {store: "UserDisplay", fk: "modifiedBy"},
			labels: {store: "CommentLabel", fk: "labels"}
		}
	}, 
	"CommentLabel"],
	initModule: function () {
		go.Alerts.on("beforeshow", function(alerts, alertConfig) {
			const alert = alertConfig.alert;
			if(alert.tag == "comment") {
				//replace panel promise
				alertConfig.panelPromise = alertConfig.panelPromise.then((panelCfg) => {
					return go.Db.store("User").single(alert.data.createdBy).then((creator) =>{
						panelCfg.html = go.util.Format.dateTime(alert.triggerAt) + ": " + t("A comment was made by {creator}").replace("{creator}", creator.displayName) + "<br /><br /><i>"+alert.data.excerpt+"</i>";
						panelCfg.notificationBody = go.util.Format.dateTime(alert.triggerAt) + ": " + t("A comment was made by {creator}").replace("{creator}", creator.displayName) + "\n\n"+alert.data.excerpt;
						return panelCfg;
					});
				});
			}
		});
	}
});

