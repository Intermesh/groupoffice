import {FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {
	comp,
	DatePicker,
	datepicker,
	DateTime,
	fieldset,
	rangefield,
	t, textarea,
	TextAreaField,
	TimeField,
	timefield
} from "@intermesh/goui";
import {progresscombo} from "./ProgressCombo.js";
import {tasklistcombo} from "./TasklistCombo.js";

export class ContinueTaskDialog extends FormWindow {
	private alertDatePicker: DatePicker;
	private alertTime: TimeField;
	private commentArea: TextAreaField;

	constructor() {
		super("Task");

		this.title = t("Continue task");
		this.height = 760;
		this.width = 700;
		this.resizable = true;
		this.maximizable = false;
		this.modal = true;

		const tomorrow = new DateTime();
		tomorrow.setDate(tomorrow.getDate() + 1);

		this.generalTab.items.add(
			fieldset({
					flex: 1
				},
				comp({
						cls: "row"
					},
					this.alertDatePicker = datepicker({
						cls: "tasks-continuetask-datepicker",
						value: tomorrow,
						showWeekNbs: false
					})
				),
				comp({
						cls: "row"
					},
					this.alertTime = timefield({
						label: t("Time"),
						value: "08:00",
						flex: 1
					})
				),
				comp({
						cls: "row"
					},
					progresscombo({
						name: "progress",
						flex: 1
					}),
					rangefield({
						name: "percentComplete",
						flex: 2,
						value: 0,
						min: 0,
						max: 100,
						step: 10
					})
				),
				comp({
						cls: "row"
					},
					tasklistcombo({
						name: "tasklistId",
						label: t("List"),
						flex: 1
					})
				),
				comp({
						cls: "row"
					},
					this.commentArea = textarea({
						flex: 1
					})
				)
			)
		);

		this.form.on("beforesave", (form, data) => {
			data.alerts = [{
				trigger: {when: this.alertDatePicker.value.format("Y-m-d") + " " + this.alertTime.value}
			}];
			data.due = this.alertDatePicker.value.format("Y-m-d");

			if (this.commentArea.value) {
				jmapds("Comment").create({
					text: this.commentArea.value,
					entity: "Task",
					entityId: this.form.currentId
				})
			}
		});
	}
}