import {modules, ModuleConfig} from "@go-core/Modules.js";
import {Notes} from "./Notes.js";
import {router} from "@go-core/Router.js";


type NoteConfig = ModuleConfig & {notes?:Notes}
modules.register(  {
	package: "community",
	name: "goui",
	init () {

		router.add(/^goui-notes\/(\d+)$/, (noteId) => {
			modules.openMainPanel("goui-notes");
			this.notes.showNote(parseInt(noteId));
		});

		modules.addMainPanel("goui-notes", "GOUI Notes", () => {

			//this will lazy load Notes when module panel is opened.
			this.notes = new Notes();
			return this.notes;
		});
	}
} as NoteConfig);