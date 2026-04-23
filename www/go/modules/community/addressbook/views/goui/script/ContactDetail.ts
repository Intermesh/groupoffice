import {
	addbutton,
	client,
	DetailPanel,
	Export,
	filesbutton,
	img,
	linkbrowsebutton,
	modules
} from "@intermesh/groupoffice-core";
import {addressBookDS, addressBookGroupDS, Contact, contactDS, ContactUrl} from "./Index.js";
import {
	a,
	arrayfield,
	avatar,
	btn,
	Button,
	collapsebtn,
	comp,
	Component,
	containerfield,
	datasourceform,
	DataSourceForm,
	DateField,
	datefield,
	displayfield,
	Format,
	h4,
	hr,
	menu,
	Notifier,
	t,
	tbar
} from "@intermesh/goui";
import {CommentsPanel} from "@intermesh/community-comments";
import {HistoryDetailPanel} from "@intermesh/community-history";
import {ContactDialog} from "./ContactDialog.js";

export class ContactDetail extends DetailPanel<Contact> {
	private readonly form: DataSourceForm;

	private emailAddressesComp: Component;
	private phoneNumbersComp: Component;
	private addressesComp: Component;
	private datesComp: Component;
	private addressBookComp: Component;

	private addressBook?: Record<string, any>;
	private actionAtField!: DateField;

	private starButton: Button;
	private removeFromGroupButton: Button;

	constructor() {
		super("Contact");

		this.form = datasourceform({
			dataSource: contactDS
		});

		this.form.items.add(
			comp({cls: "normalized card pad"},
				comp({cls: "hbox"},
					displayfield({
						name: "name",
						renderer: (v) => {
							return comp({cls: "meta"},
								this.entity!.photoBlobId ?
									img({
										cls: "goui-avatar",
										blobId: this.entity!.photoBlobId,
										title: this.entity!.name,
										style: {cursor: "pointer"},
										listeners: {
											render: (({target}) => {
												target.el.addEventListener("click", () => {
													window.open(client.downloadUrl(this.entity!.photoBlobId!) + "&inline=1");
												});
											})
										}
									}) :
									avatar({
										displayName: this.entity!.name
									})
							);
						}
					}),
					comp({cls: "vbox gap", style: {padding: "2.4rem 0"}, flex: 1},
						displayfield({
							tagName: "div",
							name: "name",
							renderer: (v) => {
								let fullName = [this.entity!.prefixes, this.entity!.name, this.entity!.suffixes]
									.filter(Boolean)
									.join(" ");

								if (this.entity!.color) {
									return comp({style: {color: "#" + this.entity!.color}, text: fullName});
								} else {
									return fullName
								}
							}
						}),
						displayfield({
							tagName: "div",
							name: "jobTitle",
							renderer: (v) => {
								if (v && this.entity!.department) {
									return `${v} - ${this.entity!.department}`;
								}

								return v ?? "";
							}
						})
					),
					displayfield({
							name: "urls",
							tagName: "div",
							flex: 1,
							renderer: (v) => {
								let urls = this.buildUrlLinks(v);

								return comp({}, ...urls);
							}
						},
					)
				),
				this.emailAddressesComp = comp({cls: "hbox", flex: 1},
					comp({
						tagName: "i",
						cls: "icon ic-email",
						style: {
							marginRight: "1.8rem"
						}
					}),
					arrayfield({
						flex: 1,
						name: "emailAddresses",
						buildField: (value) => {
							return containerfield({},
								comp({
										style: {
											cursor: "pointer",
										},
										cls: "vbox",
										listeners: {
											render: (ev) => {
												ev.target.el.on('click', (clickEvent) => {
													clickEvent.preventDefault();

													if (window.getSelection()!.toString().length > 0) {
														return;
													}

													go.showComposer({
														to: value.email,
														name: this.entity!.name,
														entity: "Contact",
														entityId: this.entity!.id
													});
												});
											}
										}
									},
									comp({text: value.email}),
									comp({tagName: "h5", text: t("emailTypes", "community", "addressbook")[value.type]})
								)
							)
						}
					})
				),
				this.phoneNumbersComp = comp({cls: "hbox", flex: 1},
					comp({
						tagName: "i",
						cls: "icon ic-phone",
						style: {
							marginRight: "1.8rem"
						}
					}),
					arrayfield({
						flex: 1,
						name: "phoneNumbers",
						buildField: (value) => {
							return containerfield({},
								comp({
										style: {
											cursor: "pointer",
										},
										cls: "vbox",
										listeners: {
											render: (ev) => {
												ev.target.el.on('click', (clickEvent) => {
													clickEvent.preventDefault();

													window.location.href = `tel:${value.number.replace(/[^0-9+]/g, '')}`;
												});
											},
										}
									},
									comp({text: value.number}),
									comp({tagName: "h5", text: t("phoneTypes", "community", "addressbook")[value.type]})
								)
							)
						}
					})
				),
				this.addressesComp = comp({},
					hr(),
					comp({cls: "hbox", flex: 1},
						comp({
							tagName: "i",
							cls: "icon ic-location-on",
							style: {
								marginRight: "1.8rem"
							}
						}),
						arrayfield({
							flex: 1,
							name: "addresses",
							buildField: (value) => {
								return containerfield({},
									comp({
											style: {
												cursor: "pointer",
											},
											cls: "vbox",
											listeners: {
												render: (ev) => {
													ev.target.el.on('click', (clickEvent) => {
														clickEvent.preventDefault();

														if (window.getSelection()!.toString().length > 0) {
															return;
														}

														go.util.streetAddress(value);
													});
												},
											}
										},
										comp({text: value.formatted}),
										comp({
											tagName: "h5",
											text: t("addressTypes", "community", "addressbook")[value.type] ?? value.type
										})
									)
								)
							}
						})
					)
				),
				this.datesComp = comp({},
					hr(),
					comp({cls: "hbox", flex: 1},
						comp({
							tagName: "i",
							cls: "icon ic-cake",
							style: {
								marginRight: "1.8rem"
							}
						}),
						arrayfield({
							flex: 1,
							name: "dates",
							buildField: (value) => {
								return containerfield({},
									comp({
											cls: "vbox",
										},
										comp({text: Format.date(value.date)}),
										comp({
											tagName: "h5",
											text: t("dateTypes", "community", "addressbook")[value.type] ?? value.type
										})
									)
								)
							}
						})
					)
				),
				comp({},
					hr(),
					comp({cls: "hbox", flex: 1},
						comp({
							tagName: "i",
							cls: "icon ic-import-contacts",
							style: {
								marginRight: "1.8rem"
							}
						}),
						comp({
								cls: "vbox",
							},
							this.addressBookComp = comp({}),
							comp({
								tagName: "h5",
								text: t("Address book")
							}),
							displayfield({name: "gender", tagName: "div"}),
							comp({
								tagName: "h5",
								text: t("Gender"),
								hidden: (this.form.value.gender === undefined)
							})
						)
					)
				),
				comp({
						flex: 1,
						hidden: (this.form.value.IBAN !== undefined) ||
							(this.form.value.vatNo !== undefined) ||
							(this.form.value.registrationNumber !== undefined) ||
							(this.form.value.debtorNumber !== undefined)
					},
					tbar({cls: "border-top"},
						h4({text: t("Company")}),
						'->',
						collapsebtn({
							target: btn => {
								return btn.parent!.nextSibling()!
							}
						})
					),
					comp({
							cls: "vbox"
						},
						displayfield({name: "IBAN", tagName: "div"}),
						comp({
							tagName: "h5",
							text: t("IBAN"),
							hidden: (this.form.value.IBAN !== undefined)
						}),
						displayfield({name: "vatNo", tagName: "div"}),
						comp({
							tagName: "h5",
							text: t("VAT number"),
							hidden: (this.form.value.vatNo !== undefined)
						}),
						displayfield({
							name: "vatReverseCharge", tagName: "div", renderer: (v) => {
								return v ? t("Yes") : t("No");
							}
						}),
						comp({
							tagName: "h5",
							text: t("Reverse charge VAT"),
							hidden: (this.form.value.vatReverseCharge !== undefined)
						}),
						displayfield({name: "registrationNumber", tagName: "div"}),
						comp({
							tagName: "h5",
							text: t("Registration number"),
							hidden: (this.form.value.registrationNumber !== undefined)
						}),
						displayfield({name: "debtorNumber", tagName: "div"}),
						comp({
							tagName: "h5",
							text: t("Debtor number"),
							hidden: (this.form.value.debtorNumber !== undefined)
						}),
					)
				)
			)
		);

		this.scroller.items.add(this.form);

		this.on("load", async ({entity}) => {
			void this.form.load(entity.id);

			this.addressBook = await addressBookDS.single(entity.addressBookId)

			this.phoneNumbersComp.hidden = !(entity.phoneNumbers && entity.phoneNumbers.length > 0);
			this.emailAddressesComp.hidden = !(entity.emailAddresses && entity.emailAddresses.length > 0);
			this.addressesComp.hidden = !(entity.addresses && entity.addresses.length > 0);
			this.datesComp.hidden = !(entity.dates && entity.dates.length > 0);

			this.addressBookComp.text = this.addressBook.name;

			this.actionAtField.value = entity.actionAt ?? undefined;

			this.starButton.text = this.entity!.starred ? t("Unstar") : t("Star");

			this.removeFromGroupButton.menu = undefined;
			this.removeFromGroupButton.disabled = true;

			if (entity.groups && entity.groups.length) {
				const groupMenu = menu();

				const response = await addressBookGroupDS.get(entity.groups);

				response.list.forEach((group) => {
					groupMenu.items.add(
						btn({
							text: group.name,
							handler: () => {
								const updateGroups = entity.groups!;

								let i = updateGroups.indexOf(group.id);

								if (i > -1) {
									updateGroups.splice(i, 1);
								}

								contactDS.update(entity.id, {
									groups: updateGroups
								});
							}
						})
					)
				});

				this.removeFromGroupButton.menu = groupMenu;
				this.removeFromGroupButton.disabled = false;
			}
		});

		this.on("reset", () => {
			this.form.reset();
		});

		if (modules.isAvailable("community", "comments")) {
			this.scroller.items.add(new CommentsPanel(this.entityName));
		}

		this.addCustomFields();
		this.addLinks();
		this.addActionDate();

		this.addFiles();
		this.addHistory();

		if (modules.isAvailable("community", "history")) {
			this.scroller.items.add(new HistoryDetailPanel(this.entityName));
		}

		this.toolbar.items.add(
			btn({
				icon: "edit",
				title: t("Edit"),
				handler: () => {
					const dlg = new ContactDialog();

					dlg.load(this.entity!.id);
					dlg.show();
				}
			}),
			addbutton(),
			linkbrowsebutton(),
			btn({
				icon: "more_vert",
				menu: menu({},
					this.starButton = btn({
						icon: "star",
						handler: async () => {
							contactDS.update(this.entity!.id, {
								starred: !this.entity!.starred
							}).then(() => {
								this.load(this.entity!.id);
							});
						}
					}),
					hr(),
					this.removeFromGroupButton = btn({
						icon: "clear",
						text: t("Remove from group")
					}),
					hr(),
					btn({
						icon: "print",
						text: t("Print"),
						handler: () => {
							this.print();
						}
					}),
					btn({
						icon: "cloud_download",
						text: t("Export (vCard)"),
						handler: () => {
							Export.toFile(
								"Contact",
								{
									id: this.entity!.id
								},
								"vcf"
							);
						}
					}),
					btn({
						icon: "attach_file",
						text: t("Send (vCard)"),
						handler: async () => {
							client.jmap("Contact/export", {extension: "vcf", ids: [this.entity!.id]})
								.then((result) => {
									GO.email.showComposer({blobs: [result.blob]});
								});
						}
					}),
					hr(),
					btn({
						icon: "delete",
						text: t("Delete"),
						handler: async () => {
							await contactDS.confirmDestroy([this.entity!.id]);
						}
					})
				)
			})
		)

		if (modules.isAvailable("legacy", "files")) {
			this.toolbar.items.insert(-1, filesbutton());
		}

	}

	private buildUrlLinks(contactUrls: ContactUrl[] | undefined) {
		if (!contactUrls) {
			return [comp()];
		}

		const links: Component[] = [];

		contactUrls.forEach((contactUrl) => {
			let isUri = contactUrl.url.toLowerCase().indexOf("http") !== -1;

			let linkUrl = contactUrl.url;

			if (!isUri) {
				switch (contactUrl.type) {
					case "twitter":
						linkUrl = "https://x.com/" + contactUrl.url;
						break;
					case "facebook":
						linkUrl = "https://www.facebook.com/" + contactUrl.url;
						break;
					case "linkedin":
						linkUrl = "https://linkedin.com/in/" + contactUrl.url;
						break;
					default:
						linkUrl = "https://" + contactUrl.url;
						break;
				}
			}

			links.push(a({cls: `addressbook-url ${contactUrl.type}`, href: linkUrl, target: "_blank"}));
		});

		return links;
	}

	private addActionDate() {
		this.scroller.items.add(
			comp(
				{
					cls: "normalized card pad"
				},
				this.actionAtField = datefield({
					flex: 1,
					label: t("Action date"),
					name: "actionAt",
					listeners: {
						change: ({newValue, oldValue}) => {
							if (newValue === oldValue) {
								return
							}

							contactDS.update(this.entity!.id, {
								actionAt: newValue
							}).catch((reason) => {
								Notifier.notify({category: "error", text: reason.message});
							});
						}
					}
				})
			)
		)
	}
}