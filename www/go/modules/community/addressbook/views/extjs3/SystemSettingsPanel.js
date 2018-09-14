go.modules.community.addressbook.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-contacts',
	autoScroll: true,
	initComponent: function () {
		this.title = t("Address book");		
		
		this.items = [new go.modules.core.customfields.SystemSettingsPanel({
				// The entity it's for
				entity: "Contact",

				//Optionally override this function to customize the fieldset dialog.
				createFieldSetDialog : function() {
					return new go.modules.community.addressbook.CustomFieldSetDialog();
				}
		})];
		
		
		go.modules.community.addressbook.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
