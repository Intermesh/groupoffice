import {ComboBox, ComboBoxConfig, createComponent, t} from "@intermesh/goui";
import {TasklistGroupComboConfig} from "./TasklistGroupCombo.js";
import {tasklistDS} from "./Index.js";

export class TasklistCombo extends ComboBox {
	constructor() {
		super(tasklistDS);

		this.label = t("List");
		this.placeholder = t("Please select...");
	}
}

export type TasklistComboConfig = Omit<ComboBoxConfig<TasklistCombo>, "dataSource">;

export const tasklistcombo = (config?: TasklistGroupComboConfig) => createComponent(new TasklistCombo(), config);