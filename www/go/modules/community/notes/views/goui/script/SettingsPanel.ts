import {Component, fieldset, form, radio, t} from "@intermesh/goui";
import {notebookcombo} from "./NoteBookCombo";

export class SettingsPanel extends Component {

	constructor() {
		super();

		this.items.add(
			form({
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
		)
	}

}