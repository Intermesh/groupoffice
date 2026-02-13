import {FormWindow, groupDS} from "@intermesh/groupoffice-core";
import {
	ArrayField,
	arrayfield, autocompletechips,
	btn, checkbox, checkboxselectcolumn, column,
	comp,
	containerfield, datasourcestore,
	fieldset,
	numberfield, select,
	t, table,
	tbar, TextField,
	textfield
} from "@intermesh/goui";

export class ImapAuthServerDialog extends FormWindow {
	private arrayFld: ArrayField;
	private smtpUserNameFld: TextField;
	private smtpPasswordFld: TextField;

	constructor() {
		super("ImapAuthServer");
		this.resizable = true;
		this.closable = true;
		this.maximizable = true;
		this.width = 800

		const imapServerFs = fieldset({
				legend: t("IMAP Server")
			},
			comp({html: t("Enter the domains this imap server should be used to authenticate. Users must login with their e-mail address and if the domain matches this profile it will be used.", "communty", "imapauthenticator")}),
			comp({html: t("Use a '*' to match all domains.", "communty", "imapauthenticator")}),
			this.arrayFld = arrayfield({
				required: true,
				name: "domains",
				label: t("Domains"),
				buildField: () => {
					const field = containerfield({
							cls: "group",
						},

						textfield({
							flex: 1,
							label: t("Name"),
							name: "name",
							placeholder: "example.com",
							required: true
						}),

						btn({
							icon: "delete",
							title: "Delete",
							handler: (btn) => {
								field.remove();
							}
						})
					);

					return field;
				}
			}),
			tbar({},
				'->',
				btn({
					icon: "add",
					cls: "primary filled",
					handler: () => {
						this.arrayFld.addValue({
							name: ""
						});
					}
				})
			),
			textfield({
				name: "imapHostname",
				label: t("Hostname"),
				required: true
			}),
			numberfield({
				decimals: 0,
				name: "imapPort",
				label: t("Port"),
				required: true,
				value: 143
			}),
			select({
				name: "imapEncryption",
				label: t("Encryption"),
				value: "tls",
				options: [
					{value: "tls", name: "TLS"},
					{value: "ssl", name: "SSL"},
					{value: null, name: t("None")}
				],
				listeners: {
					change: ({newValue}) => {
						this.form.findField("imapValidateCertificate")!.disabled = (newValue == null)
					}
				}
			}),
			checkbox({
				type: "switch",
				name: "imapValidateCertificate",
				label: t("Validate certificate")
			}),
			checkbox({
				type: "switch",
				name: "removeDomainFromUsername",
				label: t("Remove domain from username", "community", "imapauthenticator"),
				hint: t("Users must login with their full e-mail adress. Enable this option if the IMAP expects. the username without domain.", "community", "imapauthenticator")
			})
		);

		this.smtpUserNameFld = textfield({
			name: 'smtpUsername',
			label: t('Username'),
		});
		this.smtpPasswordFld = textfield({
			type: "password",
			name: 'smtpPassword',
			label: t('Password'),
		});

		const smtpServerFs = fieldset({
				legend: t("SMTP Server")
			},
			textfield({
				name: "smtpHostname",
				label: t("Hostname"),
			}),
			numberfield({
				decimals: 0,
				name: "smtpPort",
				label: t("Port"),
				value: 587
			}),
			select({
				name: "smtpEncryption",
				label: t("Encryption"),
				value: "tls",
				options: [
					{value: "tls", name: "TLS"},
					{value: "ssl", name: "SSL"},
					{value: null, name: t("None")}
				],
				listeners: {
					change: ({newValue}) => {
						this.form.findField("smtpValidateCertificate")!.disabled = (newValue == null)
					}
				}
			}),
			checkbox({
				type: "switch",
				name: "smtpValidateCertificate",
				label: t("Validate certificate")
			}),
			checkbox({
				type: "switch",
				label: t("Use IMAP credentials"),
				hint: t("Enable this if the SMTP server credentials are identical to the IMAP server."),
				value: true,
				listeners: {
					setvalue: ({newValue, oldValue}) => {
						this.smtpUserNameFld.disabled = newValue;
						this.smtpUserNameFld.hidden = newValue;
						this.smtpPasswordFld.disabled = newValue;
						this.smtpPasswordFld.hidden = newValue;
					}
				}
			}),
			this.smtpUserNameFld,
			this.smtpPasswordFld

		);

		const userOptionsFs = fieldset({
				legend: t("User Options")
			},
			autocompletechips({
				name: "groups",
				label: t("Groups"),
				list: table({
					fitParent: true,
					headers: false,
					store: datasourcestore({
						dataSource: groupDS,
						queryParams: {
							limit: 50
						}
					}),
					rowSelectionConfig: {
						multiSelect: true
					},
					columns: [
						checkboxselectcolumn({
							id: "id"
						}),
						column({
							header: "Name",
							id: "name",
							sortable: true,
							resizable: true
						})
					]
				}),
				hint: t("Users will automatically be added to these groups"),
				chipRenderer: async (chip, value) => {
					const record = await groupDS.single(value.groupId ? value.groupId : value);
					chip.text = record.name;
				},
				pickerRecordToValue(field, record): any {
					return record.id;
				},
				listeners: {
					autocomplete: ({target, input}) => {
						target.list.store.setFilter("search", {text: input});
						void target.list.store.load();
					}
				}
			})
		);

		this.generalTab.items.add(imapServerFs, smtpServerFs, userOptionsFs);

		this.form.on("beforesave", ({data}) => {
			const currentId = this.form.currentId;

			if(data.groups) {
				const arGrp = data.groups;
				delete data.groups;
				data.groups = [];
				for (const groupId of arGrp) {
					data.groups.push({serverId: currentId, groupId: groupId});
				}
			}
		});
	}
}