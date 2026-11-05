import {btn, comp, Fieldset, t, tbar} from "@intermesh/goui";
import {WopiServiceTable} from "./WopiServiceTable";
import {WopiServiceDialog} from "./WopiServiceDialog";

export class Settings extends Fieldset {
	constructor() {
		super();
		this.legend = t("Services");

		const tbl = new WopiServiceTable();
		void tbl.store.load();

		this.items.add(tbar({},
				"->",
				btn({
					icon: "add",
					cls: "filled primary",
					text: t("Add"),
					handler: () => {
						const dlg = new WopiServiceDialog();
						dlg.show();
					}
				})
			),
			tbl
		)
	}
}