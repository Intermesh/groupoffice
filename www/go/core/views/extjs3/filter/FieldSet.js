go.filter.FieldSet = Ext.extend(Ext.form.FieldSet, {
	title: t("Conditions"),
	fields: null,

	initComponent: function () {		

		this.items = [
			{
				xtype: "formcontainer",
				name: "filter",
				hideLabel: true,
				items: [
					{
						xtype: "radiogroup",
						name: 'operator',
						items: [{
								xtype: "radio",
								inputValue: "AND",
								boxLabel: t("Show results that match ALL of the conditions")
							}, {
								xtype: "radio",
								inputValue: "OR",
								boxLabel: t("Show results that match ANY of the conditions")
							}]
					}, {
						xtype: "filterconditions",
						name: "conditions",
						fields: this.fields
					}
				]
			}
		];
		
		go.filter.FieldSet.superclass.initComponent.call(this);
	}
});

Ext.reg("filterfieldset", go.filter.FieldSet);
