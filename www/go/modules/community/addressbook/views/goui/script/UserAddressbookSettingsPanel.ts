import {AppSettingsPanel, User, userDS} from "@intermesh/groupoffice-core";
import {comp, containerfield, datasourceform, DataSourceForm, fieldset, radio, t} from "@intermesh/goui";
import {addressbookcombo} from "./AddressBookCombo.js";

export class UserAddressbookSettingsPanel extends AppSettingsPanel {
	private readonly form: DataSourceForm<User>;

	constructor() {
		super();

		this.title = t("Address book");

		this.form = datasourceform({
				dataSource: userDS
			},
			containerfield({
					name: "addressBookSettings"
				},
				fieldset({},
					comp({tagName: "h3", html: t("Display options for address books")}),
					addressbookcombo({
						label: t("Default address book"),
						name: "defaultAddressBookId"
					}),
					radio({
						name: "startIn",
						type: "box",
						label: t("Start in"),
						options: [
							{
								text: t("All contacts"),
								value: 'allcontacts'
							},
							{
								text: t("Starred"),
								value: 'starred'
							},
							{
								text: t("Default address book"),
								value: 'default'
							},
							{
								text: t("Last selected address book"),
								value: 'remember'
							}
						]
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
		this.form.currentId = user.id;
		this.form.value = user;
	}
}