export * from "./CommentsPanel.js";

import {client, entities, JmapDataSource, main, modules, } from "@intermesh/groupoffice-core";
import {t, Window,router} from "@intermesh/goui";
import {CommentDetail} from "./CommentDetail";
import {CommentsPanel} from "./CommentsPanel";
import {CommentEditor} from "./CommentEditor";



modules.register({
	package: "community",
	name: "comments",
	async init() {

		// make available in legacy extjs modules
		GO.comments = {
			CommentsPanel,
			CommentEditor
		}

		client.on("authenticated", ({session}) => {
			if (!session.capabilities["go:community:comments"]) {
				// User does not have access to this module
				return;
			}
		});

		// route to item where comment was made. Used for links and search results
		router.add(/comment\/([0-9]+)/, async (commentId) => {
			const comment = await commentDS.single(commentId);
			const ent = entities.get(comment.entity);

			if(!ent) {
				void Window.error("Could not find entity " + comment.entity);
			}

			ent!.goto(comment.entityId);
		});

		main.notifier.regRenderer("Comment", (alert) => {
			if (alert.tag == "mention") {
				const creator = alert.created.name || t("Unknown user");

				return {
					title: alert.title,
					icon: alert.icon,
					category: 'event',
					text: go.util.Format.dateTime(alert.triggerAt)+": " +
						t("You were mentioned in a comment by {creator}.", "comments", "community")
							.replace("{creator}", creator.name) +
						"<br><br><i>"+alert.data.excerpt+"</i>"
				}
			}
		});
	},
	entities: [{
		name: "Comment",
		links: [{
			iconCls: "entity ic-note purple",
			searchOnly: true,
			linkDetail() {
				return new CommentDetail();
			}
		}]
	},
		"CommentLabel"
	]
});

export const commentDS = new JmapDataSource("Comment");
export const commentLabelDS = new JmapDataSource("CommentLabel");