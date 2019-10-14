go.grid.SelectAllCheckbox = Ext.extend(Ext.form.Checkbox, {
	constructor: function (config) {
		Ext.apply(config, {
			hideLabel: true,
			boxLabel: t("Select all"),
			listeners: {
				scope: this,
				check: function (cb, checked) {
					var grid = this.findParentByType("grid");
					if (checked) {
						grid.getSelectionModel().selectAll();
					} else
					{
						grid.getSelectionModel().selectRange(0, 0);
					}
				}
			}
		});
		
		
		go.grid.SelectAllCheckbox.superclass.constructor.call(this, config);
		
		
	},
	
	onRender : function(ct, position) {
		go.grid.SelectAllCheckbox.superclass.onRender.call(this, ct, position);
		
		this.wrap.addClass('go-select-all-checkbox');
	}
});

// registre xtype
Ext.reg('selectallcheckbox', go.grid.SelectAllCheckbox);
