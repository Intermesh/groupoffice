import {MainThreeColumnPanel} from "@intermesh/groupoffice-core";
import {btn, checkboxselectcolumn, column, comp, Filter, h3, hr, menu, searchbtn, t, tbar} from "@intermesh/goui";
import {tasklistgrid, TasklistGrid} from "./TasklistGrid.js";
import {TaskGrid} from "./TaskGrid.js";
import {TaskDetail} from "./TaskDetail.js";
import {taskcategorygrid, TaskCategoryGrid} from "./TaskCategoryGrid.js";

export enum ProgressType {
	'needs-action' = 'Needs action',
	'in-progress' = 'In progress',
	'completed' = 'Completed',
	'failed' = 'Failed',
	'cancelled' = 'Cancelled'
}

export class Main extends MainThreeColumnPanel {
	private taskListGrid!: TasklistGrid;
	private taskCategoryGrid!: TaskCategoryGrid;

	private taskGrid!: TaskGrid;
	private taskDetail!: TaskDetail;

	constructor() {
		super("tasks");

		this.on("render", () => {
			void this.taskListGrid.store.load();
			void this.taskCategoryGrid.store.load();
		});
	}

	protected createWest() {
		return comp({
				cls: "vbox",
				width: 300
			},
			tbar({}, this.showCenterButton()),
			tbar({
					cls: "border-bottom"
				},
				h3({
					text: t("Lists")
				}),
				"->",
				searchbtn({
					listeners: {
						input: (sender, text) => {
							(this.taskListGrid.store.queryParams.filter as Filter).text = text;
							void this.taskListGrid.store.load();
						}
					}
				}),
				btn({
					icon: "more_vert",
					menu: menu({},
						btn({
							icon: "add",
							text: t("Create task list..."),
							handler: () => {

							}
						}),
						btn({
							icon: "bookmark_added",
							text: t("Subscribe to task list..."),
							handler: () => {

							}
						})
					)
				})
			),
			comp({
					flex: 1,
					cls: "scroll"
				},
				this.taskListGrid = tasklistgrid({
					fitParent: true,
					cls: "no-row-lines",
					rowSelectionConfig: {
						multiSelect: true,
						listeners: {
							selectionchange: (tableRowSelect) => {
								const taskListIds = tableRowSelect.getSelected().map((row) => row.record.id);

								this.taskGrid.store.queryParams.filter = {
									taskListId: taskListIds
								}

								void this.taskGrid.store.load();
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
							id: "btn",
							sticky: true,
							width: 32,
							renderer: (columnValue, record, td, table, storeIndex, column) => {
								return btn({
									icon: "more_vert",
									menu: menu({},
										btn({
											icon: "edit",
											text: t("Edit..."),
											handler: () => {

											}
										}),
										btn({
											icon: "delete",
											text: t("Delete..."),
											handler: () => {

											}
										}),
										hr({}),
										btn({
											icon: "remove_circle",
											text: t("Unsubscribe"),
											handler: () => {

											}
										})
									)
								})
							}
						})
					]
				})
			),

			tbar({
					cls: "border-bottom"
				},
				h3({
					text: t("Categories")
				}),
				"->",
				btn({
					icon: "add",
					handler: () => {

					}
				})
			),
			comp({
					flex: 1,
					cls: "scroll"
				},
				this.taskCategoryGrid = taskcategorygrid({
					cls: "no-row-lines",
					fitParent: true,
					rowSelectionConfig: {
						multiSelect: true,
						listeners: {
							selectionchange: (tableRowSelect) => {
								const categoryIds = tableRowSelect.getSelected().map((row) => row.record.id);

								this.taskGrid.store.queryParams.filter = {
									categories: categoryIds
								}

								void this.taskGrid.store.load();
							}
						}
					},
					columns: [
						checkboxselectcolumn(),
						column({
							id: "name",
							header: t("Name"),
							resizable: false
						}),
						column({
							id: "btn",
							sticky: true,
							width: 32,
							renderer: (columnValue, record, td, table1, storeIndex) => {
								return btn({
									icon: "more_vert",
									menu: menu({},
										btn({
											icon: "edit",
											text: t("Edit"),
											handler: () => {

											}
										}),
										btn({
											icon: "delete",
											text: t("Delete"),
											handler: () => {

											}
										})
									)
								})
							}
						})
					]
				})
			)
		);
	}

	protected createCenter() {
		this.taskGrid = new TaskGrid();
		this.taskGrid.fitParent = true;
		void this.taskGrid.store.load();

		this.taskGrid.rowSelectionConfig = {
			multiSelect: true,
			listeners: {
				selectionchange: (tableRowSelect) => {
					const taskIds = tableRowSelect.getSelected().map((row) => row.record.id);

					if (taskIds[0]) {
						void this.taskDetail.load(taskIds[0]);
					}
				}
			}
		}

		return comp({
				cls: "vbox bg-lowest",
				flex: 1
			},
			tbar({
					cls: "bg-mid border-bottom"
				},
				this.showWestButton(),
				"->",
				searchbtn({
					listeners: {
						input: (sender, text) => {

						}
					}
				}),
				btn({
					cls: "primary filled",
					icon: "add",
					handler: () => {

					}
				}),
				btn({
					icon: "more_vert",
					menu: menu({},
						btn({
							icon: "cloud_upload",
							text: t("Import"),
							handler: () => {

							}
						}),
						btn({
							icon: "cloud_download",
							text: t("Export")
						}),
						btn({
							icon: "delete",
							text: t("Delete"),
							handler: () => {

							}
						})
					)
				})
			),
			comp({cls: "scroll", flex: 1},
				this.taskGrid
			)
		);
	}

	protected createEast() {
		this.taskDetail = new TaskDetail();

		return comp({
				cls: "vbox"
			},
			this.taskDetail
		);
	}
}