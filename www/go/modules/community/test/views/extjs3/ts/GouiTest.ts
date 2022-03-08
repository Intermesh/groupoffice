import {Button} from "../../../../../../../views/Extjs3/goui/component/Button.js";
import {Alert} from "../../../../../../../views/Extjs3/goui/Alert.js";
import {Container} from "../../../../../../../views/Extjs3/goui/component/Container.js";
import {Toolbar} from "../../../../../../../views/Extjs3/goui/component/Toolbar.js";
import {Store} from "../../../../../../../views/Extjs3/goui/data/Store.js";
import {DateColumn, Table} from "../../../../../../../views/Extjs3/goui/component/Table.js";
import {DateTime} from "../../../../../../../views/Extjs3/goui/util/DateTime.js";

declare global {
	var GO: any;
};

export class GouiTest extends Container {
	cls = "vbox fit";

	init() {
		super.init();

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
			]
		});

		this.items = [
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
			table,
			Container.create({
				cls: "border-top",
				text: "Bottom",
				height: 100
			})
		];
	}
}