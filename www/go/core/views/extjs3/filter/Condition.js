go.filter.Condition = Ext.extend(go.form.FormContainer, {
	entity: null,
	
	layout: "column",

	initComponent: function () {
		this.filters = Object.values(go.Entities.get(this.entity).filters);

		this.filters.columnSort('title');

		this.items = [this.createFilterCombo()];		
		
		go.filter.Condition.superclass.initComponent.call(this);

	},

	createFilterCombo: function () {
		this.filterCombo = new go.form.ComboBox({
			width: dp(300),
			hideLabel: true,
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
			editable: true,
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
		
		this.switchCondition(this.filters[index]);
		
		this.doLayout();
		
	},
	
	setValue : function(v) {		
		
		if(v) {
			var filter = this.filters.find(function(f) {
				return v.name == f.name
			});

			this.switchCondition(filter);
		}
		
		go.filter.Condition.superclass.setValue.call(this, v);
		
	},	
	
	switchCondition : function(filter) {
		
		var cls;
		
		if(go.filter.types[filter.type]) {
			cls = go.filter.types[filter.type];
		}else
		{
			cls = eval(filter.type);
		}

		if(!filter.typeConfig) {
			filter.typeConfig = {};
		}

		Ext.apply(filter.typeConfig, {
			columnWidth: 1,
			filter: filter,
			name: 'value',
			hiddenName: 'value',
			customfield: filter.customfield //Might be null if this is a standard filter.
		});
		
		this.add(new cls(filter.typeConfig));				
	}
	
});

Ext.reg("filtercondition", go.filter.Condition);
