GO.users.ProfilePanel = function(config)
{
	if(!config)
	{
		config={};
	}

	config.autoScroll = true;
	config.border = false;
	config.hideLabel = true;
	if (!config.title) config.title = t("Profile", "users");
	config.layout = 'form';
	config.labelWidth=125;
	config.defaults={anchor:'98%'};
	config.bodyStyle = 'padding:5px';

	config.items = [{
		xtype:'fieldset',
		title:t("Personal profile", "users"),
		autoHeight:true,
		layout:'fit',
		items:[
			new GO.users.PersonalPanel({
				cb_id:'user_countryCombo'
			})
		]
	},{
		xtype:'fieldset',
		title:t("Company profile", "users"),
		autoHeight:true,
		layout:'fit',
		items:[
			new GO.users.CompanyPanel({
				cb_id:'user_work_countryCombo'
			})
		]
	}];

	GO.users.ProfilePanel.superclass.constructor.call(this, config);
}

Ext.extend(GO.users.ProfilePanel, Ext.Panel,{
});