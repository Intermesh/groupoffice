go.modules.community.tasks.TaskDialog = Ext.extend(go.form.Dialog, {
	title: t("Task"),
	entityStore: "Task",
	width: dp(800),
	height: dp(800),
	modal: false,
	stateId: 'communityTasksTaskDialog',
	role: "list",
	redirectOnSave: true,
	collapsible: true,

	onReady: async function () {
		if (this.currentId) {
			const tl = await go.Db.store(this.support ? "SupportList" : "TaskList").single(this.tasklistCombo.getValue());
			this.role = tl.role;
			this.tasklistCombo.store.setFilter("role", {role: tl.role});
		} else {
			this.tasklistCombo.store.setFilter("role", {role: this.role});
		}


		if (!this.currentId && this.commentComposer) {//} && this.role == "support") {
			this.commentComposer.show();
			this.commentComposer.editor.allowBlank = this.role !== "support";
			if(this.role === "support") {
				this.descriptionFieldset.hide();
			}

			this.commentComposer.editor.on("ctrlenter", () => {
				this.submit();
			})

			this.on("submit", () => {
				if(this.commentComposer.editor.getValue() != "")
					this.commentComposer.save(this.role == "support" ? "SupportTicket" : "Task", this.currentId);
			}, {single:true})
		} else {
			this.commentComposer.hide();
			this.commentComposer.editor.allowBlank = true;
			this.descriptionFieldset.show();
		}
	},

	onSubmit : function() {
		switch(this.role) {
			case "support":
				if(this.redirectOnSave) {
					go.Router.goto("support/" + this.currentId);
				}
				break;

			case "board":
			case "project":
				break;

			default:
				if(this.redirectOnSave) {
					this.entityStore.entity.goto(this.currentId);
				}
				break;
		}
	},

	onLoad: function() {
		if (go.User.tasksSettings && !this.tasklistCombo.value) {
			this.tasklistCombo.value = this.initialConfig.role === "support" ? go.User.supportSettings.defaultTasklistId : go.User.tasksSettings.defaultTasklistId;
		}

	},

	setLinkEntity: function (cfg) {

		switch (cfg.entity) {
			case 'Project':
			case "Contact":
				this.formPanel.getForm().findField("title").setValue(cfg.data.name);
				break;

			case 'Order':
				this.formPanel.getForm().findField("title").setValue(cfg.data.order_id);
				break;
		}
	},


	onTaskListChange: function (combo, val) {

		if (!val) {
			return; //some bug calling this with string
		}

		const categories = this.formPanel.form.findField('categories');
		categories.comboStore.setFilter("tasklistId", {
			operator: "OR",
			conditions: [
				{tasklistId: val},
				{global: true},
				{ownerId: go.User.id}
			]
		});
		//reloads combo when trigger is clicked
		delete categories.comboBox.lastQuery;


		go.Db.store(this.support ? "SupportList" : "Tasklist").single(val).then((tasklist) => {
			this.responsibleCombo.store.setFilter("acl", {
				aclId: tasklist.aclId,
				aclPermissionLevel: go.permissionLevels.write
			});

			delete this.responsibleCombo.lastQuery;
		}).catch((e) => {
			console.error(e);
		})
	},

	initComponent: function() {
		if(this.role == "support") {
			this.title = t("Ticket", "support", "business");
		}
		this.supr().initComponent.call(this);
	},

	initFormItems: function () {
		const start = {
			flex: 1,
			xtype: 'datefield',
			width: undefined,
			name: 'start',
			itemId: 'start',
			fieldLabel: t("Start"),
			value: go.User.tasksSettings.defaultDate ? new Date() : "",
			listeners: {
				setvalue: function (me, val) {
					const due = me.nextSibling();
					//due.setMinValue(val);
					if (!due.getValue() || due.getValue() < val) {
						due.setValue(val);
					}
					if (!Ext.isEmpty(val)) {
						this.recurrenceField.setStartDate(Ext.isDate(val) ? val : Date.parseDate(val, me.format));
					}
					this.recurrenceField.setDisabled(Ext.isEmpty(val));
				},
				scope: this
			}
		};

		const due = {
			flex: 1,
			width: undefined,
			xtype: 'datefield',
			name: 'due',
			itemId: 'due',
			fieldLabel: t("Due"),
			value: go.User.tasksSettings.defaultDate ? new Date() : "",
			listeners: {
				setvalue: function (me, val) {
					const start = me.previousSibling();
					if (start.getValue() && start.getValue() > val) {
						start.setValue(val);
					}
				},
				scope: this
			}
		};

		const progress = new go.modules.community.tasks.ProgressCombo({
			flex: 1,
			value: 'needs-action'
		});

		const estimatedDuration = {
			name: "estimatedDuration",
			xtype: "durationfield",
			maxHours: 9000, // tasks should not take longer than 2h tho
			flex: 1,
			fieldLabel: t("Estimated duration"),
			asInteger: true,
			listeners: {
				change: (df, duration) => {
					const days = Math.floor(duration / 86400);

					if(!days) {
						estimatedDurationLbl.hide();
						return;
					}

					duration = duration % 86400;

					const hours = Math.floor(duration / 3600);
					duration = duration % 3600;

					const mins = Math.floor(duration / 60);

					estimatedDurationLbl.show();

					estimatedDurationLbl.setValue(days +" "+ t("days") + ", " + hours + ":" + (mins + "").padStart(2, "0") + " " + t("hours"));
				}
			}
		};

		const estimatedDurationLbl = new Ext.form.DisplayField({
			hidden: true,
			flex: 1
		});

		const priority = {
			flex: 1,
			xtype: 'combo',
			name: 'priority_text',
			hiddenName: 'priority',
			triggerAction: 'all',
			editable: false,
			selectOnFocus: true,
			forceSelection: true,
			fieldLabel: t("Priority"),
			mode: 'local',
			value: 0,
			valueField: 'value',
			displayField: 'text',
			store: new Ext.data.SimpleStore({
				fields: ['value', 'text'],
				data: [
					[9, t("Low")],
					[0, t("Normal")],
					[1, t("High")]
				]
			})
		}
		const percentComplete = new Ext.form.SliderField({
			fieldLabel: t("Percent complete"),
			name: 'percentComplete',
			minValue: 0,
			maxValue: 100,
			increment: 10,
			value: 0,
			flex: 1,
		});

		this.recurrenceField = new go.form.RecurrenceField({
			anchor: "-20",
			name: 'recurrenceRule',
			hidden: this.hideRecurrence || this.role == "support",
			disabled: true
		})

		const items = [
					{

						xtype: 'fieldset',
						items: [
							{
								xtype: "container",
								layout: "form",
								cls: 'go-hbox',
								items: [{
									flex: 1,
									xtype: 'textfield',
									name: 'title',
									allowBlank: false,
									fieldLabel: t("Subject")
								},
									{xtype: 'colorfield', emptyText: 'color', name: 'color', hideLabel: true}
								]
							},

							this.customerCombo = new go.users.UserCombo({
								flex: 1,
								disabled: this.role != "support",
								hidden: this.role != "support",
								anchor: "-20",
								fieldLabel: t('Customer'),
								hiddenName: 'createdBy',
								allowBlank: false,
								value: null
							}),

							this.ccField = new go.form.RecipientCombo({
								fieldLabel : t("CC", "email"),
								name : 'cc',
								anchor: "-20",
								flex: 1,
								hidden: this.role != "support",
								disabled: this.role != "support",
								value: ""
							})
						]


					},{
						xtype: 'fieldset',
						defaults: {
							layout: 'form',
							xtype: 'container',
							cls: "go-hbox"
						},
						mobile: {
							items: [{
								items: [start, due]
							},{
								items: [estimatedDuration, progress]
							}, {
								items: [percentComplete, priority]
							},
								this.recurrenceField]
						},
						desktop: {
							items: [
								{
									items: [start, due, estimatedDuration, estimatedDurationLbl]
								},{
									items: [progress, percentComplete, priority]
								},
								this.recurrenceField
							]
						},
					},
					{
						xtype: "fieldset",
						items: [{
							xtype: "container",
							cls: "go-hbox",
							layout: "form",
							items: [
								this.tasklistCombo = new go.modules.community.tasks.TasklistCombo({
									flex: 1,
									anchor: undefined,
									role: this.role,
									listeners: {
										change: this.onTaskListChange,
										setvalue: this.onTaskListChange,
										scope: this
									}
								}),
								this.responsibleCombo = new go.users.UserCombo({
									flex: 1,
									anchor: undefined,
									fieldLabel: t('Responsible'),
									hiddenName: 'responsibleUserId',
									allowBlank: true,
									value: null
								})

								]
							},
							{
								xtype: "chips",
								entityStore: "TaskCategory",
								comboStoreConfig: {
									filters: {
										tasklistId: {
											operator: "OR",
											conditions: [
												{tasklistId: this.tasklistCombo.getValue()},
												{global: true},
												{ownerId: go.User.id}
											]
										}
									}
								},
								displayField: "name",
								valueField: 'id',
								name: "categories",
								fieldLabel: t("Category", "tasks")
							}]
					}
					,

					{xtype: 'hidden', name: 'groupId'},


					this.descriptionFieldset = new Ext.form.FieldSet({
						xtype: "fieldset",
						defaults: {
							anchor: '100%'
						},
						items: [

							{
								xtype: 'textarea',
								name: 'description',
								fieldLabel: t("Description"),
								grow: true

							}, {
								xtype: 'textarea',
								name: 'location',
								allowBlank: true,
								fieldLabel: t("Location"),
								grow: true

							}
						]
					}),

					{
						xtype: "fieldset",
						title: t("Alerts"),
						items: [new go.modules.community.tasks.AlertFields()]
					}
				];

		if(go.Modules.isAvailable("community", "comments")) {
			this.commentComposer = new go.modules.comments.ComposerFieldset();

			items.splice(items.length - 1,0, this.commentComposer);
		}

		return items;
	}
});
