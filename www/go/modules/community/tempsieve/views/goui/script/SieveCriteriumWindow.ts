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

export class SieveCriteriumWindow extends Window {

	private form: Form;
	private formFs: Fieldset;
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

	private origRecord: SieveCriteriumEntity|undefined;

	constructor() {
		super();
		this.modal = true;
		this.resizable = true;
		this.closable = true;
		this.maximizable = true;
		this.width = 800;
		this.title = t("Set criterium");

		this.cmbField = select({
			name: "arg1",
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
				setvalue: ({newValue}) => {
					this.buildForm(newValue);;
				}
			}
		});
		this.operatorFld = select({
			name: 'type',
			value: 'contains',
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
					this.txtCriteriumFld.hidden = !newValue.endsWith("exists");
					this.txtCriteriumFld.required = !newValue.endsWith("exists");
				}
			}
		});
		this.cmbBodyOperatorFld = select({
			name: 'type',
			value: 'contains',
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
			options: [
				{name: t("before", "sieve"), value: 'value-le'}, // before
				{name: t("is", "sieve"), value: 'is'},					// is
				{name: t("after", "sieve"), value: 'value-ge'}
			]
		});
		this.dateCriteriumFld = datefield({
			name: 'arg2',
			required: true,
			disabled: true,
			hidden: true
		});
		this.txtCriteriumFld = textfield({
			name: 'arg2',
			required: true,
			disabled: true,
			hidden: true
		});
		this.numberCriteriumFld = numberfield({
			name: 'arg2',
			required: true,
			disabled: true,
			hidden: true
		});
		this.txtCustomFld = textfield({
			name: 'custom',
			placeholder: t("Custom", "legacy", "sieve"),
			label: t("Custom", "legacy", "sieve"),
			required: true,
			disabled: true,
			hidden: true
		});

		this.underOverFld = select({
			name: 'underover',
			value: 'under',
			options: [
				{name: t("Under", "sieve"), value: 'under'},
				{name: t("Over", "sieve"), value: 'over'}
			]
		});


		this.sizeGroup = radio({
			name: 'size',
			value: 'KB',
			hidden: true,
			options: [
				{text: "B", value: "B"},
				{text: "KB", value: "KB"},
				{text: "MB", value: "M"},
				{text: "GB", value: "G"},
			]
		});

		this.form = form({},
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
	}

	public load(record: SieveCriteriumEntity) {
		this.origRecord = record;
		// TODO: Refactor the code below...
		/*
		this._recordId = record.get('id');
			this.cmbOperator.store = GO.sieve.cmbOperatorStore;
			switch(record.get('test')) {

				case 'currentdate':
					this.cmbField.setValue('currentdate');
					this.cmbDateOperator.setValue(record.get('type'));
					this.dateCriterium.setValue(record.get('arg'));
					this._transForm(this.cmbField.getValue());
				break;
				case 'size':
					// We know for sure this record corresponds with a size criterium
					this.cmbField.setValue('size');
					this._transForm('size');
					// Put the Kilo/Mega/Giga scalar in the right input field
					var lastChar = record.data.arg.substr(record.data.arg.length-1,1);
					var everythingBeforeTheLastChar = record.data.arg.substr(0,record.data.arg.length-1);
					if(lastChar != 'K' && lastChar != 'M' && lastChar != 'G')
					{
						everythingBeforeTheLastChar = everythingBeforeTheLastChar+lastChar;
						lastChar = 'B';
					}
					this.cmbUnderOver.setValue(record.get('type'));
					this.numberCriterium.setValue(everythingBeforeTheLastChar);
					this.rgSize.setValue(lastChar);
					break;
				case 'exists':
					// This record can be of one of the following kinds of criteria:
					// Custom, Subject, Recipient (To), Sender (From)
					var kind = record.get('arg');
					if (kind=='Subject'||kind=='From'||kind=='To'||kind=='X-Spam-Flag'||kind=='List-Unsubscribe')
						this.cmbField.setValue(kind);
					else
						this.cmbField.setValue('custom');
					this._transForm(this.cmbField.getValue());
					this._setOperatorField(record);
					break;
				case 'header':
					// This record can be of one of the following kinds of criteria:
					// Custom, Subject, Recipient (To), Sender (From), X-Spam-Flag
					var kind = record.get('arg1');
					if (kind=='Subject'||kind=='From'||kind=='To'||kind=='X-Spam-Flag'||kind=='List-Unsubscribe')
						this.cmbField.setValue(kind);
					else
						this.cmbField.setValue('custom');
					this._transForm(this.cmbField.getValue());
					this.txtCriterium.setValue(record.get('arg2'));
					this.txtCustom.setValue(record.get('arg1'));
					this._setOperatorField(record);
					break;
				case 'body':
					this.cmbField.setValue('body');
					this._transForm(this.cmbField.getValue());
					this.txtCriterium.setValue(record.get('arg'));
					this._setOperatorField(record);
					break;
			}
		 */
		this.cmbField.value = record.test;

	}

	private buildForm(value: string) {
		this.formFs.items.clear();
		switch (value) {
			case "size":
				this.formFs.items.add(this.numberCriteriumFld, this.underOverFld, this.sizeGroup);
				break;
			case "body":
				this.formFs.items.add(this.cmbBodyOperatorFld);
				if(this.operatorFld.value !== "exists" && this.operatorFld.value !== "notexists") {
					this.formFs.items.add(this.txtCriteriumFld);
				}
				break;
			case "From":
			case "To":
			case "Subject":
				this.items.add(this.operatorFld);
				if(this.operatorFld.value !== "exists" && this.operatorFld.value !== "notexists") {
					this.formFs.items.add(this.txtCriteriumFld);
				}
				break;
			case "custom":
				this.formFs.items.add(this.txtCustomFld,this.operatorFld);
				if (this.operatorFld.value !== "exists" && this.operatorFld.value !== "notexists") {
					this.formFs.items.add(this.txtCriteriumFld);
				}
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
}