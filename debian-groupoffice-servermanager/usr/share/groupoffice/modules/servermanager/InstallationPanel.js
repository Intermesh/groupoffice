/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: InstallationPanel.js 22112 2018-01-12 07:59:41Z mschering $
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
			text: t("Edit"), 
			cls: 'x-btn-text-icon', 
			handler: function(){
				GO.servermanager.installationDialog.show(this.data.id);
			}, 
			scope: this,
			disabled : true
		}),	{
			iconCls: 'btn-link', 
			cls: 'x-btn-text-icon', 
			text: t("Links"),
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
							'<td>'+t("Name")+':</td><td>{name}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="webmaster_email.length">'+
						'<tr>'+
							'<td>'+t("webmasterEmail", "servermanager")+':</td><td>{webmaster_email}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="title.length">'+
						'<tr>'+
							'<td>'+t("title", "servermanager")+':</td><td>{title}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_country.length">'+
						'<tr>'+
							'<td>'+t("defaultCountry", "servermanager")+':</td><td>{default_country}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="language.length">'+
						'<tr>'+
							'<td>'+t("language", "servermanager")+':</td><td>{language}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_timezone.length">'+
						'<tr>'+
							'<td>'+t("defaultTimezone", "servermanager")+':</td><td>{default_timezone}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_currency.length">'+
						'<tr>'+
							'<td>'+t("defaultCurrency", "servermanager")+':</td><td>{default_currency}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_date_format.length">'+
						'<tr>'+
							'<td>'+t("defaultDateFormat", "servermanager")+':</td><td>{default_date_format}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_date_separator.length">'+
						'<tr>'+
							'<td>'+t("defaultDateSeperator", "servermanager")+':</td><td>{default_date_separator}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="default_thousands_separator.length">'+
						'<tr>'+
							'<td>'+t("defaultThousandsSeperator", "servermanager")+':</td><td>{default_thousands_separator}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="theme.length">'+
						'<tr>'+
							'<td>'+t("theme", "servermanager")+':</td><td>{theme}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="allow_themes.length">'+
						'<tr>'+
							'<td>'+t("allowThemes", "servermanager")+':</td><td>{allow_themes}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="allow_password_change.length">'+
						'<tr>'+
							'<td>'+t("allowPasswordChange", "servermanager")+':</td><td>{allow_password_change}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="allow_registration.length">'+
						'<tr>'+
							'<td>'+t("allowRegistration", "servermanager")+':</td><td>{allow_registration}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="allow_duplicate_email.length">'+
						'<tr>'+
							'<td>'+t("allowDuplicateEmail", "servermanager")+':</td><td>{allow_duplicate_email}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="auto_activate_accounts.length">'+
						'<tr>'+
							'<td>'+t("autoActivateAccounts", "servermanager")+':</td><td>{auto_activate_accounts}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="notify_admin_of_registration.length">'+
						'<tr>'+
							'<td>'+t("notifyAdminOfRegistration", "servermanager")+':</td><td>{notify_admin_of_registration}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="registration_fields.length">'+
						'<tr>'+
							'<td>'+t("registrationFields", "servermanager")+':</td><td>{registration_fields}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="required_registration_fields.length">'+
						'<tr>'+
							'<td>'+t("requiredRegistrationFields", "servermanager")+':</td><td>{required_registration_fields}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="register_modules_read.length">'+
						'<tr>'+
							'<td>'+t("registerModulesRead", "servermanager")+':</td><td>{register_modules_read}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="register_modules_write.length">'+
						'<tr>'+
							'<td>'+t("registerModulesWrite", "servermanager")+':</td><td>{register_modules_write}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="register_user_groups.length">'+
						'<tr>'+
							'<td>'+t("registerUserGroups", "servermanager")+':</td><td>{register_user_groups}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="register_visible_user_groups.length">'+
						'<tr>'+
							'<td>'+t("registerVisibleUserGroups", "servermanager")+':</td><td>{register_visible_user_groups}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="max_users.length">'+
						'<tr>'+
							'<td>'+t("maxUsers", "servermanager")+':</td><td>{max_users}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="ctime.length">'+
						'<tr>'+
							'<td>'+t("Created at")+':</td><td>{ctime}</td>'+
						'</tr>'+
					'</tpl>'+


					'<tpl if="mtime.length">'+
						'<tr>'+
							'<td>'+t("Modified at")+':</td><td>{mtime}</td>'+
						'</tr>'+
					'</tpl>'+

									
				'</table>';																		
				
				template += GO.linksTemplate;
												
				if(go.ModuleManager.isAvailable("customfields"))
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
		this.body.mask(t("Loading..."));
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
					Ext.MessageBox.alert(t("Error"), t("Could not connect to the server. Please check your internet connection."));
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