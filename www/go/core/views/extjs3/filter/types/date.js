go.filter.types.date = Ext.extend(Ext.Panel, {
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
				value: 'before',
				store: new Ext.data.ArrayStore({
					fields: ['value', 'text'],					
					data: [
						['before', t("is before, today plus")],
						['after', t("is after, today plus")]
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
			
		this.periodCombo = new go.form.ComboBox({

				hideLabel: true,
				name: "period",
				value: 'days',
				store: new Ext.data.ArrayStore({
					fields: ['value', 'text'],					
					data: [
						['days', t("days")],
						['months', t("months")],
						['years', t("years")]
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
			this.valueField,
			this.periodCombo
		];

		go.filter.types.date.superclass.initComponent.call(this);
	},
	
	createValueField: function() {
		return new GO.form.NumberField({
			serverFormats: false,
			flex: 1,
			decimals: 0,
			name: 'value'
		});
	},
	
	isFormField: true,
	
	name: 'value',
	
	
	getName : function() {
		return this.name;
	},
	
	setValue: function(v) {
				
		var regex = /([><]+) ([\-0-9]+) (days|months|years)/,						
						operator = 'before', period = 'days', number = 0;
		
		if(v) {
			var matches = v.match(regex);

			if(matches) {	
				number = parseFloat(matches[2].trim());
				period = matches[3].trim();

				switch(matches[1]) {
					case '>':
						operator = 'after';
						break;
					case '<':
						operator = 'before';
						break;
				}
			}		
		}
		this.operatorCombo.setValue(operator);
		this.valueField.setValue(number);
		this.periodCombo.setValue(period);
	},
	getValue: function() {
		
		var v =  this.valueField.getValue() + ' ' + this.periodCombo.getValue();
		
		switch(this.operatorCombo.getValue()) {				
								
			case 'after':				
				return '> ' + v;
				
			case 'before':				
				return '< ' + v;
			
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

