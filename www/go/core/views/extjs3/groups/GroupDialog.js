go.groups.GroupDialog = Ext.extend(go.form.Dialog, {
	title: t('Group'),
	entityStore: "Group",
	height: dp(800),
	width: dp(1000),
	formPanelLayout: "border",
	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel());
		this.addPanel(new go.groups.GroupModuleGrid());
		
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
});

