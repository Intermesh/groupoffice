go.filter.Condition = Ext.extend(go.form.FormContainer, {
	entity: null,
	
	layout: "hbox",

	initComponent: function () {
		this.filters = Object.values(go.Entities.get(this.entity).filters);
		this.items = [this.createFilterCombo()];		
		
		go.filter.Condition.superclass.initComponent.call(this);
	},

	createFilterCombo: function () {
		this.filterCombo = new go.form.ComboBox({
			width: dp(300),
			hideLabel: true,
			submit: false,
			name: "name",
			store: new Ext.data.JsonStore({
				fields: ['name', 'title'],
				root: 'data',
				data: {data: this.filters}
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
		
		return this.filterCombo;
	},
	
	onFieldSelect : function(combo, record, index) {
		this.items.each(function(i) {
			if(i === this.filterCombo) {
				return;
			}
			
			this.remove(i, true);
		}, this);
		
		this.switchCondition(this.filters[index].type);
		
		this.doLayout();
		
	},
	
	setValue : function(v) {		
		
		if(v) {
			var field = this.filters.find(function(f) {
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
