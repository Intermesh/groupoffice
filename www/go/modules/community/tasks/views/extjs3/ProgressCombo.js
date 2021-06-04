go.modules.community.tasks.ProgressCombo = Ext.extend(go.form.ComboBox,{
	hiddenName : 'progress',
	triggerAction : 'all',
	editable : false,
	selectOnFocus : true,
	forceSelection : true,
	fieldLabel : t("Progress"),
	mode : 'local',
	valueField : 'value',
	displayField : 'text',
	store : {
		xtype: "simplestore",
		fields : ['value', 'text'],
		data : [
			['completed', t("Completed")],
			['failed', t("Failed")],
			['in-progress', t("In Progress")],
			['needs-action', t("Needs action")],
			['cancelled', t("Cancelled")]]
	}
});