/* global Ext, go */

go.SearchField = Ext.extend(Ext.form.TwinTriggerField, {
	emptyText: t("Search"),
	hideLabel: true,
	validationEvent: false,
	validateOnBlur: false,
	trigger1Class: 'x-form-search-trigger',
	trigger2Class: 'x-form-clear-trigger',
	spellCheck: false,
	onTrigger1Click: function () {
		this.handler.call(this.scope, this, this.getValue());
	},
	
	onTrigger2Click: function () {
		this.setValue("");
		this.handler.call(this.scope, this, "");
	},	
	
	initComponent : function() {
		
		this.scope = this.scope || this;
		
		go.SearchField.superclass.initComponent.call(this);
		
		this.on("specialkey", function (field, e) {
			if (e.getKey() === Ext.EventObject.ENTER) {
				this.handler.call(this.scope, this, this.getValue());
			}
		}, this);
	},
	
	handler: function(btn, value) {
		
	}
});


Ext.reg("tbsearchfield", go.SearchField);