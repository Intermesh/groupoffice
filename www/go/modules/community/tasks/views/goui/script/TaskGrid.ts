import {
	avatar,
	checkboxcolumn,
	column,
	comp,
	DataSourceStore,
	datasourcestore,
	datecolumn,
	Format,
	StoreRecord,
	t,
	Table,
	Window
} from "@intermesh/goui";
import {img, principalDS} from "@intermesh/groupoffice-core";
import {ProgressType} from "./Main.js";
import {TaskDialog} from "./TaskDialog.js";
import {taskCategoryDS, taskDS, tasklistDS} from "./Index.js";

export class TaskGrid extends Table<DataSourceStore> {
	constructor() {
		super(
			datasourcestore({
				dataSource: taskDS,
				sort: [{property: "tasklist", isAscending: true}, {property: "start", isAscending: true}],
				relations: {
					responsible: {
						path: "responsibleUserId",
						dataSource: principalDS
					},
					creator: {
						path: "createdBy",
						dataSource: principalDS
					},
					modifier: {
						path: "modifiedBy",
						dataSource: principalDS
					},
					tasklist: {
						path: "tasklistId",
						dataSource: tasklistDS
					},
					categories: {
						path: "categories",
						dataSource: taskCategoryDS
					}
				},
				queryParams: {
					limit: 0
				},
				buildRecord: async entity => {
					return Object.assign({complete: entity.progress == "completed"}, entity);
				}
			}),
			[
				checkboxcolumn({
					id: "complete",
					hidable: false,
					listeners: {
						change: async ({checked, record}) => {
							this.mask();
							try {
								await taskDS.update(record.id, {progress: (checked ? 'completed' : 'needs-action')})
							} catch (e) {
								void Window.error(e);
							} finally {
								this.unmask();
							}
						}
					}
				}),
				column({
					id: "id",
					header: "ID",
					hidden: true,
					width: 80,
					sortable: true,
					align: "right"
				}),
				column({
					id: "title",
					header: t("Title"),
					width: 300,
					sortable: true,
					renderer: (columnValue, record, td, table, storeIndex) => {
						if (record.color) {
							td.style.color = `#${record.color}`;
						}

						return columnValue;
					},
					resizable: true
				}),
				column({
					id: "icons",
					hidable: false,
					width: 60,
					renderer: (columnValue, record, td, table, storeIndex) => {
						let v = "";

						if (record.priority != 0) {
							if (record.priority < 5) {
								v += '<i class="icon orange">priority_high</i>';
							}

							if (record.priority > 5) {
								v += '<i class="icon blue">low_priority</i>';
							}
						}

						if (record.recurrenceRule) {
							v += '<i class="icon">repeat</i>';
						}
						if (record.filesFolderId) {
							v += '<i class="icon">attachment</i>';
						}
						if (record.alerts) {
							v += '<i class="icon">alarm</i>';
						}
						if (record.timeBooked && record.timeBooked > 0) {
							v += '<i class="icon">timer</i>';
						}

						return v;
					},
					resizable: true
				}),
				datecolumn({
					id: "start",
					header: t("Start at"),
					width: 160,
					sortable: true,
					hidden: false,
					renderer: (columnValue, record, td, table) => {
						return this.startDueRenderer(columnValue, record, td);
					},
					resizable: true
				}),
				datecolumn({
					id: "due",
					header: t("Due at"),
					width: 160,
					sortable: true,
					renderer: (columnValue, record, td, table) => {
						return this.startDueRenderer(columnValue, record, td);
					},
					resizable: true
				}),
				column({
					id: "responsible",
					header: t("Responsible"),
					width: 180,
					sortable: true,
					renderer: (columnValue, record, td, table, storeIndex) => {
						if (!record.responsible) {
							return "-";
						}

						return comp({cls: "hbox"},
							record?.responsible.avatarId ?
								img({
									cls: "goui-avatar",
									blobId: record.responsible.avatarId,
									title: record.responsible.name
								}) :
								avatar({
									displayName: record.responsible.name
								}),
							comp({cls: "tasks-principal-name", text: record.responsible.name}));
					},
					resizable: true
				}),
				column({
					id: "project",
					header: t("Project", "projects3", "business"),
					width: 160,
					sortable: true,
					renderer: (columnValue) => {
						return columnValue ? columnValue.name : "-";
					},
					resizable: true
				}),
				column({
					id: "percentComplete",
					width: 150,
					sortable: true,
					header: t("% complete"),
					renderer: (columnValue, record) => {
						return comp({cls: "go-progressbar"},
							comp({style: {width: `${Math.ceil(columnValue)}%`}})
						)
					},
					resizable: true
				}),
				column({
					id: "progress",
					header: t("Progress"),
					width: 150,
					sortable: true,
					renderer: (columnValue, record, td, table, store) => {
						return comp({
							cls: "status tasks-status-" + columnValue,
							html: ProgressType[columnValue as keyof typeof ProgressType]
						});
					},
					resizable: true
				}),
				datecolumn({
					id: "createdAt",
					header: t("Created at"),
					width: 160,
					sortable: true,
					hidden: true,
					resizable: true
				}),
				datecolumn({
					id: "modifiedAt",
					header: t("Modified at"),
					width: 160,
					sortable: true,
					hidden: true,
					resizable: true
				}),
				column({
					id: "creator",
					header: t("Created by"),
					width: 160,
					sortable: true,
					renderer: (columnValue) => {
						return columnValue ? columnValue.name : "-";
					},
					hidden: true,
					resizable: true
				}),
				column({
					id: "tasklist",
					header: t("List"),
					width: 160,
					sortable: true,
					renderer: (columnValue) => {
						return columnValue ? columnValue.name : "-";
					},
					hidden: true,
					resizable: true
				}),
				column({
					id: "modifier",
					header: t("Modified by"),
					width: 160,
					sortable: true,
					renderer: (columnValue) => {
						return columnValue ? columnValue.name : "-";
					},
					hidden: true,
					resizable: true
				}),
				column({
					id: "categories",
					header: t("Categories"),
					width: 160,
					sortable: true,
					renderer: (columnValue) => {
						return columnValue.map((v: {
							name: any;
						}) => '<span class="tasks-category">' + Ext.util.Format.htmlEncode(v.name) + '</span>').join("");
					},
					resizable: true
				}),
				column({
					id: "estimatedDuration",
					header: t("Estimated duration"),
					align: "right",
					hidden: true,
					width: 100,
					renderer: (columnValue) => {
						if (parseInt(columnValue) > 0) {
							return Format.duration(columnValue);
						}
						return "";
					},
					resizable: true
				})
				// TODO: time booked
			]
		);

		this.on("rowdblclick", async ({target, storeIndex}) => {
			const dlg = new TaskDialog();
			await dlg.load(target.store.get(storeIndex)!.id);
			dlg.show();
		});

		this.groupBy = "tasklist";

		this.groupByRenderer = (grouping) => comp({
			text: t("List") + ": " + grouping.name,
			style: {color: `#${grouping.color}`}
		});

		this.draggable = true;
	}

	private startDueRenderer(columnValue: string, record: StoreRecord, td: HTMLTableCellElement) {
		const now = new Date();

		const start = record.start ? new Date(record.start) : undefined;
		const due = record.due ? new Date(record.due) : undefined;

		if (due && due < now) {
			td.cls("danger");
		} else if (start && start <= now) {
			td.cls("success");
		}

		return columnValue;
	}
}