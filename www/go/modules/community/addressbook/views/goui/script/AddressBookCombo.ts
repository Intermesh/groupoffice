import {
	AutocompleteField,
	column,
	Config,
	createComponent,
	DataSourceStore,
	datasourcestore,
	listStoreType,
	storeRecordType,
	t,
	Table,
	table
} from "@intermesh/goui";
import {addressBookDS} from "./Index";

export class AddressBookCombo extends AutocompleteField<Table<DataSourceStore>> {

	pickerRecordToValue(field: this, record: storeRecordType<listStoreType<Table<DataSourceStore>>>) {
		return record.id;
	}

	async valueToTextField(field: this, value: string): Promise<string> {
		const nb = await addressBookDS.single(value);

		return nb ? nb.name : "?";
	}

	constructor() {
		super(table({
			headers: false,
			fitParent: true,
			store: datasourcestore({
				dataSource: addressBookDS,
				queryParams: {
					limit: 20
				},
				sort: [{property: "name", isAscending: true}]
			}),
			columns: [
				column({
					header: t("Name"),
					id: "name",
					resizable: true,
					width: 312,
					sortable: true,
					htmlEncode: true
				})
			]
		}));

		this.label = t("Addressbook", "community", "addressbook");
		this.name = "addressBookId";

		this.on("autocomplete", async ({input}) => {
			this.list.store.setFilter("search", {text: input});
			await this.list.store.load();
		});

	}
}

export const addressbookcombo = (config?: Config<AddressBookCombo>) => createComponent(new AddressBookCombo(), config);