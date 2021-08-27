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
			if(alert.data && alert.data.type == "comment") {


				//replace panel promise
				alertConfig.panelPromise = alertConfig.panelPromise.then((panelCfg) => {
					return go.Db.store("User").single(alert.data.createdBy).then((creator) =>{
						panelCfg.html += ": " + t("A comment was made by {creator}").replace("{creator}", creator.displayName) + "<br /><br /><i>"+alert.data.excerpt+"</i>";
						panelCfg.notificationBody += ": " + t("A comment was made by {creator}").replace("{creator}", creator.displayName) + "\n\n"+alert.data.excerpt;
						return panelCfg;
					});

				});
			}
		});
	}
});

