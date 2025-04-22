import {
	client,
	FormWindow,
	jmapds,
	PrincipalCombo,
	principalcombo,
	RecurrenceField,
	recurrencefield
} from "@intermesh/groupoffice-core";
import {
	ArrayField,
	arrayfield,
	btn, Button,
	colorfield,
	combobox,
	comp, ContainerField, containerfield, DateField,
	datefield, DateInterval, DateTime, durationfield,
	fieldset,
	h3,
	hr,
	htmlfield, rangefield, router,
	t,
	tbar, textarea,
	textfield, timefield
} from "@intermesh/goui";
import {tasklistcombo} from "./TasklistCombo.js";
import {TaskCategoryChips, taskcategorychips} from "./TaskCategoryChips.js";
import {progresscombo} from "./ProgressCombo.js";
import {prioritycombo} from "./PriorityCombo.js";

export class TaskDialog extends FormWindow {
	private responsibleCombo: PrincipalCombo;
	private categoryChips: TaskCategoryChips;
	private startDate: DateField;
	private dueDate: DateField;
	private recurrenceField: RecurrenceField;


	constructor() {
		super("Task");

		this.title = t("Task");
		this.width = 800;
		this.height = 800;

		this.maximizable = true;
		this.collapsible = true;
		this.resizable = true;

		this.form.on("save", (form, data, isNew) => {
			router.goto("tasks/" + data.id)
		});

		this.generalTab.items.add(
			fieldset({
					flex: 1
				},
				comp({cls: "row"},
					textfield({
						flex: 4,
						name: "title",
						label: t("Subject"),
						required: true
					}),
					colorfield({
						name: "color",
						label: t("Color"),
						flex: 1
					})
				),
				comp({cls: "row"},
					this.responsibleCombo = principalcombo({
						flex: 1,
						label: t("Responsible"),
						name: "responsibleUserId"
					}),
					tasklistcombo({
						flex: 1,
						name: "tasklistId",
						required: true,
						listeners: {
							change: async (field, newValue, oldValue) => {
								if (!newValue) {
									return
								}

								const tasklist = await jmapds("TaskList").single(newValue);

								void this.categoryChips.list.store.setFilter("tasklistId", {
									operator: "OR",
									conditions: [
										{tasklistId: newValue},
										{global: true},
										{ownerId: client.user.id}
									]
								}).load();

								void this.responsibleCombo.list.store.setFilter("acl", {
									aclId: tasklist!.aclId,
									aclPermissionLevel: go.permissionLevels.write
								}).load();
							}
						}
					})
				)
			),
			fieldset({
					flex: 1
				},
				tbar({
						cls: "border-top"
					},
					h3({
						text: t("Message")
					})
				),
				htmlfield({
					name: "description"
				})
			),
			fieldset({
					flex: 1
				},
				hr(),
				this.categoryChips = taskcategorychips({
					label: t("Categories"),
					name: "categories"
				}),
				combobox({
					name: "projectId",
					label: t("Project"),
					dataSource: jmapds("Project3"),
					displayProperty: "number",
					valueProperty: "id"
				})
			),
			fieldset({
					flex: 1
				},
				tbar({
						cls: "border-top"
					},
					h3({
						text: t("Date")
					}),
					"->",
					btn({
						icon: "expand_more",
						handler: (btn) => {
							this.collapseBtnHandler(btn);
						}
					})
				),
				comp({
						cls: "flow",
						hidden: true,
						flex: 1
					},
					comp({cls: "row"},
						this.startDate = datefield({
							label: t("Start"),
							name: "start",
							flex: 1,
							listeners: {
								change: (field, newValue, oldValue) => {

									if (!newValue) {
										this.recurrenceField.disabled = true;
										return
									}

									if (!this.dueDate.value || this.dueDate.value < newValue) {
										this.dueDate.value = newValue;
									}

									this.recurrenceField.setStartDate(new DateTime(newValue));
									this.recurrenceField.disabled = false;
								}
							}
						}),
						this.dueDate = datefield({
							label: t("Due"),
							name: "due",
							flex: 1
						}),
						durationfield({
							label: t("Estimated duration"),
							name: "estimatedDuration",
							flex: 1,
							min: new DateInterval("PT0H"),
							max: new DateInterval("PT24H")
						})
					),
					comp({cls: "row"},
						progresscombo({
							label: t("Progress"),
							name: "progress",
							flex: 1
						}),
						rangefield({
							flex: 1,
							label: t("Percent complete"),
							name: "percentComplete",
							value: 0,
							step: 10,
							max: 100
						}),
						prioritycombo({
							label: t("Priority"),
							name: "priority",
							flex: 1
						})
					),
					this.recurrenceField = recurrencefield({
						name: "recurrenceRule",
						flex: 1,
						disabled: true
					})
				)
			),
			fieldset({
					flex: 1
				},
				tbar({
						cls: "border-top"
					},
					h3({
						text: t("Description") + " / " + t("Location")
					}),
					"->",
					btn({
						icon: "expand_more",
						handler: (btn) => {
							this.collapseBtnHandler(btn);
						}
					})
				),
				comp({
						cls: "flow",
						hidden: true,
						flex: 1
					},
					textarea({
						name: "description",
						label: t("Description"),
						autoHeight: true
					}),
					textarea({
						name: "location",
						label: t("Location"),
						autoHeight: true
					})
				)
			),
			fieldset({
					flex: 1
				},
				tbar({
						cls: "border-top"
					},
					h3({
						text: t("Alerts")
					}),
					"->",
					btn({
						icon: "expand_more",
						handler: (btn) => {
							this.collapseBtnHandler(btn);
						}
					})
				),
				comp({
						cls: "flow",
						hidden: true,
						flex: 1
					},
					arrayfield({
						name: "alerts",
						buildField: (value) => {
							return containerfield({
									name: "trigger",
									cls: "group"
								},
								datefield({
									name: "when",
									withTime: true,
									flex: 2,
									value: new DateTime().format("Y-m-d")
								}),
								btn({
									icon: "delete",
									handler: (btn) => {
										btn.findAncestorByType(ContainerField)!.remove();
									}
								})
							)
						}
					}),
					btn({
						width: 200,
						cls: "primary outlined",
						text: t("Add alert"),
						handler: (btn) => {
							btn.parent?.findChildByType(ArrayField)?.addValue({});
						}
					})
				)
			)
		)
	}

	private collapseBtnHandler(btn: Button) {
		const isHidden = btn.parent!.nextSibling()!.hidden;

		if (isHidden) {
			btn.parent!.nextSibling()!.show();
			btn.icon = "expand_less";
		} else {
			btn.parent!.nextSibling()!.hide();
			btn.icon = "expand_more";
		}
	}
}