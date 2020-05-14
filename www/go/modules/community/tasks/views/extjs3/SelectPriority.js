go.modules.community.tasks.SelectPriority = Ext.extend(go.form.ComboBox, {
    name : 'priority_text',
	hiddenName : 'priority',
	triggerAction : 'all',
	editable : false,
	selectOnFocus : true,
	forceSelection : true,
	fieldLabel : t("Priority"),
	mode : 'local',
	value : 1,
	valueField : 'value',
	displayField : 'text',
	store : new Ext.data.SimpleStore({
		fields : ['value', 'text'],
        data : [
			[1, t("Low")],
			[5, t("Normal")],
			[8, t("High")]]
	}),
    initComponent: function() {
        go.modules.community.tasks.SelectPriority.superclass.initComponent.call(this);
    }
});