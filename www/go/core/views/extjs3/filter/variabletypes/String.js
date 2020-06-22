go.filter.variabletypes.String = Ext.extend(Ext.Panel, {
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

		// this.operatorCombo = new go.form.ComboBox({
		//
		// 	hideLabel: true,
		// 	name: "operator",
		// 	value: '*',
		// 	store: new Ext.data.ArrayStore({
		// 		fields: ['value'],
		// 		data: [
		// 			['='],
		// 			['*']
		// 			// ['...*'],
		// 			// ['*...']
		// 		]
		// 	}),
		// 	valueField: 'value',
		// 	displayField: 'value',
		// 	mode: 'local',
		// 	triggerAction: 'all',
		// 	editable: false,
		// 	selectOnFocus: true,
		// 	forceSelection: true,
		// 	width: dp(64)
		// });

		this.valueField = new Ext.form.TextField({
			flex: 1,
			name: 'value',
			listeners: {
				scope: this,
				specialkey: function(cmp, e) {
					if (e.getKey() == e.ENTER) {
						this.fireEvent('select', this, this.getValue());
					}
				}
			}
		});


		this.items = [
			// this.operatorCombo,
			this.valueField
		];

		this.supr().initComponent.call(this);
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


		// switch(this.operatorCombo.getValue()) {
		// 	case '=':
		// 		return this.valueField.getValue();
		// 		break;
		//
		// 	case '*':
				return '%' + this.valueField.getValue() + '%';
				// break;

			// case '*...':
			// 	return '%' + this.valueField.getValue();
			// 	break;
			//
			// case '...*':
			// 	return this.valueField.getValue() + '%';
			// 	break;
		// }

	},

	getRawValue : function() {

		return this.valueField.getValue();

		// switch(this.operatorCombo.getValue()) {
		// 	case '=':
		// 		return this.valueField.getValue();
		// 		break;
		//
		// 	case '*':
		// 		return '*' + this.valueField.getValue() + '*';
		// 		break;

			// case '*...':
			// 	return '*' + this.valueField.getValue();
			// 	break;
			//
			// case '...*':
			// 	return this.valueField.getValue() + '*';
			// 	break;
		// }
	},

	reset: function() {
		return this.valueField.reset() && this.operatorCombo.reset();
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
	},
	isValid : function(preventMark){
		return this.valueField.isValid(preventMark) && this.operatorCombo.isValid(preventMark);
	}

});

