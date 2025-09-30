import {
	autocompletechips,
	checkbox, chips, column,
	comp, datasourcestore,
	DefaultEntity, fieldset, listStoreType, storeRecordType,
	t, table, textarea, TextField,
	textfield
} from "@intermesh/goui";
import {FormWindow, jmapds} from "@intermesh/groupoffice-core";

export class AliasDialog extends FormWindow {
	private domainFld: TextField;
	constructor() {
		super("MailAlias");

		this.title = t("Alias");
		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;

		this.generalTab.items.add(
			fieldset({flex: 1},
				comp({cls: "row"},
					textfield({
						name: "address",
						id: "address",
						label: t("Address"),
						required: true,
						hint: t("Use '*' for a catch all alias (not recommended)."),
					}),
					this.domainFld = textfield({
						name: "domain",
						id: "domain",
						label: t("Domain"),
						disabled: true,
						icon: "alternate_email"
					}),
				),
				autocompletechips({
					name: "recipients",
					id: "recipients",
					label: t("Goto"),
					listeners: {
						autocomplete: ({target, input}) => {
							target.list.store.setFilter("search", {text: input});
							void target.list.store.load();
						}
					},

					textInputToValue: async text => {
						return text;
					},

					pickerRecordToValue (field, record) : any {
						return record.username;
					},

					list: table({
						fitParent: true,
						headers: false,
						store: datasourcestore({
							dataSource: jmapds("MailBox"),
							queryParams: {
								limit: 50
							},
							sort: [{property: "username", isAscending: true}]
						}),
						columns: [
							column({
								header: t("Username"),
								id: "username"
							})
						]
					})
				}),
				checkbox({
					label: t("Active"),
					name: "active",
					id: "active",
					type: "switch",
					value: true
				}),
			)
		);

		this.on("ready", async () => {

			if (this.form.currentId) {
				const idField = 	this.form.findField("address")!;
				let address = idField.value as String;
				if (address.indexOf("@") > -1) {
					const parts = address.split("@")
					address = parts[0];
					if(address.length === 0) {
						address = "*";
					}

					this.domainFld.value = parts[1];
				}
				idField.value = address;

				idField.readOnly = true;

				this.form.trackReset();
			}
		});

		this.form.on("beforesave", ({data}) => {
			if(!this.form.currentId) {
				data.address = data.address + "@" + this.form.findField("domain")!.value;
			}
		});
	}

}

