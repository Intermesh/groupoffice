import {
	ArrayField,
	arrayfield, browser, BrowserStore,
	btn,
	comp,
	Component, ContainerField,
	containerfield, ContainerFieldValue, DefaultEntity, displayfield, EntityID, Fieldset,
	fieldset, Form, mapfield, Notifier,
	t,
	tbar,
	textarea, TextAreaField, TextField,
	textfield
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class DnsSettingsForm extends Component {

	public dkimKeyFlds;
	public spfFld!: TextField;
	public dmarcFld!: TextField;
	public mxFld!: TextField;

	private entity: DefaultEntity | undefined;

	constructor() {
		super();
		const fd = fieldset({legend: t("DKIM keys", "community", "maildomains")},
			this.dkimKeyFlds = mapfield({
				name: "dkim",
				keyFieldName: "selector",
				buildField: () => {
					const fld = containerfield({
						cls: "hbox",
					},
						comp({cls: "flow", flex: 1},
							comp({cls: "hbox"},
								textfield({
									name: "selector",
									flex: 1,
									label: t("Selector", "community", "maildomains"),
								}),
								displayfield({
									escapeValue: false,
									name: "status",
									renderer: (v, field) => {
										return `<i class="icon ${v ? 'success' : 'danger'}">${v ? 'check_circle' : 'warning'}</i>`;
									}
								})
							),

							comp({cls: "hbox padding-top" },
								textarea({
									flex: 1,
									readOnly: true,
									name: "publicKey",
									label: t("Public key"),
									autoHeight: true,
									buttons: [btn({
										icon: "content_copy",
										title: t("Copy to clipboard"),
										handler: (b) => {
											browser.copyTextToClipboard(b.findAncestorByType(TextAreaField)!.value + "");
											Notifier.success(t("Key is copied to clipboard"))
										}
									})]
								})
							)
						),
							btn({
								style: {alignSelf: "top"},
								icon: "delete",
								title: "Delete",
								handler: (btn) => {
									fld.remove();
								}
							})

					);
					return fld;
				}
			}),
			tbar({},
				"->",
				btn({
					icon: "add",
					cls: "outlined",
					text: t("Add"),
					handler: () => {
						this.dkimKeyFlds.add({selector: "mail" + (Object.keys(this.dkimKeyFlds.value).length + 1)});
					}
				}))
		);


		const fd2 = fieldset({},

			comp({cls: "group"},
				this.spfFld = textfield({
					flex: 1,
					name: "spf",
					label: "SPF",
					placeholder: "v=spf1 a:smtp.example.com a:smtp2.example.com ip4: ip4: -all",
					readOnly: true
				}),
				displayfield({
					escapeValue: false,
					name: "spfStatus",
					renderer: (v, field) => {
						return `<i class="icon ${v ? 'success' : 'danger'}">${v ? 'check_circle' : 'warning'}</i>`;
					}
				})
			),

			comp({cls: "group"},
				this.dmarcFld = textfield({
					flex: 1,
					name: "dmarc",
					label: "DMARC",
					placeholder: "v=DMARC1; p=quarantine; rua=postmaster@example.com",
					readOnly: true
				}),
				displayfield({
					escapeValue: false,
					name: "dmarcStatus",
					renderer: (v, field) => {
						return `<i class="icon ${v ? 'success' : 'danger'}">${v ? 'check_circle' : 'warning'}</i>`;
					}
				})
			),
			comp({cls: "group"},
			this.mxFld = textfield({
				flex: 1,
				name: "mx",
				label: "MX",
				placeholder: "smtp.example.com",
				readOnly: true
					}),
				displayfield({
					escapeValue: false,
					name: "mxStatus",
					renderer: (v, field) => {
						return `<i class="icon ${v ? 'success' : 'danger'}">${v ? 'check_circle' : 'warning'}</i>`;
					}
				})
			),
		);
		this.items.add(fd2, fd);
	}

	// public load(entity: DefaultEntity) {
	//
	// 	// this.dkimKeyFlds.value = entity.dkimRecords;
	// 	// entity.dkimRecords.forEach( (k:any,index:any) => {
	// 	// 	const c = this.dkimKeyFlds.items.first() as ContainerField, slf = c.findField("selector")!;
	// 	// 	slf.buttons[parseInt(k.status)].hidden = false;
	// 	// 	slf.buttons[k.status].hidden = false;
	// 	// });
	// 	this.spfFld.buttons[entity.spfStatus ?? 0].hidden = false;
	// 	this.dmarcFld.buttons[entity.dmarcStatus ?? 0].hidden = false;
	// 	this.mxFld.buttons[entity.mxStatus ?? 0].hidden = false;
	// }
}