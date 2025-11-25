import {btn, column, Filter, searchbtn, t, tbar, Window} from "@intermesh/goui";
import {TasklistGrid, tasklistgrid} from "./TasklistGrid.js";
import {jmapds} from "@intermesh/groupoffice-core";

export class SubscribeWindow extends Window {
	private tasklistGrid: TasklistGrid;

	constructor() {
		super();

		this.title = t("Subscribe to Tasklist");
		this.height = 800;
		this.width = 400;
		this.resizable = true;

		this.cls = "bg-lowest";

		this.items.add(
			tbar({
					cls: "border-bottom bg-high"
				},
				"->",
				searchbtn({
					listeners: {
						input: ({text}) => {
							(this.tasklistGrid.store.queryParams.filter as Filter).text = text;
							void this.tasklistGrid.store.load();
						}
					}
				})
			),
			this.tasklistGrid = tasklistgrid({
				fitParent: true,
				cls: "bg-lowest",
				headers: false,
				columns: [
					column({
						id: "name"
					}),
					column({
						id: "btn",
						sticky: true,
						width: 100,
						renderer: (columnValue, record, td, table, storeIndex, column) => {
							return btn({
								cls: "outlined",
								text: t("Subscribe"),
								handler: () => {
									if (record) {
										jmapds("TaskList").update(record.id, {isSubscribed: true});
									}
								}
							})
						}
					})
				]
			})
		);

		this.on("render", () => {
			this.tasklistGrid.store.setFilter("subscribed", {isSubscribed: false});

			void this.tasklistGrid.store.load();
		});
	}
}