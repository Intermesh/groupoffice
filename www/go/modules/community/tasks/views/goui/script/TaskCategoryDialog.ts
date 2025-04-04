import {client, FormWindow, modules} from "@intermesh/groupoffice-core";
import {checkbox, fieldset, HiddenField, hiddenfield, t, textfield} from "@intermesh/goui";
import {TasklistCombo, tasklistcombo} from "./TasklistCombo.js";

export class TaskCategoryDialog extends FormWindow {
	private ownerHiddenField?: HiddenField;
	private tasklistCombo: TasklistCombo;

	constructor() {
		super("TaskCategory");

		this.title = t("Category");
		this.width = 400;
		this.height = 400;
		this.resizable = false;
		this.maximizable = false;

		this.generalTab.items.add(
			fieldset({},
				textfield({
					name: "name",
					label: t("Name"),
					required: true
				}),
				this.tasklistCombo = tasklistcombo({
					name: "tasklistId",
					label: t("List"),
					clearable: true,
					displayProperty: "name",
					valueProperty: "id"
				})
			)
		);

		if (modules.get("community", "tasks")!.userRights.mayChangeCategories) {
			this.generalTab.items.add(
				fieldset({},
					this.ownerHiddenField = hiddenfield({
						name: "ownerId",
						value: client.user.id
					}),
					checkbox({
						label: t("Global category"),
						listeners: {
							change: (field, newValue, oldValue) => {
								this.ownerHiddenField!.value = newValue ? null : client.user.id;
								this.tasklistCombo.disabled = newValue;
							}
						}
					})
				)
			)
		}
	}
}