import {btn, Button} from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import {Notifier} from "../../../../../../../views/Extjs3/goui/script/Notifier.js";
import {tbar} from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import {Store, StoreRecord} from "../../../../../../../views/Extjs3/goui/script/data/Store.js";
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
import {TestWindow} from "./TestWindow.js";
import {
	DescriptionList,
	dl,
	DLRecord
} from "../../../../../../../views/Extjs3/goui/script/component/DescriptionList.js";
import {Format} from "../../../../../../../views/Extjs3/goui/script/util/Format.js";

declare global {
	var GO: any;
}
;

export class GouiTest extends Component {

	// class hbox devides screen in horizontal columns
	cls = "hbox fit";
	private descriptionList!: DescriptionList;

	protected init() {
		super.init();

		const center = this.createCenter(), west = this.createWest(), east = this.createEast();

		this.getItems().add(
			west,
			splitter({
				stateId: "gouidemo-splitter-west",
				resizeComponent: west
			}),
			center,
			splitter({
				stateId: "gouidemo-splitter-east",
				resizeComponent: east
			}),
			east
		);
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

				Component.create({
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
					handler: function () {
						const win = TestWindow.create();
						win.show();
					}
				})
			),
			table({
				flex: 1,
				title: "Table",
				store: Store.create({
					records: records,
					sort: [{property: "number", isAscending: true}]
				}),
				cls: "fit no-row-lines",
				columns: [
					checkboxcolumn({
						property: "selected"
					}),

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
		const records = [];
		for (let i = 1; i <= 20; i++) {
			records.push({
				number: i,
				description: "Test " + i,
				createdAt: (new DateTime()).addDays(Math.ceil(Math.random() * -365)).format("c")
			});
		}

		const tbl = table({
			flex: 1,
			title: "Table",
			store: Store.create({
				records: records,
				sort: [{property: "number", isAscending: true}]
			}),
			cls: "fit",
			columns: [
				column({
					header: "Number",
					property: "number",
					sortable: true,
					resizable: true,
					width: 200
				}),
				column({
					header: "Description",
					property: "description",
					sortable: true,
					resizable: true,
					width: 300
				}),
				datecolumn({
					header: "Created At",
					property: "createdAt",
					sortable: true
				})
			],
			rowSelection: {
				multiSelect: true,
				listeners: {
					selectionchange: (tableRowSelect) => {
						if (tableRowSelect.getSelected().length == 1) {
							const table = tableRowSelect.getTable();
							const record = table.getStore().getRecordAt(tableRowSelect.getSelected()[0]);
							this.showRecord(record);
						}
					}
				}
			},
		});

		return comp({
				cls: "vbox",
				flex: 1
			},
			tbar({
					cls: "border-bottom"
				},
				Button.create({
					text: "Test GOUI!",
					handler: function () {
						Notifier.success("Hurray! GOUI has made it's way into Extjs 3.4 :)");
					}
				}),
				Button.create({
						text: "Open files",
						handler: function () {
							// window.GO.mainLayout.openModule("files");
							window.GO.files.openFolder();
						}
					}
				)
			),
			tbl
		)
	}

	private showRecord(record: StoreRecord) {
		const records: DLRecord = [
			['Number', record.number],
			['Description', record.description],
			['Created At', Format.date(record.createdAt)]
		];

		this.descriptionList.setRecords(records);
	}
}