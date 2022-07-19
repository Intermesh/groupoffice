
import {NoteBookGrid} from "./NoteBookGrid.js";
import {AutocompleteField} from "@goui/component/form/AutocompleteField.js";
import {t} from "@goui/Translate.js";
import {Config} from "@goui/component/Component.js";
import {client} from "@goui/api/Client.js";

export class NoteBookCombo extends AutocompleteField<NoteBookGrid> {
	constructor() {
		super(new NoteBookGrid());
		this.table.headers = false;

		this.label = t("Notebook");
		this.name = "noteBookId";
		this.valueProperty = "id";

		this.on("autocomplete", async (field, input) => {
			this.table.store.queryParams = {filter: {text: input}};
			await this.table.store.load();
		});

		this.on("setvalue", async (field, newValue, oldValue) => {

			const loadText = async () => {
				// record not available in store. Load it.
				if (this.input?.value == this.value) {
					const entityStore = client.store("NoteBook");
					const nb = await entityStore.single(this.value);

					this.setInputValue(nb.name);
				}
			}

			if (this.rendered) {
				await loadText();
			} else {
				this.on("render", () => {
					loadText();
				}, {once: true})
			}
		});
	}
}

export const notebookcombo = (config?: Config<NoteBookCombo>) => Object.assign(new NoteBookCombo(), config);