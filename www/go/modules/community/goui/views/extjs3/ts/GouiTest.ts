import {Button} from "../../../../../../../views/Extjs3/goui/component/Button.js";
import {Alert} from "../../../../../../../views/Extjs3/goui/Alert.js";
import {Toolbar} from "../../../../../../../views/Extjs3/goui/component/Toolbar.js";
import {Store, StoreRecord} from "../../../../../../../views/Extjs3/goui/data/Store.js";
import {CheckboxColumn, DateColumn, Table} from "../../../../../../../views/Extjs3/goui/component/Table.js";
import {DateTime} from "../../../../../../../views/Extjs3/goui/util/DateTime.js";
import {Component} from "../../../../../../../views/Extjs3/goui/component/Component.js";
import {Splitter} from "../../../../../../../views/Extjs3/goui/component/Splitter.js";
import {TestWindow} from "./TestWindow.js";
import {DescriptionList} from "../../../../../../../views/Extjs3/goui/component/DescriptionList.js";
import {DLRecord} from "../../../../../../../views/Extjs3/goui/component/DescriptionList.js";
import {Format} from "../../../../../../../views/Extjs3/goui/util/Format.js";

declare global {
	var GO: any;
};

export class GouiTest extends Component {

	// class hbox devides screen in horizontal columns
	cls = "hbox fit";
	private descriptionList!: DescriptionList;

	protected init() {
		super.init();

		const center = this.createCenter(), west = this.createWest(), east = this.createEast();

		this.setItems([
			west,
			Splitter.create({
				stateId: "gouidemo-splitter-west",
				resizeComponent: west
			}),
			center,
			Splitter.create({
				stateId: "gouidemo-splitter-east",
				resizeComponent: east
			}),
			east
		]);
	}

	private createEast() {
		this.descriptionList = DescriptionList.create({
			cls: "pad",
			width: 300
		});

		return Component.create({
			cls: "fit vbox",
			items: [
				Toolbar.create({
					items: [
						Component.create({
							flex: 1,
							tagName: "h3",
							text: "Detail"
						}),
						Button.create({
							icon: "edit"
						})
					]
				}),
				this.descriptionList
			]
		})
	}

	private createWest() {

		const records = [];
		for(let i = 1; i <= 20; i++) {
			records.push({
				id: i,
				name: "Test " + i,
				selected: i == 1
			});
		}


		return Component.create({
			cls: "vbox",
			width: 300,
			items: [
				Toolbar.create({
					cls: "border-bottom",
					items: [
						Component.create({
							tagName: "h3",
							text: "Notebooks",
							flex: 1
						}),

						Button.create({
							icon: "add",
							handler: function () {
								const win = TestWindow.create();
								win.show();
							}
						})
					]

				}),
				Table.create({
					flex: 1,
					title: "Table",
					store: Store.create({
						records: records,
						sort: [{property: "number", isAscending: true}]
					}),
					cls: "fit no-row-lines",
					columns: [
						CheckboxColumn.create({
							property: "selected"
						}),

						{
							header: "Name",
							property: "name",
							sortable: true,
							resizable: false
						}
					]
				})
			]

		});
	}

	private createCenter() {
		const records = [];
		for(let i = 1; i <= 20; i++) {
			records.push({
				number: i,
				description: "Test " + i,
				createdAt: (new DateTime()).addDays(Math.ceil(Math.random() * -365)).format("c")
			});
		}

		const table = Table.create({
			flex: 1,
			title: "Table",
			store: Store.create({
				records: records,
				sort: [{property: "number", isAscending: true}]
			}),
			cls: "fit",
			columns: [
				{
					header: "Number",
					property: "number",
					sortable: true,
					resizable: true,
					width: 200
				},
				{
					header: "Description",
					property: "description",
					sortable: true,
					resizable: true,
					width: 300
				},
				DateColumn.create({
					header: "Created At",
					property: "createdAt",
					sortable: true
				})
			],
			rowSelection: {
				multiSelect: true,
				listeners: {
					selectionchange: (tableRowSelect) => {
						if(tableRowSelect.getSelected().length == 1) {
							const table = tableRowSelect.getTable();
							const record = table.getStore().getRecordAt(tableRowSelect.getSelected()[0]);
							this.showRecord(record);
						}
					}
				}
			},
		});

		return Component.create({
			cls: "vbox",
			flex: 1,
			items: [
				Toolbar.create({
					cls: "border-bottom",
					items: [
						Button.create({
							text: "Test GOUI!",
							handler: function () {
								Alert.success("Hurray! GOUI has made it's way into Extjs 3.4 :)");
							}
						}),
						Button.create({
							text: "Open files",
							handler: function () {
								// window.GO.mainLayout.openModule("files");
								window.GO.files.openFolder();
							}
						}),
					]

				}),
				table
			]
		})
	}

	private showRecord(record:StoreRecord) {
		const records: DLRecord = [
			['Number', record.number],
			['Description', record.description],
			['Created At', Format.date(record.createdAt)]
		];

		this.descriptionList.setRecords(records);
	}
}