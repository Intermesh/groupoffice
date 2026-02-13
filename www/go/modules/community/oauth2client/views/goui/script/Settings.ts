import {btn, comp, Fieldset, t, tbar} from "@intermesh/goui";
import {Oauth2ClientTable} from "./Oauth2ClientTable";
import {Oauth2ClientDialog} from "./Oauth2ClientDialog";
export class Settings extends Fieldset {
	constructor() {
		super();
		this.legend = t("Services");

		const tbl = new Oauth2ClientTable();
		void tbl.store.load();

		this.items.add(tbar({},
				"->",
				btn({
					icon: "add",
					cls: "filled primary",
					text: t("Add"),
					handler: () => {
						const dlg = new Oauth2ClientDialog();
						dlg.show();
					}
				})
			),
			tbl
		)
	}
}