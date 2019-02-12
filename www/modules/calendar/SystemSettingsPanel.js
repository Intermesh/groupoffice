GO.calendar.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-event',
	autoScroll: true,
	initComponent: function () {
		this.title = t("Calendar");		
		
		this.items = [
			new go.customfields.SystemSettingsPanel({
					entity: "Event",
					title: "Event custom fields",
					createFieldSetDialog : function() {
						return new GO.calendar.CustomFieldSetDialog();
					}
			}),
			new go.customfields.SystemSettingsPanel({
					entity: "Calendar",
					title: "Resource booking custom fields",
					createFieldSetDialog : function() {
						return new GO.calendar.CustomFieldSetDialog();
					}
			})
		];
		
		
		GO.calendar.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
