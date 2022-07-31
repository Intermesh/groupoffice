import {modules, ModuleConfig} from "@go-core/Modules.js";
import {Notes} from "./Notes.js";
import {router} from "@go-core/Router.js";

modules.register(  {
	package: "community",
	name: "goui",
	init () {

		let notes: Notes;

		router.add(/^goui-notes\/(\d+)$/, (noteId) => {
			modules.openMainPanel("goui-notes");
			notes.showNote(parseInt(noteId));
		});

		modules.addMainPanel("goui-notes", "GOUI Notes", () => {

			//this will lazy load Notes when module panel is opened.
			notes = new Notes();
			return notes;
		});
	}
});