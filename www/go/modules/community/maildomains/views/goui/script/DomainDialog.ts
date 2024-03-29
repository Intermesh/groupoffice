import {
	btn,
	checkbox,
	comp,
	Component,
	DefaultEntity,
	fieldset,
	Notifier,
	numberfield,
	searchbtn,
	t, tbar,
	textfield
} from "@intermesh/goui";
import {FormWindow, jmapds, userdisplaycombo} from "@intermesh/groupoffice-core";
import {AliasTable} from "./AliasTable";
import {MailboxTable} from "./MailboxTable";

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

		this.on("ready", async  () => {
			if(this.currentId) {
				const d = await jmapds("MailDomain").single(this.currentId);
				this.mailboxGrid!.store.loadData(d!.mailboxes, false);
				this.aliasGrid!.store.loadData(d!.aliases,false);
				this.entity = d;

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
		this.mailboxesTab.items.add(tbar({},
			btn({cls: "primary filled", icon: "add", text: t("Add"), handler: (btn) => {
					Notifier.notice(t("Work in progress."))
				}}),
			btn({
				icon: "delete", text: t("Delete"), handler: (btn) => {Notifier.notice("Work in progress")}
			}),
			"->",
			searchbtn({
				listeners: {
					input: (_sender, text) => {
						const records = this.entity!.mailboxes;
						const filtered = records.filter((r: { username: string; name: string}) => {
							return !text || r.username.toLowerCase().indexOf(text.toLowerCase()) > -1 || r.name.toLowerCase().indexOf(text.toLowerCase()) > -1;
						});
						this.mailboxGrid!.store.loadData(filtered, false)
					}
				}
			}),
		),this.mailboxGrid);
	}

	private createAliasTab() {
		this.aliasesTab = comp({
				cls: "scroll fit",
				title: t("Aliases"),
				disabled: true
			}
		);

		this.aliasGrid = new AliasTable();
		this.aliasesTab.items.add(tbar({},
			btn({cls: "primary filled", icon: "add", text: t("Add"), handler: (btn) => {
					Notifier.notice(t("Work in progress."))
				}}),
			btn({
				icon: "delete", text: t("Delete"), handler: (btn) => {Notifier.notice("Work in progress")}
			}),
			"->",
			searchbtn({
				listeners: {
					input: (sender, text) => {
						const records = this.entity!.aliases;
						const filtered = records.filter((r: { address: string; goto: string}) => {
							return !text || r.address.toLowerCase().indexOf(text.toLowerCase()) > -1 || r.goto.toLowerCase().indexOf(text.toLowerCase()) > -1;
						});
						this.aliasGrid!.store.loadData(filtered, false)
					}
				}
			}),
		),this.aliasGrid);

	}

}