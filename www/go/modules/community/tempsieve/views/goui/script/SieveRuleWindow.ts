import {
	btn,
	checkbox,
	column,
	comp,
	Fieldset,
	fieldset,
	Form,
	form,
	select,
	Store,
	t,
	table,
	Table,
	tbar,
	textfield,
	Window
} from "@intermesh/goui";
import {SieveActionEntity, SieveCriteriumEntity, SieveRuleEntity} from "./Index";
import {SieveCriteriumWindow} from "./SieveCriteriumWindow";
import {SieveActionWindow} from "./SieveActionWindow";
import {SieveRuleParser} from "./SieveRuleParser";

export class SieveRuleWindow extends Window {
	private accountId: string;
	private index: number | undefined;
	public frm: Form;
	private criteriaFs: Fieldset;
	private criteriaGrid: Table;
	private actionsFs: Fieldset;
	private actionsGrid: Table;
	public tests: SieveCriteriumEntity[] = [];
	public actions: SieveActionEntity[] = [];


	constructor(accountId: string) {
		super();
		this.accountId = accountId;
		this.closable = true;
		this.maximizable = true;
		this.modal = true;
		this.width = 960;

		this.criteriaGrid = table({
			store: new Store(),
			fitParent: true,
			headers: false,
			rowSelectionConfig: {
				multiSelect: true
			},
			dropOn: true,
			draggable: true,
			columns: [
				column({
					id: "test",
					renderer: (_value: any, record: SieveRuleEntity) => {
						return this.renderCriterium(record);
					}
				})
			],
			listeners: {
				rowdblclick: ({storeIndex}) => {
					const record: any = this.criteriaGrid.store.get(storeIndex);
					const win = new SieveCriteriumWindow(this.accountId!);
					void win.load(record, storeIndex);
					win.form.on("submit", ({target}) => {
						this.criteriaGrid.store.replaceAt(storeIndex, win.mangleCriterium(target.value));
						this.tests = this.criteriaGrid.store.data as SieveCriteriumEntity[];
						win.close();
					});
					win.show();
				}
			}
		});
		this.criteriaFs = fieldset({
				hidden: true,
			},
			comp({html: t("...meeting these criteria")}),
			tbar({},
				"->",
				btn({
					icon: "add",
					cls: "primary filled",
					handler: () => {
						const idx = this.criteriaGrid.store.count()
						const record: SieveCriteriumEntity = {
							id: String(idx),
							index: idx,
							test: "body",
							type: "contains",
							arg: "",
							arg1: "",
							arg2: "",
							not: false
						}
						const win = new SieveCriteriumWindow(this.accountId);
						win.load(record, idx);
						win.form.on("submit", ({target}) => {
							this.criteriaGrid.store.replaceAt(idx, win.mangleCriterium(target.value));
							this.tests = this.criteriaGrid.store.data as SieveCriteriumEntity[];
							win.close();
						});
						win.show();
					}
				}),
				btn({
					icon: "delete",
					cls: "secondary",
					handler: () => {
						this.deleteFromGrid(this.criteriaGrid);
						this.tests = this.criteriaGrid.store.data as SieveCriteriumEntity[];
					}
				})
			),
			this.criteriaGrid
		);
		this.actionsGrid = table({
			store: new Store(),
			fitParent: true,
			headers: false,
			columns: [
				column({
					id: "text"
				})
			],
			rowSelectionConfig: {
				multiSelect: true
			},
			dropOn: true,
			draggable: true,
			listeners: {
				rowdblclick: ({storeIndex}) => {
					const record: any = this.actionsGrid.store.get(storeIndex);
					const win = new SieveActionWindow(this.accountId!);
					void win.load(record, storeIndex);
					win.form.on("submit", ({target}) => {
						this.actionsGrid.store.replaceAt(storeIndex, win.mangleAction(target.value));
						this.actions = this.actionsGrid.store.data as SieveActionEntity[];
						win.close();
					});
					win.show();
				}
			}
		});
		this.actionsFs = fieldset({},
			comp({html: t("...execute the following actions")}),
			tbar({},
				"->",
				btn({
					icon: "add",
					cls: "primary filled",
					handler: () => {
						const idx = this.actionsGrid.store.count()
						const win = new SieveActionWindow(this.accountId);
						win.form.on("submit", ({target}) => {
							const record = win.mangleAction(target.value);
							record.id = String(idx);
							this.actionsGrid.store.replaceAt(idx, win.mangleAction(target.value));
							this.actions = this.actionsGrid.store.data as SieveActionEntity[];
							win.close();
						});
						win.show();
					}
				}),
				btn({
					icon: "delete",
					cls: "secondary",
					handler: () => {
						this.deleteFromGrid(this.actionsGrid);
						this.actions = this.actionsGrid.store.data as SieveActionEntity[];
					}
				})
			),
			this.actionsGrid
		);
		this.frm = form({},
			fieldset({},
				checkbox({
					type: "switch",
					label: t("Activate this filter"),
					name: "active"
				}),
				textfield({
					name: "name",
					label: t("Name"),
					required: true
				}),
				select({
					label: t("For incoming emails"),
					name: "join",
					options: [
						{name: t("that meet the following criteria"), value: "allof"},
						{name: t("that meets at least one of the following criteria"), value: "anyof"},
						{name: t("all incoming emails"), value: "any"}
					],
					listeners: {
						setvalue: ({newValue}) => {
							this.criteriaFs.hide()
							if (newValue !== "any") {
								this.criteriaFs.show();
							}
						}
					}
				})
			),
			this.criteriaFs,
			this.actionsFs,
			tbar({cls: "border-top"},
				"->",
				btn({
					type: "submit",
					text: t("Save changes")
				})
			)
		);


		this.items.add(this.frm)
	}

	public load(record: SieveRuleEntity) {
		const ruleParser = new SieveRuleParser(record);
		ruleParser.record = record;
		ruleParser.parseTests();
		ruleParser.parseActions();
		this.index = record.idx;

		this.frm.value = record;
		this.criteriaGrid.store.loadData(ruleParser.tests);
		this.actionsGrid.store.loadData(ruleParser.actions);
		this.tests = ruleParser.tests;
		this.actions = ruleParser.actions;
		this.title = `${t("Edit rule")} ${record.idx}: ${record.name}`;
	}

	private renderCriterium(record: any) {
		switch (record.test) {
			case 'currentdate':
				return this.renderCurrentdate(record.type, record.arg)
			case 'body':
				return this.renderBody(record);
			case 'header':
				return this.renderHeader(record);
			case 'exists':
				return this.renderExits(record.not, record.arg);
			case 'true':
				return 'Alle';
			case 'size':
				return this.renderSize(record.type, record.arg);
			default:
				return t("Error while displaying test line", "sieve");
		}
	}


	private renderCurrentdate(type: string, arg: string): string {
		if (type === "value-le") {
			return t("Current Date", "sieve") + ' ' + t("before", "sieve") + ' ' + arg;
		} else if (type === "is") {
			return t("Current Date", "sieve") + ' ' + t("is", "sieve") + ' ' + arg;
		} else if (type === "value-ge") {
			return t("Current Date", "sieve") + ' ' + t("after", "sieve") + ' ' + arg;
		}
		throw "Unknown type " + type;
	}


	private renderBody(record: any) {
		let s;
		if (record.type == 'contains') {
			s = record.not ? "Body doesn't contain" : "Body contains";
		} else {
			s = record.not ? "Body doesn't match" : "Body matches";
		}
		return t(s, "sieve") + ' ' + record.arg;
	}

	private renderHeader(record: any) {
		switch (record.type) {
			case "contains":
				switch (record.arg1) {
					case "Subject":
						return record.not ? t("Subject doesn't contain", "sieve") + ' ' + record.arg2 :
							t("Subject contains", "sieve") + ' ' + record.arg2;
					case "From":
						return record.not ? t("Sender doesn't contain", "sieve") + ' ' + record.arg2 :
							t("Sender contains", "sieve") + ' ' + record.arg2;
					case "To":
						return record.not ? t("Recipient doesn't contain", "sieve") + ' ' + record.arg2 :
							t("Recipient contains", "sieve") + ' ' + record.arg2;
					case "X-Spam-Flag":
						return t("Marked as spam", "sieve");
					default:
						return record.not ? t("Mailheader:", "sieve") + " " + record.arg1 + " " + t("doesn't contain", "sieve") + " " + record.arg2 :
							t("Mailheader:", "sieve") + " " + record.arg1 + " " + t("contains", "sieve") + " " + record.arg2
				}
			case "is":
				switch (record.arg1) {
					case "Subject":
						return record.not ? t("Subject is not equal to", "sieve") + ' ' + record.arg2 :
							t("Subject equals", "sieve") + ' ' + record.arg2;
					case "From":
						return record.not ? t("From is not equal to", "sieve") + ' ' + record.arg2 :
							t("From equals", "sieve") + ' ' + record.arg2;
					case "To":
						return record.not ? t("To is not equal to", "sieve") + ' ' + record.arg2 :
							t("To equals", "sieve") + ' ' + record.arg2;
					default:
						return record.not ? t("Mailheader:", "sieve") + " " + record.arg1 + " " + t("doesn't equal", "sieve") + " " + record.arg2 :
							t("Mailheader:", "sieve") + " " + record.arg1 + " " + t("equals", "sieve") + " " + record.arg2
				}
			case "matches":
				switch (record.arg1) {
					case "Subject":
						return record.not ? t("Subject doesn't match", "sieve") + ' ' + record.arg2 :
							t("Subject matches", "sieve") + ' ' + record.arg2;
					case "From":
						return record.not ? t("From doesn't match", "sieve") + ' ' + record.arg2 :
							t("From matches", "sieve") + ' ' + record.arg2;
					case "To":
						return record.not ? t("To doesn't match", "sieve") + ' ' + record.arg2 :
							t("To matches", "sieve") + ' ' + record.arg2;
					default:
						return record.not ? t("Mailheader:", "sieve") + " " + record.arg1 + " " + t("doesn't match", "sieve") + " " + record.arg2 :
							t("Mailheader:", "sieve") + " " + record.arg1 + " " + t("matches", "sieve") + " " + record.arg2
				}
			default:
				throw "Unknown type " + record.type;
		}
	}

	private renderExits(not: boolean, arg: string): string {
		if (not) {
			if (arg == 'Subject') {
				return t("Subject doesn't exist", "sieve");
			} else if (arg == 'From') {
				return t("Sender doesn't exist", "sieve");
			} else if (arg == 'To') {
				return t("Recipient doesn't exist", "sieve");
			}
			return t("Mailheader:", "sieve") + " " + arg + " " + t("doesn't exist", "sieve");
		}
		if (arg == 'Subject') {
			return t("Subject exists", "sieve");
		} else if (arg == 'From') {
			return t("Sender exists", "sieve");
		} else if (arg == 'To') {
			return t("Recipient exists", "sieve");
		} else if (arg == 'List-Unsubscribe') {
			return t("Is from mailing list", "sieve");
		}
		return t("Mailheader:", "sieve") + " " + arg + " " + t("exist", "sieve");
	}

	private renderSize(type: string, arg: string): string {
		if (type == 'under') {
			return t("Size is smaller than", "sieve") + ' ' + arg;
		}
		return t("Size is bigger than", "sieve") + ' ' + arg;
	}

	private deleteFromGrid(c: Table) {
		const selectedRows = c.rowSelection?.getSelected();
		if (selectedRows?.length === 0) {
			return;
		}
		for (const item of selectedRows!) {
			c.store.remove(item.record);
		}
	}
}