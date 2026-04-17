import {DetailPanel, filterpanel, MainThreeColumnPanel} from "@intermesh/groupoffice-core";
import {
	btn,
	checkbox,
	comp,
	Component,
	EntityID,
	h3,
	hr,
	menu,
	radio,
	router,
	searchbtn,
	t,
	tbar
} from "@intermesh/goui";
import {AddressBookGrid, addressbookgrid} from "./AddressBookGrid.js";
import {contactgrid, ContactGrid} from "./ContactGrid.js";
import {ContactDetail} from "./ContactDetail.js";
import {ContactDialog} from "./ContactDialog.js";
import {AddressBookDialog} from "./AddressBookDialog.js";

export class Main extends MainThreeColumnPanel {
	private addressBookGrid!: AddressBookGrid;
	private contactGrid!: ContactGrid;

	constructor() {
		super("addressbook");

		this.setup(this.createCenter(), this.createWest(), this.createEast());

		this.on("render", async () => {
			void this.addressBookGrid.store.load();

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
							const rs = this.addressBookGrid.rowSelection!;
							newValue ? rs.selectAll() : rs.clear();
						}
					}
				}),
				h3(t("Address books")),
				'->',
				searchbtn({
					listeners: {
						input: ({text}) => {
							this.addressBookGrid.store.setFilter("search", {text});
							void this.addressBookGrid.store.load();
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
			// todo allow drop from contactGrid
			this.addressBookGrid = addressbookgrid({
				flex: 1,
				cls: "no-row-lines",
				rowSelectionConfig: {
					multiSelect: true,
					listeners: {
						selectionchange: ({selected}) => {
							const addressBookIds = selected.map((row) => row.record.id);

							this.contactGrid.store.setFilter("addressbook", {
								addressBookId: addressBookIds
							});

							void this.contactGrid.store.load();
						}
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
							text: t("Import")
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
										//todo
									}
								}),
								btn({
									icon: "unknown_document",
									text: t("Microsoft Excel"),
									handler: () => {
										//todo
									}
								}),
								btn({
									icon: "html",
									text: t("Web page") + " (HTML)",
									handler: () => {
										//todo
									}
								}),
								btn({
									icon: "text_snippet",
									text: "JSON",
									handler: () => {
										//todo
									}
								}),
								hr(),
								btn({
									icon: "print",
									text: t("Labels"),
									handler: () => {
										//todo
									}
								})
							)
						}),
						hr(),
						btn({
							icon: "merge",
							text: t("Look for duplicates"),
							handler: () => {
								// todo
							}
						})
					)
				})
			),
			comp({
					cls: "scroll bg-lowest",
					flex: 1,
				},
				this.contactGrid = contactgrid({
					stateId: "addressbook-contactgrid",
					rowSelectionConfig: {
						multiSelect: true, //todo merge toolbar
						listeners: {
							selectionchange: ({selected}) => {
								const contactIds = selected.map((row) => row.record.id);

								if (contactIds[0]) {
									router.goto("contact/" + contactIds[0]);
								}
							}
						}
					}
				})
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