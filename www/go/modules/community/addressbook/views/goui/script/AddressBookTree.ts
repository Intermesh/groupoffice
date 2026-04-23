import {
	btn,
	checkboxselectcolumn,
	column,
	Config,
	createComponent,
	hr,
	menu,
	t,
	Tree,
	treecolumn,
	TreeRecord
} from "@intermesh/goui";
import {addressBookDS, addressBookGroupDS} from "./Index.js";
import {AddressBookDialog} from "./AddressBookDialog.js";
import {AddressBookGroupDialog} from "./AddressBookGroupDialog.js";


export class AddressBookTree extends Tree {
	private filterText?: string;

	constructor() {
		super(
			async (record): Promise<TreeRecord[]> => {
				if (record) {
					return [];
				}

				const q = await addressBookDS.query({
					sort: [{property: "name", isAscending: true}],
					filter: {
						text: {
							text: this.filterText ?? ""
						}
					}
				});

				const getResponse = await addressBookDS.get(q.ids);

				return Promise.all(getResponse.list.map(async (r) => {
					const childGroups = await addressBookGroupDS.get(r.groups);

					childGroups.list.forEach((group) => {
						group.icon = "group";
						group.childIds = null;
					});

					return {
						id: r.id,
						name: r.name,
						children: childGroups.list.map(l => {
							return {...l, children: []};
						})
					};
				}));
			},
			[
				checkboxselectcolumn(),
				treecolumn({
					id: "name"
				}),
				column({
					id: "btn",
					width: 48,
					sticky: true,
					renderer: (columnValue, record, td, tree, storeIndex) => {
						return btn({
							icon: "more_vert",
							menu: menu({isDropdown: true},
								btn({
									icon: "edit",
									text: t("Edit"),
									handler: () => {
										const record = this.store.get(storeIndex)!;
										const id = record.id!;

										let dlg;

										if (record.addressBookId !== undefined) {
											dlg = new AddressBookGroupDialog();
										} else {
											dlg = new AddressBookDialog();
										}

										dlg.form.on("submit", () => {
											this.store.load();
										});

										dlg.load(id);
										dlg.show();
									}
								}),
								btn({
									icon: "delete",
									text: t("Delete"),
									handler: () => {
										const record = this.store.get(storeIndex)!;
										const id = record.id!;

										if (record.addressBookId !== undefined) {
											addressBookGroupDS.confirmDestroy([id]);
										} else {
											addressBookDS.confirmDestroy([id]);
										}

										this.store.load();
									}
								}),
								hr({
									hidden: record.addressBookId !== undefined
								}),
								btn({
									icon: "group",
									text: t("Add group"),
									hidden: record.addressBookId !== undefined,
									handler: async () => {
										const id = this.store.get(storeIndex)!.id!;

										const dlg = new AddressBookGroupDialog();

										dlg.form.value = {
											addressBookId: id
										}

										dlg.form.on("save", ({data}) => {
											this.store.load();
										});

										dlg.show();
									}
								})
							)
						})
					}
				})
			]
		);

		this.headers = false;
		this.draggable = false;
		this.dropOn = true;
	}

	public filter(text: string) {
		this.filterText = text;

		this.store.load();
	}
}

export const addressbooktree = (config?: Config<AddressBookTree>) => createComponent(new AddressBookTree(), config);
