go.filter.Condition = Ext.extend(Ext.Panel, {
	fields: null,
	
	layout: "hbox",

	initComponent: function () {
		
		this.items = [this.createFieldCombo()];

		go.filter.Condition.superclass.initComponent.call(this);
	},

	createFieldCombo: function () {
		this.fieldCombo = new go.form.ComboBox({
			hideLabel: true,
			submit: false,
			name: "name",
			store: new Ext.data.JsonStore({
				fields: ['name', 'title'],
				root: 'data',
				data: {data: this.fields}
			}),
			valueField: 'name',
			displayField: 'title',
			mode: 'local',
			triggerAction: 'all',
			editable: false,
			selectOnFocus: true,
			forceSelection: true,
			listeners: {
				scope: this,
				select: this.onFieldSelect
			}
		});
		
		return this.fieldCombo;
	},
	
	onFieldSelect : function(combo, record, index) {
		this.items.each(function(i) {
			if(i === this.fieldCombo) {
				return;
			}
			
			this.remove(i, false);
		}, this);
		
		switch(this.fields[index].type) {
			case 'string':
				this.add(new go.filter.types.StringPanel());				
				break;
		}
		
		this.doLayout();
	}
	
});

Ext.reg("filtercondition", go.filter.Condition);

Ext.ns("go.filter.types");
go.filter.types.StringPanel = Ext.extend(Ext.Panel, {
	layout: "hbox",
	initComponent: function () {
		
		this.operatorCombo = new go.form.ComboBox({
				submit: false,
				hideLabel: true,
				name: "operator",
				value: 'contains',
				store: new Ext.data.ArrayStore({
					fields: ['value', 'text'],					
					data: [
						['contains', t("Contains")],
						['equals', t("Equals")],
						['startsWith', t("Starts with")],
						['endsWith', t("Ends with")]
					]
				}),
				valueField: 'value',
				displayField: 'text',
				mode: 'local',
				triggerAction: 'all',
				editable: false,
				selectOnFocus: true,
				forceSelection: true
			});
			
			
		this.valueField = this.createValueField();
		
		this.items = [
			this.operatorCombo,
			this.valueField
		];

		go.filter.Condition.superclass.initComponent.call(this);
	},
	
	createValueField: function() {
		return new Ext.form.TextField({
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
		
		// TODO 
		this.operatorCombo.setValue('equals');
		this.valueField.setValue(v);
	},
	getValue: function() {
		
		var v =  this.valueField.getValue();
		
		switch(this.operatorCombo.getValue()) {
			case 'contains':				
				return '%' + v + '%';
				
			case 'equals':				
				return v;
								
			case 'startswith':				
				return v + '%';
				
			case 'endswith':				
				return '%' + v;
		}
	},
	validate: function() {
		return this.valueField.validate();
	},
	markInvalid : function() {
		return this.valueField.markInvalid();
	},
	clearInvalid : function() {
		return this.valueField.clearInvalid();
	}
	
});
