import {AppSettingsPanel, User} from "@intermesh/groupoffice-core";
import {
	ArrayField,
	arrayfield,
	autocompletechips,
	btn,
	column,
	comp,
	ContainerField,
	containerfield,
	datasourceform,
	DataSourceForm,
	datasourcestore,
	datefield,
	fieldset,
	hr,
	radio,
	select,
	t,
	table,
	textarea,
	textfield
} from "@intermesh/goui";
import {contactDS} from "@intermesh/business/catalog";
import {Contact} from "@intermesh/community/addressbook";

export class UserProfileSettingsPanel extends AppSettingsPanel {
	private readonly form: DataSourceForm<Contact>;

	constructor() {
		super();

		this.title = t("Profile");

		this.form = datasourceform({
				dataSource: contactDS
			},

			fieldset({},
				textfield({
					name: "name",
					label: t("Name")
				}),
				textfield({
					name: "jobTitle",
					label: t("Job title")
				}),
				textfield({
					name: "department",
					label: t("Department")
				}),
				radio({
					name: "gender",
					type: "box",
					value: null,
					options: [
						{text: t("Unknown"), value: null},
						{text: t("Male"), value: 'M'},
						{text: t("Female"), value: 'F'},
					]
				}),
				autocompletechips({
					label: t("Organizations"),
					name: "organizationIds",
					list: table({
						fitParent: true,
						headers: false,
						store: datasourcestore({
							dataSource: contactDS,
							filters: {
								defaults: {
									isOrganization: true
								}
							}
						}),
						columns: [
							column({
								id: "name"
							})
						]
					}),
					chipRenderer: async (chip, value) => {
						contactDS.single(value).then(record => {
							chip.text = record.name;
						});
					},
					listeners: {
						autocomplete: ({target, input}) => {
							target.list.store.setFilter("autocomplete", {text: input});
							void target.list.store.load();
						}
					}
				})
			),
			fieldset({},
				hr(),
				comp({tagName: "h3", html: t("Communication")}),
				arrayfield({
					name: "phoneNumbers",
					buildField: () => {
						return containerfield({cls: "group"},
							select({
								name: "type",
								label: t("Type"),
								valueField: "value",
								textRenderer: (t) => {
									return t.name
								},
								options: this.getSelectTypes("phoneTypes"),
								value: "work"
							}),
							textfield({
								name: "number",
								label: t("Number"),
								required: true
							}),
							btn({
								icon: "delete",
								handler: (btn) => {
									btn.findAncestorByType(ContainerField)!.remove();
								}
							})
						);
					}
				}),
				btn({
					width: 250,
					text: t("Add phone number"),
					handler: () => {
						(this.form.findField("phoneNumbers")! as ArrayField).addValue({});
					}
				}),
				arrayfield({
					name: "addresses",
					buildField: () => {
						return containerfield({},
							comp({cls: "hbox gap"},
								comp({cls: "flow gap", flex: 1},
									select({
										name: "type",
										label: t("Type"),
										textRenderer: (t) => {
											return t.name
										},
										options: this.getSelectTypes("addressTypes"),
										value: "work"
									}),
									textarea({
										name: "address",
										label: t("Address"),
										autoHeight: true
									}),
									textfield({
										name: "zipCode",
										label: t("ZIP code")
									}),
									textfield({
										name: "city",
										label: t("City")
									}),
									textfield({
										name: "state",
										label: t("State")
									}),
									select({
										name: "countryCode",
										label: t("Country"),
										options: this.getCountries(),
										textRenderer: (t) => {
											return t.name
										}
									})
								),
								hr(),
								btn({
									icon: "delete",
									handler: (btn) => {
										btn.findAncestorByType(ContainerField)!.remove();
									}
								})
							)
						)
					}
				}),
				btn({
					width: 250,
					text: t("Add street address"),
					handler: () => {
						(this.form.findField("addresses")! as ArrayField).addValue({});
					}
				}),
			),
			fieldset({},
				hr(),
				comp({tagName: "h3", html: t("Other")}),
				comp({cls: "flow gap", flex: 1},
					arrayfield({
						name: "dates",
						buildField: () => {
							return containerfield({cls: "group"},
								select({
									name: "type",
									label: t("Type"),
									valueField: "value",
									textRenderer: (t) => {
										return t.name
									},
									options: this.getSelectTypes("dateTypes"),
									value: "birthday"
								}),
								datefield({
									name: "date",
									label: t("Date"),
									required: true
								}),
								btn({
									icon: "delete",
									handler: (btn) => {
										btn.findAncestorByType(ContainerField)!.remove();
									}
								})
							)
						}
					}),
					btn({
						width: 250,
						text: t("Add date"),
						handler: () => {
							(this.form.findField("dates")! as ArrayField).addValue({});
						}
					})
				),
				comp({cls: "flow gap", flex: 1},
					arrayfield({
						name: "urls",
						buildField: () => {
							return containerfield({cls: "group"},
								select({
									name: "type",
									label: t("Type"),
									valueField: "value",
									textRenderer: (t) => {
										return t.name
									},
									options: this.getSelectTypes("urlTypes"),
									value: "homepage"
								}),
								textfield({
									name: "url",
									label: t("URL"),
									required: true
								}),
								btn({
									icon: "delete",
									handler: (btn) => {
										btn.findAncestorByType(ContainerField)!.remove();
									}
								})
							)
						}
					}),
					btn({
						width: 250,
						text: t("Add online url"),
						handler: () => {
							(this.form.findField("urls")! as ArrayField).addValue({});
						}
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
		const contact = await contactDS.single(user.id);
		this.form.currentId = contact.id;
		this.form.value = contact;
	}

	// Separate from getSelectTypes because translate on types doesn't work without package and module
	private getCountries() {
		const countries = t("countries");

		return Object.entries(countries).map(([value, name]) => ({
			value,
			name
		}));
	}

	private getSelectTypes(translateKey: string) {
		const types = t(translateKey, "community", "addressbook");

		return Object.entries(types).map(([value, name]) => ({
			value,
			name
		}));
	}
}