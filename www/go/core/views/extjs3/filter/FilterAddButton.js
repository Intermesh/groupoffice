go.filter.FilterAddButton = Ext.extend(Ext.Button,{
	entity: null,
	iconCls: "ic-add",
	initComponent: function() {
		this.menu = [
			{
				text: t("Filter"),
				iconCls: 'ic-filter-list',
				handler: function() {
					var dlg = new go.filter.FilterDialog({
						entity: this.entity
					});
					dlg.show();
				},
				scope: this
			},
			{
				text: t("Input field"),
				iconCls: 'ic-search',
				handler: function() {
					var dlg = new go.filter.VariableFilterDialog({
						entity: this.entity
					});
					dlg.show();
				},
				scope: this
			}
		];

		this.supr().initComponent.call(this);
	}
});

Ext.reg('filteraddbutton', go.filter.FilterAddButton);