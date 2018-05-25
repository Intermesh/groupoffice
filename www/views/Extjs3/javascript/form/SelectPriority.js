GO.form.SelectPriority = function(config){
		
	Ext.apply(this, config);

	GO.form.SelectPriority.superclass.constructor.call(this,{
		hiddenName:'priority',
		fieldLabel:t("Priority"),
		store: new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : [
			[0, t("Low")],
			[1, t("Normal")],
			[2, t("High")]
			]
		}),
		value:1,
		valueField:'value',
		displayField:'text',
		mode:'local',
		triggerAction:'all',
		editable:false,
		selectOnFocus:true,
		forceSelection:true
	});
}
 
Ext.extend(GO.form.SelectPriority, GO.form.ComboBox);
