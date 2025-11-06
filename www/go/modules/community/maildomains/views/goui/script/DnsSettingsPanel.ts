import {
	ArrayField,
	arrayfield, browser, BrowserStore,
	btn, checkbox,
	comp,
	Component, ContainerField,
	containerfield, ContainerFieldValue, DefaultEntity, displayfield, EntityID, Fieldset,
	fieldset, Form, mapfield, Notifier,
	t,
	tbar,
	textarea, TextAreaField, TextField,
	textfield, Window
} from "@intermesh/goui";
import {jmapds, modules} from "@intermesh/groupoffice-core";
import {MailDomain} from "./MailDomain.js";
import {ImportDkimKeyDialog} from "./ImportDkimKeyDialog.js";

export class DnsSettingsPanel extends Component {

	public dkimKeyFlds;
	public spfFld!: TextField;
	public dmarcFld!: TextField;
	public mxFld!: TextField;

	public domain?: MailDomain
	private async save() {
		this.mask();
		try {
			await jmapds("MailDomain").update(this.domain!.id, {dkim: this.dkimKeyFlds.value})
			return true;
		} catch(e) {
			void Window.error(e);
			return false;
		} finally {
			this.unmask();
		}


	}

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
							comp({cls: "hbox gap"},
								textfield({
									name: "selector",
									flex: 1,
									label: t("Selector", "community", "maildomains"),
									listeners: {
										change:field => {
											this.save();
										}
									}
								}),
								checkbox({
									width: 200,
									name: "enabled",
									type: "switch",
									label: t("Enabled"),
									value: false,
									listeners: {
										change:field => {
											this.save();
										}
									}
								}),
								displayfield({
									escapeValue: false,
									name: "status",
									value: false,
									renderer: (v, field) => {
										return `<i class="icon ${v ? 'success' : 'danger'}">${v ? 'check_circle' : 'warning'}</i>`;
									}
								})
							),

							comp({cls: "hbox padding-top" },
								textarea({
									flex: 1,
									readOnly: true,
									name: "DNS",
									label: "DNS TXT record",
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
								handler: async (btn) => {

									const confirmed = await Window.confirm(t("Are you sure you want to delete the selected item?"));
									if(confirmed) {
										fld.remove();
										this.save();
									}
								}
							})

					);
					return fld;
				}
			}),
			tbar({},
				btn({
					icon: "cloud_upload",
					text: t("Import private key"),
					handler: () => {
						const d = new ImportDkimKeyDialog(this.domain!);
						d.show();

					}

				}),
				"->",
				btn({
					icon: "add",
					cls: "filled primary",
					text: t("Add"),
					handler: async () => {
						this.dkimKeyFlds.add({selector: "mail" + (Object.keys(this.dkimKeyFlds.value).length + 1)});
						this.save();
					}
				}))
		);


		const fd2 = fieldset({},

			comp({cls: "hbox gap"},
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
					value: false,
					style: {alignSelf: "start"},
					renderer: (v, field) => {
						const mod = modules.get("community", "maildomains")!;
						if(!v) {
							if(!this.spfFld.value) {
								this.spfFld.setInvalid(t("Your SPF record is not set"));
							} else {
								this.spfFld.setInvalid(t("Your SPF record does not allow '{mailhost}'").replace("{mailhost}", mod.settings.mailHost));
							}
						} else {
							this.spfFld.clearInvalid();
						}

						return `<i class="icon ${v ? 'success' : 'danger'}">${v ? 'check_circle' : 'warning'}</i>`;
					}
				})
			),

			comp({cls: "hbox gap"},
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
					style: {alignSelf: "start"},
					renderer: (v, field) => {

						if(!v) {
							if(!this.dmarcFld.value) {
								this.dmarcFld.setInvalid(t("Your DMARC record is not set"));
							} else {
								this.dmarcFld.setInvalid(t("Your DMARC record is invalid"));
							}
						} else {
							this.dmarcFld.clearInvalid();
						}

						return `<i class="icon ${v ? 'success' : 'danger'}">${v ? 'check_circle' : 'warning'}</i>`;
					}
				})
			),
			comp({cls: "hbox gap"},
				this.mxFld = textfield({
					flex: 1,
					name: "mx",
					label: "MX",
					placeholder: "smtp.example.com",
					readOnly: true
					}),
				displayfield({
					style: {alignSelf: "start"},
					escapeValue: false,
					name: "mxStatus",
					renderer: (v, field) => {

						if(!v) {
							if(!this.mxFld.value) {
								this.mxFld.setInvalid(t("Your MX record is not set"));
							} else {
								this.mxFld.setInvalid(t("Your MX record is not set to this mailserver"));
							}
						} else {
							this.mxFld.clearInvalid();
						}

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