import {btn, comp, Component, t, tbar} from "@intermesh/goui";
import {KeyGrid} from "./KeyGrid.js";
import {KeyDialog} from "./KeyDialog.js";

export class SystemSettingsPanel extends Component {
	constructor() {
		super();

		this.title = t("API keys");

		const keyGrid = new KeyGrid();
		void keyGrid.store.load();

		this.items.add(
			comp({
				cls: "pad",
				text: t("API keys can be used for other services to connect to the API. A website feeding contact information for example.")
			}),
			tbar({},
				btn({
					text: t("Add key"),
					handler: () => {
						const dlg = new KeyDialog();
						dlg.show();
					}
				})
			),
			keyGrid
		)
	}
}