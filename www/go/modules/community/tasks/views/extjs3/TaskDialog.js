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
		
		this.titleField = new Ext.form.TextField({
			name : 'title',
			allowBlank : false,
			fieldLabel : t("Subject")
		});

		var checkDateInput = function(field) {
			if (field.name == 'due') {
				if (start.getValue() > due.getValue()) {
					start.setValue(due.getValue());
				}
			} else {
				if (start.getValue() > due.getValue()) {
					due.setValue(start.getValue());
				}
			}
		}

		var now = new Date();

		var start = new Ext.form.DateField({
			name : 'start',
			fieldLabel : t("Starts at", "tasks"),
			value : now.format(go.User.dateFormat),
			listeners : {
				change : {
					fn : checkDateInput,
					scope : this
				}
			}
		});

		var due = new Ext.form.DateField({
			name : 'due',
			allowBlank : false,
			fieldLabel : t("Due at", "tasks"),
			value : now.format(go.User.dateFormat),
			listeners : {
				change : {
					fn : checkDateInput,
					scope : this
				}
			}
		});

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

		this.selectTasklist = new go.form.ComboBoxReset({
			hiddenName:'tasklistId',
			fieldLabel:t("Tasklist"),
			valueField:'id',
			displayField:'name',			
			store: new go.data.Store({
				fields:['id','name','user_name'],
				entityStore: "Tasklist",
				displayField: "name",
			}),
			mode:'local',
			triggerAction:'all',
			emptyText:t("Select tasklist"),
			editable:false,
			selectOnFocus:true,
			forceSelection:true,
			pageSize: parseInt(GO.settings['max_rows_list'])
		});
		

		this.taskCombo = new go.modules.community.tasks.TaskCombo({});
		this.selectPriority = new go.modules.community.tasks.SelectPriority();
		this.descriptionField = new Ext.form.TextArea({
			name : 'description',
			allowBlank : false,
			fieldLabel : t("Description")
		});

		var propertiesPanel = new Ext.Panel({
			hideMode : 'offsets',
			title : t("Properties"),
			
			layout : 'form',
			autoScroll : true,
			items : [{
					xtype: "fieldset",
					defaults : {
					anchor : '100%'
			},
			labelWidth:120,
					items:[
				this.titleField, 
				start,
				due,
				this.selectTasklist,
				this.selectCategory,
				this.selectPriority,
				this.descriptionField
				]
			}
			]
		});

		this.recurrencePanel = new go.modules.community.tasks.RecurrencePanel();
		// start other options tab
		this.optionsPanel = new Ext.Panel({
			title : t("Alerts"),
			layout : 'form',
			hideMode : 'offsets',
			autoScroll : true,
			items: [
				new go.modules.community.tasks.AlertFields()
			],
		});

		var items = [
			propertiesPanel,{
				hideMode : 'offsets',
				cls:"go-form-panel",
				title: t("Recurrence"),
				items:[this.recurrencePanel]
			},this.optionsPanel
		];

		this.tabPanel = new Ext.TabPanel({
			activeTab : 0,
			deferredRender : false,
			border : false,
			anchor : '100% 100%',
			hideLabel : true,
			items : items
		});
		
		this.selectTasklist.store.load();
		return this.tabPanel;
	}
});
