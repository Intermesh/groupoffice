import {
	avatar,
	column,
	comp,
	Config,
	createComponent,
	datasourcestore,
	DataSourceStore,
	datecolumn, h3,
	t,
	Table
} from "@intermesh/goui";
import {AclLevel, img, JmapDataSource, principalDS} from "@intermesh/groupoffice-core";
import {addressBookDS, Contact, contactDS} from "./Index";

export class ContactGrid extends Table<DataSourceStore<JmapDataSource<Contact>, Contact>> {
	//todo
	// group by and letter column
	// stable width on columns

	constructor() {
		const store = datasourcestore({
			dataSource: contactDS,
			queryParams: {
				limit: 20
			},
			filters: {
				permissionLevel: {permissionLevel: AclLevel.READ}
			},
			sort: [{property: "name", isAscending: true}],
			relations: {
				creator: {
					dataSource: principalDS,
					path: "createdBy"
				},
				modifier: {
					dataSource: principalDS,
					path: "modifiedBy"
				},
				organizations: {
					dataSource: contactDS,
					path: "organizationIds"
				},
				addressbook: {
					dataSource: addressBookDS,
					path: "addressBookId"
				}
			}
		});

		const columns = [
			column({
				id: "index",
				width: 48,
				renderer: (columnValue, record, td, table, storeIndex, column) => {
					if (table.store.sort[0] == undefined || table.store.sort[0].property !== "name" && table.store.sort[0].property !== "firstName" && table.store.sort[0].property !== "lastName")
						return "";

					const sortBy = record.isOrganization ? "name" : "firstName";

					if (!record[sortBy])
						return "";

					const lastRecord = storeIndex > 0 ? table.store.get(storeIndex - 1) : false;
					const lastSortBy = !lastRecord || !lastRecord.isOrganization ? "firstName" : "name";

					const deaccentChar = (c: string) => c[0].toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

					const char = deaccentChar(record[sortBy]);
					if (!lastRecord || !lastRecord[lastSortBy] || deaccentChar(lastRecord[lastSortBy]) !== char) {
						return h3({text: char});
					}

					return "";
				}
			}),
			column({
				id: "id",
				header: t("ID"),
				sortable: true,
				hidden: true,
				width: 60
			}),
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
								...(record.isOrganization ? {
									displayName: record.name,
									icon: "business"
								} : {displayName: record.name})
							}));
				}
			}),
			column({
				header: t("Name"),
				id: "name",
				resizable: true,
				sortable: true,
				htmlEncode: false,
				renderer: (columnValue, record) => {
					if (record.color) {
						return `<div style="color: #${record.color}">${columnValue}</div>`
					} else {
						return columnValue
					}
				}
			}),
			column({
				id: "gender",
				header: t("Gender"),
				width: 160,
				hidden: true,
				sortable: true,
				renderer: (columnValue) => {
					switch (columnValue) {
						case 'M':
							return t("Male");
						case 'F':
							return t("Female");
						case 'N':
							return t("Non-binary");
						case 'P':
							return t("Won't say");
						default:
							return "";
					}
				}
			}),
			column({
				id: "organizations",
				header: t("Organizations"),
				sortable: false,
				width: 300,
				renderer: (columnValue) => {
					return columnValue ? columnValue.column("name").join(", ") : "";
				}
			}),
			column({
				id: "addressbook",
				header: t("Address book"),
				sortable: false,
				renderer: (columnValue) => {
					if (columnValue)
						return columnValue.name;
				},
				width: 200,
				hidden: true
			}),
			datecolumn({
				id: "lastContactAt",
				header: t("Last contact at"),
				width: 160,
				sortable: true
			}),
			datecolumn({
				id: "createdAt",
				header: t("Created at"),
				sortable: true,
				width: 160,
				hidden: true
			}),
			column({
				id: "creator",
				header: t("Created by"),
				width: 160,
				hidden: true,
				renderer: (columnValue) => {
					if (columnValue)
						return columnValue.displayName;
				}
			}),
			column({
				id: "modifier",
				header: t("Modified by"),
				width: 160,
				hidden: true,
				renderer: (columnValue) => {
					if (columnValue)
						return columnValue.displayName;
				}
			}),
			column({
				id: "jobTitle",
				header: t("Job title"),
				width: 160,
				sortable: true,
				hidden: true
			}),
			column({
				id: "department",
				header: t("Department"),
				width: 160,
				sortable: true,
				hidden: true
			}),
			column({
				id: "registrationNumber",
				header: t("Registration number"),
				width: 160,
				sortable: true,
				hidden: true
			}),
			column({
				id: "debtorNumber",
				header: t("Debtor number"),
				width: 160,
				sortable: true,
				hidden: true
			}),
			column({
				id: "IBAN",
				header: t("IBAN"),
				width: 160,
				sortable: true,
				hidden: true
			}),
			column({
				id: "vatNo",
				header: t("VAT number"),
				width: 160,
				sortable: true,
				hidden: true
			}),
			column({
				id: "phoneNumbers",
				header: t("Phone numbers"),
				sortable: false,
				width: 300,
				hidden: true,
				renderer: (columnValue) => {
					return columnValue ? columnValue.column("number").join(", ") : "";
				}
			}),
			column({
				id: "emailAddresses",
				header: t("E-mail addresses"),
				sortable: false,
				width: 300,
				hidden: true,
				renderer: (columnValue) => {
					return columnValue ? columnValue.column("email").join(", ") : "";
				}
			}),
			column({
				id: "firstName",
				header: t("First name"),
				sortable: true,
				width: 160,
				hidden: true
			}),
			column({
				id: "middleName",
				header: t("Middle name"),
				sortable: true,
				width: 160,
				hidden: true
			}),
			column({
				id: "lastName",
				header: t("Last name"),
				sortable: true,
				width: 160,
				hidden: true
			}),
			column({
				id: "birthday",
				header: t("Birthday"),
				sortable: true,
				width: 160,
				hidden: true,
				renderer: (columnValue, record) => {
					if (!record.dates) {
						return "";
					}
					let bday = "";
					record.dates.forEach((date: { type: string, date: string }) => {
						if (date.type === "birthday") {
							bday = date.date;
						}
					});
					return bday;
				}
			}),
			column({
				id: "age",
				header: t("Age"),
				sortable: true,
				width: 160,
				hidden: true
			}),
			datecolumn({
				id: "actionAt",
				header: t("Action date"),
				sortable: true,
				width: 160,
				hidden: true
			}),
			column({
				id: "address",
				header: t("Address"),
				sortable: true,
				width: 300,
				hidden: true
			}),
			column({
				id: "zipCode",
				header: t("ZIP code"),
				sortable: true,
				width: 300,
				hidden: true
			}),
			column({
				id: "city",
				header: t("City"),
				sortable: true,
				width: 300,
				hidden: true
			}),
			column({
				id: "state",
				header: t("State"),
				sortable: true,
				width: 300,
				hidden: true
			}),
			column({
				id: "country",
				header: t("Country"),
				sortable: true,
				width: 300,
				hidden: true
			})
		];

		super(store, columns);
		this.headers = true;
		this.fitParent = true;
		this.scrollLoad = true;
	}
}

export const contactgrid = (config?: Config<ContactGrid>) => createComponent(new ContactGrid(), config);