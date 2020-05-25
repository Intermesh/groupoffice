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

	initBbar : function() {
		go.filter.Conditions.superclass.initBbar.call(this);

		this.bbar.push('->');

		this.bbar.push({
			text: t("Add sub group"),
			handler: function() {
				var wrap = this.addPanel();
				var firstComboOption = Object.values(go.Entities.get(this.entity).filters).columnSort('title')[0].name;
				var condition = {};
				condition[firstComboOption] = "";
				wrap.formField.setValue({name: 'subconditions', value: {
					operator: "AND",
					conditions: [condition]
				}});

				this.doLayout();

				wrap.formField.items.itemAt(1).handler();
			},
			scope: this
		});
	},
	
	getValue : function() {
		var v = go.filter.Conditions.superclass.getValue.call(this), conditions = [];
		for(var i = 0, l = v.length; i < l; i++) {
			var condition = {};
			if(v[i].name == "subconditions") {
				conditions.push(v[i].value);
			} else
			{
				condition[v[i].name] = v[i].value;
				conditions.push(condition);
			}

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

			if(condition.operator) {
				v.push({name: "subconditions", value: condition});
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