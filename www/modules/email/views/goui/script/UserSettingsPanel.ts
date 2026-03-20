import {checkbox, comp, containerfield, datasourceform, DataSourceForm, fieldset, t} from "@intermesh/goui";
import {AppSettingsPanel, User, userDS} from "@intermesh/groupoffice-core";

export class UserSettingsPanel extends AppSettingsPanel {
	private readonly form: DataSourceForm<User>

	constructor() {
		super();

		this.title = t("E-mail");

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
							name: "always_respond_to_notifications",
							label: t("Always respond to a read notification")
						}),
						checkbox({
							name: "sort_email_addresses_by_time",
							label: t("Sort on last contact mail time")
						})
					)
				)
			)
		);

		this.items.add(this.form);
	}

	async save() {
		return this.form.submit();
	}

	async load(user: User) {
		this.form.currentId = user.id;
		this.form.value = user;
	}
}