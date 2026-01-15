go.modules.business.wopi.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {
	itemId: "wopi", //will make it routable.	
  title: t("Office Online"),
  iconCls: "ic-wopi",
	initComponent: function () {

    this.items = [
			new go.modules.business.wopi.ServiceGridPanel()
		];
		
		go.modules.business.wopi.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
