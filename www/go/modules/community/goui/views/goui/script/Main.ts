import {btn, checkboxselectcolumn, column, comp, EntityID, menu, router, searchbtn, t, tbar} from "@intermesh/goui";

import {NoteGrid} from "./NoteGrid.js";
import {NoteBookGrid, notebookgrid} from "./NoteBookGrid.js";
import {NoteDetail} from "./NoteDetail.js";
import {NoteDialog} from "./NoteDialog.js";
import {NoteBookDialog} from "./NoteBookDialog";
import {MainThreeColumnPanel} from "@intermesh/groupoffice-core";


export class Main extends MainThreeColumnPanel {
	private noteBookGrid!: NoteBookGrid;
	private noteGrid!: NoteGrid;
	protected east!: NoteDetail;

	constructor() {
		super("goui-notes");

		this.on("render", async () => {
			void this.noteBookGrid.store.load();
			const first = this.noteBookGrid.store.first();
			if(first)
				this.noteBookGrid.rowSelection!.add(first);
		})
	}
	protected createWest() {

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
						const dlg = new NoteBookDialog();
						dlg.show();
					}
				}),
				this.showCenterButton()
			),
			comp({flex: 1, cls: "scroll"},
	this.noteBookGrid = notebookgrid({
					fitParent: true,
					cls: "no-row-lines",
					rowSelectionConfig: {
						multiSelect: true,
						listeners: {
							selectionchange: (tableRowSelect) => {

								const noteBookIds = tableRowSelect.getSelected().map((row) => row.record.id);

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
						}),
						column({
							width: 48,
							id: "btn",
							// headerRenderer: (col: TableColumn, headerEl: HTMLTableCellElement, table: Table) => {
							// 	headerEl.style.position = "sticky";
							// 	headerEl.style.right = "0";
							// 	return "";
							// },
							renderer: (columnValue: any, record, td, table, rowIndex) => {
								// td.style.position = "sticky";
								// td.style.right = "0";
								return btn({
									icon: "more_vert",
									menu: menu({

									},
										btn({
											icon: "edit",
											text: t("Edit"),
											handler: () => {
												const record = table.store.get(rowIndex)!

												const dlg = new NoteBookDialog();
												dlg.load(record.id);
												dlg.show();
											}
										}))

								})
							}
						})
					]
				})
			)
		);
	}

	protected createCenter() {

		this.noteGrid = new NoteGrid();

		this.noteGrid.title = "Notes";

		this.noteGrid.rowSelectionConfig = {
			multiSelect: true,
			listeners: {
				selectionchange: (tableRowSelect) => {
					if (tableRowSelect.getSelected().length == 1) {
						const record = tableRowSelect.getSelected()[0].record;
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
				this.showWestButton(),
				"->",
				searchbtn({
					listeners: {
						input: (sender, text) => {
							this.noteGrid.store.setFilter("search", {text});
							void this.noteGrid.store.load();
						}
					}
				}),
				btn({
					cls: "filled primary",
					text: "Add",
					icon: "add",
					handler: () => {
						const dlg = new NoteDialog();
						const noteBookId = this.noteBookGrid.rowSelection!.getSelected()[0].record.id;

						dlg.form.value = {
							noteBookId: noteBookId
						};
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

	protected createEast(){
		const d= new NoteDetail();
		d.toolbar.items.insert(0, this.showCenterButton());
		return d;
	}


	showNote(noteId: EntityID) {
		void this.east.load(noteId);
		this.activatePanel(this.east);
	}

}
