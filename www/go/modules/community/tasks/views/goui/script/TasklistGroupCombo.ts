import {ComboBox, ComboBoxConfig, createComponent, t} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class TasklistGroupCombo extends ComboBox {
	constructor() {
		super(jmapds("TaskListGrouping"));

		this.label = t("Group");
		this.name = "groupingId";
		this.placeholder = t("Please select...");
	}
}

export type TasklistGroupComboConfig = Omit<ComboBoxConfig<TasklistGroupCombo>, "dataSource">;

export const tasklistgroupcombo = (config?: TasklistGroupComboConfig) => createComponent(new TasklistGroupCombo(), config);