import {
	btn,
	checkboxselectcolumn,
	column,
	comp,
	EntityID,
	Filter,
	router,
	menu,
	searchbtn,
	t,
	tbar, Button, hr
} from "@intermesh/goui";
import {MainThreeColumnPanel, filterpanel, jmapds, client} from "@intermesh/groupoffice-core";
import {notebookgrid, NoteBookGrid} from "./NoteBookGrid";
import {NoteBookDialog} from "./NoteBookDialog";
import {NoteGrid} from "./NoteGrid";
import {NoteDetail} from "./NoteDetail";
import {NoteDialog} from "./NoteDialog";

export class Main extends MainThreeColumnPanel {
	private noteBookGrid!: NoteBookGrid;
	private noteGrid!: NoteGrid;
	protected east!: NoteDetail;
	private addButton!: Button;

	constructor() {
		super("notes");

		this.on("render", async () => {
			void this.noteBookGrid.store.load();
		});
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
				searchbtn({
					listeners: {
						input: (sender, text) => {
							(this.noteBookGrid.store.queryParams.filter as Filter).text = text;
							this.noteBookGrid.store.load();
						}
					}
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
			comp({
				flex: 1,
				cls: "scroll"
			}, this.noteBookGrid = notebookgrid({
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

							this.addButton.disabled = !noteBookIds[0];

							if (client.user.notesSettings.rememberLastItems) {
								jmapds("User").update(client.user.id, {
									notesSettings: {
										lastNoteBookIds: noteBookIds
									}
								})
							}
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
						renderer: (columnValue: any, record, td, table, rowIndex) => {
							return btn({
								icon: "more_vert",
								menu: menu({},
									btn({
										icon: "edit",
										text: t("Edit"),
										handler: () => {
											const record = table.store.get(rowIndex)!;

											const dlg = new NoteBookDialog();
											dlg.load(record.id);
											dlg.show();

										}
									})
								)
							})
						}
					})
				]
			})),
			filterpanel({
				flex: 1,
				store: this.noteGrid.store,
				entityName: "Note"
			})
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

						if (record) {
							router.goto("notes/" + record.id);
						}
					}
				}
			}
		};

		this.addButton = btn({
			cls: "filled primary",
			text: "Add",
			icon: "add",
			disabled: true,
			handler: () => {
				const dlg = new NoteDialog();

				const noteBookId = this.noteBookGrid.rowSelection!.getSelected()[0].record.id;

				dlg.form.value = {
					noteBookId: noteBookId
				};
				dlg.show();
			}
		});

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
				this.addButton,
				btn({
					icon: "more_vert",
					menu: menu({},
						btn({
							icon: "cloud_upload",
							text: t("Import"),
							handler: () => {
								go.util.importFile(
									'Note',
									'.csv, .xlsx, .json',
									{},
									{}
								);
							}
						}),
						hr(),
						btn({
							icon: "cloud_download",
							text: t("Export"),
							menu: menu({},
								btn({
									icon: "unknown_document",
									text: t("Microsoft Excel"),
									handler: () => {
										go.util.exportToFile(
											'Note',
											this.noteGrid.store.queryParams,
											"xlsx");
									}
								}),
								btn({
									icon: "csv",
									text: "Comma Seperated Values",
									handler: () => {
										go.util.exportToFile(
											'Note',
											this.noteGrid.store.queryParams,
											"csv");
									}
								}),
								btn({
									icon: "html",
									text: t("Web page") + " (HTML)",
									handler: () => {
										go.util.exportToFile(
											'Note',
											this.noteGrid.store.queryParams,
											"html");
									}
								}),
								btn({
									icon: "text_snippet",
									text: "JSON",
									handler: () => {
										go.util.exportToFile(
											'Note',
											this.noteGrid.store.queryParams,
											"json");
									}
								})
							)
						})
					)
				})
			),
			comp({
					cls: "scroll bg-lowest",
					flex: 1
				},
				this.noteGrid
			)
		)
	}

	protected createEast() {
		const detail = new NoteDetail();
		detail.toolbar.items.insert(0, this.showCenterButton());
		return detail;
	}

	public showNote(noteId: EntityID) {
		this.activatePanel(this.east);
		void this.east.load(noteId)
	}
}