import {btn, Button} from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import {Notifier} from "../../../../../../../views/Extjs3/goui/script/Notifier.js";
import {tbar} from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import {store, Store, StoreRecord} from "../../../../../../../views/Extjs3/goui/script/data/Store.js";
import {
	checkboxcolumn,
	column,
	datecolumn,
	table,
	Table
} from "../../../../../../../views/Extjs3/goui/script/component/Table.js";
import {DateTime} from "../../../../../../../views/Extjs3/goui/script/util/DateTime.js";
import {comp, Component} from "../../../../../../../views/Extjs3/goui/script/component/Component.js";
import {splitter} from "../../../../../../../views/Extjs3/goui/script/component/Splitter.js";

import {
	DescriptionList,
	dl,
	DLRecord
} from "../../../../../../../views/Extjs3/goui/script/component/DescriptionList.js";
import {Format} from "../../../../../../../views/Extjs3/goui/script/util/Format.js";
import {jmapstore} from "../../../../../../../views/Extjs3/goui/script/api/JmapStore.js";
import {NoteGrid} from "./NoteGrid.js";
import {NoteBookGrid, notebookgrid} from "./NoteBookGrid.js";
import {rowselect} from "../../../../../../../views/Extjs3/goui/script/component/TableRowSelect.js";

declare global {
	var GO: any;
}
;




class GouiTest extends Component {

	// class hbox devides screen in horizontal columns
	private descriptionList!: DescriptionList;
	private noteBookGrid!: NoteBookGrid;
	private noteGrid!: NoteGrid;

	public constructor() {
		super();

		this.cls = "hbox fit";

		const center = this.createCenter(), west = this.createWest(), east = this.createEast();

		this.items.add(
			west,
			splitter({
				stateId: "gouidemo-splitter-west",
				resizeComponentPredicate: west
			}),
			center,
			splitter({
				stateId: "gouidemo-splitter-east",
				resizeComponentPredicate: east
			}),
			east
		);

		this.on("render", () => {
			this.noteBookGrid.store.load();
		})
	}

	private createEast() {
		this.descriptionList = dl({
			cls: "pad"
		});

		return comp({
				cls: "fit vbox",
				width: 300
			},
			tbar({},

				comp({
					flex: 1,
					tagName: "h3",
					text: "Detail"
				}),

				btn({
					icon: "edit"
				})
			),
			this.descriptionList
		)
	}

	private createWest() {

		const records = [];
		for (let i = 1; i <= 20; i++) {
			records.push({
				id: i,
				name: "Test " + i,
				selected: i == 1
			});
		}


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
					handler:  () => {

					}
				})
			),
			this.noteBookGrid = notebookgrid({
				flex: 1,
				cls: "fit no-row-lines",
				rowSelection: {

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
					// checkboxcolumn({
					// 	property: "selected"
					// }),

					column({
						header: "Name",
						property: "name",
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
		this.noteGrid.cls = "fit";
		this.noteGrid.rowSelection = {
			multiSelect: true,
			listeners: {
				selectionchange: (tableRowSelect) => {
					if (tableRowSelect.selected.length == 1) {
						const table = tableRowSelect.table;
						const record = table.store.get(tableRowSelect.selected[0]);
						this.showRecord(record);
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
				btn({
					text: "Test GOUI!",
					handler: () => {
						Notifier.success("Hurray! GOUI has made it's way into Extjs 3.4 :)");
					}
				}),
				btn({
						text: "Open files",
						handler:  () => {
							// window.GO.mainLayout.openModule("files");
							window.GO.files.openFolder();
						}
					}
				)
			),
			this.noteGrid
		)
	}

	private showRecord(record: StoreRecord) {
		const records: DLRecord = [
			['Number', record.number],
			['Description', record.description],
			['Created At', Format.date(record.createdAt)]
		];

		this.descriptionList.records = records;
	}
}

export const gouiTest = new GouiTest();