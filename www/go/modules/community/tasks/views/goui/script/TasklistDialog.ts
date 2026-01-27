import {FormWindow} from "@intermesh/groupoffice-core";
import {colorfield, fieldset, hiddenfield, t, textfield} from "@intermesh/goui";
import {tasklistgroupcombo} from "./TasklistGroupCombo.js";

export class TasklistDialog extends FormWindow {
	constructor() {
		super("TaskList");

		this.title = t("List");
		this.width = 600;
		this.height = 500;

		this.resizable = true;
		this.maximizable = true;
		this.modal = true;

		this.generalTab.items.add(
			fieldset({},
				hiddenfield({
					name: "role",
					value: "list",
					required: true
				}),
				textfield({
					name: "name",
					label: t("Name"),
					required: true
				}),
				colorfield({
					name: "color",
					label: t("Color")
				}),
				tasklistgroupcombo({
					valueProperty: "id",
					displayProperty: "name"
				})
			)
		);

		this.addSharePanel();
	}
}