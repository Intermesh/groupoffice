import {modules} from "@go-core/Modules.js";
import {Main} from "./Main.js";
import {router} from "@go-core/Router.js";
import {client} from "@goui/jmap/Client.js";

modules.register(  {
	package: "community",
	name: "goui",
	async init () {

		client.on("authenticated", async () => {
			const session = await client.session;

			if(!session.capabilities["go:community:goui"]) {
				// User has no access to this module
				return;
			}

			let notes: Main;

			router.add(/^goui-notes\/(\d+)$/, (noteId) => {
				modules.openMainPanel("goui-notes");
				notes.showNote(parseInt(noteId));
			});

			modules.addMainPanel("goui-notes", "GOUI Notes", () => {

				//this will lazy load Notes when module panel is opened.
				notes = new Main();
				return notes;
			});
		});
	}
});