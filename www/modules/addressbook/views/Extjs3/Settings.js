GO.mainLayout.onReady(function(){
	
	if(GO.addressbook && GO.settings.show_addresslist_tab === "1")
		GO.moduleManager.addSettingsPanel('addresslists', GO.addressbook.AddresslistsSettingsPanel,{},4);
	
	
	if(GO.customfields && GO.customfields.settingsPanels)
	{
		for(var i=0;i < GO.customfields.settingsPanels.panels.length;i++)
		{
			var id = '';
			id = GO.customfields.settingsPanels.panels[i].category_id;

			GO.moduleManager.addSettingsPanel('contact_cf_panel_'+i,GO.customfields.CustomFormPanel, GO.customfields.settingsPanels.panels[i],i+5);
		}
	}	
});