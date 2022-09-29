go.modules.community.tasks.TaskDialog = Ext.extend(go.form.Dialog, {
	title: t("Task"),
	entityStore: "Task",
	width: dp(800),
	height: dp(600),
	modal: false,
	stateId: 'communityTasksTaskDialog',
	role: "list",

	onReady: async function() {
		if(this.currentId) {
			const tl = await go.Db.store("Tasklist").single(this.tasklistCombo.getValue());
			this.tasklistCombo.store.setFilter("role", {role: tl.role});
		} else {
			this.tasklistCombo.store.setFilter("role", {role: this.role});
		}
	},

	setLinkEntity : function(cfg) {

		switch(cfg.entity) {
			case 'Project':
			case "Contact":
				this.formPanel.getForm().findField("title").setValue(cfg.data.name);
				break;

			case 'Order':
				this.formPanel.getForm().findField("title").setValue(cfg.data.order_id);
				break;
		}
	},


	onTaskListChange : function(combo, val) {

		if(!Ext.isNumber(val)) {
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


		go.Db.store("Tasklist").single(val).then((tasklist) => {
			this.userCombo.store.setFilter("acl", {
				aclId: tasklist.aclId,
				aclPermissionLevel: go.permissionLevels.write
			});

			delete this.userCombo.lastQuery;
		}).catch((e) => {
			console.error(e);
		})
	},

	initFormItems: function () {

		// this.taskCombo = new go.modules.community.tasks.TaskCombo({});

		const start = {
			xtype:'datefield',
			name : 'start',
			itemId: 'start',
			fieldLabel : t("Start"),
			value: go.User.tasksSettings.defaultDate ? new Date() : "",
			listeners : {
				setvalue : function(me,val) {
					const due = me.nextSibling();
					//due.setMinValue(val);
					if(!due.getValue() || due.getValue() < val) {
						due.setValue(val);
					}
					if(!Ext.isEmpty(val)) {
						this.recurrenceField.setStartDate(Ext.isDate(val) ? val : Date.parseDate(val, me.format));
					}
					this.recurrenceField.setDisabled(Ext.isEmpty(val));
				},
				scope : this
			}
		};

		const due = {
			xtype:'datefield',
			name : 'due',
			itemId: 'due',
			fieldLabel : t("Due"),
			value: go.User.tasksSettings.defaultDate ? new Date() : "",
			listeners : {
				setvalue : function(me,val) {
					const start = me.previousSibling();
					if(start.getValue() && start.getValue() > val) {
						start.setValue(val);
					}
				},
				scope : this
			}
		};

		const progress = new go.modules.community.tasks.ProgressCombo ({
			width:dp(150),

			value : 'needs-action'
		});

		const estimatedDuration = {
			name: "estimatedDuration",
			xtype: "nativetimefield",
			width:dp(150),
			fieldLabel: t("Estimated duration"),
			asInteger: true
		};

		const priority = {
			xtype: 'combo',
			name: 'priority_text',
			hiddenName: 'priority',
			triggerAction: 'all',
			editable: false,
			selectOnFocus: true,
			width: dp(150),
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
			flex: 1,
			name: 'percentComplete',
			minValue: 0,
			maxValue: 100,
			increment: 10,
			value: 0
		});



		const propertiesPanel = new Ext.Panel({
			hideMode : 'offsets',
			//title : t("Properties"),
			labelAlign: 'top',
			layout : 'form',
			autoScroll : true,
			items : [{
				xtype: "container",
				layout: "form",
				defaults : {
					anchor : '100%'
				},
				labelWidth:90,
				items:[
					{
						xtype: 'fieldset',
						layout: 'column',
						items: [
							{
								xtype:'textfield',
								name : 'title',
								columnWidth:.87,
								allowBlank : false,
								emptyText : t("Subject")
							},
							{html:' ', columnWidth:.03},
							{xtype: 'colorfield', emptyText:'color', name: 'color', columnWidth:.1}
						]
					},{
						xtype:'fieldset',
						// collapsible: true,
						// title: t("Schedule"),


						items: [
							{
								layout:'column',
								defaults: {
									layout: 'form',
									xtype:'container',
									labelAlign:'top'
								},
								mobile : {
									items:[
										{
											columnWidth: .5,
											items: [start,due, priority]
										},
										{
											columnWidth: .5,
											items: [progress, estimatedDuration, percentComplete]
										}
									]
								},
								items:[
									{
										columnWidth: .35,
										items: [start,due]
									},
									{
										columnWidth: .35,
										items: [progress, estimatedDuration]
									},
									{
										columnWidth: .3,
										items: [priority, percentComplete]
									}
								],
							},
							this.recurrenceField = new go.form.RecurrenceField({
								anchor: "100%",
								name: 'recurrenceRule',
								hidden: this.hideRecurrence,
								disabled: true,
							})
						]
					},
					{
						xtype: "fieldset",
						// collapsible: true,
						// title: t("Assignment"),
						items: [{
							layout: "hbox",
							items: [
								{
									style: "padding-right: 8px",
									layout: "form",
									xtype: "container",
									flex: 1,
									items: [
										this.tasklistCombo = new go.modules.community.tasks.TasklistCombo({
											listeners: {
												change: this.onTaskListChange,
												setvalue: this.onTaskListChange,
												scope: this
											}
										})
									]
								},
								{
									layout: "form",
									xtype: "container",
									flex: 1,
									items: [
										this.userCombo = new go.users.UserCombo({
											fieldLabel: t('Responsible'),
											hiddenName: 'responsibleUserId',
											anchor: '100%',
											allowBlank: true,
											value: null
										})
									]
								}]
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
									}}
							},
							displayField: "name",
							valueField: 'id',
							name: "categories",
							fieldLabel:t("Category", "tasks")
						}]
					}
					,

					{xtype:'hidden', name: 'groupId'},

					{
						xtype: "fieldset",
						// collapsible: true,
						// title: t("Other"),
						defaults : {
							anchor : '100%'
						},
						items: [

							{
								xtype: 'textarea',
								name: 'description',
								//allowBlank : false,
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
					},

					{
						xtype: "fieldset",
						// collapsible: true,
						title: t("Alerts"),
						items: [new go.modules.community.tasks.AlertFields()]
					}
				]
			}]
		});

		//this.recurrencePanel = new go.modules.community.tasks.RecurrencePanel();

		this.tabPanel = new Ext.form.FieldSet({
			activeTab : 0,
			deferredRender : false,
			border : false,
			anchor : '100% 100%',
			hideLabel : true,
			items : []
		});

		return [propertiesPanel];//this.tabPanel;
	}
});
