import {btn, checkbox, comp, containerfield, fieldset, Fieldset, h3, t, tbar, textfield} from "@intermesh/goui";
import {KeyGrid} from "./KeyGrid";
import {KeyDialog} from "./KeyDialog";

export class Settings extends Fieldset {
	constructor() {
		super();
		this.legend = t("API keys");

		const keyGrid = new KeyGrid();
		void keyGrid.store.load();

		this.items.add(comp({
				cls: "pad",
				text: t("API keys can be used for other services to connect to the API. A website feeding contact information for example.")
			}),
			tbar({},
				"->",
				btn({
					icon: "add",
					cls: "filled accent",
					text: t("Add key"),
					handler: () => {
						const dlg = new KeyDialog();
						dlg.show();
					}
				})
			),
			keyGrid
		);
	}
}