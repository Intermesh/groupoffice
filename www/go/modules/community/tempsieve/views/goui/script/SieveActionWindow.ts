import {
	btn,
	Fieldset,
	fieldset,
	form,
	Form, NumberField, numberfield,
	select,
	SelectField,
	t,
	tbar, textarea, TextAreaField,
	TextField,
	textfield,
	Window
} from "@intermesh/goui";
import {SieveActionEntity} from "@intermesh/community/tempsieve";

export class SieveActionWindow extends Window {
	private origRecord: SieveActionEntity | undefined;
	private itemIndex: number | undefined;
	private accountId: string;
	public form: Form;
	private formFs: Fieldset
	private cmbAction: SelectField;
	private readonly cmbFolder: SelectField;
	private readonly emailAddressFld: TextField;
	private readonly optionalEmailAddressFld: TextField;
	private readonly subjectFld: TextField;
	private readonly messageFld: TextAreaField;
	private readonly daysFld: NumberField;

	constructor(accountId: string) {
		super();
		this.modal = true;
		this.resizable = true;
		this.closable = true;
		this.maximizable = false;
		this.width = 800;
		this.title = t("Set action");
		this.accountId = accountId;
		this.form = form({
				cls: "flow",
			},
			fieldset({},
				this.cmbAction = select({
					name: "type",
					label: t("Action", "community", "email"),
					required: true,
					options: [
						{name: t("Mark message as read",), value: 'addflag'},
						{name: t("Move email to selected folder"), value: 'fileinto'},
						{name: t("Copy email to selected folder"), value: 'fileinto_copy'},
						{name: t("Copy to e-mail"), value: 'redirect_copy'},
						{name: t("Redirect to"), value: 'redirect'},
						{name: t("Reply to message"), value: 'vacation'},
						{name: t("Reject with message"), value: 'reject'},
						{name: t("Discard"), value: 'discard'},
						{name: t("Stop"), value: 'stop'}
					],
					listeners: {
						change: ({newValue}) => {
							this.buildForm(newValue);
						}
					}
				})
			),
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

		this.cmbFolder = select({
			name: "target",
			label: t("To folder"),
			required: true
		});

		this.optionalEmailAddressFld = textfield({
			name: 'email',
			required: false,
			type: "email",
			label: t("Activate also for these aliases (separated by comma)")
		});

		this.emailAddressFld = textfield({
			name: 'email',
			type: "email",
			label: t("E-mail"),
			required: true
		});


		this.subjectFld = textfield({
			required: false,
			label: t("Subject"),
			name: "subject"
		});

		this.messageFld = textarea({
			name: "message",
			required: true,
			label: t("Message")
		});

		this.daysFld = numberfield({
			name: "days",
			decimals: 0,
			min: 1,
			value: 7,
			label: t("Reply every x days")
		});

		// Temporary: retrieve all folders for current account ID through the old email module. The result is to be used
		// as the options for the folder select box.
		// TODO: When merging with new email module, replace with proper JMAP call
		GO.request({
			timeout: 300000,
			url: GO.url("email/folder/store"),
			params: {
				account_id: accountId
			},
			success: (_response: any, _options: any, result: any) => {
				const folderOptions = [];
				for (const item of result.results) {
					folderOptions.push({
						name: item.name,
						value: item.name
					});
				}
				this.cmbFolder.options = folderOptions;
			}
		});

	}

	public load(record: SieveActionEntity, idx: number) {
		this.origRecord = record;
		this.itemIndex = idx;
		this.buildForm(record.type);
		this.form.value = record;
	}

	private buildForm(type: string) {

		this.formFs.items.clear();
		switch (type) {
			case 'fileinto':
			case 'fileinto_copy':
				this.formFs.items.add(this.cmbFolder);
				break;
			case 'redirect':
			case 'redirect_copy':
				this.formFs.items.add(this.emailAddressFld);
				break;
			case 'reject':
				this.formFs.items.add(this.messageFld)
				break;
			case 'vacation':
				this.formFs.items.add(this.optionalEmailAddressFld, this.daysFld, this.subjectFld, this.messageFld);
				break
			case 'addflag':
			case 'discard':
			case 'stop':
				break;
			default:
				throw "Unknow action " + type;
		}
	}

	public mangleAction(values: any): SieveActionEntity {
		// Build up the data before adding the data to the grid.
		let type = '',
			target = '',
			days = '',
			addresses = '',
			reason = '',
			subject = '',
			text = '';

		switch (this.cmbAction.value) {
			case 'addflag':
				type = 'addflag';
				target = '\\Seen';
				text = t("Mark message as read", "sieve");
				break;
			case 'fileinto':
				type = 'fileinto';
				target = this.cmbFolder.value as string;
				text = t("Move email to the folder", "sieve") + ': ' + target;
				break;
			case 'fileinto_copy':
				type = 'fileinto_copy';
				target = this.cmbFolder.value as string;
				text = t("Copy email to the folder", "sieve") + ': ' + target;
				break;
			case 'redirect':
				type = 'redirect';
				target = this.emailAddressFld.value;
				text = t("Redirect to", "sieve") + ': ' + target;
				break;
			case 'redirect_copy':
				type = 'redirect_copy';
				target = this.emailAddressFld.value;
				text = t("Send a copy to", "sieve") + ': ' + target;
				break;
			case 'reject':
				type = 'reject';
				target = this.messageFld.value as string;
				text = t("Reject with message", "sieve") + ': "' + target + '"';
				break;
			case 'vacation':
				type = 'vacation';
				target = '';
				days = String(this.daysFld.value); // Y THO?
				addresses = this.optionalEmailAddressFld.value as string;
				reason = this.messageFld.value as string;
				subject = this.subjectFld.value as string;
				let addressText;
				if (addresses.length > 0) {
					addressText = t("Autoreply is active for", "sieve") + ': ' + addresses + '. ';
				}
				text = `${t("Reply every", "sieve")} ${days} ${t("day(s)", "sieve")}. ${addressText} ${t("Message:", "sieve")} "${reason}"`;
				break;
			case 'discard':
				type = 'discard';
				target = '';
				text = t("Discard", "sieve");
				break;
			case 'stop':
				type = 'stop';
				target = '';
				text = t("Stop", "sieve");
				break;
			default:
				break;
		}

		return {
			id: String(this.itemIndex),
			type: type,
			target: target,
			days: days,
			reason: reason,
			subject: subject === '' ? null : subject,
			addresses: addresses,
			text: text
		};
	}
}