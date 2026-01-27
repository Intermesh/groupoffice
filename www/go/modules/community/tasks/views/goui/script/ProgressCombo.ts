import {createComponent, FieldConfig, SelectField, t} from "@intermesh/goui";

export class ProgressCombo extends SelectField {
	constructor() {
		super();

		this.options = [
			{value: "completed", name: t("Completed")},
			{value: "failed", name: t("Failed")},
			{value: "in-progress", name: t("In progress")},
			{value: "needs-action", name: t("Needs action")},
			{value: "cancelled", name: t("Cancelled")},
		]
	}
}

export const progresscombo = (config?: FieldConfig<ProgressCombo>) => createComponent(new ProgressCombo(), config);