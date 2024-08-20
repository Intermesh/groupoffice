import {btn, comp, Component, Notifier, paginator, searchbtn, t, tbar, Window} from "@intermesh/goui";
import {authManager, client, jmapds, User} from "@intermesh/groupoffice-core";
import {DomainTable} from "./DomainTable";
import {DomainDialog} from "./DomainDialog";

export class MainPanel extends Component {
	private tbl: DomainTable;
	private center: Component;
	private ptrStatus: Component
	private user: User | undefined;

	constructor() {
		super("section");

		this.id = "support";
		this.cls = "vbox fit";
		this.on("render", async () => {
			try {
				this.user = await authManager.requireLogin();
			} catch (e) {
				console.warn(e);
				Notifier.error(t("Login is required on this page"));
			}
			await this.tbl.store.load();
			client.jmap( "MailDomain/checkPtr", {}).then((result) => {
				let ptrOk = true;
				for(const rr of result) {
					ptrOk = ptrOk && rr.status === "SUCCESS";
				}
				if (ptrOk) {
					this.ptrStatus.html = '<i class="icon success">check</i>&nbsp;' +t("PTR OK")
				} else {
					this.ptrStatus.html = '<i class="icon warning">warning</i>&nbsp;' + t("PTR error");
				}
			}).catch((e) => {
				this.ptrStatus.html = '<i class="icon warning">warning</i>&nbsp;' + t("PTR error");

				Window.error(e);
			})
		});
		this.tbl = new DomainTable();

		this.items.add(
			comp({
					cls: "hbox mobile-cards"
				},
				this.center = comp({
						cls: 'active vbox fit',
						itemId: 'table-container',
					},

					tbar({},
						'->',
						this.ptrStatus = comp({html: ""}),
						searchbtn({
							listeners: {
								input: (sender, text) => {
									this.tbl!.store.setFilter("search", {text: text});
									this.tbl!.store.load(false);
								}
							}
						}),
						btn({
							itemId: "add",
							icon: "add",
							text: t("Add"),
							cls: "filled primary",
							handler: async () => {
								const dlg = new DomainDialog();
								dlg.show();
								dlg.form.value = {userId: this.user!.id, active: 1};
							}
						}),
						btn({
							itemId: "delete",
							icon: "delete",
							text: t("Delete"),
							handler: async () => {
								const ids = this.tbl!.rowSelection!.selected.map(index => this.tbl!.store.get(index)!.id);
								await jmapds("MailDomain")
									.confirmDestroy(ids);
							}
						})
					),

					comp({
							flex: 1,
							stateId: "maildomains",
							cls: "scroll border-top main fit"
						},
						this.tbl
					)
				),
			)
		);
	}
}
