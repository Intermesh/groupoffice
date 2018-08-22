/**
 * 
 * {
			xtype: 'radiogroup',
			fieldLabel: t("Gender"),
			name:"gender",
			items: [
				{boxLabel: t("Unknown"), inputValue: null},
				{boxLabel: t("Male"), inputValue: 'M'},
				{boxLabel: t("Female"), inputValue: 'F'}
			]
		}
 */
go.form.RadioGroup = Ext.extend(Ext.form.RadioGroup, {
	/**
	 * Override to return inputValue instead of component
	 * 
	 * @return text
	 */
	getValue: function () {

		var out = go.form.RadioGroup.superclass.getValue.call(this);
		return out ? out.inputValue : null;
	},
	
	//make radio buttons inherit group name
	onRender : function(ct, position) {
		var me = this;
		this.items.forEach(function(i) {
			if(!i.name) {
				i.name = me.name;
			}
		});
		
		go.form.RadioGroup.superclass.onRender.call(this, ct, position);
	},
	
	setValueForItem : function(val){
		//override to support null values.
		this.eachItem(function(item){
				item.setValue(val == item.inputValue);
		});
   }
});

Ext.reg("radiogroup", go.form.RadioGroup);