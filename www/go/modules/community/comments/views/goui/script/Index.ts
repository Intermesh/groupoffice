import {client, JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {t} from "@intermesh/goui";
import {CommentDetail} from "./CommentDetail";
import {CommentsPanel} from "./CommentsPanel";

export * from "./CommentsPanel.js";

modules.register({
	package: "community",
	name: "comments",
	async init() {

		// make available in legacy extjs modules

		GO.comments = {
			CommentsPanel
		}

		client.on("authenticated", ({session}) => {
			if (!session.capabilities["go:community:comments"]) {
				// User does not have access to this module
				return;
			}
		});

		go.Alerts.on("beforeshow", function (alerts: any, alertConfig: any) {
			const alert = alertConfig.alert;
			if (alert.tag == "mention") {
				//replace panel promise
				alertConfig.panelPromise = alertConfig.panelPromise.then(async (panelCfg: any) => {

					let creator;
					try {
						creator = await go.Db.store("Principal").single(alert.createdBy);
					} catch (e) {

					}

					if (!creator) {
						creator = {name: t("Unknown user")};
					}

					panelCfg.html = go.util.Format.dateTime(alert.triggerAt) + ": " + t("You were mentioned in a comment by {creator}.", "comments", "community").replace("{creator}", creator.name) + "<br /><br /><i>" + alert.data.excerpt + "</i>";
					panelCfg.notificationBody = go.util.Format.dateTime(alert.triggerAt) + ": " + t("You were mentioned in a comment by  {creator}.", "comments", "community").replace("{creator}", creator.name) + "\n\n" + alert.data.excerpt;
					return panelCfg;

				});
			}
		});
	},
	entities: [{
		name: "Comment",
		links: [{
			iconCls: "entity ic-note purple",
			searchOnly: true,

			/**
			 * Return component for the detail view
			 *
			 */
			linkDetail: function () {
				return new CommentDetail();
			}
		}]
	},
		"CommentLabel"
	]
});

export const commentDS = new JmapDataSource("Comment");
export const commentLabelDS = new JmapDataSource("CommentLabel");