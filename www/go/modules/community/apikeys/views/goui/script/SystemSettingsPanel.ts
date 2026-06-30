import {btn, comp, Component, Fieldset, h4, p, t, tbar} from "@intermesh/goui";
import {KeyGrid} from "./KeyGrid.js";
import {KeyDialog} from "./KeyDialog.js";

export class SystemSettingsPanel extends Fieldset {
	constructor() {
		super();

		this.title = t("API keys");

		const keyGrid = new KeyGrid();
		void keyGrid.store.load();

		this.items.add(

			h4(t("API keys")),

			p({text: t("API keys can be used for other services to connect to the API. A website feeding contact information for example.")}),

			comp({cls: "card vbox"},
				tbar({cls: "bg-low"},
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
			)
		)
	}
}