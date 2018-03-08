GO.addressbook.SexCombobox = function(config){

	Ext.apply(this, config);

	GO.addressbook.SexCombobox.superclass.constructor.call(this,{
		store: new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : [
			['M', GO.lang['strMale']],
			['F', GO.lang['strFemale']]
//			[1, GO.lang['strMale']],
//			[2, GO.lang['strFemale']]
			]

		}),
		valueField:'value',
		displayField:'text',
		mode: 'local',
		triggerAction: 'all',
		editable: false,
		selectOnFocus:true,
		forceSelection: true,
		fieldLabel: GO.lang.strSex,
		hiddenName:'sex'
	});

}
 
Ext.extend(GO.addressbook.SexCombobox, Ext.form.ComboBox);