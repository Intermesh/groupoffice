import {
	btn,
	checkbox,
	comp,
	Component,
	DefaultEntity, EntityID,
	fieldset,
	numberfield,
	searchbtn,
	t,
	tbar,
	textfield
} from "@intermesh/goui";
import {FilterCondition, FormWindow, jmapds, userdisplaycombo} from "@intermesh/groupoffice-core";
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
					name: "totalQuota",
					id: "totalQuota",
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

				this.entity = d;
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
			this.openMailboxDlg(table.store.get(rowIndex)!.id);
		});
		this.mailboxesTab.items.add(tbar({cls: "border-bottom"},
			btn({
				cls: "primary filled", icon: "add", text: t("Add"), handler: (_btn) => {
					this.openMailboxDlg();
				}
			}),
			btn({
				icon: "delete", text: t("Delete"), handler: async(_btn) => {
					const selectedRows = this.mailboxGrid?.rowSelection?.selected;
					if (!selectedRows?.length) {
						return;
					}
					for (const kenny of selectedRows) {
						jmapds('MailBox').destroy(this.mailboxGrid!.store.get(kenny)!.id);
					}
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
		this.aliasGrid.on("rowdblclick", async (table, rowIndex, ev) => {
			await this.openAliasDlg(table.store.get(rowIndex)!.id);
		});

		this.aliasesTab.items.add(tbar({cls: "border-bottom"},
			btn({
				cls: "primary filled", icon: "add", text: t("Add"), handler: async (_btn) => {
					await this.openAliasDlg();
				}
			}),
			btn({
				icon: "delete", text: t("Delete"), handler: (btn) => {
					const selectedRows = this.aliasGrid?.rowSelection?.selected;
					if (!selectedRows?.length) {
						return;
					}
					for (const kenny of selectedRows) {
						jmapds('MailAlias').destroy(this.mailboxGrid!.store.get(kenny)!.id);
					}
				}
			}),
			"->",
			searchbtn({
				listeners: {
					input: (sender, text) => {
						(this.aliasGrid!.store.queryParams.filter as FilterCondition).text = text;
						this.aliasGrid!.store.load();
					}
				}
			}),
		), this.aliasGrid);

	}

	private async openMailboxDlg(id?: EntityID): Promise<void> {
		const dlg = new MailboxDialog();
		dlg.entity = this.entity;
		dlg.show();
		if (id) {
			await dlg.load(id);
		}
	}

	private async openAliasDlg(id?: EntityID): Promise<void> {
		const dlg = new AliasDialog();
		dlg.entity = this.entity;
		dlg.show();
		if(id) {
			await dlg.load(id);
		}
	}
}