go.filter.Condition = Ext.extend(go.form.FormContainer, {
	fields: null,
	
	layout: "hbox",

	initComponent: function () {
		
		this.items = [this.createFieldCombo()];

		go.filter.Condition.superclass.initComponent.call(this);
	},

	createFieldCombo: function () {
		this.fieldCombo = new go.form.ComboBox({
			width: dp(300),
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
			allowBlank: false,
			listeners: {
				scope: this,
				select: this.onFieldSelect
//				change: this.onFieldChange
			}
		});
		
		return this.fieldCombo;
	},
	
	onFieldSelect : function(combo, record, index) {
		this.items.each(function(i) {
			if(i === this.fieldCombo) {
				return;
			}
			
			this.remove(i, true);
		}, this);
		
		this.switchCondition(this.fields[index].type);
		
		this.doLayout();
		
	},
	
	setValue : function(v) {		
		
		if(v) {
			var field = this.fields.find(function(f) {
				return v.name == f.name
			});

			this.switchCondition(field.type);
		}
		
		go.filter.Condition.superclass.setValue.call(this, v);
		
	},
	
	switchCondition : function(type) {
		switch(type) {
			case 'string':
				this.add(new go.filter.types.StringPanel());				
				break;
		}		
	}
	
});

Ext.reg("filtercondition", go.filter.Condition);

Ext.ns("go.filter.types");
go.filter.types.StringPanel = Ext.extend(Ext.Panel, {
	layout: "hbox",
	flex: 1,
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
						['startswith', t("Starts with")],
						['endswith', t("Ends with")]
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

		go.filter.types.StringPanel.superclass.initComponent.call(this);
	},
	
	createValueField: function() {
		return new Ext.form.TextField({
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
		
		var wildCardPrefix = v.substring(0,1) == "%", l = v.length, wildCardSuffix = v.substring(l -1, l) == "%", operator = "equals";
		
		if(wildCardPrefix && wildCardSuffix) {
			operator = "contains";
		} else if(wildCardPrefix) {
			operator = "endswith";
		} else if(wildCardSuffix) {
			operator = "startswith";
		}
		
		if(wildCardPrefix) {
			v = v.slice(1);
		}
		
		if(wildCardSuffix) {
			v = v.slice(0, -1);
		}
		
		this.operatorCombo.setValue(operator);
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
