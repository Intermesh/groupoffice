import {btn, checkboxselectcolumn, column, comp, Component, router, splitter, t, tbar} from "@intermesh/goui";

import {NoteGrid} from "./NoteGrid.js";
import {NoteBookGrid, notebookgrid} from "./NoteBookGrid.js";
import {NoteDetail} from "./NoteDetail.js";
import {NoteDialog} from "./NoteDialog.js";


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
			comp({flex: 1, cls: "scroll"},
	this.noteBookGrid = notebookgrid({
					fitParent: true,
					cls: "no-row-lines",
					rowSelectionConfig: {
						multiSelect: true,
						listeners: {
							selectionchange: (tableRowSelect) => {

								const noteBookIds = tableRowSelect.selected.map((index) => tableRowSelect.list.store.get(index)!.id);

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
			)
		);
	}

	private createCenter() {

		this.noteGrid = new NoteGrid();

		this.noteGrid.title = "Notes";

		this.noteGrid.rowSelectionConfig = {
			multiSelect: true,
			listeners: {
				selectionchange: (tableRowSelect) => {
					if (tableRowSelect.selected.length == 1) {
						const table = tableRowSelect.list;
						const record = table.store.get(tableRowSelect.selected[0]);

						if(record) {
							router.goto("goui-notes/" + record.id);
						}
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
					cls: "filled primary",
					text: "Add",
					icon: "add",
					handler: () => {
						const dlg = new NoteDialog();
						const noteBookId = this.noteBookGrid.store.get(this.noteBookGrid.rowSelection!.selected[0])!.id;

						dlg.form.setValues({
							noteBookId: noteBookId
						});
						dlg.show();

					}
				})
			),
			comp({
					cls: "scroll light-bg",
					flex: 1
				},
				this.noteGrid
			)
		)
	}

	public  showNote(noteId: number) {
		this.noteDetail.load(noteId);
	}

}
