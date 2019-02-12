go.groups.GroupDialog = Ext.extend(go.form.Dialog, {
	title: t('Group'),
	entityStore: "Group",
	height: dp(600),
	initComponent : function() {
		go.groups.GroupDialog.superclass.initComponent.call(this);
		this.formPanel.layout = "border";
	},
	initFormItems: function () {
		
		return [{
				region: "north",
				autoHeight: true,
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
			this.groupUserGrid = new go.groups.GroupUserGrid({
				//anchor: '100% -' + dp(64),
				region: "center",
				hideLabel: true
			})
		];
	}
	
//	load : function(id) {
//		
//		this.groupUserGrid.load(id);
//		
//		return go.groups.GroupDialog.superclass.load.call(this, id);
//	}
});

