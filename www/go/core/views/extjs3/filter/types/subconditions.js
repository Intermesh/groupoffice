go.filter.SubConditionsDialog = Ext.extend(go.Window, {
	title: t("Conditions"),
	entity: null,
	height: dp(480),
	width: dp(1000),

	initComponent: function () {

		this.items = [{
			xtype: "filterfieldset",
			entity: this.entity
		}];

		this.buttons = [{
			text: t("Ok"),
			handler: function() {
				this.typeCmp.setValue(this.items.itemAt(0).conditionsField.getValue());
				this.close();
			},
			scope: this
		}]

		this.value = [];

		go.filter.SubConditionsDialog.superclass.initComponent.call(this);
	}
});



go.filter.types.subconditions = Ext.extend(Ext.Button, {

	text: t("Edit"),
	initComponent: function () {
		go.filter.types.subconditions.superclass.initComponent.call(this);
	},

	handler : function() {
		var e = this.findParentByType("filterfieldset").entity;
		var dlg = new go.filter.SubConditionsDialog({
			entity: e,
			typeCmp: this
		});

		dlg.items.itemAt(0).conditionsField.setValue(this.value);
		dlg.show();

	},


	isFormField: true,

	name: 'value',

	getName : function() {
		return this.name;
	},

	setValue: function(v) {
		this.value = v;

		var c = [];

		v.conditions.forEach(function(i) {
			for(var key in i) {
				c.push(key + ": " + i[key] + " ");
			}
		})

		this.setText(c.length ? v.operator + " " + c.join( " " + v.operator + " ") : t("Emtpy"));
	},
	getValue: function() {
		return this.value;
	},
	validate: function() {
		return true;
	},
	isValid : function(preventMark){
		return true;
	},

	markInvalid : function() {

	},
	clearInvalid : function() {

	},
	isDirty : function() {
		return true;
	}

});

