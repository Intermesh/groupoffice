import {createComponent, FieldConfig, SelectField, t} from "@intermesh/goui";

export class PriorityCombo extends SelectField {
	constructor() {
		super();

		this.options = [
			{value: 9, name: t("Low")},
			{value: 0, name: t("Normal")},
			{value: 1, name: t("High")}
		]
	}
}

export const prioritycombo = (config?: FieldConfig<PriorityCombo>) => createComponent(new PriorityCombo(), config);