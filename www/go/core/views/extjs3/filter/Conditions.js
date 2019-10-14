go.filter.Conditions = Ext.extend(go.form.FormGroup, {
	entity: null,
	name: "conditions",
	addButtonText: t("Add condition"),	
	hideLabel: true,
	initComponent : function() {
		
		this.itemCfg = {
			xtype: "filtercondition"	,
			entity: this.entity
		};
		
		go.filter.Conditions.superclass.initComponent.call(this);
	},
	
	getValue : function() {
		var v = go.filter.Conditions.superclass.getValue.call(this), conditions = [];
		for(var i = 0, l = v.length; i < l; i++) {
			var condition = {};
			condition[v[i].name] = v[i].value;
			conditions.push(condition);
		}
		
		return conditions;
	},
	
	setValue : function(conditions) {
		
		if(!conditions) {
			return;
		}
		
		var v = [];
		
		for(var i = 0, l = conditions.length; i < l; i++) {
			var condition = conditions[i];			
			if(!Ext.isObject(condition)) {
				//invalid filter
				continue;
			}
			for(var name in condition) {
				v.push({
					name: name,
					value: condition[name]
				});
			}
		}		
		
		go.filter.Conditions.superclass.setValue.call(this, v);		
	}

});

Ext.reg("filterconditions", go.filter.Conditions);