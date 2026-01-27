import {FormWindow, modules} from "@intermesh/groupoffice-core";
import {checkbox, fieldset, t, TextField, textfield} from "@intermesh/goui";

export class WopiServiceDialog extends FormWindow {
	private wopiClientUrlFld: TextField;

	constructor() {
		super("WopiService");
		this.title = t("Service");

		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;
		this.height = 800;

		this.generalTab.items.add(
			fieldset({},
				textfield({
					name: "url",
					label: t("URL"),
					required: true
				}),
				checkbox({
					type: "switch",
					name: "useAltWopiClientUri",
					label: t("Use alternative WOPI client URI (Only for Microsoft Office Online)"),
					listeners: {
						change: ({newValue}) => {
							this.wopiClientUrlFld.disabled = !newValue;
						}
					}

				}),
				this.wopiClientUrlFld = textfield({
					name: "wopiClientUri",
					label: t("WOPI client URI"),
					value: modules.get("core", "core")?.settings.URL + "/wopi",
					required: true,
					disabled: true
				}),
			)
		);
		this.addSharePanel([
			{value: "", name: ""},
			{value: 10, name: t("Read")},
			{value: 30, name: t("Write")},
		]);

		this.form.on("beforesave", ({data}) => {
			if (data.useAltWopiClientUri === false) {
				data.wopiClientUri = null;
			} else if (data.useAltWopiClientUri === true && !data.wopiClientUri) {
				data.wopiClientUri = this.wopiClientUrlFld.value;
			}
			delete data.useAltWopiClientUri;
		});

		this.form.on("beforeload", ({data}) => {
			if (data.wopiClientUri) {
				data.useAltWopiClientUri = true;
				this.wopiClientUrlFld.disabled = false;
			}
		})
	}

}