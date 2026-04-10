import {
	AutocompleteField,
	avatar,
	column,
	comp,
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
import {contactDS} from "./Index"
import {img} from "@intermesh/groupoffice-core";

export class ContactCombo extends AutocompleteField<Table<DataSourceStore>> {
	public isOrganization: boolean | undefined;

	pickerRecordToValue(field: this, record: storeRecordType<listStoreType<Table<DataSourceStore>>>) {
		return record.id;
	}

	async valueToTextField(field: this, value: string): Promise<string> {
		const nb = await contactDS.single(value);
		return nb ? nb.name! : "?";
	}

	constructor(isOrganization: boolean | null | undefined) {
		super(table({
			store: datasourcestore({
				dataSource: contactDS,
				queryParams: {
					limit: 20
				},
				sort: [{property: "name", isAscending: true}]
			}),
			columns: [
				column({
					id: 'photoBlobId',
					width: 75,
					sortable: false,
					renderer: (value, record) => {
						return comp({cls: "meta"},
							record?.photoBlobId ?
								img({
									cls: "goui-avatar",
									blobId: value,
									title: record.name
								}) :

								avatar({
									displayName: record.name
								}));
					}
				}),
				column({
					header: t("Name"),
					id: "name",
					resizable: true,
					sortable: true,
					htmlEncode: false
				})
			]
		}));

		if (!this.label) {
			this.label = isOrganization ? t("Organization", "community", "addressbook") : t("Contact", "community", "addressbook");
		}
		this.name = this.name || "coantactId";

		if (typeof isOrganization === "boolean") {
			this.list.store.setFilter("isOrganization", {isOrganization: isOrganization});
		}

		this.on("autocomplete", async ({input}) => {
			this.list.store.setFilter("search", {text: input});
			await this.list.store.load();
		});

	}
}

export const contactcombo = (config?: Config<ContactCombo>) => createComponent(new ContactCombo(config?.isOrganization), config);