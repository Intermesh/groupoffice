import {
	ArrayField,
	arrayfield,
	autocompletechips,
	btn,
	checkboxselectcolumn,
	colorfield,
	column,
	comp,
	ContainerField,
	containerfield,
	datasourcestore,
	datefield,
	fieldset,
	h3,
	hr,
	select,
	t,
	table,
	textarea,
	textfield
} from "@intermesh/goui";
import {client, FormWindow, imagefield, languagefield} from "@intermesh/groupoffice-core";
import {addressbookcombo, contactDS, typeStoreData} from "./Index.js";

export class ContactDialog extends FormWindow {
	constructor() {
		super("Contact");

		this.title = t("Contact");

		this.stateId = "contact-dialog";
		this.maximizable = true;
		this.resizable = true;
		this.hasLinks = true;

		this.width = 800;
		this.height = 1000;

		const countries = t("countries");

		const countryStoreData = Object.entries(countries).map(([value, name]) => ({
			value,
			name
		}));

		this.generalTab.items.add(
			fieldset({cls: "fit vbox gap"},
				comp({cls: "hbox"},
					comp({cls: "vbox gap", flex: 1},
						comp({cls: "hbox gap"},
							textfield({
								name: "firstName",
								label: t("First name"),
								flex: 1
							}),
							textfield({
								name: "middleName",
								label: t("Middle")
							}),
							textfield({
								name: "lastName",
								label: t("Last name"),
								flex: 1
							})
						),
						comp({cls: "hbox gap"},
							textfield({
								name: "prefixes",
								label: t("Prefix"),
								flex: 1
							}),
							textfield({
								name: "suffixes",
								label: t("Suffix"),
								flex: 1
							})
						),
						textfield({
							name: "salutation",
							label: t("Salutation")
						})
					),
					imagefield({
						name: "photoBlobId",
					})
				),
				comp({cls: "vbox gap",},
					comp({cls: "hbox gap"},
						colorfield({
							name: "color",
							label: t("Color"),
							flex: 1
						})
					),
					comp({cls: "hbox gap"},
						textfield({
							name: "jobTitle",
							label: t("Job title"),
							flex: 2
						}),
						select({
							name: "gender",
							label: t("Gender"),
							options: [
								{value: null, name: t("Unknown")},
								{value: "M", name: t("Male")},
								{value: "F", name: t("Female")},
								{value: "N", name: t("Non-binary")},
								{value: "P", name: t("Won't say")}
							],
							flex: 1
						})
					),
					textfield({
						name: "department",
						label: t("Department"),
						flex: 1
					})
				),
				autocompletechips({
					name: "organizationIds",
					label: t("Organizations"),
					list: table({
						fitParent: true,
						headers: false,
						rowSelectionConfig: {
							multiSelect: true
						},
						store: datasourcestore({
							dataSource: contactDS,
							filters: {
								organization: {
									isOrganization: true
								}
							},
							sort: [{property: "name", isAscending: true}]
						}),
						columns: [
							checkboxselectcolumn(),
							column({
								id: "name",
								sortable: true
							})
						]
					}),
					chipRenderer: async (chip, value) => {
						chip.text = (await contactDS.single(value)).name!;
					},
					pickerRecordToValue(field, record): any {
						return record.id;
					},
					listeners: {
						autocomplete: ({target, input}) => {
							target.list.store.setFilter("autocomplete", {text: input});
							void target.list.store.load();
						}
					}
				}),
				addressbookcombo({
					name: "addressBookId",
					label: t("Address book"),
					value: client.user.addressBookSettings ? client.user.addressBookSettings.defaultAddressBookId : null
				}),
				hr(),
				h3({text: t("Communications")}),
				comp({cls: "hbox gap"},
					comp({flex: 1, cls: "vbox gap"},
						arrayfield({
							name: "emailAddresses",
							flex: 1,
							buildField: (value) => {
								return containerfield({
										cls: "group"
									},
									select({
										name: "type",
										options: typeStoreData("emailTypes"),
										value: "work"
									}),
									textfield({
										name: "email",
										type: "email",
										flex: 1,
										label: t("E-mail"),
										required: true
									}),
									btn({
										icon: "delete",
										handler: (button) => {
											button.parent!.remove();
										}
									})
								)
							}
						}),
						btn({
							text: t("Add e-mail address"),
							handler: (button) => {
								(button.previousSibling() as ArrayField).addValue({type: "work"});
							}
						})
					),
					comp({flex: 1, cls: "vbox gap"},
						arrayfield({
							name: "phoneNumbers",
							flex: 1,
							buildField: (value) => {
								return containerfield({
										cls: "group"
									},
									select({
										name: "type",
										options: typeStoreData("phoneTypes"),
										value: "work"
									}),
									textfield({
										name: "number",
										flex: 1,
										label: t("Number"),
										required: true
									}),
									btn({
										icon: "delete",
										handler: (button) => {
											button.parent!.remove();
										}
									})
								)
							}
						}),
						btn({
							text: t("Add phone number"),
							handler: (button) => {
								(button.previousSibling() as ArrayField).addValue({type: "work"});
							}
						})
					)
				),
				comp({cls: "hbox gap"},
					languagefield({
						name: "language",
						flex: 1
					}),
					comp({flex: 1})
				),
				hr(),
				arrayfield({
					name: "addresses",
					buildField: value => {
						return containerfield({},
							comp({cls: "hbox gap", style: {marginBottom: "20px"}},
								comp({cls: "flow gap", flex: 1},
									select({
										name: "type",
										label: t("Type"),
										textRenderer: (t) => {
											return t.name
										},
										options: typeStoreData("addressTypes"),
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
										options: countryStoreData,
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
				comp({},
					btn({
						width: 300,
						text: t("Add street address"),
						handler: (button) => {
							(button.parent!.previousSibling() as ArrayField).addValue({});
						}
					})
				),
				comp({},
					hr(),
					h3({text: t("Other")}),
					comp({cls: "hbox gap"},
						comp({flex: 1, cls: "vbox gap"},
							datefield({
								name: "actionAt",
								label: t("Action at"),
								width: 200
							}),
							arrayfield({
								name: "dates",
								flex: 1,
								buildField: (value) => {
									return containerfield({
											cls: "group"
										},
										select({
											name: "type",
											options: typeStoreData("dateTypes"),
											value: "birthday"
										}),
										datefield({
											name: "date",
											flex: 1,
											label: t("Date"),
											required: true
										}),
										btn({
											icon: "delete",
											handler: (button) => {
												button.parent!.remove();
											}
										})
									)
								}
							}),
							btn({
								text: t("Add date"),
								width: "auto",
								handler: (button) => {
									(button.previousSibling() as ArrayField).addValue({type: "work"});
								}
							})
						),
						comp({flex: 1, cls: "vbox gap"},
							arrayfield({
								name: "urls",
								flex: 1,
								buildField: (value) => {
									return containerfield({
											cls: "group"
										},
										select({
											name: "type",
											options: typeStoreData("urlTypes"),
											value: "homepage"
										}),
										textfield({
											name: "url",
											type: "url",
											flex: 1,
											label: t("URL"),
											required: true
										}),
										btn({
											icon: "delete",
											handler: (button) => {
												button.parent!.remove();
											}
										})
									);
								}
							}),
							btn({
								text: t("Add  online url"),
								handler: (button) => {
									(button.previousSibling() as ArrayField).addValue({type: "work"});
								}
							})
						)
					)
				)
			)
		);

		this.cards.items.add(
			fieldset({
					title: t("Notes"),
					cls: "fit"
				},
				textarea({
					name: "notes",
					cls: "fit",
					autoHeight: false
				})
			)
		);

	}
}