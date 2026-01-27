go.modules.community.wopi.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {
	itemId: "wopi", //will make it routable.	
  title: t("Office Online"),
  iconCls: "ic-wopi",
	initComponent: function () {

    this.items = [
			new go.modules.community.wopi.ServiceGridPanel()
		];
		
		go.modules.community.wopi.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
