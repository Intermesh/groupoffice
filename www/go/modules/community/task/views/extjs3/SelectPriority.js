go.modules.community.task.SelectPriority = Ext.extend(go.form.ComboBox, {
    name : 'priority_text',
	hiddenName : 'priority',
	triggerAction : 'all',
	editable : false,
	selectOnFocus : true,
	forceSelection : true,
	fieldLabel : t("Status"),
	mode : 'local',
	value : 1,
	valueField : 'value',
	displayField : 'text',
	store : new Ext.data.SimpleStore({
		fields : ['value', 'text'],
        data : [
			[0, t("Low")],
			[1, t("Normal")],
			[2, t("High")]]
	}),
    initComponent: function() {
        go.modules.community.task.SelectPriority.superclass.initComponent.call(this);
    }
});