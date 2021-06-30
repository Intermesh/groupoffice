go.filter.FieldSet = Ext.extend(Ext.form.FieldSet, {
	title: t("Conditions"),
	entity: null,

	initComponent: function () {		

		this.items = [
			this.conditionsField = new go.form.FormContainer({
				xtype: "formcontainer",
				name: "filter",
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
			})
		];
		
		go.filter.FieldSet.superclass.initComponent.call(this);
	}
});

Ext.reg("filterfieldset", go.filter.FieldSet);
