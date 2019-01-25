GO.calendar.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-event',
	autoScroll: true,
	initComponent: function () {
		this.title = t("Calendar");		
		
		this.items = [
			new go.modules.core.customfields.SystemSettingsPanel({
					entity: "Event",
					title: "Event custom fields"
	//				createFieldSetDialog : function() {
	//					return new go.modules.community.addressbook.CustomFieldSetDialog();
	//				}
			}),
			new go.modules.core.customfields.SystemSettingsPanel({
					entity: "Calendar",
					title: "Calendar custom fields"
	//				createFieldSetDialog : function() {
	//					return new go.modules.community.addressbook.CustomFieldSetDialog();
	//				}
			})
		];
		
		
		GO.calendar.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
