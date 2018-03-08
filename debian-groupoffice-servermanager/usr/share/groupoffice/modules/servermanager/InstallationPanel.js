/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: InstallationPanel.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.servermanager.InstallationPanel = function(config)
{
	Ext.apply(this, config);
	
	this.split=true;
	this.autoScroll=true;
	
	
	
	this.newMenuButton = new GO.NewMenuButton();
		
	
	this.tbar = [
		this.editButton = new Ext.Button({
			iconCls: 'btn-edit', 
			text: GO.lang['cmdEdit'], 
			cls: 'x-btn-text-icon', 
			handler: function(){
				GO.servermanager.installationDialog.show(this.data.id);
			}, 
			scope: this,
			disabled : true
		}),	{
			iconCls: 'btn-link', 
			cls: 'x-btn-text-icon', 
			text: GO.lang.cmdBrowseLinks,
			handler: function(){
				GO.linkBrowser.show({link_id: this.data.id,link_type: "13",folder_id: "0"});				
			},
			scope: this
		},	
		this.newMenuButton
	];	
	
	
	GO.servermanager.InstallationPanel.superclass.constructor.call(this);		
}

Ext.extend(GO.servermanager.InstallationPanel, Ext.Panel,{
	
	initComponent : function(){
	
		var template = 
			'<div>'+
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
						'<td colspan="2" class="display-panel-heading">Information</td>'+
					'</tr>'+
				
					'<tpl if="name.length">'+
						'<tr>'+
							'<td>'+GO.lang.strName+':</td><td>{name}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="webmaster_email.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.webmasterEmail+':</td><td>{webmaster_email}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="title.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.title+':</td><td>{title}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_country.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.defaultCountry+':</td><td>{default_country}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="language.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.language+':</td><td>{language}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_timezone.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.defaultTimezone+':</td><td>{default_timezone}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_currency.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.defaultCurrency+':</td><td>{default_currency}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_date_format.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.defaultDateFormat+':</td><td>{default_date_format}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_date_separator.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.defaultDateSeperator+':</td><td>{default_date_separator}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_thousands_separator.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.defaultThousandsSeperator+':</td><td>{default_thousands_separator}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="theme.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.theme+':</td><td>{theme}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="allow_themes.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.allowThemes+':</td><td>{allow_themes}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="allow_password_change.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.allowPasswordChange+':</td><td>{allow_password_change}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="allow_registration.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.allowRegistration+':</td><td>{allow_registration}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="allow_duplicate_email.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.allowDuplicateEmail+':</td><td>{allow_duplicate_email}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="auto_activate_accounts.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.autoActivateAccounts+':</td><td>{auto_activate_accounts}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="notify_admin_of_registration.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.notifyAdminOfRegistration+':</td><td>{notify_admin_of_registration}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="registration_fields.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.registrationFields+':</td><td>{registration_fields}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="required_registration_fields.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.requiredRegistrationFields+':</td><td>{required_registration_fields}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="register_modules_read.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.registerModulesRead+':</td><td>{register_modules_read}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="register_modules_write.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.registerModulesWrite+':</td><td>{register_modules_write}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="register_user_groups.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.registerUserGroups+':</td><td>{register_user_groups}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="register_visible_user_groups.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.registerVisibleUserGroups+':</td><td>{register_visible_user_groups}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="max_users.length">'+
						'<tr>'+
							'<td>'+GO.servermanager.lang.maxUsers+':</td><td>{max_users}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="ctime.length">'+
						'<tr>'+
							'<td>'+GO.lang.strCtime+':</td><td>{ctime}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="mtime.length">'+
						'<tr>'+
							'<td>'+GO.lang.strMtime+':</td><td>{mtime}</td>'+
						'</tr>'+
					'</tpl>'+

									
				'</table>';																		
				
				template += GO.linksTemplate;
												
				if(GO.customfields)
				{
					template +=GO.customfields.displayPanelTemplate;
				}
	    	
	  var config = {};
		
		
				
		template+='</div>';
		
		this.template = new Ext.XTemplate(template, config);
		
		GO.servermanager.InstallationPanel.superclass.initComponent.call(this);
	},
	
	loadInstallation : function(installation_id)
	{
		this.body.mask(GO.lang.waitMsgLoad);
		Ext.Ajax.request({
			url: GO.settings.modules.servermanager.url+'json.php',
			params: {
				task: 'installation_with_items',
				installation_id: installation_id
			},
			callback: function(options, success, response)
			{
				this.body.unmask();
				if(!success)
				{
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strRequestError']);
				}else
				{
					var responseParams = Ext.decode(response.responseText);
					this.setData(responseParams.data);
				}				
			},
			scope: this			
		});
		
	},
	
	setData : function(data)
	{
		this.data=data;
		this.editButton.setDisabled(!data.write_permission);
		
		if(data.write_permission)
			this.newMenuButton.setLinkConfig({
				id:this.data.id,
				type:13,
				text: this.data.name,
				callback:function(){
					this.loadInstallation(this.data.id);				
				},
				scope:this
			});
		
		this.template.overwrite(this.body, data);	
	}
	
});			