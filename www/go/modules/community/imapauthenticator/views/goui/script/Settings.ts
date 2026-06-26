import {btn, Fieldset, t, tbar} from "@intermesh/goui";
import {ImapAuthServerTable} from "./ImapAuthServerTable";
import {ImapAuthServerDialog} from "./ImapAuthServerDialog";

export class Settings extends Fieldset {
	constructor() {
		super();

		this.legend = t("Profiles", "community", "imapauthenticator")

		const tbl = new ImapAuthServerTable();
		void tbl.store.load();

		this.items.add(tbar({},
				"->",
				btn({
					icon: "add",
					cls: "filled primary",
					text: t("Add"),
					handler: () => {
						const dlg = new ImapAuthServerDialog();
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