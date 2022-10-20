import {comp, Component} from "@goui/component/Component.js";
import {splitter} from "@goui/component/Splitter.js";
import {tbar} from "@goui/component/Toolbar.js";
import {btn} from "@goui/component/Button.js";
import {checkboxselectcolumn, column} from "@goui/component/table/TableColumns.js";
import {StoreRecord} from "@goui/data/Store.js";

import {NoteGrid} from "./NoteGrid.js";
import {NoteBookGrid, notebookgrid} from "./NoteBookGrid.js";
import {NoteDetail} from "./NoteDetail.js";
import {NoteDialog} from "./NoteDialog.js";
import {router} from "@goui/Router.js";
import {t} from "@goui/Translate.js";


export class Main extends Component {

	// class hbox devides screen in horizontal columns
	private noteBookGrid!: NoteBookGrid;
	private noteGrid!: NoteGrid;
	readonly noteDetail!: NoteDetail;

	public constructor() {
		super();

		this.cls = "hbox fit";

		const west = this.createWest();

		this.noteDetail = new NoteDetail();

		this.items.add(
			west,
			splitter({
				stateId: "gouidemo-splitter-west",
				resizeComponentPredicate: west
			}),
			this.createCenter(),
			splitter({
				stateId: "gouidemo-splitter-east",
				resizeComponentPredicate: this.noteDetail
			}),
			this.noteDetail
		);

		this.on("render", async () => {
			const records = await this.noteBookGrid.store.load();
			this.noteBookGrid.rowSelection!.selected = [0];
		})
	}



	private createWest() {


		return comp({
				cls: "vbox",
				width: 300
			},
			tbar({
					cls: "border-bottom"
				},
				comp({
					tagName: "h3",
					text: "Notebooks",
					flex: 1
				}),

				btn({
					icon: "add",
					handler: () => {

					}
				})
			),
			this.noteBookGrid = notebookgrid({
				flex: 1,
				cls: "fit no-row-lines",
				rowSelectionConfig: {
					multiSelect: true,
					listeners: {
						selectionchange: (tableRowSelect) => {

							const noteBookIds = tableRowSelect.selected.map((index) => tableRowSelect.table.store.get(index).id);

							this.noteGrid.store.queryParams.filter = {
								noteBookId: noteBookIds
							};

							this.noteGrid.store.load();
						}
					}
				},
				columns: [
					checkboxselectcolumn(),
					column({
						header: t("Name"),
						id: "name",
						sortable: true,
						resizable: false
					})
				]
			})
		);
	}

	private createCenter() {

		this.noteGrid = new NoteGrid();
		this.noteGrid.flex = 1;
		this.noteGrid.title = "Notes";
		this.noteGrid.cls = "fit light-bg";
		this.noteGrid.rowSelectionConfig = {
			multiSelect: true,
			listeners: {
				selectionchange: (tableRowSelect) => {
					if (tableRowSelect.selected.length == 1) {
						const table = tableRowSelect.table;
						const record = table.store.get(tableRowSelect.selected[0]);

						router.goto("goui-notes/" + record.id);
					}
				}
			}
		};


		return comp({
				cls: "vbox",
				flex: 1
			},
			tbar({
					cls: "border-bottom"
				},
				"->",
				// textfield({
				// 	label: t("Search"),
				// 	buttons: [
				// 		btn({icon: "clear", handler:(btn) => (btn.parent!.parent! as Field).value = ""})
				// 	]
				// }),
				btn({
					cls: "primary",
					icon: "add",
					handler: () => {
						const dlg = new NoteDialog();
						const noteBookId = this.noteBookGrid.store.get(this.noteBookGrid.rowSelection!.selected[0]).id;

						dlg.form.setValues({
							noteBookId: noteBookId
						});
						dlg.show();

					}
				})
			),
			this.noteGrid
		)
	}

	public  showNote(noteId: number) {
		this.noteDetail.load(noteId);
	}

}
