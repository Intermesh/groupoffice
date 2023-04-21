GO.zpushadmin.MainPanel = Ext.extend(Ext.Panel, {
	layout: 'border',

	initComponent() {

		this.centerPanel = new GO.zpushadmin.DevicesGrid({
			region:'center',
			id:'zpadmin-center-panel',
			border:true,
			tbar: [{
				xtype:'tbtitle',
				html:t("ActiveSync management"),
			},
			'->',
			new GO.form.SearchField({
				store: GO.zpushadmin.deviceStore,
				width:150
			}),{
				itemId:'delete',
				iconCls: 'btn-delete',
				text: t("Delete"),
				disabled:this.standardTbarDisabled,
				handler: function(){
					this.deleteSelected();
				},
				scope: this.centerPanel
			}],
			listeners: {
				"delayedrowselect": (grid, rowIndex, r) => {
					this.devicePanel.load(r.data.id);
				}
			}
		});

		this.devicePanel = new GO.zpushadmin.DevicePanel({
			region:'east',
			width:400,
			border:true
		});

		this.items = [
			this.centerPanel,
			this.devicePanel
		];

		this.supr().initComponent.call(this);
	}
});



GO.moduleManager.addModule('zpushadmin', GO.zpushadmin.MainPanel, {
	title : t("ActiveSync", "zpushadmin"),
	iconCls : 'go-tab-icon-zpushadmin',
	admin :true
});