import {
	btn,
	checkbox, column,
	comp,
	DataSourceForm,
	datasourceform, DefaultEntity,
	displayfield, EntityID, Format,
	h2,
	h3, hr,
	menu, mstbar, Notifier, searchbtn,
	t,
	tbar, Window
} from "@intermesh/goui";
import {DetailPanel, FilterCondition, jmapds} from "@intermesh/groupoffice-core";
import {DnsSettingsPanel} from "./DnsSettingsPanel";
import {MailboxTable} from "./MailboxTable";
import {MailboxDialog} from "./MailboxDialog";
import {AliasDialog} from "./AliasDialog";
import {AliasTable} from "./AliasTable";
import {MailboxExportDialog} from "./MailboxExportDialog";
import {MailDomain, mailDomainStatus} from "./MailDomain";
import {DomainDialog} from "./DomainDialog";

export class DomainDetail extends DetailPanel<MailDomain> {
	private form: DataSourceForm<MailDomain>;
	private dnsSettingsForm: DnsSettingsPanel;
	private aliasTable!: AliasTable;
	private mailboxTable!: MailboxTable;

	constructor() {
		super("MailDomain");


		this.scroller.items.add(
			this.form = datasourceform({
				dataSource: jmapds("MailDomain")
			},

				comp({cls: "card "},
					tbar({},
						displayfield({
							escapeValue: false,
							name: "active",
							value: false,
							renderer: (v, field) => {
								if(!this.entity) {
									return "";
								}
								return mailDomainStatus(this.entity);
							}
						}),
						comp({},
							displayfield({
								tagName: "h3",
								name: "domain"
							}),

							displayfield({
								tagName: "small",
								name: "description"
							}),
						),

					),

					comp({cls: "pad flow"},
						displayfield({
							label: t("User"),
							name: "userId",
							renderer: async (userId) => {
								return jmapds("UserDisplay").single(userId).then(u => u?.displayName ?? t("Not found"));
							}
						}),

						displayfield({
							label: t("Quota"),
							name: "totalQuota",
							flex: 1,
							renderer: (v) => {
								if(v === 0) {
									return t("Unlimited");
								}
								v *= 1024;
								return Format.fileSize(v);
							}
						}),

						displayfield({
							flex: 1,
							label: t("Used quota"),
							name: "sumUsedQuota",
							renderer: (v, _record) => {
								v = parseInt(v);
								v *= 1024;
								return (v > 0) ? Format.fileSize(v) : "0B";
							}
						}),

						displayfield({
							flex: 1,
							label: t("Usage"),
							name: "sumUsage",
							width: 100,
							renderer: (v, _record) => {
								v = parseInt(v);
								return (v > 0) ? Format.fileSize(v) : "0B";
							},
							hidden: true

						}),
					)
				),

				comp({cls: "card "},
					tbar({},
						h3("DNS Settings"),
						"->",
						btn({
							icon: "refresh",
							handler: async (btn) => {
								const card = btn.parent!.parent!;
								card.mask();

								try {
									await jmapds("MailDomain").update(this.entity!.id, {
										checkDNS: true
									})

									Notifier.success(t("DNS check completed"));
								} catch(e) {
									Window.error(e);
								} finally {
									card.unmask();
								}
							}
						})
					),
					this.dnsSettingsForm = new DnsSettingsPanel()
				),

				this.createMailboxesTab(),

				this.createAliasTab()
			)

		)


		this.toolbar.items.add(

			btn({
				icon: "edit",
				title: t("Edit"),
				handler: (button, ev) => {
					const win = new DomainDialog()
					win.load(this.entity!.id);
					win.show();
				}
			}),


			btn({
				icon: "more_vert",
				menu: menu({},

					btn({
						icon: "import_export",
						text: t("Export"),
						handler: async (_btn) => {
							void this.openExportDlg()

						}
					}),
					hr(),
					btn({
						icon: "delete",
						text: t("Delete"),
						handler: () => {
							jmapds("Application").confirmDestroy([this.entity!.id]);
						}
					})
					)
			})
		)

		this.on("load",(detailPanel, entity) => {
			void this.form.load(entity.id);

			this.mailboxTable.store.setFilter("domainId", {domainId: entity.id})
			void this.mailboxTable.store.load();

			this.aliasTable.store.setFilter("domainId", {domainId: entity.id})
			void this.aliasTable.store.load();

			this.dnsSettingsForm.domain = entity;
		});

		this.on("reset", () => {
			this.form.reset();
		});
	}


	private createMailboxesTab() {
		const mailboxTable = new MailboxTable();

		mailboxTable.on("rowdblclick", async (table, rowIndex, _ev) => {
			await this.openMailboxDlg(table.store.get(rowIndex)!.id);
		});

		this.mailboxTable = mailboxTable;

		return comp({
				cls: "card scroll fit"
			},

			tbar({cls: "border-bottom"},
				h3(t("Mailboxes")),
				'->',
				searchbtn({
					listeners: {
						input: (_sender, text) => {
							(mailboxTable!.store.queryParams.filter as FilterCondition).text = text;
							mailboxTable!.store.load();
						}
					}
				}),
				btn({
					cls: "primary filled",
					icon: "add",
					text: t("Add"),
					handler: async (_btn) => {
						await this.openMailboxDlg();
					}
				}),
				// btn({
				// 	icon: "delete",
				// 	title: t("Delete"),
				// 	handler: async (_btn) => {
				// 		const ids = mailboxTable!.rowSelection!.selected.map(index => mailboxTable!.store.get(index)!.id);
				// 		await jmapds("MailBox")
				// 			.confirmDestroy(ids);
				// 	}
				// }),



				mstbar({table: mailboxTable},
					"->",
					btn({
						icon: "delete",
						handler: async (btn) => {

							const ids = mailboxTable.rowSelection!.selected.map(index => mailboxTable.store.get(index)!.id);

							const result = await jmapds("Mailbox")
								.confirmDestroy(ids);

							if(result != false) {
								btn.parent!.hide();
							}

						}
					})
				)


			), mailboxTable
		);


	}

	private async openMailboxDlg(id?: EntityID): Promise<void> {
		const dlg = new MailboxDialog();

		const d = this.form.value;

		dlg.form.value = {
			domainId: d.id,
			domain: this.form.findField("domain")!.value,
			quota:  d.defaultQuota
		};
		dlg.show();
		if (id) {
			await dlg.load(id);
		}
	}



	private createAliasTab() {

		const aliasTable = new AliasTable();
		aliasTable.on("rowdblclick", async (table, rowIndex, _ev) => {
			await this.openAliasDlg(table.store.get(rowIndex)!.id);
		});

		this.aliasTable = aliasTable;

		return comp({
				cls: "card scroll fit"
			},
			tbar({cls: "border-bottom"},
				h3(t("Aliases")),
				'->',
				searchbtn({
					listeners: {
						input: (_sender, text) => {
							(aliasTable!.store.queryParams.filter as FilterCondition).text = text;
							aliasTable!.store.load();
						}
					}
				}),
				btn({
					cls: "primary filled",
					icon: "add",
					text: t("Add"),
					handler: async (_btn) => {
						await this.openAliasDlg();
					}
				}),
				// btn({
				// 	icon: "delete",
				// 	title: t("Delete"),
				// 	handler: async (_btn) => {
				// 		const ids = aliasTable!.rowSelection!.selected.map(index => aliasTable!.store.get(index)!.id);
				// 		await jmapds("MailAlias")
				// 			.confirmDestroy(ids);
				//
				// 	}
				// }),

				mstbar({table: aliasTable},
					"->",
					btn({
						icon: "delete",
						handler: async (btn) => {

							const ids = aliasTable.rowSelection!.selected.map(index => aliasTable.store.get(index)!.id);

							const result = await jmapds("Alias")
								.confirmDestroy(ids);

							if(result != false) {
								btn.parent!.hide();
							}

						}
					})
				)



			), aliasTable
		);
	}

	private async openAliasDlg(id?: EntityID): Promise<void> {
		const dlg = new AliasDialog();

		const d = this.form.value;
		dlg.form.value = {
			domainId: d.id,
			domain: this.form.findField("domain")!.value
		}
		dlg.show();
		if (id) {
			await dlg.load(id);
		}
	}


	private async openExportDlg(): Promise<void> {
		const ids = Array.from(this.mailboxTable.store.data, (m: DefaultEntity) => m.id);
		const w = new MailboxExportDialog();
		w.load(this.form.value, ids);
		w.show();
	}

}