import {avatar, column, comp, datasourcestore, DataSourceStore, t, Table} from "@intermesh/goui";
import {img, JmapDataSource} from "@intermesh/groupoffice-core";
import {Contact, contactDS} from "@intermesh/community/addressbook";

export class ContactTable extends Table<DataSourceStore<JmapDataSource<Contact>, Contact>> {
	constructor() {
		const store = datasourcestore({
			dataSource: contactDS,
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
				// width: 312,
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
