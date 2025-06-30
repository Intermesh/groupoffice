import {
	btn,
	checkbox,
	CheckboxField,
	column,
	comp,
	EntityID,
	Filter,
	searchbtn,
	t,
	tbar,
	Window
} from "@intermesh/goui";
import {TasklistGrid, tasklistgrid} from "./TasklistGrid.js";

export class ImportTaskDialog extends Window {
	private tasklistGrid: TasklistGrid;
	private selectedTasklistId?: EntityID;
	private importIDFromFileCheckBox: CheckboxField;

	constructor() {
		super();

		this.title = t("Choose a tasklist");
		this.height = 800;
		this.width = 800;
		this.modal = true;
		this.resizable = true;

		this.items.add(
			comp({cls: "pad"},
				this.importIDFromFileCheckBox = checkbox({
					label: t("Import list ID from CSV file"),
					listeners: {
						change: ({newValue}) => {
							this.tasklistGrid.disabled = newValue;
						}
					}
				})
			),
			tbar({},
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
			comp({
					cls: "scroll",
					flex: 1
				},
				this.tasklistGrid = tasklistgrid({
					cls: "bg-lowest",
					fitParent: true,
					columns: [
						column({
							id: "id",
							header: "ID",
							width: 40,
							hidden: true,
							sortable: true
						}),
						column({
							id: "name",
							header: t("Name"),
							width: 75,
							sortable: true
						}),
						column({
							id: "role",
							header: t("Role"),
							width: 75,
							hidden: true,
							sortable: true
						}),
						column({
							id: "creator",
							header: t("Created by"),
							width: 160,
							hidden: true,
							sortable: true,
							renderer: (v) => {
								return v ? v.name : "-";
							}
						})
					],
					rowSelectionConfig: {
						multiSelect: false,
						listeners: {
							selectionchange: ({target}) => {
								const tasklistId = target.getSelected().map((row) => row.record.id)[0];

								if (tasklistId) {
									this.selectedTasklistId = tasklistId;
								}
							}
						}
					}
				})
			),
			tbar({
					cls: "border-top"
				},
				"->",
				btn({
					icon: "upload",
					text: t("Upload"),
					handler: () => {
						if (!this.selectedTasklistId) {
							Window.alert(t("You have not selected any list. Select a list before proceeding."), t("List not selected"));
						} else {
							let TLvalues = {};

							if (!this.importIDFromFileCheckBox.value) {
								TLvalues = {tasklistId: this.selectedTasklistId}
							}

							go.util.importFile(
								"Task",
								".ics, .csv",
								TLvalues,
								{},
								{
									labels: {
										start: t("start"),
										due: t("due"),
										completed: t("completed"),
										title: t("title"),
										description: t("description"),
										status: t("status"),
										priority: t("priority"),
										percentComplete: t("percentage completed"),
										categories: t("categories")
									}
								}
							);
						}
					}
				})
			)
		)

		this.on("show", () => {
			void this.tasklistGrid.store.load();
		});
	}
}