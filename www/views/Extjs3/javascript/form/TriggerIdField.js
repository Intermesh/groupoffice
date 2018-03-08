GO.form.TriggerIdField = Ext.extend(Ext.form.TwinTriggerField, {
	
	trigger1Class : 'x-form-clear-trigger',

	onRender : function(ct, position){

		GO.form.TriggerIdField.superclass.onRender.call(this, ct, position);
		this.hiddenField = this.el.insertSibling({
			tag:'input', 
			type:'hidden', 
			name: this.name
			},'before', true);

		// prevent input submission
		this.el.dom.removeAttribute('name');
		
	},
	onTrigger2Click : function() {
		this.onTriggerClick();
	},
	onTrigger1Click : function() {

		var oldValue = this.hiddenField.value;

		this.hiddenField.value="";
		this.setRawValue('');
		//this.triggers[0].setDisplayed(false);

		this.fireEvent('change', this, this.getValue(), oldValue);
		this.fireEvent('clear', this);
	},

	// private
	initValue : function(){
		GO.form.TriggerIdField.superclass.initValue.call(this);
		if(this.hiddenField){
			this.hiddenField.value =
			this.hiddenValue !== undefined ? this.hiddenValue :
			this.value !== undefined ? this.value : '';
		}
	},

	setValue : function(v){
		if(this.hiddenField){
			this.hiddenField.value = v;
		}
	//GO.form.TriggerIdField.superclass.setValue.call(this, v);
	},

	setText : function(text){
		this.setRawValue(text);
	}
});