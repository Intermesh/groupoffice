import {modules, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {client} from "@intermesh/goui";

modules.register(  {
	package: "community",
	name: "goui",
	async init () {

		client.on("authenticated", (client, session) => {

			if(!session.capabilities["go:community:goui"]) {
				// User has no access to this module
				return;
			}

			let notes: Main = new Main();

			router.add(/^goui-notes\/(\d+)$/, (noteId) => {
				modules.openMainPanel("goui-notes");
				notes.showNote(parseInt(noteId));
			});

			modules.addMainPanel("community", "goui","goui-notes", "GOUI Notes", () => {

				//this will lazy load Notes when module panel is opened.
				return notes;
			});
		});
	}
});