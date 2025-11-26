import {
	Button,
	btn,
	column,
	comp,
	Component,
	ComponentEventMap,
	h3,
	Menu,
	menu,
	SelectField,
	Store,
	t,
	table,
	tbar,
	datasourcestore,
	Window,
	select
} from "@intermesh/goui";
import {
	jmapds,
	MainThreeColumnPanel,
	User
} from "@intermesh/groupoffice-core";
import {SieveRuleEntity} from "./Index";
import {SieveRuleWindow} from "./SieveRuleWindow";


export class MainPanel extends MainThreeColumnPanel {
	private accountsGrid: Menu<ComponentEventMap> | undefined;
	private accountId: string | undefined;
	private successCmp: Component | undefined;
	private warningCmp: Component | undefined;
	private scriptsCombo: SelectField | undefined;
	private rulesGrid: Component | undefined;
	private oooPanel: Component | undefined;

	protected createWest(): Component {
		return comp({
				width: 260,
				itemId: "west",
				stateId: "tempsieve-west",
				cls: "scroll"
			},
			tbar({
					cls: "border-bottom"
				},
				h3(t("Accounts"))
			),
			this.accountsGrid = menu({cls: "west-menu absence-west-menu"})
		);
	}

	protected createEast(): Component {
		return comp({
			hidden: true
		});
	}

	protected createCenter(): Component {
		return comp({
				cls: "vbox pad",
			},
			this.successCmp = comp({cls: "success", hidden: true, html: t("This account supports Sieve!")}),
			this.warningCmp = comp({cls: "warning", hidden: true}),
			this.rulesGrid = comp({hidden: true}),
			this.oooPanel = comp({html: "TODO: Out of office panel", hidden: true})
		);
	}

	constructor() {
		super("tempsieve");
		const store = datasourcestore({
			dataSource: jmapds("Account"),
			queryParams: {
				limit: 20,
				filter: {}
			},
			sort: [{property: "username", isAscending: true}]
		});

		this.on("render", async () => {
			this.accountsGrid!.items.clear();
			let accountBtns: Button[] = [];
			store.load().then((v) => {
				for (const curr of v) {
					accountBtns.push(btn({
						text: curr.username,
						cls: curr.id === this.accountId ? "pressed" : "",
						icon: "account_circle",
						handler: () => {
							this.accountId = curr.id;
							this.loadAccount(curr.id)
						}
					}));
				}
				this.accountsGrid!.items.add(...accountBtns);
			});

		});
	}

	public async loadAccount(id: string) {
		this.successCmp!.hidden = true;
		this.warningCmp!.hidden = true;
		this.rulesGrid!.hidden = true;
		this.rulesGrid!.items.clear();
		this.oooPanel!.hidden = true;
		const response = await go.Jmap.request({
			method: "community/tempsieve/Sieve/isSupported",
			params: {accountId: id},
		});
		if (response.isSupported) {
			this.successCmp!.hidden = false;
			this.rulesGrid!.hidden = false;
			this.oooPanel!.hidden = false;
		} else {
			this.warningCmp!.hidden = false;
			this.warningCmp!.html = response.message;
		}

		const p1 = go.Jmap.request({
				method: "community/tempsieve/Sieve/rules",
				params: {accountId: id},
			}),
			p2 = go.Jmap.request({
				method: "community/tempsieve/Sieve/ooo",
				params: {accountId: id},
			}),
			p3 = go.Jmap.request({
				method: "community/tempsieve/Sieve/scripts",
				params: {accountId: id},
			});
		Promise.all([p1, p2, p3]).then(arResult => {
			if (arResult[0].success) {
				this.renderRulesTable(arResult[0].rules);
				this.scriptsCombo!.options = arResult[2].results;
				this.scriptsCombo!.value = arResult[2].active;
				// console.log(arResult[0].rules);
			}
			if (arResult[1].success) {
				this.oooPanel!.html = arResult[1].ooo;
			}
		});
	}

	private renderRulesTable(result: SieveRuleEntity[]) {
		this.scriptsCombo = select({
			label: t("Filterset"),
			name: "filterset",
			options: []
		});
		const tbl = table({
			fitParent: false,
			store: new Store(),
			cls: "border",
			columns: [
				column({
					id: "name",
					header: t("Sieve rule"),
				}),
				column({
					id: "active",
					header: t("Active"),
					width: 120,
					align: "right",
					renderer: (v) => {
						return v ? t("Yes") : t("No");
					}
				}),
				column({
					id: "more",
					width: 30,
					sticky: true,
					renderer: (columnValue, record, td, table1, storeIndex) => {
						return this.renderActions(record);
					}
				})
			],
			listeners: {
				rowdblclick: ({storeIndex}) => {
					const record: SieveRuleEntity = tbl.store.get(storeIndex) as SieveRuleEntity;
					const win = new SieveRuleWindow(this.accountId!);
					void win.load(record);
					win.show();
				}
			}
		});
		tbl.store.loadData(result, false);
		this.rulesGrid!.items.add(comp({
				cls: "vbox pad",
			},
			tbar({cls: "border-bottom"},
				this.scriptsCombo,
				btn({
					text: t("Activate"),
					handler: async () => {
						const c = await Window.confirm(t("Are you sure you want to activate this Sieve script?"), t("Confirm"));
						if (c) {
							go.Jmap.request({
								method: "community/tempsieve/Sieve/scripts",
								params: {accountId: this.accountId, activateScriptName: this.scriptsCombo!.value},
							}).then(() => {
								this.loadAccount(this.accountId!)
							});
						}
					}
				}),
				"->",
				btn({
					cls: "primary filled",
					icon: "add",
					handler: async () => {}
				})
			),
			tbl
		));
	}

	private renderActions(record: SieveRuleEntity) {
		const editBtn = btn({
			text: "Edit",
			icon: "edit",
			handler: () => {
				const w = new SieveRuleWindow(this.accountId!);
				void w.load(record);
				w.show();
			}
		}), deleteBtn = btn({
			text: "Delete",
			icon: "delete",
			handler: async () => {
				const c = await Window.confirm(t("Are you sure that you want to delete this rule?"), t("Confirm"));
				if (c) {
					//void jmapds("Registration").destroy(record.id);
					Window.alert("TODO");
				}
			}
		});
		return btn({
			icon: "more_vert",
			menu: menu({},
				editBtn,
				deleteBtn
			)
		});

	}
}