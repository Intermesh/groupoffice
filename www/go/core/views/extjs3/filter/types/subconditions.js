go.filter.SubConditionsDialog = Ext.extend(go.Window, {
	title: t("Conditions"),
	entity: null,
	height: dp(480),
	width: dp(1000),

	initComponent: function () {

		this.items = [{
			xtype: "fieldset",
			items: [
			this.conditionsField = new go.form.FormContainer({
					xtype: "formcontainer",
					//name: "filter",
					hideLabel: true,
					items: [
						{
							xtype: "radiogroup",
							name: 'operator',
							value: "AND",
							items: [{
								xtype: "radio",
								inputValue: "AND",
								boxLabel: t("Match ALL of the conditions")
							}, {
								xtype: "radio",
								inputValue: "OR",
								boxLabel: t("Match ANY of the conditions")
							}, {
								xtype: "radio",
								inputValue: "NOT",
								boxLabel: t("Match NONE of the conditions")
							}]
						}, {
							xtype: "filterconditions",
							name: "conditions",
							entity: this.entity
						}
					]
			})]
		}
		];

		this.buttons = [{
			text: t("Ok"),
			handler: function() {
				console.warn(this.conditionsField.getValue());
				this.typeCmp.setValue(this.conditionsField.getValue());
				this.close();
			},
			scope: this
		}]

		this.value = [];

		go.filter.SubConditionsDialog.superclass.initComponent.call(this);
	}
});

Ext.reg("filterfieldset", go.filter.FieldSet);


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

		dlg.conditionsField.setValue(this.value);
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

		this.setText(c.length ? c.join( " " + v.operator + " ") : t("Emtpy"));
	},
	getValue: function() {
		return this.value;
	},
	validate: function() {
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

