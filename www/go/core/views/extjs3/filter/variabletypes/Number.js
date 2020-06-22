go.filter.variabletypes.Number = Ext.extend(Ext.Panel, {
	layout: "hbox",
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

		this.addEvents({select: true});

		this.operatorCombo = new go.form.ComboBox({

			hideLabel: true,
			name: "operator",
			value: '=',
			store: new Ext.data.ArrayStore({
				fields: ['value'],
				data: [
					['<'],
					['<='],
					['>'],
					['>='],
					['=']
				]
			}),
			valueField: 'value',
			displayField: 'value',
			mode: 'local',
			triggerAction: 'all',
			editable: false,
			selectOnFocus: true,
			forceSelection: true,
			width: dp(64)
		});

		this.valueField = new GO.form.NumberField({
			serverFormats: false,
			flex: 1,
			name: 'value'
		});


		this.items = [
			this.operatorCombo,
			this.dateField
		];

		go.filter.types.date.superclass.initComponent.call(this);
	},

	onSelect: function() {
		this.fireEvent('select', this, this.getValue());
	},

	isFormField: true,

	name: 'value',

	getName : function() {
		return this.name;
	},

	getValue: function() {
		if(this.operatorCombo.getValue() == '=') {
			return this.dateField.getValue();
		}
		return this.operatorCombo.getValue() + ' ' + this.dateField.getValue();
	},

	getRawValue : function() {
		if(this.operatorCombo.getValue() == '=') {
			return this.dateField.getRawValue();
		}
		return this.operatorCombo.getValue() + ' ' + this.dateField.getRawValue();
	},

	reset: function() {
		return this.dateField.reset() && this.operatorCombo.reset();
	},

	validate: function() {
		return this.dateField.validate() && this.operatorCombo.validate();
	},
	markInvalid : function() {
		return this.dateField.markInvalid();
	},
	clearInvalid : function() {
		return this.dateField.clearInvalid();
	},
	isDirty : function() {
		return this.dateField.isDirty() || this.operatorCombo.isDirty();
	},
	isValid : function(preventMark){
		return this.dateField.isValid(preventMark) && this.operatorCombo.isValid(preventMark);
	}

});

