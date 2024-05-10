import {
	ArrayField,
	arrayfield,
	btn,
	comp,
	Component,
	containerfield, DefaultEntity, EntityID,
	fieldset,
	t,
	tbar,
	textarea, TextField,
	textfield
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class DnsSettingsForm extends Component {

	public dkimKeyFlds: ArrayField | undefined;
	public spfFld: TextField | undefined;
	public dmarcFld: TextField | undefined;
	public mxFld: TextField | undefined;

	private entity: DefaultEntity | undefined;

	constructor() {
		super();
		const fd = fieldset({legend: t("DKIM keys", "community", "maildomains")},
			this.dkimKeyFlds = arrayfield({
				name: "dkimRecords",
				buildField: () => {
					const fld = containerfield({
						cls: "group",
					},
						comp({cls: "vbox fit"},
							comp({cls: "hbox"},
								textfield({
									name: "selector",
									flex: 1,
									label: t("Selector", "community", "maildomains"),
									placeholder: "s1",
									buttons: [btn({
										icon: "warning",
										hidden: true,
										cls: "accent"
									}),btn({
										icon: "check_circle",
										title: t("OK"),
										hidden: true
									})]
								}),
							),

							comp({cls: "hbox padding-top" },
								textarea({
									flex: 1,
									name: "txt",
									label: t("mail_domainkey TXT record")
								}),
								btn({
									icon: "delete",
									disabled: true,
									title: "Delete",
									handler: (btn) => {
										fld.remove();
									}
								})
							)
						)
					);
					return fld;
				},
				value: []
			}),
			tbar({}, "->", btn({disabled: true, icon: "add", cls: "outlined", text: t("Add"), handler: () => {}}))
		); // f

		const fd2 = fieldset({},
			this.spfFld = textfield({
				name: "spf",
				label: "SPF",
				placeholder: "v=spf1 a:smtp.example.com a:smtp2.example.com ip4: ip4: -all",
				buttons: [btn({
					icon: "warning",
					cls: "accent",
					hidden: true
				}),btn({
					icon: "check_circle",
					title: t("OK"),
					hidden: true
				})]
			}),
			
			this.dmarcFld = textfield({
				name: "dmarc",
				label: "DMARC",
				placeholder: "v=DMARC1; p=quarantine; rua=postmaster@example.com",
				buttons: [btn({
					icon: "warning",
					cls: "accent",
					hidden: true
				}),btn({
					icon: "check_circle",
					title: t("OK"),
					hidden: true
				})]
			}),
			
			this.mxFld = textfield({
				// icon: "check_circle",
				name: "mx",
				label: "MX",
				placeholder: "smtp.example.com",
				buttons: [btn({
					icon: "warning",
					cls: "accent",
					hidden: true
				}),btn({
					icon: "check_circle",
					title: t("OK"),
					hidden: true
				})]
			})
		);
		this.items.add(fd, fd2);
	}

	public load(entity: DefaultEntity) {
		this.dkimKeyFlds.value = entity.dkimRecords;
		entity.dkimRecords.forEach( (k,r) => {
			const c = this.dkimKeyFlds.items.items[r], slf = c.findField("selector");
			slf.buttons[parseInt(k.status)].hidden = false;
		});
		this.spfFld.buttons[parseInt(entity.spfStatus)].hidden = false;
		this.dmarcFld.buttons[parseInt(entity.dmarcStatus)].hidden = false;
		this.mxFld.buttons[parseInt(entity.mxStatus)].hidden = false;
	}
}