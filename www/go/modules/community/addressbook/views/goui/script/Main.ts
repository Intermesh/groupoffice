import {client, DetailPanel, Export, filterpanel, MainThreeColumnPanel} from "@intermesh/groupoffice-core";
import {
	btn,
	checkbox,
	comp,
	Component,
	EntityID,
	h3,
	hr,
	menu,
	mstbar,
	radio,
	router,
	searchbtn,
	t,
	tbar,
	Window
} from "@intermesh/goui";
import {AddressBookTree, addressbooktree} from "./AddressBookTree.js";
import {contactgrid, ContactGrid} from "./ContactGrid.js";
import {ContactDetail} from "./ContactDetail.js";
import {ContactDialog} from "./ContactDialog.js";
import {AddressBookDialog} from "./AddressBookDialog.js";
import {contactDS} from "./Index.js";
import {LabelsDialog} from "./LabelsDialog.js";
import {DuplicateDialog} from "./DuplicateDialog.js";

export class Main extends MainThreeColumnPanel {
	private addressBookTree!: AddressBookTree;
	private contactGrid!: ContactGrid;

	constructor() {
		super("addressbook");

		this.setup(this.createCenter(), this.createWest(), this.createEast());

		this.on("render", async () => {
			void this.addressBookTree.store.load();

			void this.contactGrid.store.load();
		});
	}

	protected createWest(): Component {
		return comp({
				cls: "scroll",
				width: 300
			},
			tbar({},
				'->',
				this.showCenterButton()
			),
			radio({
				flex: 1,
				type: "list",
				options: [
					{value: "all", text: t("All contacts"), icon: "select_all"},
					{value: "starred", text: t("Starred"), icon: "star"},
					{value: "orgs", text: t("Organization"), icon: "business"},
					{value: "contacts", text: t("Contacts"), icon: "person"}
				],
				listeners: {
					change: ({newValue}) => {
						this.contactGrid.store.clearFilter("org");
						this.contactGrid.store.clearFilter("starred");

						switch (newValue) {
							case "orgs":
								this.contactGrid.store.setFilter("org", {isOrganization: true});
								break;
							case "contacts":
								this.contactGrid.store.setFilter("org", {isOrganization: false});
								break;
							case 'starred':
								this.contactGrid.store.setFilter("starred", {starred: true});
								break;
						}

						this.contactGrid.store.load();
					},
					render: (ev) => {
						ev.target.value = "all";
					}
				}
			}),
			tbar({},
				checkbox({
					listeners: {
						change: ({newValue}) => {
							const rs = this.addressBookTree.rowSelection!;
							newValue ? rs.selectAll() : rs.clear();
						}
					}
				}),
				h3(t("Address books")),
				'->',
				searchbtn({
					listeners: {
						input: ({text}) => {
							this.addressBookTree.filter(text);
						}
					}
				}),
				btn({
					icon: "add",
					handler: () => {
						const dlg = new AddressBookDialog();

						dlg.show();
					}
				})
			),
			this.addressBookTree = addressbooktree({
				cls: "no-row-lines",
				dropOn: true,
				sortableGroup: "contactgrid-addressbooktree",
				rowSelectionConfig: {
					multiSelect: true,
					listeners: {
						selectionchange: ({selected}) => {
							const records = selected.map((row) => row.record);

							const addressBookIds = records.filter(r => r.addressBookId === undefined).map((record) => record.id);
							const groupIds = records.filter(r => r.addressBookId !== undefined).map((record) => record.id);

							if (addressBookIds.length) {
								this.contactGrid.store.setFilter("addressbook", {
									addressBookId: addressBookIds
								});
							} else {
								this.contactGrid.store.clearFilter("addressbook");
							}

							if (groupIds.length) {
								this.contactGrid.store.setFilter("group", {
									groupId: groupIds
								});
							} else {
								this.contactGrid.store.clearFilter("group");
							}

							void this.contactGrid.store.load();
						}
					}
				},
				listeners: {
					drop: async ({target, dragDataSet, droppedOn, fromIndex, source, toIndex}) => {
						if (client.user.confirmOnMove) {
							const confirm = await Window.confirm(t('Are you sure you want to move the item(s)?'), t("Confirm"));

							if (!confirm) {
								return
							}
						}

						const contact = this.contactGrid.store.get(fromIndex)!;
						const dropTarget = target.store.get(toIndex)!;

						if (dropTarget.addressBookId === undefined) {
							contactDS.update(contact.id, {
								addressBookId: dropTarget.id,
								groups: []
							});
						} else {
							const groups = contact.groups ?? [];

							if (groups.includes(dropTarget.id!)) {
								return;
							}

							groups.push(dropTarget.id!);

							contactDS.update(contact.id, {
								addressBookId: dropTarget.addressBookId,
								groups: groups
							});
						}

						this.contactGrid.store.load();
					}
				}
			}),
			filterpanel({
				flex: 1,
				store: this.contactGrid.store,
				entityName: "Contact"
			})
		);
	}

	protected createCenter(): Component {
		this.contactGrid = contactgrid({
			stateId: "addressbook-contactgrid",
			draggable: true,
			sortableGroup: "contactgrid-addressbooktree",
			rowSelectionConfig: {
				multiSelect: true,
				listeners: {
					selectionchange: ({selected}) => {
						const contactIds = selected.map((row) => row.record.id);

						if (contactIds[0]) {
							router.goto("contact/" + contactIds[0]);
						}
					}
				}
			}
		});

		return comp({
				cls: "vbox bg-lowest"
			},
			tbar({cls: "bg-mid border-bottom"},
				this.showWestButton(),
				'->',
				searchbtn({
					listeners: {
						input: ({text}) => {
							this.contactGrid.store.setFilter("search", {text});
							void this.contactGrid.store.load();
						}
					}
				}),
				btn({
					text: t("Add"),
					cls: "primary filled",
					menu: menu({
							isDropdown: true
						},
						btn({
							icon: "person",
							text: t("Contact"),
							handler: () => {
								const dlg = new ContactDialog();

								dlg.form.on("submit", () => {
									void this.contactGrid.store.load();
								});

								dlg.show();
							}
						}),
						btn({
							icon: "business",
							text: t("Organization"),
							handler: () => {
								const dlg = new ContactDialog(true);

								dlg.form.on("submit", () => {
									void this.contactGrid.store.load();
								});

								dlg.show();
							}
						})
					)
				}),
				btn({
					icon: "more_vert",
					menu: menu({
							isDropdown: true
						},
						btn({
							icon: "cloud_upload",
							text: t("Import"),
							handler: () => {
								go.util.importFile(
									'Contact',
									".csv, .vcf, text/vcard, .json, .xlsx",
									{addressBookId: client.user.addressBookSettings.defaultAddressBookId},
									{
										// These fields can be selected to update contacts if ID or e-mail matches
										lookupFields: {'id': "ID", 'email': 'E-mail'},

										// This hash map is used to aid in auto selecting the right mappings. Key is possible header in CSV and value is property name in Group-Office
										aliases: {
											"Given name": "firstName",
											"First name": "firstName",

											"Middle name": "middleName",

											"Family Name": "lastName",
											"Last Name": "lastName",

											"Job Title": "jobTitle",
											"Suffix": "suffixes",
											"Web page": {field: "urls[].url", fixed: {"type": "homepage"}},
											"Birthday": {field: "dates[].date", fixed: {"type": "birthday"}},
											"Anniversary": {field: "dates[].date", fixed: {"type": "anniversary"}},

											"E-mail 1 - Value": {
												field: "emailAddresses[].email",
												related: {"type": "E-mail 1 - Type"}
											},
											"email": {field: "emailAddresses[].email", fixed: {"type": "work"}},
											"E-mail Address": {
												field: "emailAddresses[].email",
												fixed: {"type": "work"}
											},
											"E-mail 2 Address": {
												field: "emailAddresses[].email",
												fixed: {"type": "work"}
											},
											"E-mail 3 Address": {
												field: "emailAddresses[].email",
												fixed: {"type": "work"}
											},
											"E-mail": {field: "emailAddresses[].email", fixed: {"type": "work"}},

											"Primary Phone": {field: "phoneNumbers[].number", fixed: {"type": "work"}},
											"Home Phone": {field: "phoneNumbers[].number", fixed: {"type": "home"}},
											"Home Phone 2": {field: "phoneNumbers[].number", fixed: {"type": "home"}},

											"Business Phone": {field: "phoneNumbers[].number", fixed: {"type": "work"}},
											"Business Phone 2": {
												field: "phoneNumbers[].number",
												fixed: {"type": "work"}
											},

											"Mobile Phone": {field: "phoneNumbers[].number", fixed: {"type": "cell"}},
											"Pager": {field: "phoneNumbers[].number", fixed: {"type": "other"}},
											"Home Fax": {field: "phoneNumbers[].number", fixed: {"type": "fax"}},

											"Other Phone": {field: "phoneNumbers[].number", fixed: {"type": "other"}},
											"Other Fax": {field: "phoneNumbers[].number", fixed: {"type": "fax"}},

											"Home Street": {
												field: "addresses[].street",
												fixed: {type: "home"},
												related: {
													city: "Home City",
													state: "Home State",
													zipCode: "Home Postal Code",
													country: "Home Country"
												}
											},
											"Business Street": {
												field: "addresses[].street",
												fixed: {type: "work"},
												related: {
													city: "Business City",
													state: "Business State",
													zipCode: "Business Postal Code",
													country: "Business Country"

												}
											},
											"Other Street": {
												field: "addresses[].street",
												fixed: {type: "other"},
												related: {
													city: "Other City",
													state: "Other State",
													zipCode: "Other Postal Code",
													country: "Other Country"

												}
											},

											"Company": "organizations"
										},

										// Fields with labels and possible subproperties.
										// For example e-mail and type of an array of e-mail addresses should be grouped together.
										fields: {
											prefixes: {label: t("Prefixes")},
											initials: {label: t("Initials")},
											salutation: {label: t("Salutation")},
											color: {label: t("Color")},
											firstName: {label: t("First name")},
											middleName: {label: t("Middle name")},
											lastName: {label: t("Last name")},
											name: {label: t("Name")},
											suffixes: {label: t("Suffixes")},
											gender: {label: t("Gender")},
											notes: {label: t("Notes")},
											isOrganization: {label: t("Is organization")},
											IBAN: {label: t("IBAN")},
											registrationNumber: {label: t("Registration number")},
											vatNo: {label: t("VAT number")},
											vatReverseCharge: {label: t("Reverse charge VAT")},
											debtorNumber: {label: t("Debtor number")},
											photoBlobId: {label: t("Photo blob ID")},
											language: {label: t("Language")},
											jobTitle: {label: t("Job title")},
											uid: {label: t("UUID")},
											//starred: {label: t("Starred")},

											"emailAddresses": {
												label: t("E-mail addresses"),
												properties: {
													"email": {label: "E-mail"},
													"type": {label: t("Type")}
												}
											},

											"dates": {
												label: t("Dates"),
												properties: {
													"date": {label: "Date"},
													"type": {label: t("Type")}
												}
											},

											"phonenumbers": {
												label: t("Phone numbers"),
												properties: {
													"number": {label: "Number"},
													"type": {label: t("Type")}
												}
											},

											"urls": {
												label: t("URL's"),
												properties: {
													"url": {label: "URL"},
													"type": {label: t("Type")}
												}
											},

											"addresses": {
												label: t("Addresses"),
												properties: {
													"type": {label: t("Type")},
													"street": {label: t("Street")},
													"street 2": {label: t("Street 2")},
													"zipCode": {label: t("ZIP code")},
													"city": {label: t("City")},
													"state": {label: t("state")},
													"country": {label: t("Country")},
													"countryCode": {label: t("Country code")},
													"latitude": {label: t("Latitude")},
													"longitude": {label: t("Longitude")}
												}
											}
										}
									});
							}
						}),
						btn({
							icon: "cloud_download",
							text: t("Export"),
							menu: menu({
									isDropdown: true
								},
								btn({
									icon: "contact_mail",
									text: t("vCard (Virtual Contact File)"),
									handler: () => {
										Export.toFile(
											"Contact",
											this.contactGrid.store.queryParams,
											"vcf"
										);
									}
								}),
								btn({
									icon: "unknown_document",
									text: t("Microsoft Excel"),
									handler: () => {
										Export.toFile(
											"Contact",
											this.contactGrid.store.queryParams,
											"xlsx"
										);
									}
								}),
								btn({
									icon: "csv",
									text: "Comma Seperated Values",
									handler: () => {
										Export.toFile(
											"Contact",
											this.contactGrid.store.queryParams,
											"csv"
										);
									}
								}),
								btn({
									icon: "html",
									text: t("Web page") + " (HTML)",
									handler: () => {
										Export.toFile(
											"Contact",
											this.contactGrid.store.queryParams,
											"html"
										);
									}
								}),
								btn({
									icon: "text_snippet",
									text: "JSON",
									handler: () => {
										Export.toFile(
											"Contact",
											this.contactGrid.store.queryParams,
											"json"
										);
									}
								}),
								hr(),
								btn({
									icon: "print",
									text: t("Labels"),
									handler: () => {
										const dlg = new LabelsDialog(this.contactGrid.store.queryParams);

										dlg.show();
									}
								})
							)
						}),
						hr(),
						btn({
							icon: "merge",
							text: t("Look for duplicates"),
							handler: () => {
								const dlg = new DuplicateDialog();

								dlg.show();
							}
						})
					)
				}),
				mstbar({
						table: this.contactGrid
					},
					"->",
					btn({
						icon: "merge",
						title: t("Merge"),
						handler: async (btn) => {
							const confirm = await Window.confirm(t("Are you sure you want to merge the selected items? This can't be undone."), t("Confirm"));

							if (!confirm) {
								return
							}

							const ids = this.contactGrid!.rowSelection!.getSelected().map((row) => row.record.id);

							contactDS.merge(ids);
							btn.parent!.hide();
						}
					}),
					btn({
						icon: "delete",
						title: t("Delete"),
						handler: async (btn) => {
							const ids = this.contactGrid!.rowSelection!.getSelected().map((row) => row.record.id);

							await contactDS.confirmDestroy(ids);

							btn.parent!.hide();
						}
					})
				)
			),
			comp({
					cls: "scroll bg-lowest",
					flex: 1,
				},
				this.contactGrid
			)
		);
	}

	protected createEast(): Component {
		return new ContactDetail();
	}

	public showContact(contactId: EntityID) {
		this.activatePanel(this.east);
		void (this.east as DetailPanel).load(contactId);
	}
}