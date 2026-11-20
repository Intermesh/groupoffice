import {checkbox, ComboBox, combobox, comp, ContainerField, containerfield, datasourceform, DataSourceForm, fieldset, t} from "@intermesh/goui";
import {AclLevel, AppSettingsPanel, jmapds, User, userDS} from "@intermesh/groupoffice-core";

export class UserSettingsPanel extends AppSettingsPanel {
	private readonly form: DataSourceForm<User>
	private readonly accountCombo: ComboBox
	private readonly syncSettingsField: ContainerField

	constructor() {
		super();

		this.title = t("E-mail");
		this.icon = "email";

		this.form = datasourceform({
				dataSource: userDS
			},
			containerfield({
					name: "emailSettings"
				},

				fieldset({cls: "vbox gap flow"},
					comp({flex: 1},
						checkbox({
							name: "use_html_markup",
							label: t("Use HTML markup")
						}),
						checkbox({
							name: "show_from",
							label: t("Show from field by default")
						}),
						checkbox({
							name: "show_cc",
							label: t("Show CC field by default")
						}),
						checkbox({
							name: "show_bcc",
							label: t("Show BCC field by default")
						}),
						checkbox({
							name: "email_show_linked_tasks",
							label: t("Display linked Tasks in messages list")
						})
					),
					comp({flex: 1},
						checkbox({
							name: "use_desktop_composer",
							label: t("Use desktop email client to compose")
						}),
						checkbox({
							name: "skip_unknown_recipients",
							label: t("Don't show unknown recipients dialog")
						}),
						checkbox({
							name: "always_request_notification",
							label: t("Always request a read notification")
						}),
						checkbox({
							name: "always_respond_to_notifications",
							label: t("Always respond to a read notification")
						}),
						checkbox({
							name: "sort_email_addresses_by_time",
							label: t("Sort on last contact mail time")
						}),
						checkbox({
							name: "notify_new_email",
							label: t("Create desktop notification for new e-mails")
						})
					)
				),

			),

			this.syncSettingsField = containerfield({name: "syncSettings", hidden: true},

				fieldset({legend: t("ActiveSync")},
					this.accountCombo = combobox({
						label: t("E-Mail Account"),
						flex: 1,
						name: "account_id",
						displayProperty: "username",
						dataSource: jmapds("Account")
					}),
					checkbox({
						name: "allowDeletes",
						label: t("Allow delete via synchronization", "legacy", "sync"),
						hint: t("By default devices are not allowed to delete items. This was added due to a bug in Android 12 that deleted contacts after an update.", "legacy", "sync")
					})
				)

			)
		);

		this.items.add(this.form);
	}

	async save() {
		return this.form.submit();
	}

	async load(user: User) {
		// Only accounts that the user may write can be used for sync
		this.accountCombo.filter = {permissionLevel: AclLevel.WRITE, permissionLevelUserId: user.id};

		this.form.currentId = user.id;
		// The user only has sync settings when the sync module is installed
		this.syncSettingsField.hidden = !user.syncSettings;
		if(user.syncSettings) {
			// The sync account is 0 when it's not set
			user = {...user, syncSettings: {...user.syncSettings, account_id: user.syncSettings.account_id || null}};
		}
		this.form.value = user;
	}
}