/* global Ext, go */

go.form.ComboBoxReset = Ext.extend(go.form.ComboBox, {

	initComponent : function() {
		this.preInitComp();

		this.triggerConfig = {
			tag:'span', cls:'x-form-twin-triggers', cn:[
				{tag: "img", src: Ext.BLANK_IMAGE_URL, alt: "", cls: "x-form-trigger " + this.trigger1Class},
				{tag: "img", src: Ext.BLANK_IMAGE_URL, alt: "", cls: "x-form-trigger " + this.trigger2Class}
			]};


	 Ext.form.TwinTriggerField.prototype.initComponent.call(this);
		go.form.ComboBox.superclass.initComponent.call(this);

		this.postInitComp();

	},
	getTrigger : Ext.form.TwinTriggerField.prototype.getTrigger,
	getTriggerWidth : function() {return 0; },
	initTrigger : Ext.form.TwinTriggerField.prototype.initTrigger,
	trigger1Class : 'x-form-clear-trigger',
	trigger2Class : 'x-form-arrow-trigger',
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
