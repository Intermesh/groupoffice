import {Checkbox, FormWindow, groupDS} from "@intermesh/groupoffice-core";
import {
	ArrayField,
	arrayfield,
	autocompletechips, btn, checkbox, CheckboxField,
	checkboxselectcolumn,
	column,
	comp, containerfield,
	datasourcestore,
	fieldset, NumberField, numberfield, select,
	t,
	table, tbar, textarea, TextField, textfield
} from "@intermesh/goui";

export class LdapAuthServerDialog extends FormWindow {
	private arrayFld: ArrayField;
	private ldapUserNameFld: TextField;
	private ldapPasswordFld: TextField;
	private ldapUseAuthCb: CheckboxField;
	private createEmailCheckbox: CheckboxField;
	private smtpUsernameFld: TextField;
	private smtpPasswordFld: TextField;
	private syncUsersDeleteCb: CheckboxField;
	private syncUsersDelePercFld: NumberField;
	private syncGroupsDelePercFld: NumberField;
	private syncGroupsDeleteCb: CheckboxField;

	constructor() {
		super("LdapAuthServer");
		this.resizable = true;
		this.closable = true;
		this.maximizable = true;
		this.width = 800;

		this.ldapUserNameFld = textfield({
			name: 'username',
			label: t('Username'),
			autocomplete: "new-password",
			placeholder: "cn=Administrator,dc=com"
		});
		this.ldapPasswordFld = textfield({
			name: 'password',
			disabled: true,
			label: t('Password'),
			type: "password",
			autocomplete: "new-password"
		});

		const imapFs = fieldset({
				legend: t("IMAP Server", "community", "ldapauthenticator"),
			},
			checkbox({
				type: "switch",
				label: t("Use e-mail instead of LDAP username as IMAP username", "community", "ldapauthenticator"),
				name: "imapUseEmailForUsername"
			}),
			textfield({
				name: "imapHostname",
				label: t("Imap Hostname"),
				required: true,
			}),
			numberfield({
				decimals: 0,
				name: "imapPort",
				label: t("Port"),
				value: 143
			}),
			select({
				name: "imapEncryption",
				label: t("Encryption"),
				value: "tls",
				options: [
					{value: "tls", name: "TLS"},
					{value: "ssl", name: "SSL"},
					{value: "", name: t("None")}
				]
			}),
			checkbox({
				type: "switch",
				name: 'imapValidateCertificate',
				label: t("Validate certicate"),
				value: true
			}),
		);

		this.smtpUsernameFld = textfield({
			name: "smtpUsername",
			label: t("Username")
		});
		this.smtpPasswordFld = textfield({
			name: "smtpPassword",
			label: t("Password"),

		})
		const smtpFs = fieldset({
				legend: t("SMTP Server", "community", "ldapauthenticator"),
			},
			textfield({
				name: "smtpHostname",
				label: t("SMTP Hostname"),
			}),
			numberfield({
				decimals: 0,
				name: "smtpPort",
				label: t("Port"),
				value: 587
			}),
			checkbox({
				type: "switch",
				label: t("Use user credentials", "community", "ldapauthenticator"),
				name: "smtpUseUserCredentials",
				hint: t("Enable this if the SMTP server credentials are identical to the IMAP server.", "community", "ldapauthenticator"),
				listeners: {
					setvalue: ({newValue, oldValue}) => {
						this.smtpUsernameFld.hidden = !newValue;
						this.smtpPasswordFld.hidden = !newValue;
						this.smtpUsernameFld.required = newValue;
						this.smtpPasswordFld.required = newValue;
						this.smtpUsernameFld.disabled = !newValue;
						this.smtpPasswordFld.disabled = !newValue;
					}
				}
			}),
			this.smtpUsernameFld,
			this.smtpPasswordFld,

			select({
				name: "smtpEncryption",
				label: t("Encryption"),
				value: "tls",
				options: [
					{value: "tls", name: "TLS"},
					{value: "ssl", name: "SSL"},
					{value: "", name: t("None")}
				]
			}),
			checkbox({
				type: "switch",
				name: 'smtpValidateCertificate',
				label: t("Validate certicate"),
				value: true
			}),
		);

		const ldapServerFs = fieldset({
				legend: t("LDAP Server", "community", "ldapauthenticator"),
			},
			comp({html: t("Enter the domains this imap server should be used to authenticate. Users must login with their e-mail address and if the domain matches this profile it will be used.", "communty", "imapauthenticator")}),
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
				name: "hostname",
				label: t("Hostname"),
				required: true
			}),
			numberfield({
				required: true,
				label: t("Port"),
				name: "port",
				value: 389,
				decimals: 0
			}),
			select({
				name: "encryption",
				label: t("Encryption"),
				value: "tls",
				options: [
					{value: "tls", name: "TLS"},
					{value: "ssl", name: "SSL"},
					{value: "", name: t("None")}
				]
			}),
			checkbox({
				type: "switch",
				name: 'ldapVerifyCertificate',
				label: t("Verify SSL certicate"),
				value: true
			}),
			this.ldapUseAuthCb = checkbox({
				type: "switch",
				label: t('Use authentication', 'ldapauthenticator'),
				name: 'ldapUseAuthentication',
				hint: t("Enable this if the LDAP server requires authentication to lookup users or groups"),
				listeners: {
					setvalue: ({newValue, oldValue}) => {
						this.ldapUserNameFld.disabled = !newValue;
						this.ldapPasswordFld.disabled = !newValue;
					}
				}
			}),
			this.ldapUserNameFld,
			this.ldapPasswordFld,
			checkbox({
				type: "switch",
				label: t("Follow referrals"),
				name: 'followReferrals',
				value: true,
				hint: t("For older Microsoft ActiveDirectory installation this has to be disabled")
			})
		);

		const userFs = fieldset({
				legend: t("Users")
			},
			textfield({
				name: 'usernameAttribute',
				label: t("Username attribute"),
				value: "uid",
				required: true,
				hint: t("Use 'samaccountname' for Microsoft ActiveDirectory.")
			}),
			checkbox({
				type: "switch",
				name: 'loginWithEmail',
				label: t("Login with e-mail address")

			}),
			textarea({
				name: 'syncUsersQuery',
				label: t("User query"),
				required: true,
				value: "(objectClass=InetOrgPerson)",
				hint: t("For Microsoft ActiveDirectory use '(objectCategory=InetOrgPerson)'")
			}),
			textfield({
				name: "peopleDN",
				required: true,
				value: "ou=people,dc=example,dc=com",
				hint: t("For Microsoft ActiveDirectory it's typically 'cn=Users,dc=example,dc=com'."),
			}),
			textfield({
				name: 'groupsDN',
				label: "groupsDN",
				required: true,
				value: "ou=people,dc=example,dc=com",
				hint: t("For Microsoft ActiveDirectory it's typically 'cn=Groups,dc=example,dc=com'."),
			}),
			this.createEmailCheckbox = checkbox({
				type: "switch",
				label: t("Create e-mail account for users"),
				name: 'createUserEmail',
				listeners: {
					setvalue: ({newValue, oldValue}) => {
						imapFs.hidden = !newValue;
						smtpFs.hidden = !newValue;

						if (newValue) {
							// TODO?
						}
					}
				}
			})
		);


		const userOptionsFs = fieldset({
				legend: t("User Options", "community", "ldapauthenticator"),
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

		/*
		 {
					xtype: 'textarea',
					grow: true,
					name: 'syncGroupsQuery',
					fieldLabel: t("Group query"),
					required: true,
					value: "(objectClass=Group)",
					hint: t("For Microsoft ActiveDirectory use '(objectCategory=group)'")
				}
			]
		 */
		this.syncUsersDelePercFld = numberfield({
			name: "syncUsersMaxDeletePercentage",
			label: t("Max delete percentage", "community", "ldapauthenticator"),
			value: 5,
			decimals: 0
		});
		this.syncUsersDeleteCb = checkbox({
			type: "switch",
			label: t("Delete users"),
			name: "syncUsersDelete",
			listeners: {
				setvalue: ({newValue, oldValue}) => {
					this.syncUsersDelePercFld.hidden = !newValue;
				}
			}
		});
		this.syncGroupsDelePercFld = numberfield({
			name: "syncGroupsMaxDeletePercentage",
			label: t("Max delete percentage", "community", "ldapauthenticator"),
			value: 5,
			decimals: 0
		});
		this.syncGroupsDeleteCb = checkbox({
			type: "switch",
			label: t("Delete groups"),
			name: "syncGroupsDelete",
			listeners: {
				setvalue: ({newValue, oldValue}) => {
					this.syncGroupsDelePercFld.hidden = !newValue;
				}
			}
		});

		const syncFs = fieldset({
				legend: t("Synchronization")
			},
			checkbox({
				type: "switch",
				value: false,
				label: t('Synchronize users'),
				name: 'syncUsers',
				listeners: {
					setvalue: ({newValue, oldValue}) => {
						this.syncUsersDeleteCb.value = !newValue;
						this.syncUsersDeleteCb.hidden = !newValue;
					}
				}
			}),
			this.syncUsersDeleteCb,
			this.syncUsersDelePercFld,
			checkbox({
				type: "switch",
				value: false,
				label: t('Synchronize groups'),
				name: 'syncGroups',
				listeners: {
					setvalue: ({newValue, oldValue}) => {
						this.syncGroupsDeleteCb.value = !newValue;
						this.syncGroupsDeleteCb.hidden = !newValue
					}
				}
			}),
			this.syncGroupsDeleteCb,
			this.syncGroupsDelePercFld,
			textarea({
				name: "syncGroupsQuery",
				label: t("Group query"),
				required: true,
				value: "(objectClass=Group)",
				hint: t("For Microsoft ActiveDirectory use '(objectCategory=group)'")
			})
		);

		this.generalTab.items.add(ldapServerFs, userFs, imapFs, smtpFs, userOptionsFs, syncFs);

		this.form.on("load", ({data}) => {
			// debugger;
			this.ldapUseAuthCb.value = true;
			if (data.username && data.password) {
				this.ldapUseAuthCb.value = true;
			}
		})
	}
}