import {modules, ModuleInit} from "@go-core/Modules.js";
import {Notes} from "./Notes.js";

modules.register({
	package:"community",
	name: "goui",
	init() {
		this.addMainPanel("Notes", function(this:ModuleInit) {
			return new Notes();
		});
	}
});