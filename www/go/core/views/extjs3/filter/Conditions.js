go.filter.Conditions = Ext.extend(go.form.FormGroup, {
	fields: null,
	name: "filter",
	addButtonText: t("Add condition"),
	initComponent : function() {
		
		this.itemCfg = {
			xtype: "filtercondition"	,
			fields: this.fields
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
		
		return {
			operator: "AND",
			conditions: conditions
		};
	}

});

Ext.reg("filterconditions", go.filter.Conditions);