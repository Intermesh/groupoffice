go.filter.types.number = Ext.extend(Ext.Panel, {
	layout: "hbox",
	flex: 1,
	initComponent: function () {
		
		this.operatorCombo = new go.form.ComboBox({
				submit: false,
				hideLabel: true,
				name: "operator",
				value: 'equals',
				store: new Ext.data.ArrayStore({
					fields: ['value', 'text'],					
					data: [
						['equals', t("Equals")],
						['greater', t("Greater than")],
						['greaterorequal', t("Greater than or equal")],
						['less', t("Less than")],
						['lessorequal', t("Less than or equal")]
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

		go.filter.types.string.superclass.initComponent.call(this);
	},
	
	createValueField: function() {
		return new GO.form.NumberField({
			serverFormats: false,
			flex: 1,
			submit: false,
			name: 'value'
		});
	},
	
	isFormField: true,
	
	name: 'value',
	
	submit : false,
	
	getName : function() {
		return this.name;
	},
	
	setValue: function(v) {
		
		var regex = /([>=<]+)(.*)/,
						matches = v.match(regex),
						operator = 'equals';
		
		console.log(matches);
		
		if(matches) {		
			v = parseFloat(matches[2].trim());
			
			console.log(v);
			
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
				return '<' + v;
				
			case 'lessorqual':				
				return '<=' + v;
		}
	},
	validate: function() {
		return this.valueField.validate() && this.operatorCombo.validate();
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

