import {ComboBox, ComboBoxConfig, createComponent, t} from "@intermesh/goui";
import {tasklistGroupingDS} from "./Index.js";

export class TasklistGroupCombo extends ComboBox {
	constructor() {
		super(tasklistGroupingDS);

		this.label = t("Group");
		this.name = "groupingId";
		this.placeholder = t("Please select...");
	}
}

export type TasklistGroupComboConfig = Omit<ComboBoxConfig<TasklistGroupCombo>, "dataSource">;

export const tasklistgroupcombo = (config?: TasklistGroupComboConfig) => createComponent(new TasklistGroupCombo(), config);