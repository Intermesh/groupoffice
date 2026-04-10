import {
	checkboxselectcolumn,
	column,
	Config,
	createComponent,
	DataSourceStore,
	datasourcestore,
	t,
	Table
} from "@intermesh/goui";
import {AclLevel, JmapDataSource} from "@intermesh/groupoffice-core";
import {AddressBook, addressBookDS} from "./Index";

export class AddressBookGrid extends Table<DataSourceStore<JmapDataSource<AddressBook>, AddressBook>> {
	constructor() {
		const store = datasourcestore({
			dataSource: addressBookDS,
			queryParams: {
				limit: 20
			},
			sort: [{property: "name", isAscending: true}],
			filters: {
				permissionLevel: {permissionLevel: AclLevel.READ}
			}
		});

		const columns = [
			checkboxselectcolumn({
				id: "id"
			}),
			column({
				header: t("Name"),
				id: "name",
				resizable: true,
				width: 312,
				sortable: true
			})
		];

		super(store, columns);
		this.headers = false;
		this.fitParent = true;
	}
}

export const addressbookgrid = (config?: Config<AddressBookGrid>) => createComponent(new AddressBookGrid(), config);
