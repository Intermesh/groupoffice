import {btn, Fieldset, t, tbar} from "@intermesh/goui";
import {LdapAuthServerTable} from "./LdapAuthServerTable";
import {LdapAuthServerDialog} from "./LdapAuthServerDialog";

export class Settings extends Fieldset {
	constructor() {
		super();

		this.legend = t("Profiles");

		const tbl = new LdapAuthServerTable();
		void tbl.store.load();

		this.items.add(tbar({},
				"->",
				btn({
					icon: "add",
					cls: "filled primary",
					text: t("Add"),
					handler: () => {
						const dlg = new LdapAuthServerDialog();
						dlg.show();
					}
				})
			),
			tbl
		)
	}

	async load(): Promise<any>  {
		// Do not do anything
	}
}