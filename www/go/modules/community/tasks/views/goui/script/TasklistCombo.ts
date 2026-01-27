import {ComboBox, ComboBoxConfig, createComponent, t} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {TasklistGroupComboConfig} from "./TasklistGroupCombo.js";

export class TasklistCombo extends ComboBox {
	constructor() {
		super(jmapds("TaskList"));

		this.label = t("List");
		this.placeholder = t("Please select...");
	}
}

export type TasklistComboConfig = Omit<ComboBoxConfig<TasklistCombo>, "dataSource">;

export const tasklistcombo = (config?: TasklistGroupComboConfig) => createComponent(new TasklistCombo(), config);