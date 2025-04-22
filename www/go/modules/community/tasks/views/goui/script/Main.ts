import {client, filterpanel, jmapds, MainThreeColumnPanel} from "@intermesh/groupoffice-core";
import {
	btn, checkbox, CheckboxField,
	checkboxselectcolumn,
	column,
	comp, EntityID,
	Filter,
	h3,
	hr,
	menu, mstbar, MultiSelectToolbar, router,
	searchbtn,
	t, Table,
	tbar, Toolbar, Window
} from "@intermesh/goui";
import {tasklistgrid, TasklistGrid} from "./TasklistGrid.js";
import {TaskGrid} from "./TaskGrid.js";
import {TaskDetail} from "./TaskDetail.js";
import {taskcategorygrid, TaskCategoryGrid} from "./TaskCategoryGrid.js";
import {TasklistDialog} from "./TasklistDialog.js";
import {SubscribeWindow} from "./SubscribeWindow.js";
import {TaskCategoryDialog} from "./TaskCategoryDialog.js";
import {TaskDialog} from "./TaskDialog.js";
import {schedulefilter} from "./ScheduleFilter.js";
import {ImportTaskDialog} from "./ImportTaskDialog.js";

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

	private assignedToMeCheckbox!: CheckboxField;
	private unassignedCheckbox!: CheckboxField;

	private taskGridToolbar!: Toolbar;

	constructor() {
		super("tasks");

		this.on("render", () => {
			void this.taskListGrid.store.load();
			void this.taskCategoryGrid.store.load();
		});
	}

	protected createWest() {
		const west = comp({
				cls: "vbox scroll fit",
				width: 300
			},
			this.showCenterButton(),
			schedulefilter({
				columns: [
					column({
						id: "icon",
						width: 32,
						renderer: (value, record) => {
							return comp({tagName: "i", cls: "icon " + record.iconCls, text: value});
						}
					}),
					column({
						id: "name"
					})
				],
				rowSelectionConfig: {
					multiSelect: false,
					listeners: {
						selectionchange: (rowSelect) => {
							const value = rowSelect.getSelected().map((row) => row.record.value)[0];

							const filters: Record<string, Filter> = {
								today: {start: "<=now"},
								week: {due: "<=7days"},
								unscheduled: {scheduled: false},
								scheduled: {scheduled: true},
								all: {}
							};

							const filter = filters[value];

							if (filter) {
								this.taskGrid.store.setFilter("status", filter);

								void this.taskGrid.store.load();
							}
						}
					}
				}
			}),
			comp({cls: "pad"},
				checkbox({
					value: true,
					label: t("Show completed"),
					listeners: {
						change: (field, newValue, oldValue) => {
							this.taskGrid.store.setFilter("completed", newValue ? {} : {complete: false});
							void this.taskGrid.store.load();
						}
					}
				}),
				h3({text: t("Assigned")}),
				this.assignedToMeCheckbox = checkbox({
					label: t("Mine"),
					listeners: {
						change: () => {
							this.setAssignmentFilters();
						}
					}
				}),
				this.unassignedCheckbox = checkbox({
					label: t("Unassigned"),
					listeners: {
						change: () => {
							this.setAssignmentFilters();
						}
					}
				})
			),
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
								const dlg = new TasklistDialog();
								dlg.show();
							}
						}),
						btn({
							icon: "bookmark_added",
							text: t("Subscribe to task list..."),
							handler: () => {
								const wdw = new SubscribeWindow();
								wdw.show();
							}
						})
					)
				})
			),
			comp({
					cls: "scroll"
				},
				this.taskListGrid = tasklistgrid({
					fitParent: true,
					cls: "no-row-lines",
					dropOn: true,
					sortableGroup: "TaskToList",
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
						checkboxselectcolumn({
							id: "check",
							listeners: {
								render: (column1, result, record, storeIndex, td) => {
									const checkbox = result as CheckboxField;
									checkbox.color = "#" + record.color;
								}
							}
						}),
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
											handler: async () => {
												const dlg = new TasklistDialog();
												await dlg.load(record.id);
												dlg.show();
											}
										}),
										btn({
											icon: "delete",
											text: t("Delete..."),
											handler: () => {
												void jmapds("TaskList").confirmDestroy([record.id]);
											}
										}),
										hr(),
										btn({
											icon: "remove_circle",
											text: t("Unsubscribe"),
											handler: async () => {
												await jmapds("TaskList").update(record.id, {isSubscribed: false});
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
						const dlg = new TaskCategoryDialog();
						dlg.show();
					}
				})
			),
			comp({
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
											handler: async () => {
												const dlg = new TaskCategoryDialog();
												await dlg.load(record.id);
												dlg.show();
											}
										}),
										btn({
											icon: "delete",
											text: t("Delete"),
											handler: async () => {
												await jmapds("TaskCategory").confirmDestroy([record.id]);
											}
										})
									)
								})
							}
						})
					]
				})
			),
			filterpanel({
				store: this.taskGrid.store,
				entityName: "Task"
			})
		);

		this.taskListGrid.on("drop", async (toComponent, toIndex, fromIndex, droppedOn, fromComp, dragDataSet) => {
			const fromTable = fromComp as Table;

			const tasklist = toComponent.store.get(toIndex);
			const task = fromTable.store.get(fromIndex);

			if (tasklist && task) {
				void jmapds("Task").update(task.id, {tasklistId: tasklist.id});
				void fromTable.store.load();
			}
		});

		return west;
	}

	protected createCenter() {
		this.taskGrid = new TaskGrid();
		this.taskGrid.fitParent = true;
		this.taskGrid.draggable = true;
		this.taskGrid.sortableGroup = "TaskToList";
		void this.taskGrid.store.load();

		this.taskGrid.rowSelectionConfig = {
			multiSelect: true,
			listeners: {
				selectionchange: (tableRowSelect) => {
					const taskIds = tableRowSelect.getSelected().map((row) => row.record.id);

					if (taskIds[0]) {
						router.goto("tasks/" + taskIds[0]);
					}

					taskIds.length > 1 ? this.taskGridToolbar.hide() : this.taskGridToolbar.show();
				}
			}
		}

		return comp({
				cls: "vbox bg-lowest",
				flex: 1
			},
			this.taskGridToolbar = tbar({
					cls: "bg-mid border-bottom"
				},
				this.showWestButton(),
				"->",
				searchbtn({
					listeners: {
						input: (sender, text) => {
							(this.taskGrid.store.queryParams.filter as Filter).text = text;
							void this.taskGrid.store.load();
						}
					}
				}),
				btn({
					cls: "primary filled",
					icon: "add",
					handler: () => {
						const dlg = new TaskDialog();
						dlg.show();
					}
				}),
				btn({
					icon: "more_vert",
					menu: menu({},
						btn({
							icon: "cloud_upload",
							text: t("Import"),
							handler: () => {
								const dlg = new ImportTaskDialog();
								dlg.show();
							}
						}),
						btn({
							icon: "cloud_download",
							text: t("Export"),
							menu: menu({},
								btn({
									icon: "calendar_today",
									text: t("vCalendar"),
									handler: () => {
										go.util.exportToFile(
											"Task",
											this.taskGrid.store.queryParams,
											"ics"
										);
									}
								}),
								btn({
									icon: "unknown_document",
									text: t("Microsoft Excel"),
									handler: () => {
										go.util.exportToFile(
											"Task",
											this.taskGrid.store.queryParams,
											"xlsx"
										);
									}
								}),
								btn({
									icon: "csv",
									text: "Comma Seperated Values",
									handler: () => {
										go.util.exportToFile(
											"Task",
											this.taskGrid.store.queryParams,
											"csv"
										);
									}
								}),
								btn({
									icon: "html",
									text: t("Web page") + " (HTML)",
									handler: () => {
										go.util.exportToFile(
											"Task",
											this.taskGrid.store.queryParams,
											"html"
										);
									}
								})
							)
						}),
						hr(),
						btn({
							icon: "delete",
							text: t("Delete"),
							handler: () => {
								const taskIds = this.taskGrid.rowSelection!.getSelected().map((row) => row.record.id);

								jmapds("Task").confirmDestroy(taskIds);
							}
						})
					)
				})
			),
			mstbar({
					table: this.taskGrid
				},
				"->",
				btn({
					icon: "merge",
					handler: async () => {
						const taskIds = this.taskGrid.rowSelection!.getSelected().map((row) => row.record.id);

						if (taskIds.length < 2) {
							Window.alert(t("Please select at least two items"), t("Error"));
						} else {
							const confirmed = await Window.confirm(t("The selected items will be merged into one. The item you selected first will be used primarily. Are you sure?"), t("Merge"));

							if (confirmed) {
								try {
									const result = await jmapds("Task").merge(taskIds);

									const dlg = new TaskDialog();
									await dlg.load(result.id);
									dlg.show();
								} catch (e) {
									Window.error(e);
								}
							}
						}
					}
				}),
				btn({
					icon: "delete",
					handler: () => {
						const taskIds = this.taskGrid.rowSelection!.getSelected().map((row) => row.record.id);

						jmapds("Task").confirmDestroy(taskIds);
					}
				})
			),
			comp({cls: "scroll", flex: 1},
				this.taskGrid
			)
		);
	}

	protected createEast() {
		this.taskDetail = new TaskDetail();

		return this.taskDetail;
	}

	private setAssignmentFilters() {
		if (!this.assignedToMeCheckbox.value && !this.unassignedCheckbox.value) {
			this.taskGrid.store.setFilter("assignedToMe", {});
		} else if (this.assignedToMeCheckbox.value && !this.unassignedCheckbox.value) {
			this.taskGrid.store.setFilter("assignedToMe", {responsibleUserId: client.user.id});
		} else if (!this.assignedToMeCheckbox.value && this.unassignedCheckbox.value) {
			this.taskGrid.store.setFilter("assignedToMe", {responsibleUserId: null})
		} else {
			this.taskGrid.store.setFilter("assignedToMe",
				{
					operator: "OR",
					conditions: [
						{responsibleUserId: client.user.id},
						{responsibleUserId: null}
					]
				}
			);
		}

		void this.taskGrid.store.load();
	}

	public showTask(taskId: EntityID) {
		void this.taskDetail.load(taskId);
	}
}