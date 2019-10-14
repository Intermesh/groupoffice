/* global Ext, go */

go.form.ComboBoxReset = Ext.extend(go.form.ComboBox, {

	initComponent : Ext.form.TwinTriggerField.prototype.initComponent,
	getTrigger : Ext.form.TwinTriggerField.prototype.getTrigger,
	getTriggerWidth : function() {return 0; },
	initTrigger : Ext.form.TwinTriggerField.prototype.initTrigger,
	trigger1Class : 'x-form-clear-trigger',
	//hideTrigger1 : true,
	onTrigger2Click : function() {
		this.onTriggerClick();
	},
	onTrigger1Click : function() {
		if(this.disabled)
			return;

		var oldValue = this.getValue();
		this.clearValue();	
		this.fireEvent('change', this, this.getValue(), oldValue);
		this.fireEvent('clear', this);
	}

});
