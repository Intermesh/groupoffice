import {Component, datasourceform, fieldset, Form, form, radio, t} from "@intermesh/goui";
import {notebookcombo} from "./NoteBookCombo";
import {jmapds} from "@intermesh/groupoffice-core";

export class SettingsPanel extends Component {
	private form;

	constructor() {
		super();

		this.form = datasourceform({
				dataSource: jmapds(""),
				title: t("Display options for notebooks")
			},
			fieldset({},
				notebookcombo({
					name: "defaultNotebook",
					label: t("Default note book")
				}),
				radio({
					name: "startInRadio",
					label: t("Start in"),
					type: "box",
					value: "lastNotebook",
					options: [
						{text: t("Default note book"), value: "defaultNotebook"},
						{text: t("Remember last selected note book"), value: "lastNotebook"}
					]
				})
			)
		)

		this.items.add(this.form);

	}

}