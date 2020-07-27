go.modules.community.tasks.TaskDialog = Ext.extend(go.form.Dialog, {
	title: t("Task"),
	entityStore: "Task",
	width: dp(520),
	height: dp(480),

	onLoad: function (values) {
		this.recurrencePanel.onLoad(values.start, values.recurrenceRule);
		go.modules.community.tasks.TaskDialog.superclass.onLoad.call(this,values);
	},
	initFormItems: function () {
		var formFieldSets = go.customfields.CustomFields.getFormFieldSets("Task").filter(function(fs) {
			return !fs.fieldSet.isTab;
		});
		var fieldSetAnchor = formFieldSets.length ? '100% 80%' : '100% 100%';


		var checkDateInput = function(field) {
			if (field.itemId === 'due') {
				var start = field.ownerCt.get('start');
				if (start.getValue() > field.getValue()) {
					start.setValue(field.getValue());
				}
			} else {
				var due = field.ownerCt.get('due');
				if (field.getValue() > due.getValue()) {
					due.setValue(field.getValue());
				}
			}
		}

		this.selectCategory = new go.form.Chips({
			anchor: '-20',
			xtype: "chips",
			entityStore: "TaskCategory",
			displayField: "name",
			valueField: 'id',
			storeBaseParams: {
				filter: {
					//isOrganization: true
				}
			},
			name: "categories",
			fieldLabel:t("Category", "tasks")
		}),

		this.taskCombo = new go.modules.community.tasks.TaskCombo({});

		this.selectPriority = {
			xtype:'combo',
			name: 'priority_text',
			hiddenName: 'priority',
			triggerAction: 'all',
			editable: false,
			selectOnFocus: true,
			width: 120,
			forceSelection: true,
			fieldLabel: t("Priority"),
			mode: 'local',
			value: 1,
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
		};

		this.descriptionField = new Ext.form.TextArea({
			name : 'description',
			allowBlank : false,
			fieldLabel : t("Description")
		});

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
						layout:'hbox',
						defaults: {xtype:'container',layout:'form'},
						items: [
							{items:[{
								xtype:'datefield',
								name : 'start',
								itemId: 'start',
								fieldLabel : t("Starts at", "tasks"),
								listeners : {
									change : checkDateInput,
									scope : this
								}
							}]},
							{items:[{
								xtype:'datefield',
								name : 'due',
								itemId: 'due',
								fieldLabel : t("Due at", "tasks"),
								listeners : {
									change : checkDateInput,
									scope : this
								}
							}]},
							{items:[this.selectPriority]}
						]
					},
					{xtype:'hidden', name: 'tasklistId'},
					{xtype:'hidden', name: 'groupId'},
					this.selectCategory,
					this.descriptionField,
					{
						xtype:'container',
						title : t("Alerts"),
						layout : 'form',
						hideMode : 'offsets',
						autoScroll : true,
						items: [
							new go.modules.community.tasks.AlertFields()
						]
					}
				]
			}]
		});

		this.recurrencePanel = new go.modules.community.tasks.RecurrencePanel();
		// start other options tab

		var items = [
			propertiesPanel
		];

		this.tabPanel = new Ext.form.FieldSet({
			activeTab : 0,
			deferredRender : false,
			border : false,
			anchor : '100% 100%',
			hideLabel : true,
			items : items
		});

		return propertiesPanel;//this.tabPanel;
	}
});
