import {
	avatar,
	checkboxcolumn,
	column,
	comp, DataSourceStore,
	datasourcestore,
	datecolumn,
	Format,
	t,
	Table, Window
} from "@intermesh/goui";
import {img, jmapds} from "@intermesh/groupoffice-core";
import {ProgressType} from "./Main.js";

export class TaskGrid extends Table<DataSourceStore> {
	constructor() {
		super(
			datasourcestore({
				dataSource: jmapds("Task"),
				sort: [{property: "start", isAscending: true}],
				relations: {
					responsible: {
						path: "responsibleUserId",
						dataSource: jmapds("Principal")
					},
					creator: {
						path: "createdBy",
						dataSource: jmapds("Principal")
					},
					modifier: {
						path: "modifiedBy",
						dataSource: jmapds("Principal")
					},
					tasklist: {
						path: "tasklistId",
						dataSource: jmapds("TaskList")
					},
					categories: {
						path: "categories",
						dataSource: jmapds("TaskCategory")
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
						change: async (col, checkbox, value, record, storeIndex) => {
							this.mask();
							try {
								await jmapds("Task").update(record.id, {progress: (value ? 'completed' : 'needs-action')})
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
					align: "right",
				}),
				column({
					id: "title",
					header: t("Title"),
					width: 300,
					sortable: true,
					renderer: (columnValue, record, td, table, storeIndex) => {
						if (record.color) {
							td.style.color = record.color;
						}

						return columnValue;
					}
				}),
				column({
					id: "icons",
					hidable: false,
					width: 60,
					renderer: (columnValue, record, td, table, storeIndex) => {
						let v = "";

						if (record.priority != 0) {
							if (record.priority < 5) {
								v += '<i class="icon small orange">priority_high</i>';
							}

							if (record.priority > 5) {
								v += '<i class="icon small blue">low_priority</i>';
							}
						}

						if (record.recurrenceRule) {
							v += '<i class="icon small">repeat</i>';
						}
						if (record.filesFolderId) {
							v += '<i class="icon small">attachment</i>';
						}
						if (record.alerts && record.alerts.length > 0) {
							v += '<i class="icon small">alarm</i>';
						}

						if (record.timeBooked && record.timeBooked > 0) {
							v += '<i class="icon small">timer</i>';
						}

						return v;
					}
				}),
				datecolumn({
					id: "start",
					header: t("Start at"),
					width: 160,
					sortable: true,
					hidden: false
				}),
				datecolumn({
					id: "due",
					header: t("Due at"),
					width: 160,
					sortable: true
				}),
				column({
					id: "responsible",
					header: t("Responsible"),
					width: 180,
					sortable: true,
					renderer: (columnValue, record, td, table, storeIndex) => {
						if(!record.responsible){
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
					}
				}),
				column({
					id: "project",
					header: t("Project", "projects3", "business"),
					width: 160,
					sortable: true,
					renderer: (columnValue) => {
						return columnValue ? columnValue.name : "-";
					}
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
					}
				}),
				column({
					id: "progress",
					header: t("Progress"),
					width: 150,
					sortable: true,
					renderer: (columnValue, record, td, table, store) => {
						return comp({cls: "status tasks-status-" + columnValue, html: ProgressType[columnValue as keyof typeof ProgressType]});
					}
				}),
				datecolumn({
					id: "createdAt",
					header: t("Created at"),
					width: 160,
					sortable: true,
					hidden: true
				}),
				datecolumn({
					id: "modifiedAt",
					header: t("Modified at"),
					width: 160,
					sortable: true,
					hidden: true
				}),
				column({
					id: "creator",
					header: t("Created by"),
					width: 160,
					sortable: true,
					renderer: (columnValue) => {
						return columnValue ? columnValue.name : "-";
					},
					hidden: true
				}),
				column({
					id: "tasklist",
					header: t("List"),
					width: 160,
					sortable: true,
					renderer: (columnValue) => {
						return columnValue ? columnValue.name : "-";
					},
					hidden: true
				}),
				column({
					id: "modifier",
					header: t("Modified by"),
					width: 160,
					sortable: true,
					renderer: (columnValue) => {
						return columnValue ? columnValue.name : "-";
					},
					hidden: true
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
					}
				}),
				column({
					id: "estimatedDuration",
					header: t("Estimated duration"),
					align: "right",
					hidden: true,
					width: 100,
					renderer: (columnValue) => {
						if (parseInt(columnValue) > 0) {
							return Format.duration(columnValue, false);
						}
						return "";
					}
				}),
				// TODO: time booked
			]
		);
	}
}