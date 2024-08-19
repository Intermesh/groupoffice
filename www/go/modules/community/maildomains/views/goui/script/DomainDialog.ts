import {
	btn,
	checkbox,
	comp,
	Component,
	DefaultEntity,
	EntityID,
	fieldset,
	NumberField,
	numberfield,
	searchbtn,
	t,
	tbar,
	textfield
} from "@intermesh/goui";
import {
	client,
	FilterCondition,
	FormWindow,
	JmapDataSource,
	jmapds,
	userdisplaycombo
} from "@intermesh/groupoffice-core";
import {AliasTable} from "./AliasTable";
import {MailboxTable} from "./MailboxTable";
import {MailboxExportDialog} from "./MailboxExportDialog";
import {MailboxDialog} from "./MailboxDialog";
import {AliasDialog} from "./AliasDialog";
import {DnsSettingsForm} from "./DnsSettingsForm";

export class DomainDialog extends FormWindow {

	private mailboxesTab: Component | undefined;
	private mailboxGrid!: MailboxTable;

	private aliasesTab!: Component;
	private aliasGrid!: AliasTable;

	private dnsSettingsTab!: Component;
	private dnsSettingsForm!: DnsSettingsForm;

	private totalQuotaFld: NumberField;
	private defaultQuotaFld: NumberField;

	constructor() {
		super("MailDomain");

		this.title = t("Domain");

		this.stateId = "maildomain-dialog";
		this.maximizable = true;
		this.resizable = true;
		this.closable = true;
		this.width = 1024;

		this.createMailboxesTab();
		this.createAliasTab();
		this.createDnsSettingsTab();

		this.generalTab.items.add(
			fieldset({flex: 1},
				userdisplaycombo({
					name: "userId",
					id: "userId",
					label: t("User"),
					required: true
				}),
				textfield({
					name: "domain",
					id: "domain",
					label: t("Domain"),
					required: true
				}),
				textfield({
					name: "description",
					id: "description",
					label: t("Description")
				}),
				textfield({
					name: "transport",
					id: "transport",
					value: "virtual",
					hidden: true,
					readOnly: true
				}),
				numberfield({
					name: "maxAliases",
					id: "maxAliases",
					label: t("Max aliases"),
					decimals: 0,
				}),
				numberfield({
					name: "maxMailboxes",
					id: "maxMailboxes",
					label: t("Max mailboxes"),
					decimals: 0,
				}),

				this.defaultQuotaFld = numberfield({
					name: "defaultQuota",
					id: "defaultQuota",
					label: t("Default quota (MB)"),
					multiplier: 1 / (1024 * 1024), // convert bytes to MB
					decimals: 0,
					required: true,
					value: 0
				}),

				this.totalQuotaFld = numberfield({
					name: "totalQuota",
					id: "totalQuota",
					label: t("Max quota (MB)"),
					multiplier: 1 / (1024 * 1024), // convert bytes to MB
					decimals: 0,
					required: true,
					value: 0
				}),
				checkbox({
					label: t("Active"),
					name: "active",
					id: "active",
					type: "switch"
				}),
				checkbox({
					label: t("Backup MX"),
					name: "backupMx",
					id: "backupMx",
					type: "switch"
				})
			)
		);

		this.cards.items.add(this.mailboxesTab!, this.aliasesTab!, this.dnsSettingsTab!)
		this.addSharePanel();

		this.on("ready", async () => {
			if (this.currentId) {

				this.form.findField("domain")!.disabled = true;

				this.mailboxGrid!.store.queryParams.filter = {
					domainId: this.currentId
				};
				this.mailboxGrid!.store.load().then(() => {
					this.mailboxesTab!.disabled = false;
				});

				this.aliasGrid!.store.queryParams.filter = {
					domainId: this.currentId
				};
				this.aliasGrid!.store.load().then(() => {
					this.aliasesTab!.disabled = false;
				});

				this.dnsSettingsTab!.disabled = false;

				const toolbar = this.form.items.last();

				toolbar!.items.insert(0, btn({
					text: t("Export"),
					handler: (_btn) => {
						this.openExportDlg(this.form.value);
					}}));

			}
		});

	}

	private createMailboxesTab() {
		this.mailboxGrid = new MailboxTable();
		this.mailboxesTab = comp({
				cls: "scroll fit",
				title: t("Mailboxes"),
				disabled: true
			}
		);
		this.mailboxGrid.on("rowdblclick", async (table, rowIndex, _ev) => {
			await this.openMailboxDlg(table.store.get(rowIndex)!.id);
		});
		this.mailboxesTab.items.add(tbar({cls: "border-bottom"},
			btn({
				cls: "primary filled", icon: "add", text: t("Add"), handler: async (_btn) => {
					await this.openMailboxDlg();
				}
			}),
			btn({
				icon: "delete", text: t("Delete"), handler: async (_btn) => {
					const ids = this.mailboxGrid!.rowSelection!.selected.map(index => this.mailboxGrid!.store.get(index)!.id);
					await jmapds("MailBox")
						.confirmDestroy(ids);
				}
			}),
			"->",
			searchbtn({
				listeners: {
					input: (_sender, text) => {
						(this.mailboxGrid!.store.queryParams.filter as FilterCondition).text = text;
						this.mailboxGrid!.store.load();
					}
				}
			}),
		), this.mailboxGrid);
	}

	private createAliasTab() {
		this.aliasesTab = comp({
				cls: "scroll fit",
				title: t("Aliases"),
				disabled: true
			}
		);

		this.aliasGrid = new AliasTable();
		this.aliasGrid.on("rowdblclick", async (table, rowIndex, _ev) => {
			await this.openAliasDlg(table.store.get(rowIndex)!.id);
		});

		this.aliasesTab.items.add(tbar({cls: "border-bottom"},
			btn({
				cls: "primary filled", icon: "add", text: t("Add"), handler: async (_btn) => {
					await this.openAliasDlg();
				}
			}),
			btn({
				icon: "delete", text: t("Delete"), handler: async (_btn) => {
					const ids = this.aliasGrid!.rowSelection!.selected.map(index => this.aliasGrid!.store.get(index)!.id);
					await jmapds("MailAlias")
						.confirmDestroy(ids);

				}
			}),
			"->",
			searchbtn({
				listeners: {
					input: (_sender, text) => {
						(this.aliasGrid!.store.queryParams.filter as FilterCondition).text = text;
						this.aliasGrid!.store.load();
					}
				}
			}),
		), this.aliasGrid);

	}

	private createDnsSettingsTab(): void {
		this.dnsSettingsTab = comp({
				cls: "scroll fit",
				title: t("DNS Settings"),
				disabled: true
			}
		);

		this.dnsSettingsForm = new DnsSettingsForm();

		this.dnsSettingsTab.items.add(
			tbar({cls: "border-bottom"},
				"->",
				btn({
					icon: "dns",
					text: t("Refresh DNS", "community", "maildomoins"),
					handler: async (_btn, _ev) => {
						if(!this.currentId) {
							return;
						}

						jmapds("MailDomain").update(this.currentId, {
							checkDNS: true
						})
					}
				})),
			this.dnsSettingsForm
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

	private async openExportDlg(entity: DefaultEntity): Promise<void> {
		const ids = Array.from(this.mailboxGrid!.store.data, (m: DefaultEntity) => m.id);
		const w = new MailboxExportDialog();
		w.load(entity, ids);
		w.show();
	}

}