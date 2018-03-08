GO.users.ProfilePanel = function(config)
{
	if(!config)
	{
		config={};
	}

	config.autoScroll = true;
	config.border = false;
	config.hideLabel = true;
	if (!config.title) config.title = GO.users.lang.profile;
	config.layout = 'form';
	config.labelWidth=125;
	config.defaults={anchor:'98%'};
	config.bodyStyle = 'padding:5px';

	config.items = [{
		xtype:'fieldset',
		title:GO.users.lang.personalProfile,
		autoHeight:true,
		layout:'fit',
		items:[
			new GO.users.PersonalPanel({
				cb_id:'user_countryCombo'
			})
		]
	},{
		xtype:'fieldset',
		title:GO.users.lang.companyProfile,
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