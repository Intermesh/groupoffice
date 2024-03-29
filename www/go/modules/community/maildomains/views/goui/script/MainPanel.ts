import {btn, comp, Component, Notifier, paginator, searchbtn, t, tbar} from "@intermesh/goui";
import {authManager, User} from "@intermesh/groupoffice-core";
import {DomainTable} from "./DomainTable";
import {DomainDialog} from "./DomainDialog";

export class MainPanel extends Component {
	private tbl: DomainTable;
	private center: Component;
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
								// TODO
							}
						})
					),

					comp({
							flex: 1,
							stateId: "maildomains",
							cls: "scroll border-top main fit"
						},
						this.tbl
					),


					paginator({
						store: this.tbl.store
					})
				),
			)
		);
	}
}
