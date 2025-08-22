import {checkbox, Component, containerfield, datasourceform, DataSourceForm, fieldset, radio, t} from "@intermesh/goui";
import {User, userDS} from "@intermesh/groupoffice-core";
import {tasklistcombo} from "./TasklistCombo.js";

export class SettingsPanel extends Component {
	private readonly form: DataSourceForm;

	constructor() {
		super();

		this.form = datasourceform({
				dataSource: userDS
			},
			fieldset({
					legend: t("Dislay options for lists")
				},
				containerfield({
					name: "tasksSettings"
				},
					tasklistcombo({
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
					})
				)
			)
		);

		this.items.add(this.form);
	}

	onSubmit() {
		return this.form.submit();
	}

	onLoad(user: User) {
		this.form.currentId = user.id;
		this.form.value = user;
	}
}