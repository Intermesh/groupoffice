import {ComboBox, Config, createComponent, t} from "@intermesh/goui";
import {noteBookDS} from "./Index";

export class NoteBookCombo extends ComboBox {
	constructor() {
		super(noteBookDS, "name", "id");
		this.label = t("Notebook");
		this.name = "noteBookId";
	}
}

export const notebookcombo = (config?: Config<NoteBookCombo>) => createComponent(new NoteBookCombo(), config);