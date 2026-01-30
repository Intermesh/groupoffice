import {AutocompleteField, Config, createComponent, listStoreType, storeRecordType, t} from "@intermesh/goui";
import {AddressBookTable} from "./AddressBookTable.js";
import {principalDS} from "@intermesh/groupoffice-core";
import {addressBookDS} from "@intermesh/community/addressbook";

export class AddressBookCombo extends AutocompleteField<AddressBookTable> {

	pickerRecordToValue(field: this, record: storeRecordType<listStoreType<AddressBookTable>>) {
		return record.id;
	}

	async valueToTextField(field: this, value: string): Promise<string> {
		const entityStore = addressBookDS;
		const nb = await entityStore.single(value);

		return nb ? nb.name : "?";
	}

	constructor() {
		super(new AddressBookTable());

		this.label = t("Addressbook", "community", "addressbook");
		this.name = "addressBookId";

		this.on("autocomplete", async ( {input}) => {
			this.list.store.setFilter("search", {text: input});
			await this.list.store.load();
		});

	}

}

export const addressbookcombo = (config?: Config<AddressBookCombo>) => createComponent(new AddressBookCombo(), config);