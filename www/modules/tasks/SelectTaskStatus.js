GO.tasks.SelectTaskStatus = Ext.extend(GO.form.ComboBox,{
	name : 'status_text',
	hiddenName : 'status',
	triggerAction : 'all',
	editable : false,
	selectOnFocus : true,
	forceSelection : true,
	fieldLabel : GO.lang.strStatus,
	mode : 'local',
	value : 'ACCEPTED',
	valueField : 'value',
	displayField : 'text',
	store : new Ext.data.SimpleStore({
		fields : ['value', 'text'],
		data : [
		['NEEDS-ACTION',
		GO.tasks.lang.needsAction],
		['ACCEPTED', GO.tasks.lang.accepted],
		['DECLINED', GO.tasks.lang.declined],
		['TENTATIVE', GO.tasks.lang.tentative],
		['DELEGATED', GO.tasks.lang.delegated],
		['COMPLETED', GO.tasks.lang.completed],
		['IN-PROCESS', GO.tasks.lang.inProcess]]
	})
});