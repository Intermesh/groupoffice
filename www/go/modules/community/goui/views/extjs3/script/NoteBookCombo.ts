import {AutocompleteField} from "../../../../../../../views/Extjs3/goui/script/component/form/AutocompleteField.js";
import {NoteBookGrid} from "./NoteBookGrid.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {EntityStore} from "../../../../../../../views/Extjs3/goui/script/api/EntityStore.js";
import {client} from "../../../../../../../views/Extjs3/goui/script/api/Client.js";
import {Config} from "../../../../../../../views/Extjs3/goui/script/component/Component.js";

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
				if(this.input?.value == this.value) {
					const entityStore = client.store("NoteBook");
					const nb = await entityStore.single(this.value);

					this.setInputValue(nb.name);
				}
			}

			if(this.rendered) {
				await loadText();
			} else
			{
				this.on("render", () => {
					loadText();
				}, {once: true})
			}
		});
	}
}

export const notebookcombo = (config?: Config<NoteBookCombo>) => Object.assign(new NoteBookCombo(), config);