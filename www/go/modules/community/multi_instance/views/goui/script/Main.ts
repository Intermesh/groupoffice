import {
	btn,
	checkbox,
	column,
	comp,
	Component,
	datasourcestore,
	DataSourceStore,
	datecolumn,
	Format,
	menu,
	mstbar,
	searchbtn,
	t,
	Table,
	table,
	tbar,
	Window
} from "@intermesh/goui";
import {client} from "@intermesh/groupoffice-core";
import {instanceDS} from "./Index.js";
import {InstanceDialog} from "./InstanceDialog.js";

export class Main extends Component {
	private readonly table: Table<DataSourceStore>;

	constructor() {
		super();

		this.cls = "vbox fit";

		const store = datasourcestore({
			dataSource: instanceDS
		});

		store.setFilter("enabled", {enabled: true});

		this.table = table({
			store: store,
			fit: true,
			cls: "bg-lowest",
			stateId: "multi_instance-grid",
			scrollLoad: true,
			rowSelectionConfig: {
				multiSelect: true
			},
			listeners: {
				rowdblclick: ({storeIndex}) => {
					this.edit(this.table.store.get(storeIndex)!.id);
				}
			},
			columns: [
				column({
					id: "id",
					header: "ID",
					width: 40,
					hidden: true,
					sortable: true
				}),
				column({
					id: "hostname",
					header: t("Hostname"),
					width: 200,
					sortable: true,
					resizable: true,
					renderer: (v, record, td) => {
						if (!record.enabled) {
							td.classList.add("deactivated");
						}
						return v;
					}
				}),
				column({
					id: "isTrial",
					header: t("Trial"),
					width: 60,
					sortable: true,
					resizable: true,
					renderer: (v) => v ? comp({html: '<i class="icon">check</i>'}) : ""
				}),
				datecolumn({
					id: "createdAt",
					header: t("Created at"),
					width: 160,
					sortable: true,
					resizable: true
				}),
				datecolumn({
					id: "lastLogin",
					header: t("Last login"),
					width: 160,
					sortable: true,
					resizable: true
				}),
				column({
					id: "userCount",
					header: t("User count"),
					width: 100,
					sortable: true,
					resizable: true,
					align: "right"
				}),
				column({
					id: "usersMax",
					header: t("Maximum users"),
					width: 120,
					sortable: true,
					resizable: true,
					align: "right"
				}),
				column({
					id: "loginCount",
					header: t("Login count"),
					width: 100,
					sortable: true,
					resizable: true,
					align: "right"
				}),
				column({
					id: "adminDisplayName",
					header: t("Admin name"),
					width: 160,
					sortable: true,
					resizable: true
				}),
				column({
					id: "adminEmail",
					header: t("Admin E-mail"),
					width: 200,
					sortable: true,
					resizable: true
				}),
				column({
					id: "storageQuota",
					header: t("Storage quota"),
					width: 120,
					sortable: true,
					resizable: true,
					align: "right",
					renderer: (v) => Format.fileSize(v)
				}),
				column({
					id: "storageUsage",
					header: t("Storage usage"),
					width: 120,
					sortable: true,
					resizable: true,
					align: "right",
					renderer: (v) => Format.fileSize(v)
				}),
				column({
					id: "version",
					header: t("Version"),
					width: 100,
					sortable: true,
					resizable: true
				}),
				column({
					sticky: true,
					width: 32,
					id: "btn",
					renderer: (columnValue: any, record) => {
						return btn({
							icon: "more_vert",
							menu: menu({},
								btn({
									icon: "lock_open",
									text: t("Login as administrator"),
									handler: async () => {
										await this.loginAs(record);
									}
								}),
								"-",
								btn({
									icon: "block",
									text: record.enabled ? t("Deactivate instance") : t("Activate instance"),
									handler: async () => {
										try {
											this.mask();
											await instanceDS.update(record.id, {enabled: !record.enabled});
										} catch (e) {
											void Window.error(e);
										} finally {
											this.unmask();
										}
									}
								}),
								btn({
									icon: "edit",
									text: t("Edit"),
									handler: () => {
										this.edit(record.id);
									}
								}),
								btn({
									icon: "delete",
									text: t("Delete"),
									handler: async () => {
										void instanceDS.confirmDestroy([record.id]);
									}
								})
							)
						})
					}
				})
			]
		});

		this.items.add(
			tbar({
					cls: "border-bottom bg-mid"
				},
				checkbox({
					type: "button",
					label: `<i class="icon">block</i> ${t("Show disabled")}`,
					listeners: {
						change: ({newValue}) => {
							this.table.store.setFilter("enabled", newValue ? {} : {enabled: true});
							void this.table.store.load();
						}
					}
				}),
				checkbox({
					type: "button",
					label: `<i class="icon">star</i> ${t("Show trials")}`,
					value: true,
					listeners: {
						change: ({newValue}) => {
							this.table.store.setFilter("isTrial", newValue ? {} : {isTrial: false});
							void this.table.store.load();
						}
					}
				}),
				"->",
				btn({
					icon: "add",
					title: t("Add"),
					handler: () => {
						const dlg = new InstanceDialog();

						dlg.show();
					}
				}),
				searchbtn({
					listeners: {
						input: ({text}) => {
							this.table.store.setFilter("text", {text: text});
							void this.table.store.load();
						}
					}
				}),
				btn({
					icon: "more_vert",
					menu: menu({},
						btn({
							icon: "download",
							text: t("Download site config"),
							handler: () => {
								window.open(client.downloadUrl("community/multi_instance/siteConfig"));
							}
						})
					)
				}),
				mstbar({
						table: this.table
					},
					"->",
					btn({
						icon: "email",
						text: t("E-mail selected"),
						handler: () => {
							let str = "";
							this.table.rowSelection!.getSelected().forEach(row => {
								const r = row.record;
								if (r.adminEmail && str.indexOf(r.adminEmail) == -1) {
									str += '"' + r.adminDisplayName + '" <' + r.adminEmail + '>, ';
								}
							});

							void Window.alert(str.replace(/</g, "&lt;").replace(/>/g, "&gt;"), t("E-mail addresses"));
						}
					}),
					btn({
						icon: "delete",
						handler: async (btn) => {
							const ids = this.table.rowSelection!.getSelected().map(row => row.record.id);

							const result = await instanceDS.confirmDestroy(ids);

							if (result != false) {
								btn.parent!.hide();
							}
						}
					})
				)
			),

			comp({flex: 1, cls: "scroll fit bg-lowest"},
				this.table
			)
		);

		this.on("render", () => {
			void this.table.store.load();
		});
	}

	private edit(id: string) {
		const dlg = new InstanceDialog();
		void dlg.load(id);
		dlg.show();
	}

	private async loginAs(record: any) {
		try {
			this.mask();

			const result = await client.jmap("Instance/login", {
				id: record.id
			});

			window.open("about:blank", "groupoffice_instance");

			const f = document.createElement("form");
			f.setAttribute("method", "post");
			f.setAttribute("target", "groupoffice_instance");
			f.setAttribute("action", document.location.protocol + "//" + record.hostname + ":" + document.location.port);

			const i = document.createElement("input");
			i.setAttribute("type", "hidden");
			i.setAttribute("name", "accessToken");
			i.setAttribute("value", result.accessToken);
			f.appendChild(i);

			document.body.appendChild(f);
			f.submit();
			document.body.removeChild(f);
		} catch (e) {
			void Window.error(e);
		} finally {
			this.unmask();
		}
	}
}