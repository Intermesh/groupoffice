import {
	btn,
	datefield,
	DateField,
	fieldset,
	Fieldset,
	Form,
	form,
	numberfield,
	NumberField,
	radio,
	RadioField,
	select,
	SelectField,
	t,
	tbar,
	textfield,
	TextField,
	Window
} from "@intermesh/goui";
import {SieveCriteriumEntity} from "./Index";
import {SieveRuleWindow} from "./SieveRuleWindow";

export class SieveCriteriumWindow extends Window {

	private form: Form;
	private formFs: Fieldset;
	private testFld: TextField;
	private cmbField: SelectField;
	private operatorFld: SelectField;
	private cmbBodyOperatorFld: SelectField;
	private cmbDateOperatorFld: SelectField;
	private dateCriteriumFld: DateField;
	private txtCriteriumFld: TextField;
	private numberCriteriumFld: NumberField;
	private txtCustomFld: TextField;
	private underOverFld: SelectField;
	private sizeGroup: RadioField;
	private accountId: string;
	private origRecord: SieveCriteriumEntity | undefined;
	public itemIndex: number | undefined;

	constructor(accountId: string) {
		super();
		this.modal = true;
		this.resizable = true;
		this.closable = true;
		this.maximizable = false;
		this.width = 800;
		this.title = t("Set criterium");
		this.accountId = accountId;
		this.form = form({
				cls: "flow",
				handler: form1 => {
					const crit = this.mangleCriterium(form1.value);

				}
			},
			this.formFs = fieldset({}),
			tbar({
					cls: "border-top"
				},
				"->",
				btn({
					type: "submit",
					text: t("Save")
				})
			)
		);
		this.items.add(this.form);

		this.testFld = textfield({
			name: "test",
			hidden: true
		});

		this.cmbField = select({
			name: "arg1",
			required: true,
			options: [
				{name: t("subject", "sieve"), value: 'Subject'},
				{name: t("sender", "sieve"), value: 'From'},
				{name: t("recipient", "sieve"), value: 'To'},
				{name: t("size", "sieve"), value: 'size'},
				{name: t("Body", "sieve"), value: 'body'},
				{name: t("Spam flag", "sieve"), value: 'X-Spam-Flag'},
				{name: t("Mailing list", "sieve"), value: 'List-Unsubscribe'},
				{name: t("Current Date", "sieve"), value: 'currentdate'},
				{name: t("Custom", "sieve"), value: 'custom'}
			],
			listeners: {
				change: ({newValue}) => {
					this.buildForm(newValue);
				}
			}
		});
		this.operatorFld = select({
			name: 'type',
			value: 'contains',
			required: true,
			options: [
				{name: t("contains", "sieve"), value: 'contains'},
				{name: t("doesn't contain", "sieve"), value: 'notcontains'},
				{name: t("is", "sieve"), value: 'is'},
				{name: t("doesn't equal", "sieve"), value: 'notis'},
				{name: t("matches", "sieve"), value: 'matches'},
				{name: t("doesn't match", "sieve"), value: 'notmatches'},
				{name: t("exists", "sieve"), value: 'exists'},
				{name: t("doesn't exist", "sieve"), value: 'notexists'}
			],
			listeners: {
				setvalue: ({newValue}) => {
					if (!this.form.findChild(this.txtCriteriumFld)) {
						return;
					}
					this.txtCriteriumFld.hidden = newValue.indexOf("exist") > -1;
					this.txtCriteriumFld.required = newValue.indexOf("exist") > -1;
				}
			}
		});
		this.cmbBodyOperatorFld = select({
			name: 'type',
			value: 'contains',
			required: true,
			options: [
				{name: t("contains", "sieve"), value: 'contains'},
				{name: t("doesn't contain", "sieve"), value: 'notcontains'},
				{name: t("matches", "sieve"), value: 'matches'},
				{name: t("doesn't match", "sieve"), value: 'notmatches'}
			]
		});
		this.cmbDateOperatorFld = select({
			name: 'type',
			value: 'is',
			required: true,
			options: [
				{name: t("before", "sieve"), value: 'value-le'}, // before
				{name: t("is", "sieve"), value: 'is'},					// is
				{name: t("after", "sieve"), value: 'value-ge'}
			]
		});
		this.dateCriteriumFld = datefield({
			name: 'arg2',
			required: true
		});
		this.txtCriteriumFld = textfield({
			name: 'arg2',
			value: "YES",
			required: true
		});
		this.numberCriteriumFld = numberfield({
			name: 'arg2',
			required: true
		});
		this.txtCustomFld = textfield({
			name: 'custom',
			placeholder: t("Custom", "legacy", "sieve"),
			label: t("Custom", "legacy", "sieve"),
			required: true
		});

		this.underOverFld = select({
			name: 'underover',
			value: 'under',
			required: true,
			options: [
				{name: t("Under", "sieve"), value: 'under'},
				{name: t("Over", "sieve"), value: 'over'}
			]
		});


		this.sizeGroup = radio({
			name: 'size',
			value: 'KB',
			required: true,
			options: [
				{text: "B", value: "B"},
				{text: "KB", value: "KB"},
				{text: "MB", value: "M"},
				{text: "GB", value: "G"},
			]
		});
	}

	public load(record: SieveCriteriumEntity, idx: number) {
		console.log(record);
		this.origRecord = record;
		this.itemIndex = idx;
		this.buildForm(record.test === "header" ? record.arg1 : record.test);

		let hdrType;
		switch (record.test) {
			case 'currentdate':
				this.cmbDateOperatorFld.value = record.type;
				this.dateCriteriumFld.value = record.arg;
				break;
			case "size":
				// Put the Kilo/Mega/Giga scalar in the right input field
				const arg = record.arg;
				let lastChar = arg.slice(-1),
					everythingBeforeTheLastChar = arg.slice(0, arg.length - 1);
				if (lastChar != 'K' && lastChar != 'M' && lastChar != 'G') {
					everythingBeforeTheLastChar = everythingBeforeTheLastChar + lastChar;
					lastChar = 'B';
				}
				this.underOverFld.value = record.type;
				this.numberCriteriumFld.value = everythingBeforeTheLastChar;
				this.sizeGroup.value = lastChar;
				break;
			case "exists":
				hdrType = record.arg;
				if (["Subject", "From", "To", "X-Spam-Flag", "List-Unsubscribe"].includes(hdrType)) {
					this.cmbField.value = hdrType;
				} else {
					this.cmbField.value = "custom";
				}
				this.setOperator(record);
				break;
			case "header":
				hdrType = record.arg1;
				if (["Subject", "From", "To", "X-Spam-Flag", "List-Unsubscribe"].includes(hdrType)) {
					this.cmbField.value = hdrType;
				} else {
					this.cmbField.value = "custom";
				}
				this.txtCriteriumFld.value = record.arg2;
				this.setOperator(record);
				break;
			case "body":
				this.txtCriteriumFld.value = record.arg;
				this.setOperator(record);
				break;
			default:
				throw "Unknown test: " + record.test;
		}
		this.testFld.value = record.test;
	}

	private setOperator(record: SieveCriteriumEntity) {
		const not = record.not;
		switch (record.type) {
			case 'contains':
				this.operatorFld.value = not ? "notcontains" : "contains";
				this.cmbDateOperatorFld.value = not ? "notmatches" : "matches";
				break;
			case 'matches':
				this.operatorFld.value = not ? "notmatches" : "matches";
				this.cmbBodyOperatorFld.value = not ? "notmatches" : "matches";
				break;
			case 'is':
				this.operatorFld.value = not ? 'notis' : "is";
				break;
			default:
				this.operatorFld.value = not ? "notexists" : "exists";
				break;

		}
	}

	private buildForm(value: string) {
		this.formFs.items.clear();
		this.formFs.items.add(this.testFld, this.cmbField);

		switch (value) {
			case "size":
				this.formFs.items.add(this.numberCriteriumFld, this.underOverFld, this.sizeGroup);
				break;
			case "body":
				this.formFs.items.add(this.cmbBodyOperatorFld, this.txtCriteriumFld);
				break;
			case "From":
			case "To":
			case "Subject":
				this.formFs.items.add(this.operatorFld, this.txtCriteriumFld);
				break;
			case "custom":
				this.formFs.items.add(this.txtCustomFld, this.operatorFld, this.txtCriteriumFld);
				break;
			case "currentdate":
				this.formFs.items.add(this.cmbDateOperatorFld, this.dateCriteriumFld);
				break;
			case "List-Unsubscribe":
			case "X-Spam-Flag":
			default:
				break;
		}
	}

	/**
	 * Convert form data into something that can be shot into the API as Sieve rules
	 *
	 * @param values
	 * @private
	 */
	private mangleCriterium(values: any): SieveCriteriumEntity {
		const crit: SieveCriteriumEntity = {
			id: "crit_" + this.itemIndex,
			not: false,
			type: "",
			test: "",
			arg: "",
			arg1: "",
			arg2: "",
			part: ""
		};

		Object.assign(crit, values);
		if (crit.type === 'exists' || crit.type === 'notexists' || crit.arg1 === 'X-Spam-Flag' || crit.arg1 == 'List-Unsubscribe') {
			crit.arg2 = 'sometext';
		}

		if (crit.arg2 !== '') {
			switch (crit.arg1) {
				case 'custom':
					crit.not = crit.type.startsWith("not");
					if (crit.type.endsWith("xists")) {
						crit.test = 'exists';
						crit.arg = this.txtCustomFld.value;
						crit.arg1 = "";
						crit.arg2 = "";
					} else {
						crit.test = 'header';
						crit.arg = '';
						crit.arg1 = this.txtCustomFld.value;
						crit.arg2 = this.txtCriteriumFld.value;
					}
					crit.type = this.parseTypeField(crit.type);
					break;
				case 'List-Unsubscribe':
					crit.test = 'exists';
					crit.not = false;
					// crit.type = this.cmbOperator.getValue(); // Should be sorted
					crit.arg = 'List-Unsubscribe';
					crit.arg1 = '';
					crit.arg2 = '';
					break;
				case 'X-Spam-Flag':
					crit.test = 'header';
					crit.not = false;
					// type	= this.cmbOperator.getValue();
					crit.arg = '';
					crit.arg1 = 'X-Spam-Flag';
					crit.arg2 = 'YES';
					break;
				case 'body':
					crit.test = 'body';
					crit.not = crit.type.startsWith("not");
					crit.type = crit.type.indexOf("contains") ? "contains" : "matches";
					crit.arg = crit.arg2;
					crit.arg1 = '';
					crit.arg2 = '';
					break;
				default:
					crit.not = crit.type.startsWith("not");
					if (crit.type.endsWith("xists")) {
						crit.test = 'exists';
						crit.type = '';
						crit.arg = crit.arg1;//this.cmbField.getValue();
						crit.arg1 = '';
						crit.arg2 = '';
					} else {
						crit.test = 'header';
						crit.arg = '';
						// crit.arg1 = this.cmbField.getValue();
						// crit.arg2 = this.txtCriterium.getValue();
						crit.type = this.parseTypeField(crit.type);
					}
					break;
			}
		} else if (crit.arg1 == 'size') {
			crit.test = 'size';
			crit.type = this.sizeGroup.value as string;
			crit.arg1 = '';
			crit.arg2 = '';
			crit.arg = this.numberCriteriumFld.value;
			if (this.sizeGroup.value !== "B") {
				crit.arg += String(this.sizeGroup.value);
			}
		} else if (crit.arg1 === "currentdate") {
			crit.test = "currentdate";
			crit.not = false;
			crit.arg = this.dateCriteriumFld.value;
			crit.part = "date";
			crit.arg1 = '';
			crit.arg2 = '';
			crit.type = this.cmbDateOperatorFld.value! as string;
		}
		console.log(crit);

		return crit;
	}

	private parseTypeField(type: string): string {
		if (type.endsWith("ontains")) {
			return "contains";
		}
		if (type.endsWith("matches")) {
			return "matches";
		}
		if (type.endsWith("is")) {
			return "is";
		}
		return "";
	}
}