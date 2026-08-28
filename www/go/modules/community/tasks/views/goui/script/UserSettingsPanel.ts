import {checkbox, containerfield, datasourceform, DataSourceForm, fieldset, radio, t} from "@intermesh/goui";
import {AppSettingsPanel, User, userDS} from "@intermesh/groupoffice-core";
import {tasklistcombo} from "./TasklistCombo.js";
import {tasklistchips} from "./TaskListChips.js";

export class UserSettingsPanel extends AppSettingsPanel {
	private readonly form: DataSourceForm<User>;
	private syncTaskListIds;
	private taskListCombo;

	constructor() {
		super();

		this.title = t("Tasks");

		this.form = datasourceform({
				dataSource: userDS
			},
			containerfield({
				name: "tasksSettings"
				},
				fieldset({
						legend: t("Display options for lists")
					},

					this.taskListCombo = tasklistcombo({
						label: t("Default list"),
						name: "defaultTasklistId"
					}),
					radio({
						name: "rememberLastItems",
						label: t("Start in"),
						options: [
							{text: t("Default tasklist"), value: 0},
							{text: t("Remember last selected tasklist"), value: 1}
						]
					}),
					checkbox({
						label: t("Set today for start and due date when creating new tasks"),
						name: "defaultDate"
					}),

					this.syncTaskListIds = tasklistchips({
						name: "syncTaskListIds",
						label: t("Synchronize tasklists")
					})
				)

			)
		);

		this.items.add(this.form);
	}

	async save() {
		return this.form.submit();
	}

	async load(user: User) {
		this.form.currentId = user.id;
		this.form.value = user;

		this.syncTaskListIds.list.store.setFilter("permissions", {permissionLevelUserId: user.id});
		this.taskListCombo.list.store.setFilter("permissions", {permissionLevelUserId: user.id});
	}
}