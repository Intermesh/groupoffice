import {
	btn,
	checkbox,
	comp,
	Component, ComponentEventMap,
	DateField,
	datefield,
	DateTime,
	fieldset,
	form,
	Form, numberfield,
	t, tbar,
	textarea, TextField, textfield
} from "@intermesh/goui";
import {SieveRuleParser} from "./SieveRuleParser";
import {SieveRuleEntity} from "@intermesh/community/tempsieve";

export interface OutOfOfficeEventMap extends ComponentEventMap {
	ooosave: {}
}

export class OutOfOfficePanel extends Component<OutOfOfficeEventMap> {

	private form: Form;
	private rule: SieveRuleEntity | undefined;
	private scheduleActivateField: DateField;
	private scheduleDeactivateField: DateField;
	private ruleParser: SieveRuleParser;

	constructor() {
		super();
		this.rule = {
			index: 0,
			name: "Out of office",
			active: true,
			raw: "",
		};
		this.ruleParser = new SieveRuleParser();
		this.form = form({},
			comp({cls: "hbox"},
				comp({flex: 0.5},
					fieldset({
							legend: t("Schedule")
						},
						comp({
							html: t('In here you can schedule when the "Out of office" message needs to be activated.')
						}),
						this.scheduleActivateField = datefield({
							name: 'value-ge',
							required: false,
							label: t("Activate at", "sieve"),
							listeners: {
								focus: () => {
									this.scheduleActivateField.min = new DateTime();
									this.scheduleActivateField.value = new DateTime().format("Y-m-d");
								},

								change: ({newValue}) => {
									if (!this.scheduleDeactivateField.value) {
										this.scheduleDeactivateField.value = newValue;
										this.scheduleDeactivateField.min = new DateTime(newValue);
									}
								}
							}
						}),
						this.scheduleDeactivateField = datefield({
							name: 'value-le',
							required: false,
							label: t("Deactivate after", "sieve"),
							listeners: {
								focus: () => {
									if (!this.scheduleActivateField.isModified()) {
										this.scheduleDeactivateField.min = new DateTime();
										this.scheduleDeactivateField.value = new DateTime().format("Y-m-d");
									}
								},
							}
						})
					),
				),
				comp({flex: 0.5},
					fieldset({
							legend: t("Activate filterset")
						},
						comp({html: t("Activate this filter by checking the checkbox below.")}),
						checkbox({
							type: "switch",
							label: t("Activate", "sieve") + " " + t("Out of office"),
							name: 'active'
						})
					),
				),
			),
			comp({
					flex: 1
				},

				fieldset({
						legend: t("Message")
					},
					comp({
						html: t("Activate this filter by checking the checkbox below.")
					}),
					textarea({
						name: "reason",
						required: false,
						height: 300,
						listeners: {
							change: ({newValue}) => {
							}
						}
					})
				),
				fieldset({
						legend: t("Advanced"),
					},

					textfield({
						name: "subject",
						required: false,
						label: t("Set a custom subject")
					}),
					textfield({
						name: 'addresses',
						required: false,
						label: t("Aliases"),
						hint: t("Fill in the aliases on which this message also needs to apply to. If you have multiple aliases, then separate each alias with a comma (,).")
					}),
					numberfield({
						name: "days",
						decimals: 0,
						required: false,
						label: t("Reply every X days"),
						value: 3,
						hint: t("Senders will only be notified periodically. You can set the number of days below.")
					})
				),
				fieldset({
						legend: t("Example")
					},
					textfield({
						readOnly: true,
						name: "text",
						required: false,
					}),
					textfield({
						readOnly: true,
						name: "type",
						required: false
					})
				)
			)
		);

		const tb = tbar({}, "->", btn({
			icon: "save",
			cls: "primary filled",
			text: t("Apply"),
			handler: () => {
				const v = this.form.value;
				this.ruleParser.tests = [{
					not: false,
					test: "currentdate",
					type: "value-ge",
					part: "date",
					arg: v["value-ge"]
				}, {
					not: false,
					test: "currentdate",
					type: "value-le",
					part: "date",
					arg: v["value-le"]
				}];
				this.ruleParser.actions = [{
					addresses: v.addresses,
					days: v.days,
					subject: v.subject,
					reason: v.reason,
					type: "vacation",
					text: ""
				}];
				this.rule!.active = v.active;
				this.ruleParser?.convert(this.ruleParser.tests, this.ruleParser.actions);
				this.rule!.raw = this.ruleParser?.raw!;
				this.fire("ooosave", {});
			}
		}))

		this.items.add(this.form, tb);
	}

	public setValues(value: SieveRuleEntity): void {
		this.rule = value;
		this.ruleParser.record = value;
		this.ruleParser.parseTests();
		this.ruleParser.parseActions();
		const v: any = {
			active: value.active
		};
		if (!this.ruleParser.tests.length) {
			const today = new DateTime().format("Y-m-d");
			this.ruleParser.addTest({
				not: false,
				test: "currentdate",
				type: "value-ge",
				part: "date",
				arg: today
			});
			this.ruleParser.addTest({
				not: false,
				test: "currentdate",
				type: "value-le",
				part: "date",
				arg: today
			});
		}
		for (const test of this.ruleParser.tests) {
			v[test.type!] = test.arg;
		}
		if (!this.ruleParser.actions.length) {
			this.ruleParser.addAction({
				type: "vacation",
				days: "3",
				reason: "",
				subject: "",
				text: ""
			});
		}
		Object.assign(v, this.ruleParser.actions[0]);
		this.form.value = v;
	}

	public get raw(): string | undefined {
		return this.rule?.raw;
	}

}