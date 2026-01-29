import {avatar, column, comp, DataSourceStore, datasourcestore, t, Table} from "@intermesh/goui";
import {img, JmapDataSource, Principal, principalDS} from "@intermesh/groupoffice-core";
import {AddressBook, addressBookDS} from "@intermesh/community/addressbook";

export class AddressBookTable extends Table<DataSourceStore<JmapDataSource<AddressBook>, AddressBook>> {
	constructor() {
		const store = datasourcestore({
			dataSource: addressBookDS,
			queryParams: {
				limit: 20
			},
			sort: [{property: "name", isAscending: true}]
			// filters: {
			// 	isEmployee: {
			// 		isEmployee: true
			// 	}
			// }
		});

		const columns = [
			column({
				header: t("Name"),
				id: "name",
				resizable: true,
				width: 312,
				sortable: true,
				htmlEncode: false,
				renderer: (value, record) => {
					return `<div>${value.htmlEncode()}</div>`;
				}
			})
		];

		super(store, columns);
		this.headers = false;
		this.fitParent = true;
	}
}

