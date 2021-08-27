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
	
		if(!this.rendered) {
			return this.value;
		}

		var out = go.form.RadioGroup.superclass.getValue.call(this);
		return out ? out.inputValue : null;
	},
	
	initValue : function() {		
		this.originalValue = this.getValue();
	},
	
	setValue : function(v) {
		go.form.RadioGroup.superclass.setValue.call(this, v);
		this.originalValue = this.value = v;
	},
	
	isDirty : function() {
		return this.originalValue != this.getValue();
	},
	
	//make radio buttons inherit group name
	onRender : function(ct, position) {
		var me = this;

		me.groupName = this.name + "-" + Ext.id();

		this.items.forEach(function(i) {
			if(!i.name) {
				i.name = me.groupName;
			}
			
			i.checked = me.value === i.inputValue;
		});
		
		go.form.RadioGroup.superclass.onRender.call(this, ct, position);
	},

	getErrors: function() {


		var errors = Ext.form.CheckboxGroup.superclass.getErrors.apply(this, arguments);

		if (!this.allowBlank) {
			var blank = this.getValue() == null;



			if (blank) errors.push(this.blankText);
		}

		return errors;
	},


	setValueForItem : function(val){
		//override to support null values.
		this.eachItem(function(item){
				item.setValue(val == item.inputValue);
		});
   }
});

Ext.reg("radiogroup", go.form.RadioGroup);