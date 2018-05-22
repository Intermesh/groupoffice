go.modules.core.groups.GroupDialog = Ext.extend(go.form.Dialog, {
	title: t('Group'),
	entityStore: go.Stores.get("Group"),
	height: dp(600),
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',						
						allowBlank: false
					}]
			},
			this.groupUserGrid = new go.modules.core.groups.GroupUserGrid({
				anchor: '100% -' + dp(32),
				hideLabel: true
			})
		];
	}
	
//	load : function(id) {
//		
//		this.groupUserGrid.load(id);
//		
//		return go.modules.core.groups.GroupDialog.superclass.load.call(this, id);
//	}
});

