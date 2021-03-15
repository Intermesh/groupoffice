go.filter.types.number = Ext.extend(Ext.Panel, {
	layout: "hbox",
	flex: 1,
	/**
	 * Filter definition
	 * {
					name: 'text', //Filter name
					type: "string", //Sting type of go.filters.type or a full class name
					multiple: false, // nly applies to query field parsing. You can use name: Value1,Value2 nad it will turn into an array for an OR group
					title: "Query",
					customfield: model //When it's a custom field
				},
	 */
	filter: null,
	initComponent: function () {
		
		this.operatorCombo = new go.form.ComboBox({
				
				hideLabel: true,
				name: "operator",
				value: 'equals',
				store: new Ext.data.ArrayStore({
					fields: ['value', 'text'],					
					data: [
						['equals', t("equals")],
						['greater', t("is greater than")],
						['greaterorequal', t("is greater than or equal")],
						['less', t("is less than")],
						['lessorequal', t("is less than or equal")]
					]
				}),
				valueField: 'value',
				displayField: 'text',
				mode: 'local',
				triggerAction: 'all',
				editable: false,
				selectOnFocus: true,
				forceSelection: true,
				width: Math.ceil(dp(200))
			});
			
			
		this.valueField = this.createValueField();
		
		this.items = [
			this.operatorCombo,
			this.valueField
		];

		go.filter.types.number.superclass.initComponent.call(this);
	},
	
	createValueField: function() {
		return new GO.form.NumberField({
			serverFormats: false,
			flex: 1,
			name: 'value'
		});
	},
	
	isFormField: true,
	
	name: 'value',
	
	
	getName : function() {
		return this.name;
	},
	
	setValue: function(v) {
		
		var regex = /([>=<]+)(.*)/,						
						operator = 'equals';
		
		if(v) {
			var matches = (v + '').match(regex);
			if(matches) {		
				v = parseFloat(matches[2].trim());			

				switch(matches[1]) {
					case '>':
						operator = 'greater';
						break;

					case '>=':
						operator = 'greaterorequal';
						break;

					case '<':
						operator = 'less';
						break;

					case '<=':
						operator = 'lessorequal';
						break;
				}
			}		
		}
		
		this.operatorCombo.setValue(operator);
		this.valueField.setValue(v);
	},
	getValue: function() {
		
		var v =  this.valueField.getValue();
		
		switch(this.operatorCombo.getValue()) {				
			case 'equals':				
				return v;
								
			case 'greater':				
				return '> ' + v;
				
			case 'greaterorequal':				
				return '>= ' + v;
				
			case 'less':				
				return '< ' + v;
				
			case 'lessorequal':
				return '<= ' + v;
		}
	},
	validate: function() {
		return this.valueField.validate() && this.operatorCombo.validate();
	},
	isValid : function(preventMark){
		return this.valueField.isValid(preventMark) && this.operatorCombo.isValid(preventMark);
	},
	markInvalid : function() {
		return this.valueField.markInvalid();
	},
	clearInvalid : function() {
		return this.valueField.clearInvalid();
	},
	isDirty : function() {
		return this.valueField.isDirty() || this.operatorCombo.isDirty();
	}
	
});

