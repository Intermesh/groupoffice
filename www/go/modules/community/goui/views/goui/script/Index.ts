import {client, modules, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";

modules.register(  {
	package: "community",
	name: "goui",
	async init () {

		let notes: Main;

		client.on("authenticated", (client, session) => {

			if(!session.capabilities["go:community:goui"]) {
				// User has no access to this module
				return;
			}

			router.add(/^goui-notes\/(\d+)$/, (noteId) => {
				modules.openMainPanel("goui-notes");
				notes.showNote(noteId);
			});

			modules.addMainPanel("community", "goui","goui-notes", "GOUI Notes", () => {
				notes = new Main();
				//this will lazy load Notes when module panel is opened.
				return notes;
			});
		});
	}
});