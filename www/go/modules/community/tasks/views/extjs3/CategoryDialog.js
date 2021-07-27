go.modules.community.tasks.CategoryDialog = Ext.extend(go.form.Dialog, {
	title: t("Category", "tasks"),
	entityStore: "TaskCategory",
	titleField: "name",
	resizable: false,
	width: dp(400),
	autoHeight: true,
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
					]
			}
		];
		if(GO.settings.has_admin_permission)
		{
			this.ownerIdField = new Ext.form.Hidden({name:'ownerId',value:go.User.id, listeners:{
				'setvalue': (me, val) => {  this.checkbox.setValue(!val) }
			}});
			this.checkbox = new Ext.form.Checkbox({
				xtype:'xcheckbox',
				boxLabel:t("Global category", "tasks"),
				hideLabel:true,
				submit:false,
				anchor: '100%',
				listeners: {scope:this,'check': function(me, checked) { this.ownerIdField.setValue(checked ? null : go.User.id) }}
			});

			items[0].items.push(this.checkbox,this.ownerIdField);
		}
		return items;
	}
});
