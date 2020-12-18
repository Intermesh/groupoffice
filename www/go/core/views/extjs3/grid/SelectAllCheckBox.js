go.grid.SelectAllCheckbox = Ext.extend(Ext.form.Checkbox, {
	/**
	 * when unchecking select the first record. If false then nothing will be selected.
	 */
	selectFirst: true,
	constructor: function (config) {
		Ext.apply(config, {
			hideLabel: true,
			boxLabel: t("Select all"),
			listeners: {
				scope: this,
				afterrender: function(cb, ownerCt, index) {

					this.grid = this.findParentByType("grid");

					this.grid.getSelectionModel().on('selectionchange', this.checkChecked, this);
				},
				check: function (cb, checked) {
					if (checked) {
						this.grid.getSelectionModel().selectAll();
					} else if(this.selectFirst)
					{
						this.grid.getSelectionModel().selectRange(0, 0);
					} else {
						this.grid.getSelectionModel().clearSelections();
					}
				}
			}
		});
		
		go.grid.SelectAllCheckbox.superclass.constructor.call(this, config);
	},

	checkChecked : function() {

		this.suspendEvents(false);
		this.setValue(this.grid.getSelectionModel().getCount() == this.grid.store.getCount());
		this.resumeEvents();
	},
	
	onRender : function(ct, position) {
		go.grid.SelectAllCheckbox.superclass.onRender.call(this, ct, position);
		
		this.wrap.addClass('go-select-all-checkbox');
	}
});

// registre xtype
Ext.reg('selectallcheckbox', go.grid.SelectAllCheckbox);
