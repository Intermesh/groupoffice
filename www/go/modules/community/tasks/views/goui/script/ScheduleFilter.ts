import {createComponent, store, t, Table, TableConfig} from "@intermesh/goui";

export class ScheduleFilter extends Table {
	constructor() {
		super(
			store({
				data: [
					{
						name: t("Today"),
						icon: "content_paste",
						iconCls: "green",
						value: "today"
					},
					{
						name: t("Due in seven days"),
						icon: "filter_7",
						iconCls: "purple",
						value: "week"
					},
					{
						name: t("All"),
						icon: "assignment",
						iconCls: "red",
						value: "all"
					},
					{
						name: t("Unscheduled"),
						icon: "event_busy",
						iconCls: "blue",
						value: "unscheduled"
					},
					{
						name: t("Scheduled"),
						icon: "event",
						iconCls: "orange",
						value: "scheduled"
					}
				]
			}),
			[]
		);

		this.headers = false;
		this.fitParent = true;
		this.cls = "no-row-lines tasks-filter-table";
	}
}

export type ScheduleFilterConfig = Omit<TableConfig<ScheduleFilter>, "store">;

export const schedulefilter = (config?: ScheduleFilterConfig) => createComponent(new ScheduleFilter(), config);