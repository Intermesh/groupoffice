import {AutocompleteChips, Component, containerfield, datasourceform, fieldset, radio, t} from "@intermesh/goui";
import {notebookcombo} from "./NoteBookCombo";
import {AppSettingsPanel, User, userDS} from "@intermesh/groupoffice-core";
import {notebookchips} from "./NoteBookChips.js";

export class SettingsPanel extends AppSettingsPanel {
	private readonly form;
	private syncNoteBooksField;
	private noteBookCombo;

	constructor() {
		super();

		this.title = t("Notes");

		this.form = datasourceform({
				dataSource: userDS
			},
			fieldset({
					legend: t("Display options for notebooks")
				},
				containerfield({
						name: "notesSettings"
					},
					this.noteBookCombo = notebookcombo({
						name: "defaultNoteBookId",
						label: t("Default note book")
					}),
					radio({
						name: "rememberLastItems",
						label: t("Start in"),
						type: "box",
						value: "lastNotebook",
						options: [
							{text: t("Default note book"), value: 0},
							{text: t("Remember last selected note book"), value: 1}
						]
					}),



					this.syncNoteBooksField = notebookchips({
						label: t("Synchronize"),
						name: "syncNoteBookIds"
					}),
				)
			)
		)

		this.items.add(this.form);
	}

	async save() {
		return this.form.submit();
	}

	async load(user: User) {
		this.form.currentId = user.id;
		this.form.value = user;

		this.syncNoteBooksField.list.store.setFilter("permissions", {permissionLevelUserId: user.id});
		this.noteBookCombo.list.store.setFilter("permissions", {permissionLevelUserId: user.id});
	}
}