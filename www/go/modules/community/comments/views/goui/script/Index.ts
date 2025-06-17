import {client, modules} from "@intermesh/groupoffice-core";

export * from "./CommentsPanel.js";

modules.register({
	package: "community",
	name: "comments",
	async init() {
		client.on("authenticated", (client, session) => {
			if (!session.capabilities["go:community:comments"]) {
				// User does not have access to this module
				return;
			}
		});
	}
})