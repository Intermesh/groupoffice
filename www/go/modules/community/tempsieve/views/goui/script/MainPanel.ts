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
	Store,
	t,
	table,
	tbar,
	datasourcestore,
	Table,
	Window,
	autocomplete, AutocompleteField, store, TextAreaField, textarea, Notifier
} from "@intermesh/goui";
import {
	client,
	jmapds,
	MainThreeColumnPanel
} from "@intermesh/groupoffice-core";
import {SieveRuleEntity, SieveScriptEntity} from "./Index";
import {SieveRuleWindow} from "./SieveRuleWindow";
import {SieveScriptParser} from "./SieveScriptParser";
import {SieveRuleParser} from "./SieveRuleParser";


export class MainPanel extends MainThreeColumnPanel {
	private accountsGrid: Menu<ComponentEventMap> | undefined;
	private accountId: string | undefined;
	private successCmp: Component | undefined;
	private warningCmp: Component | undefined;
	private scriptsCombo: AutocompleteField | undefined;
	private rulesPnl: Component | undefined;
	private rulesGrid: Table | undefined;
	private oooPanel: Component | undefined;
	private rawEditor: TextAreaField | undefined;
	private scriptParser: SieveScriptParser | undefined;

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
				flex: 1
			},
			this.successCmp = comp({cls: "success", hidden: true, html: t("This account supports Sieve!")}),
			this.warningCmp = comp({cls: "warning", hidden: true}),
			this.rulesPnl = comp({hidden: true}),
			this.rawEditor = textarea({hidden: true, name: "raw", height: 800}),
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
		this.rulesPnl!.hidden = true;
		this.rulesPnl!.items.clear();
		this.oooPanel!.hidden = true;
		this.rawEditor!.value = undefined;
		this.rawEditor!.hidden = true;
		const response = await go.Jmap.request({
			method: "community/tempsieve/Sieve/isSupported",
			params: {accountId: id},
		});
		if (response.isSupported) {
			this.successCmp!.hidden = false;
			this.rulesPnl!.hidden = false;
			this.oooPanel!.hidden = false;
		} else {
			this.warningCmp!.hidden = false;
			this.warningCmp!.html = response.message;
		}

		const p1 = go.Jmap.request({
				method: "community/tempsieve/SieveScript/get",
				params: {
					accountId: id
				},
			}),
			p2 = go.Jmap.request({
					method: "community/tempsieve/VacationResponse/get",
					params: {
						filter: {
							accountId: id
						}
					}
				}
			);
		Promise.all([p1, p2]).then(arResult => {
			const activeRuleSet = arResult[0].list.find((i: any) => i.isActive);
			this.renderRulesTable(activeRuleSet);
			this.scriptsCombo!.list.store.loadData(arResult[0].list);
			this.scriptsCombo!.value = activeRuleSet.id;
			this.rawEditor!.hidden = false;
			this.rawEditor!.value = activeRuleSet.script;
			this.oooPanel!.html = "TODO: Out of Office / vacation";
		});
	}

	private renderRulesTable(script: SieveScriptEntity) {
		this.scriptsCombo = autocomplete({
			label: t("Filterset"),
			name: "filterset",

			pickerRecordToValue: (field, record) => {
				return record.id;
			},

			async valueToTextField(field, value: any): Promise<string> {
				const record = field.list.store.find(r => r.id == value);
				let r = "";
				if (record) {
					r = value;
					if (record.active) {
						r += " (" + t("Active") + ")";
					}
				}
				return r;
			},

			list: table({
				headers: false,
				fitParent: true,
				store: store(),
				columns: [
					column({
						header: "id",
						id: "id",
						sortable: true,
						resizable: true,
						renderer: (v, record) => {
							let r = v;
							if (record.active) {
								r += " (" + t("Active") + ")";
							}
							return r;
						}
					})
				]
			})
		});
		this.rulesGrid = table({
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
						return this.renderActions(record, table1.store, storeIndex);
					}
				})
			],
			listeners: {
				rowdblclick: ({storeIndex}) => {
					const record: SieveRuleEntity = this.rulesGrid!.store.get(storeIndex) as SieveRuleEntity;
					record.index = storeIndex;
					this.openSieveRuleWindow(record, this.rulesGrid!.store, storeIndex);
				}
			}
		});
		this.rulesPnl!.items.add(comp({
				cls: "vbox pad",
			},
			tbar({cls: "border-bottom"},
				this.scriptsCombo,
				btn({
					text: t("Activate"),
					disabled: true, // TODO
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
					cls: "filled",
					icon: "data_check",
					handler: async () => {
						const activeScriptName = this.scriptsCombo!.value as string,
							f = new File([this.rawEditor!.value as string], `${activeScriptName}_${this.accountId}.siv`, {type: "application/sieve"});
						client.upload(f).then((response: any) => {
							const updateParam: any = {};
							updateParam[activeScriptName] = {blobId: response.blobId};
							go.Jmap.request({
								method: "community/tempsieve/SieveScript/set",
								params: {
									accountId: this.accountId,
									onSuccessActivateScript: activeScriptName,
									update: updateParam
								}
							}).
							then((res: any) => {
								if (res.notUpdated && res.notUpdated[activeScriptName]) {
									const errorDescription = res.notUpdated[activeScriptName].type;
									this.rawEditor!.setInvalid(errorDescription);
									Notifier.error(errorDescription);
								} else {
									this.rawEditor!.clearInvalid();
									Notifier.success(t("Script validated successfully"));
								}
							});
						});
						return;
					}
				}),
				btn({
					cls: "primary filled",
					icon: "add",
					handler: async () => {
						const c = this.rulesGrid!.store.count();
						const record: SieveRuleEntity = {
							index: c,
							name: "",
							raw: "",
							active: true
						};
						this.openSieveRuleWindow(record, this.rulesGrid!.store, c);
					}
				})
			),
			this.rulesGrid
		));
		this.scriptParser = new SieveScriptParser(script);
		this.rulesGrid.store.loadData(this.scriptParser.rules, false);
	}

	private renderActions(record: SieveRuleEntity, store: Store, storeIndex: number) {
		const editBtn = btn({
			text: "Edit",
			icon: "edit",
			handler: () => {
				void this.openSieveRuleWindow(record, store, storeIndex);
			}
		}), deleteBtn = btn({
			text: "Delete",
			icon: "delete",
			handler: async () => {
				const c = await Window.confirm(t("Are you sure that you want to delete this rule?"), t("Confirm"));
				if (c) {
					store.removeAt(storeIndex);
					this.updateRawScript();
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

	/**
	 * Generic method to open a rule window and handle form submits
	 *
	 * @param record
	 * @param store
	 * @param storeIndex
	 * @private
	 */
	private openSieveRuleWindow(record: SieveRuleEntity, store: Store, storeIndex: number): void {
		const win = new SieveRuleWindow(this.accountId!);
		void win.load(record);
		win.frm.on("submit", ({target}) => {
			Object.assign(record, target.value);
			const scriptParser = new SieveRuleParser(record);
			scriptParser.convert(win.tests, win.actions);
			record.raw = scriptParser.raw;
			store.replaceAt(storeIndex, record);
			this.updateRawScript();
			win.close();
		});
		win.show();
	}

	/**
	 * Upon saving an individual rule, update the full script for sending into the JMAP API
	 *
	 * @private
	 */
	private updateRawScript(): void {
		let r = `require ${this.scriptParser?.requirements}\n`;
		for (const item of this.rulesGrid!.store.getArray()) {
			r += item.raw + "\n";
		}
		this.rawEditor!.value = r;
	}
}