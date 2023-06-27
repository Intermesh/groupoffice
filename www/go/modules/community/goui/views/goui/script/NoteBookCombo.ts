import {NoteBookGrid} from "./NoteBookGrid.js";
import {AutocompleteField, Config, createComponent, listStoreType, storeRecordType, t} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";


export class NoteBookCombo extends AutocompleteField<NoteBookGrid> {

	pickerRecordToValue(field: this, record: storeRecordType<listStoreType<NoteBookGrid>>): string {
		return record.id;
	}

	async valueToTextField(field: this, value: string): Promise<string> {
		const entityStore = jmapds("NoteBook");
		const nb = await entityStore.single(value);

		return nb ? nb.name : "?";
	}

	constructor() {
		super(new NoteBookGrid());
		this.list.headers = false;
		this.list.fitParent = true;

		this.label = t("Notebook");
		this.name = "noteBookId";

		this.on("autocomplete", async (field, input) => {
			this.list.store.queryParams = {filter: {text: input}};
			await this.list.store.load();
		});
	}
}

export const notebookcombo = (config?: Config<NoteBookCombo>) => createComponent(new NoteBookCombo(), config);