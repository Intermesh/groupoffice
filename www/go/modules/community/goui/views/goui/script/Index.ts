import {modules, ModuleConfig} from "@go-core/Modules.js";
import {Notes} from "./Notes.js";



modules.register(  new class {
	package = "community";
	name = "goui";

	routes = {
		"goui-notes/(\\d+)": (noteId: string) => {
			modules.openMainPanel("goui-notes");
			this.notes!.showNote(parseInt(noteId));
		}
	}
	private notes?: Notes;

	constructor() {
		modules.addMainPanel("goui-notes", "GOUI Notes", () => {

			//this will lazy load Notes when module panel is opened.
			this.notes = new Notes();
			return this.notes;
		});
	}
} );