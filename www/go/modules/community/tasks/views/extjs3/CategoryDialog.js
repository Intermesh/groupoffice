go.modules.community.tasks.CategoryDialog = Ext.extend(go.form.Dialog, {
	title: t("Category", "tasks"),
	entityStore: "TaskCategory",
	titleField: "name",
	resizable: false,
	width: dp(400),
	height: dp(400),
	redirectOnSave: false,
	initFormItems: function () {
		var items = [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					},
					this.tasklistCombo = new go.modules.community.tasks.TasklistComboBoxReset({
						allowBlank: true,
						emptyText: t("All")
					})
					]
			}
		];

		if(go.Modules.get("community", "tasks").userRights.mayChangeCategories)
		{
			this.ownerIdField = new Ext.form.Hidden({name:'ownerId',value:go.User.id});

			this.checkbox = new Ext.form.Checkbox({
				xtype:'xcheckbox',
				boxLabel:t("Global category", "tasks"),
				hideLabel:true,
				submit:false,
				anchor: '100%',
				listeners: {scope:this,'check': function(me, checked) {
					this.ownerIdField.setValue(checked ? null : go.User.id);
					this.tasklistCombo.setDisabled(checked);
				}}
			});

			items[0].items.push(this.checkbox,this.ownerIdField);
		}
		return items;
	},

	onLoad: function() {
		this.supr().onLoad.call(this);

		if (go.Modules.get("community", "tasks").userRights.mayChangeCategories) {
			this.checkbox.setValue(!this.tasklistCombo.getValue() && !this.ownerIdField.getValue());
		}

	}
});
