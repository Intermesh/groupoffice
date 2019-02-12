GO.files.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-folder',
	autoScroll: true,
	initComponent: function () {
		this.title = t("Files");		
		
		this.items = [
			new go.customfields.SystemSettingsPanel({
					entity: "File",
					title: t("Custom file fields")
//					createFieldSetDialog : function() {
//						return new GO.projects2.CustomFieldSetDialog();
//					}
			}),
			
			new go.customfields.SystemSettingsPanel({
					entity: "Folder",
					title: t("Custom folder fields")				
			})
		];
		
		
		GO.files.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});

