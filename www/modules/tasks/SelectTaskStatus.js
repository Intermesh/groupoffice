GO.tasks.SelectTaskStatus = Ext.extend(GO.form.ComboBox,{
	name : 'status_text',
	hiddenName : 'status',
	triggerAction : 'all',
	editable : false,
	selectOnFocus : true,
	forceSelection : true,
	fieldLabel : t("Status"),
	mode : 'local',
	value : 'ACCEPTED',
	valueField : 'value',
	displayField : 'text',
	store : new Ext.data.SimpleStore({
		fields : ['value', 'text'],
		data : [
		['NEEDS-ACTION',
		t("Needs action", "tasks")],
		['ACCEPTED', t("Accepted", "tasks")],
		['DECLINED', t("Declined", "tasks")],
		['TENTATIVE', t("Tentative", "tasks")],
		['DELEGATED', t("Delegated", "tasks")],
		['COMPLETED', t("Completed", "tasks")],
		['IN-PROCESS', t("In process", "tasks")]]
	})
});
