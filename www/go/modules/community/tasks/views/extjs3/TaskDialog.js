go.modules.community.tasks.TaskDialog = Ext.extend(go.form.Dialog, {
	title: t("Task"),
	entityStore: "Task",
	width: dp(580),
	height: dp(480),
	modal: false,

	// onLoad: function (values) {
	// 	this.recurrencePanel.onLoad(values.start, values.recurrenceRule);
	// 	go.modules.community.tasks.TaskDialog.superclass.onLoad.call(this,values);
	// },
	initFormItems: function () {

		// this.taskCombo = new go.modules.community.tasks.TaskCombo({});

		var propertiesPanel = new Ext.Panel({
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
						items:[
							{columnWidth: .35, items:[{
									xtype:'datefield',
									name : 'start',
									itemId: 'start',
									fieldLabel : t("Start"),
									listeners : {
										setvalue : function(me,val) {
											console.log(val);
											me.nextSibling().setMinValue(val);
											this.recurrenceField.setDisabled(Ext.isEmpty(val));
										},
										scope : this
									}
								},{
									xtype:'datefield',
									name : 'due',
									itemId: 'due',
									fieldLabel : t("Due"),
									listeners : {
										change : function(me,val) {
											me.previousSibling().setMaxValue(val);
										},
										scope : this
									}
								}]},
							{columnWidth: .35, items:[
								// new go.form.ComboBox({
								// 	hiddenName : 'status',
								// 	triggerAction : 'all',
								// 	editable : false,
								// 	selectOnFocus : true,
								// 	forceSelection : true,
								// 	fieldLabel : t("Status"),
								// 	mode : 'local',
								// 	width:dp(150),
								// 	value : 'confirmed',
								// 	valueField : 'value',
								// 	displayField : 'text',
								// 	store : new Ext.data.SimpleStore({
								// 		fields : ['value', 'text'],
								// 		data : [
								// 			['confirmed', t("Confirmed")],
								// 			['cancelled', t("Cancelled")],
								// 			['tentative', t("Tentative")]]
								// 	})
								// }),
									new go.form.ComboBox({
									hiddenName : 'progress',
									triggerAction : 'all',
									editable : false,
									selectOnFocus : true,
									forceSelection : true,
									fieldLabel : t("Progress"),
									mode : 'local',
									value : 'needs-action',
									width:dp(150),
									valueField : 'value',
									displayField : 'text',
									store : new Ext.data.SimpleStore({
										fields : ['value', 'text'],
										data : [
											['completed', t("Completed")],
											['failed', t("Failed")],
											['in-progress', t("In Progress")],
											['needs-action', t("Needs action")],
											['cancelled', t("Cancelled")]]
									})
								}),new go.users.UserCombo({
									fieldLabel: t('Responsible'),
									hiddenName: 'responsibleUserId',
										anchor:'90%',
										allowBlank: true
								})
								]},
							{columnWidth: .3, items:[{
									xtype:'combo',
									name: 'priority_text',
									hiddenName: 'priority',
									triggerAction: 'all',
									editable: false,
									selectOnFocus: true,
									width: dp(150),
									forceSelection: true,
									fieldLabel: t("Priority"),
									mode: 'local',
									value: 5,
									valueField: 'value',
									displayField: 'text',
									store: new Ext.data.SimpleStore({
										fields: ['value', 'text'],
										data: [
											[1, t("Low")],
											[5, t("Normal")],
											[8, t("High")]
										]
									})
								},new Ext.form.SliderField({
									fieldLabel: t("Percent complete"),
									flex: 1,
									name: 'percentComplete',
									minValue: 0,
									maxValue: 100,
									increment: 10,
									value: 0,
									listeners: {
										scope: this,
										change: function (combo, newValue) {
											// if (newValue == 100)
											// 	this.taskStatusField.setValue("COMPLETED");
										}
									}
								})
								]}
						]
					},
					this.recurrenceField = new go.form.RecurrenceField({
						name: 'recurrenceRule',
						disabled: true,
					}),
					{xtype:'hidden', name: 'tasklistId'},
					{xtype:'hidden', name: 'groupId'},
					{
						xtype:'chips',
						xtype: "chips",
						entityStore: "TaskCategory",
						displayField: "name",
						valueField: 'id',
						name: "categories",
						fieldLabel:t("Category", "tasks")
					},{
						xtype:'textarea',
						name : 'description',
						//allowBlank : false,
						fieldLabel : t("Description")
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

		return propertiesPanel;//this.tabPanel;
	}
});
