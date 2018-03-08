/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LoginPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.users.LoginPanel = function(config)
{
	if(!config)
	{
		config={};
	}
	
	config.autoHeight=true;
	config.border=true;
	config.hideLabel=true;
	config.title = t("Login information", "users");
	config.layout='form';
	config.defaults={anchor:'100%'};
	config.defaultType = 'textfield';
	//config.cls='go-form-panel';
	config.labelWidth=140;
	
	config.items=[
		{
      xtype: 'plainfield',
			fieldLabel: t("Registration time", "users"),
			name: 'ctime'
		},
		{
      xtype: 'plainfield',
			fieldLabel: t("Last Login", "users"),
			name: 'lastlogin'
		},
		{
      xtype: 'plainfield',
			fieldLabel: t("Number of logins", "users"),
			name: 'logins'
		}		
	];

	GO.users.LoginPanel.superclass.constructor.call(this, config);		
}


Ext.extend(GO.users.LoginPanel, Ext.form.FieldSet,{
	

});			