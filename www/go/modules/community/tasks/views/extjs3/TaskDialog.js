go.modules.community.tasks.TaskDialog = Ext.extend(go.form.Dialog, {
	title: t("Task"),
	entityStore: "Task",
	width: dp(800),
	height: dp(600),
	modal: false,

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

	initFormItems: function () {

		// this.taskCombo = new go.modules.community.tasks.TaskCombo({});

		const start = {
			xtype:'datefield',
			name : 'start',
			itemId: 'start',
			fieldLabel : t("Start"),
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
				xtype: "fieldset",
				defaults : {
					anchor : '100%'
				},
				labelWidth:90,
				items:[
					{
						xtype: 'container',
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
						xtype:'container',
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
						]
					},
					{
						xtype: "container",
						layout:"hbox",
						items:[
							{
								style: "padding-right: 8px",
								layout: "form",
								xtype: "container",
								flex: 1,
								items: [
									this.tasklistCombo = new go.modules.community.tasks.TasklistCombo({
										listeners: {
											change: (combo, val) => {
												const categories = this.formPanel.form.findField('categories');
												categories.comboStore.setFilter("tasklistId", {tasklistId: val});
												//reloads combo when trigger is clicked
												delete categories.comboBox.lastQuery;
											}
										},
										value: go.User.tasksSettings.defaultTasklistId,
										allowBlank: false
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
										allowBlank: true
									})
								]
							}
						]
					}
					,
					this.recurrenceField = new go.form.RecurrenceField({
						name: 'recurrenceRule',
						hidden: this.hideRecurrence,
						disabled: true,
					}),
					{xtype:'hidden', name: 'groupId'},
					{
						xtype: "chips",
						entityStore: "TaskCategory",
						comboStoreConfig: {
							filters: {tasklistId: {tasklistId:this.tasklistCombo.getValue()}}
						},
						displayField: "name",
						valueField: 'id',
						name: "categories",
						fieldLabel:t("Category", "tasks")
					},{
						xtype:'textarea',
						name : 'description',
						//allowBlank : false,
						fieldLabel : t("Description"),
						grow: true

					},{
						xtype:'textarea',
						name : 'location',
						allowBlank : true,
						fieldLabel : t("Location"),
						grow: true

					},
					new go.modules.community.tasks.AlertFields()
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
