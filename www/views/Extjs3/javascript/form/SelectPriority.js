GO.form.SelectPriority = function(config){
		
	Ext.apply(this, config);

	GO.form.SelectPriority.superclass.constructor.call(this,{
		hiddenName:'priority',
		fieldLabel:GO.lang.priority,
		store: new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : [
			[0, GO.lang.priority_low],
			[1, GO.lang.priority_normal],
			[2, GO.lang.priority_high]
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