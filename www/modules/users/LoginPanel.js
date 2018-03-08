/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LoginPanel.js 14816 2013-05-21 08:31:20Z mschering $
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
	config.title = GO.users.lang.loginInfo;
	config.layout='form';
	config.defaults={anchor:'100%'};
	config.defaultType = 'textfield';
	//config.cls='go-form-panel';
	config.labelWidth=140;
	
	config.items=[
		{
      xtype: 'plainfield',
			fieldLabel: GO.users.lang.cmdFormLabelRegistrationTime,
			name: 'ctime'
		},
		{
      xtype: 'plainfield',
			fieldLabel: GO.users.lang.cmdFormLabelLastLogin,
			name: 'lastlogin'
		},
		{
      xtype: 'plainfield',
			fieldLabel: GO.users.lang.numberOfLogins,
			name: 'logins'
		}		
	];

	GO.users.LoginPanel.superclass.constructor.call(this, config);		
}


Ext.extend(GO.users.LoginPanel, Ext.form.FieldSet,{
	

});			