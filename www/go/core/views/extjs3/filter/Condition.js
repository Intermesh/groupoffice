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
		this.add(new go.filter.types[type]);				
	}
	
});

Ext.reg("filtercondition", go.filter.Condition);
