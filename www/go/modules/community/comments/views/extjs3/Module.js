Ext.ns('go.modules.comments');

go.Modules.register('community', 'comments', {
	title: t("Comments", "comments"),
	entities: [{
		name: "Comment",
		relations: {
			creator: {store: "UserDisplay", fk: "createdBy"},
			modifier: {store: "UserDisplay", fk: "modifiedBy"},
			labels: {store: "CommentLabel", fk: "labels"}
		},
		links: [{
			iconCls: "entity ic-note purple",
			searchOnly: true,
			/**
			 * Return component for the detail view
			 *
			 * @returns {go.detail.Panel}
			 */
			linkDetail: function () {
				return new go.modules.community.comments.CommentLinkDetail();
			}
		}]
	}, 
	"CommentLabel"],
	initModule: function () {

		go.Router.add(/comment\/([0-9]+)/, async (commentId) => {
			const comment = await go.Db.store("Comment").single(commentId);
			console.warn(comment);

			const ent = go.Entities.get(comment.entity);

			if(!ent) {
				GO.errorDialog.show("Could not find entity " + comment.entity);
			}

			ent.goto(comment.entityId);
		});
		go.Alerts.on("beforeshow", function(alerts, alertConfig) {
			const alert = alertConfig.alert;
			if(alert.tag == "comment") {
				//replace panel promise
				alertConfig.panelPromise = alertConfig.panelPromise.then((panelCfg) => {
					return go.Db.store("User").single(alert.data.createdBy).then((creator) =>{
						if(!creator) {
							creator = {displayName: t("Unknown")};
						}
						panelCfg.html = go.util.Format.dateTime(alert.triggerAt) + ": " + t("A comment was made by {creator}").replace("{creator}", creator.displayName) + "<br /><br /><i>"+alert.data.excerpt+"</i>";
						panelCfg.notificationBody = go.util.Format.dateTime(alert.triggerAt) + ": " + t("A comment was made by {creator}").replace("{creator}", creator.displayName) + "\n\n"+alert.data.excerpt;
						return panelCfg;
					});
				});
			}
		});
	}
});

