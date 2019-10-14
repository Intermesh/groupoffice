GO.addressbook.SexCombobox = function(config){

	Ext.apply(this, config);

	GO.addressbook.SexCombobox.superclass.constructor.call(this,{
		store: new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : [
			['M', t("Male")],
			['F', t("Female")]
//			[1, t("Male")],
//			[2, t("Female")]
			]

		}),
		valueField:'value',
		displayField:'text',
		mode: 'local',
		triggerAction: 'all',
		editable: false,
		selectOnFocus:true,
		forceSelection: true,
		fieldLabel: t("Sex"),
		hiddenName:'sex'
	});

}
 
Ext.extend(GO.addressbook.SexCombobox, Ext.form.ComboBox);
