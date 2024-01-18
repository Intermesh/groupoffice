Ext.ns('go.modules.comments');

go.Modules.register('community', 'comments', {
	title: t("Comments", "comments"),
	entities: [{
		name: "Comment",
		relations: {
			creator: {store: "Principal", fk: "createdBy"},
			modifier: {store: "Principal", fk: "modifiedBy"},
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
				alertConfig.panelPromise = alertConfig.panelPromise.then(async (panelCfg) => {

					let creator;
					try {
						creator = await go.Db.store("Principal").single(alert.data.createdBy);
					} catch (e) {
						creator = {name: t("Unknown user")};
					}

					panelCfg.html = go.util.Format.dateTime(alert.triggerAt) + ": " + t("A comment was made by {creator}").replace("{creator}", creator.name) + "<br /><br /><i>"+alert.data.excerpt+"</i>";
					panelCfg.notificationBody = go.util.Format.dateTime(alert.triggerAt) + ": " + t("A comment was made by {creator}").replace("{creator}", creator.name) + "\n\n"+alert.data.excerpt;
					return panelCfg;

				});
			}
		});
	}
});

