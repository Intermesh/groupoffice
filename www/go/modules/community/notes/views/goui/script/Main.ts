import {
	btn,
	Button,
	checkbox,
	checkboxselectcolumn,
	column,
	comp,
	EntityID,
	h3,
	hr,
	menu,
	menucolumn,
	mstbar,
	router,
	searchbtn,
	t,
	tbar,
	Toolbar
} from "@intermesh/goui";
import {AclLevel, client, filterpanel, jmapds, MainThreeColumnPanel} from "@intermesh/groupoffice-core";
import {notebookgrid, NoteBookGrid} from "./NoteBookGrid";
import {NoteBookDialog} from "./NoteBookDialog";
import {NoteGrid} from "./NoteGrid";
import {NoteDetail} from "./NoteDetail";
import {NoteDialog} from "./NoteDialog";
import {noteBookDS, noteDS} from "./Index.js";

export class Main extends MainThreeColumnPanel {
	private noteBookGrid!: NoteBookGrid;
	private noteGrid!: NoteGrid;
	private noteGridToolbar!: Toolbar;
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
				cls: "scroll",
				width: 300,
			},
			tbar({},
				checkbox({
					listeners: {
						change: ({newValue}) => {
							const rs = this.noteBookGrid.rowSelection!
							newValue ? rs.selectAll() : rs.clear();
						}
					}
				}),
				h3(t("Notebooks")),
				"->",
				searchbtn({
					listeners: {
						input: ({text}) => {
							this.noteBookGrid.store.setFilter("search", {text});
							void this.noteBookGrid.store.load();
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
				flex: 1
			}, this.noteBookGrid = notebookgrid({
				headers: false,
				fitParent: true,
				stateId: "notes-noteBookGrid",
				cls: "no-row-lines",
				rowSelectionConfig: {
					multiSelect: true,
					listeners: {
						selectionchange: ({selected}) => {

							const noteBookIds = selected.map((row) => row.record.id);

							this.noteGrid.store.setFilter("notebook", {
								noteBookId: noteBookIds
							});

							void this.noteGrid.store.load();

							this.addButton.disabled = !noteBookIds[0];

							if (client.user.notesSettings.rememberLastItems) {
								void jmapds("User").update(client.user.id, {
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

					menucolumn({
							menu: menu({
									listeners: {
										show: ({target}) => {
											const record = this.noteBookGrid.store.get(target.dataSet.rowIndex)!;

											target.findChild("edit")!.disabled = record.permissionLevel < AclLevel.MANAGE;
											target.findChild("delete")!.disabled = !go.Modules.get("community", 'notes').userRights.mayChangeNoteBooks || record.permissionLevel < AclLevel.MANAGE;

										}
									}
								},
								btn({
									itemId: "edit",
									icon: "edit",
									text: t("Edit"),
									handler: (btn) => {
										const record = this.noteBookGrid.store.get(btn.parent!.dataSet.rowIndex)!

										const dlg = new NoteBookDialog();
										void dlg.load(record.id);
										dlg.show();

									}
								}),

								btn({
									itemId: "delete",
									icon: "delete",
									text: t("Delete"),
									handler: (btn) => {
										const record = this.noteBookGrid.store.get(btn.parent!.dataSet.rowIndex)!
										void noteBookDS.confirmDestroy([record.id]);
									}
								})
							)
						}
					),


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

		this.noteGrid.rowSelectionConfig = {
			multiSelect: true,
			listeners: {
				selectionchange: ({selected}) => {
					const noteIds = selected.map((row) => row.record.id);

					if (noteIds[0]) {
						router.goto("note/" + noteIds[0]);
					}

					noteIds.length > 1 ? this.noteGridToolbar.hide() : this.noteGridToolbar.show();
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
				cls: "vbox bg-lowest"
			},
			this.noteGridToolbar = tbar({
					cls: "bg-mid border-bottom"
				},
				this.showWestButton(),
				"->",
				searchbtn({
					listeners: {
						input: ({text}) => {
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
			mstbar({
					cls: "border-bottom",
					table: this.noteGrid
				},
				"->",
				btn({
					icon: "delete",
					handler: async () => {
						const noteIds = this.noteGrid!.rowSelection!.getSelected().map((row) => row.record.id);

						await noteDS.confirmDestroy(noteIds);
					}
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
