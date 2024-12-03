import {client, modules, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";

modules.register({
	package: "community",
	name: "notes",
	async init() {

		let notes: Main;

		client.on("authenticated", (client, session) => {
			if (!session.capabilities["go:community:notes"]) {
				// User has no access to this module
				return;
			}

			router.add(/^notes\/(\d+)$/, (noteId) => {
				modules.openMainPanel("notes");
				notes.showNote(noteId);
			});

			modules.addMainPanel("community", "notes", "notes", "Notes", () => {
				notes = new Main();
				return notes;
			});

		});
	}
});