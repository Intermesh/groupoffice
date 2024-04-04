import {
	btn,
	checkbox,
	comp,
	Component,
	DefaultEntity,
	fieldset,
	numberfield,
	searchbtn,
	t,
	tbar,
	textfield
} from "@intermesh/goui";
import {FormWindow, jmapds, userdisplaycombo} from "@intermesh/groupoffice-core";
import {AliasTable} from "./AliasTable";
import {MailboxTable} from "./MailboxTable";
import {MailboxDialog} from "./MailboxDialog";
import {AliasDialog} from "./AliasDialog";

export class DomainDialog extends FormWindow {

	private mailboxesTab: Component | undefined;
	private mailboxGrid: MailboxTable | undefined;

	private aliasesTab: Component | undefined;
	private aliasGrid: AliasTable | undefined;

	private entity: DefaultEntity | undefined;

	constructor() {
		super("MailDomain");

		this.title = t("Domain");

		this.stateId = "maildomain-dialog";
		this.maximizable = true;
		this.resizable = true;
		this.closable = true;
		this.width = 1024;
		this.height = 800;

		this.createMailboxesTab();
		this.createAliasTab();

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
					required: true,
					readOnly: !!this.currentId,
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
					flex: 0.5
				}),
				numberfield({
					name: "maxMailboxes",
					id: "maxMailboxes",
					label: t("Max mailboxes"),
					decimals: 0,
					flex: 0.5
				}),
				numberfield({
					name: "maxQuota",
					id: "maxQuota",
					label: t("Max quota (MB)"),
					decimals: 2,
					flex: 0.5
				}),
				numberfield({
					name: "defaultQuota",
					id: "defaultQuota",
					label: t("Default quota (MB)"),
					decimals: 2,
					flex: 0.5
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
		this.cards.items.add(this.mailboxesTab!, this.aliasesTab!)
		this.addSharePanel();

		this.on("ready", async () => {
			if (this.currentId) {
				const d = await jmapds("MailDomain").single(this.currentId);
				this.mailboxGrid!.store.loadData(d!.mailboxes, false);
				this.aliasGrid!.store.loadData(d!.aliases, false);
				this.entity = d;
				this.mailboxGrid!.entity = d;
				this.aliasGrid!.entity = d;

				this.mailboxesTab!.disabled = false;
				this.aliasesTab!.disabled = false;
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
		this.mailboxGrid.on("rowdblclick", async (table, rowIndex, ev) => {
			this.openMailboxDlg(table.store.get(rowIndex)!);
		});
		this.mailboxesTab.items.add(tbar({cls: "border-bottom"},
			btn({
				cls: "primary filled", icon: "add", text: t("Add"), handler: (_btn) => {
					this.openMailboxDlg({domainId: this.entity!.id, active: true})
				}
			}),
			btn({
				icon: "delete", text: t("Delete"), handler: (_btn) => {
					const selectedRows = this.mailboxGrid?.rowSelection?.selected;
					if (!selectedRows?.length) {
						return;
					}
					let selectedIds: number[] = [];
					for (const kenny of selectedRows) {
						selectedIds.push(this.mailboxGrid!.store.get(kenny)!.id);
					}
					const mbs = this.entity!.mailboxes.filter((mb: any) => {
						return selectedIds.indexOf(mb.id) === -1;
					});
					jmapds("MailDomain").update(this.currentId!, {mailboxes: mbs}).then(() => {
						this.mailboxGrid!.store.loadData(mbs, false);
					});

				}
			}),
			"->",
			searchbtn({
				listeners: {
					input: (_sender, text) => {
						const records = this.entity!.mailboxes;
						const filtered = records.filter((r: { username: string; name: string }) => {
							return !text || r.username.toLowerCase().indexOf(text.toLowerCase()) > -1 || r.name.toLowerCase().indexOf(text.toLowerCase()) > -1;
						});
						this.mailboxGrid!.store.loadData(filtered, false)
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
		this.aliasGrid.on("rowdblclick", async (table, rowIndex, ev) => {
			await this.openAliasDlg(table.store.get(rowIndex)!);
		});

		this.aliasesTab.items.add(tbar({cls: "border-bottom"},
			btn({
				cls: "primary filled", icon: "add", text: t("Add"), handler: async (_btn) => {
					await this.openAliasDlg({active: true});
				}
			}),
			btn({
				icon: "delete", text: t("Delete"), handler: (btn) => {
					const selectedRows = this.aliasGrid?.rowSelection?.selected;
					if (!selectedRows?.length) {
						return;
					}
					let selectedIds: number[] = [];
					for (const kenny of selectedRows) {
						selectedIds.push(this.aliasGrid!.store.get(kenny)!.id);
					}
					const aa = this.entity!.aliases.filter((a: any) => {
						return selectedIds.indexOf(a.id) === -1;
					});
					jmapds("MailDomain").update(this.currentId!, {aliases: aa}).then(() => {
						this.aliasGrid!.store.loadData(aa, false);
					});
				}
			}),
			"->",
			searchbtn({
				listeners: {
					input: (sender, text) => {
						const records = this.entity!.aliases;
						const filtered = records.filter((r: { address: string; goto: string }) => {
							return !text || r.address.toLowerCase().indexOf(text.toLowerCase()) > -1 || r.goto.toLowerCase().indexOf(text.toLowerCase()) > -1;
						});
						this.aliasGrid!.store.loadData(filtered, false)
					}
				}
			}),
		), this.aliasGrid);

	}

	private async openMailboxDlg(record: any): Promise<void> {
		const dlg = new MailboxDialog(this.entity!);
		dlg.show();
		dlg.on("close", () => {
			return this.load(this.currentId!).then((_value) => {
				this.mailboxGrid!.store.loadData(this.entity!.mailboxes, false);
			});
		});

		await dlg.load(record);

	}

	private async openAliasDlg(record: any): Promise<void> {
		const dlg = new AliasDialog(this.entity!);
		dlg.show();
		dlg.on("close", () => {
			return this.load(this.currentId!).then((_value) => {
				this.aliasGrid!.store.loadData(this.entity!.aliases, false);
			});
		});

		await dlg.load(record);

	}
}