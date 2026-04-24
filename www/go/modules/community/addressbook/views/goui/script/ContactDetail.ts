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
	h3,
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

	private addressBook?: Record<string, any>;
	private actionAtField!: DateField;

	private starButton: Button;
	private removeFromGroupButton: Button;

	constructor() {
		super("Contact");

		this.form = datasourceform({
			dataSource: contactDS
		});

		this.scroller.items.add(this.form);

		this.on("load", async ({entity}) => {
			void this.form.load(entity.id);

			this.addressBook = await addressBookDS.single(entity.addressBookId);

			this.form.items.clear();

			this.form.items.add(
				comp({cls: "normalized card pad"},
					comp({cls: "hbox"},
						displayfield({
							name: "name",
							renderer: () => comp({cls: "meta"},
								entity.photoBlobId
									? img({
										cls: "goui-avatar",
										blobId: entity.photoBlobId,
										title: entity.name,
										style: {cursor: "pointer"},
										listeners: {
											render: ({target}) => {
												target.el.addEventListener("click", () => {
													window.open(client.downloadUrl(entity.photoBlobId!) + "&inline=1");
												});
											}
										}
									})
									: avatar({
										...(entity.isOrganization
											? {displayName: entity.name, icon: "business"}
											: {displayName: entity.name})
									})
							)
						}),
						comp({cls: "vbox gap", style: {padding: "2.4rem 0"}, flex: 1},
							displayfield({
								tagName: "div",
								name: "name",
								renderer: () => {
									const fullName = [entity.prefixes, entity.name, entity.suffixes]
										.filter(Boolean)
										.join(" ");
									return entity.color
										? comp({style: {color: "#" + entity.color}, text: fullName})
										: fullName;
								}
							}),
							displayfield({
								tagName: "div",
								name: "jobTitle",
								renderer: (v) => (v && entity.department) ? `${v} - ${entity.department}` : v ?? ""
							})
						),
						displayfield({
							name: "urls",
							tagName: "div",
							flex: 1,
							renderer: (v) => comp({}, ...this.buildUrlLinks(v))
						})
					),

					comp({cls: "hbox", flex: 1, hidden: !entity.emailAddresses?.length},
						comp({tagName: "i", cls: "icon ic-email", style: {marginRight: "1.8rem"}}),
						arrayfield({
							flex: 1,
							name: "emailAddresses",
							buildField: (value) => containerfield({},
								comp({
										style: {cursor: "pointer"},
										cls: "vbox",
										listeners: {
											render: (ev) => {
												ev.target.el.on('click', (clickEvent) => {
													clickEvent.preventDefault();
													if (window.getSelection()!.toString().length > 0) return;
													go.showComposer({
														to: value.email,
														name: entity.name,
														entity: "Contact",
														entityId: entity.id
													});
												});
											}
										}
									},
									comp({
										tagName: "h5",
										text: t("emailTypes", "community", "addressbook")[value.type]
									}),
									comp({text: value.email})
								)
							)
						})
					),

					comp({cls: "hbox", flex: 1, hidden: !entity.phoneNumbers?.length},
						comp({tagName: "i", cls: "icon ic-phone", style: {marginRight: "1.8rem"}}),
						arrayfield({
							flex: 1,
							name: "phoneNumbers",
							buildField: (value) => containerfield({},
								comp({
										style: {cursor: "pointer"},
										cls: "vbox",
										listeners: {
											render: (ev) => {
												ev.target.el.on('click', (clickEvent) => {
													clickEvent.preventDefault();
													window.location.href = `tel:${value.number.replace(/[^0-9+]/g, '')}`;
												});
											}
										}
									},
									comp({
										tagName: "h5",
										text: t("phoneTypes", "community", "addressbook")[value.type]
									}),
									comp({text: value.number})
								)
							)
						})
					),

					hr({hidden: !entity.addresses?.length}),
					comp({cls: "hbox", flex: 1, hidden: !entity.addresses?.length},
						comp({tagName: "i", cls: "icon ic-location-on", style: {marginRight: "1.8rem"}}),
						arrayfield({
							flex: 1,
							name: "addresses",
							buildField: (value) => containerfield({},
								comp({
										style: {cursor: "pointer"},
										cls: "vbox",
										listeners: {
											render: (ev) => {
												ev.target.el.on('click', (clickEvent) => {
													clickEvent.preventDefault();
													if (window.getSelection()!.toString().length > 0) return;
													go.util.streetAddress(value);
												});
											}
										}
									},
									comp({
										tagName: "h5",
										text: t("addressTypes", "community", "addressbook")[value.type] ?? value.type
									}),
									comp({text: value.formatted})
								)
							)
						})
					),

					hr({hidden: !entity.dates?.length}),
					comp({cls: "hbox", flex: 1, hidden: !entity.dates?.length},
						comp({tagName: "i", cls: "icon ic-cake", style: {marginRight: "1.8rem"}}),
						arrayfield({
							flex: 1,
							name: "dates",
							buildField: (value) => containerfield({},
								comp({cls: "vbox"},
									comp({
										tagName: "h5",
										text: t("dateTypes", "community", "addressbook")[value.type] ?? value.type
									}),
									comp({text: Format.date(value.date)})
								)
							)
						})
					),

					hr(),
					comp({cls: "hbox", flex: 1},
						comp({tagName: "i", cls: "icon ic-import-contacts", style: {marginRight: "1.8rem"}}),
						comp({cls: "vbox"},
							comp({tagName: "h5", text: t("Address book")}),
							comp({text: this.addressBook.name}),
							...(entity.gender !== undefined ? [
								comp({tagName: "h5", text: t("Gender")}),
								displayfield({
									name: "gender",
									tagName: "div",
									renderer: v => {
										switch (v) {
											case 'M':
												return t("Male");
											case 'F':
												return t("Female");
											case 'N':
												return t("Non-binary");
											case 'P':
												return t("Won't say");
											default:
												return "";
										}
									}
								})
							] : [])
						)
					),

					...(entity.IBAN || entity.registrationNumber || entity.debtorNumber ? [
						tbar({cls: "border-top"},
							h4({text: t("Company")}),
							'->',
							collapsebtn({target: btn => btn.parent!.nextSibling()!})
						),
						comp({cls: "vbox"},
							displayfield({name: "IBAN", label: t("IBAN"), hideWhenEmpty: true}),
							displayfield({name: "vatNo", label: t("VAT number"), hideWhenEmpty: true}),
							displayfield({
								name: "vatReverseCharge",
								label: t("Reverse charge VAT"),
								renderer: (v) => v ? t("Yes") : t("No"),
								hideWhenEmpty: true
							}),
							displayfield({
								name: "registrationNumber",
								label: t("Registration number"),
								hideWhenEmpty: true
							}),
							displayfield({name: "debtorNumber", label: t("Debtor number"), hideWhenEmpty: true}),
						)
					] : []),
				),

				...(entity.notes ? [
					comp({cls: "normalized card"},
						tbar({}, h3({text: t("Notes")})),
						displayfield({
							name: "notes",
							cls: "pad",
							tagName: "div",
							flex: 1
						})
					)
				] : []),
			);

			this.actionAtField.value = entity.actionAt ?? undefined;
			this.starButton.text = entity.starred ? t("Unstar") : t("Star");

			this.removeFromGroupButton.menu = undefined;
			this.removeFromGroupButton.disabled = true;

			if (entity.groups?.length) {
				const groupMenu = menu({});
				const response = await addressBookGroupDS.get(entity.groups);

				response.list.forEach((group) => {
					groupMenu.items.add(
						btn({
							text: group.name,
							handler: () => {
								const updateGroups = entity.groups!;
								const i = updateGroups.indexOf(group.id);
								if (i > -1) updateGroups.splice(i, 1);
								contactDS.update(entity.id, {groups: updateGroups});
							}
						})
					);
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
					const dlg = new ContactDialog(this.entity!.isOrganization);

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