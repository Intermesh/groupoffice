/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ReadPanelContact.js 20201 2016-07-07 09:25:28Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.ContactReadPanel = Ext.extend(GO.DisplayPanel,{
	
	model_name : "GO\\Addressbook\\Model\\Contact",

	stateId : 'ab-contact-panel',

	editGoDialogId : 'contact',
	
	editHandler : function(){
		GO.addressbook.showContactDialog(this.model_id);		
	},	
	
	initComponent : function(){	
		
		this.loadUrl=('addressbook/contact/display');
		
		this.template = 
				'{[this.collapsibleSectionHeader(GO.addressbook.lang.contact+": "+ values.name, "contactpane-"+values.panelId, "name")]}'+
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="contactpane-{panelId}">'+
//				'<tr>'+
//						'<td colspan="2" class="display-panel-heading">'+GO.addressbook.lang.contact+': {name}</td>'+
//				'</tr>'+
					
					'<tr>'+
						
						// PERSONAL DETAILS+ 1e KOLOM
						'<td valign="top">'+
							'<table cellpadding="0" cellspacing="0" border="0">'+

								'<tr>'+
									'<td>ID:</td><td>{id}</td>'+
								'</tr>'+
								'<tr>'+
									'<td>'+GO.addressbook.lang.addressbook+':</td><td>{addressbook_name}</td>'+
								'</tr>'+
								//NAME
								'<tr>'+
									'<td>' +
										'<tpl if="!GO.util.empty(title)">'+
											'{title} '+
										'</tpl>'+
										'{name}'+
										'<tpl if="!GO.util.empty(suffix)">'+
											', {suffix} '+
										'</tpl>'+
										'<br />'+
										'<div class="readPanelSubHeading">'+GO.addressbook.lang.privateAddress+':</div>'+
										'<tpl if="!GO.util.empty(google_maps_link)">'+
											'<a href="{google_maps_link}" target="_blank">'+
										'</tpl>'+
										'{formatted_address}'+
										'<tpl if="!GO.util.empty(google_maps_link)">'+
											'</a>'+
										'</tpl>'+
									'</td>'+
								'</tr>'+
							'</table>'+
						'</td>'+
						'<tpl if="photo_url">'+
							'<td rowspan="2" align="right">' +
							
								'<tpl if="write_permission">'+
									'<img src="{photo_url}" class="ab-photo" style="cursor:pointer;" onClick="GO.addressbook.showContactDialog({id}, \\{activeTab:1\\} );"/>' +
								'</tpl>'+
								
								'<tpl if="!write_permission">'+
									'<a href="{original_photo_url}" target="_blank">'+
									'<img src="{photo_url}" class="ab-photo" />' +
									'</a>'+
								'</tpl>'+
								
							'</td>' +
						'</tpl>'+
					'</tr>' +

					'<tr>' +
						// COMPANY DETAILS
						'<td valign="top">'+
							'<table cellpadding="0" cellspacing="0" border="0">'+
								
								//INITIALS
								'<tpl if="!GO.util.empty(initials)">'+
									'<tr>'+
										'<td>' + GO.lang['strInitials'] + ':</td><td> {initials}</td>'+
									'</tr>'+						
								'</tpl>'+
	
								//BIRTHDAY							
								'<tpl if="!GO.util.empty(birthday)">'+
									'<tr>'+
										'<td>' + GO.lang['strBirthday'] + ':</td><td> {birthday}</td>'+
									'</tr>'+						
								'</tpl>'+
							'</table>'+
						'</td>'+
					'</tr>'+

					
				'<tpl if="this.isContactFieldset(values)">'+
					
						//CONTACT DETAILS
				'</table>'+
				
				'{[this.collapsibleSectionHeader(GO.addressbook.lang.cmdFieldsetContact, "contactpane2-"+values.panelId, "name")]}'+
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="contactpane2-{panelId}">'+
						
						'<tr>'+
							// CONTACT DETAILS+ 1e KOLOM
							'<td valign="top">'+
								'<table cellpadding="0" cellspacing="0" border="0">'+
									
									//EMAIL							
									'<tpl if="!GO.util.empty(email)">'+
										'<tr>'+
											'<td class="contactCompanyLabelWidth">' + GO.lang['strEmail'] + ':</td><td>{[this.mailTo(values.email, values.name)]}</td>'+
										'</tr>'+						
									'</tpl>'+
		
									//EMAIL2							
									'<tpl if="!GO.util.empty(email2)">'+
										'<tr>'+
											'<td class="contactCompanyLabelWidth">' + GO.lang['strEmail'] + ' 2:</td><td>{[this.mailTo(values.email2, values.name)]}</td>'+
										'</tr>'+						
									'</tpl>'+
		
									//EMAIL3							
									'<tpl if="!GO.util.empty(email3)">'+
										'<tr>'+
											'<td class="contactCompanyLabelWidth">' + GO.lang['strEmail'] + ' 3:</td><td>{[this.mailTo(values.email3, values.name)]}</td>'+
										'</tr>'+						
									'</tpl>'+
									
									'<tpl if="this.isPhoneFieldset(values)">'+
										'<tr><td colspan="2">&nbsp;</td></tr>'+
										
										//PHONE							
										'<tpl if="!GO.util.empty(home_phone)">'+
											'<tr>'+
												'<td class="contactCompanyLabelWidth">' + GO.addressbook.lang['contactHome_phone'] + ':</td><td>{[GO.util.callToLink(values.home_phone)]}</td>'+
											'</tr>'+						
										'</tpl>'+

										//CELLULAR							
										'<tpl if="!GO.util.empty(cellular)">'+
											'<tr>'+
												'<td class="contactCompanyLabelWidth">' + GO.lang['strCellular'] + ':</td><td>{[GO.util.callToLink(values.cellular)]}</td>'+
											'</tr>'+						
										'</tpl>'+
										
										//CELLULAR2							
										'<tpl if="!GO.util.empty(cellular2)">'+
											'<tr>'+
												'<td class="contactCompanyLabelWidth">' + GO.lang['cellular2'] + ':</td><td>{[GO.util.callToLink(values.cellular2)]}</td>'+
											'</tr>'+						
										'</tpl>'+
										
										//FAX							
										'<tpl if="!GO.util.empty(fax)">'+
											'<tr>'+
												'<td class="contactCompanyLabelWidth">' + GO.addressbook.lang['contactFax'] + ':</td><td>{fax}</td>'+
											'</tr>'+						
										'</tpl>'+
										
									'</tpl>'+ //end this.isPhoneFieldset()
								'</table>'+
							'</td>'+
							
								// SOCIAL MEDIA URLs
							'<tpl if="this.isSocialMediaFieldset(values)">'+
									'<tr>'+
										'<td>'+
												'<tpl if="!GO.util.empty(url_linkedin)">'+
													'<a href="{url_linkedin}" target="_blank"><div class="linkedin-icon"></div></a>'+
												'</tpl>'+
												'<tpl if="!GO.util.empty(url_facebook)">'+
													'<a href="{url_facebook}" target="_blank"><div class="facebook-icon"></div></a>'+
												'</tpl>'+
												'<tpl if="!GO.util.empty(url_twitter)">'+
													'<a href="{url_twitter}" target="_blank"><div class="twitter-icon"></div></a>'+
												'</tpl>'+
												'<tpl if="!GO.util.empty(skype_name)">'+
													'<a href="skype:{skype_name}?call"><div class="skype-icon" title="'+GO.addressbook.lang['callOnSkype']+'"></div></a>'+
												'</tpl>'+
										'</td>'+
									'</tr>'+
							'</tpl>'+
							
							
							'<tpl if="this.isWorkPhoneFieldset(values)">'+
							
								// CONTACT DETAILS+ 2e KOLOM
								'<td valign="top">'+
									'<table cellpadding="0" cellspacing="0" border="0">'+
										
										//PHONE WORK							
										'<tpl if="!GO.util.empty(work_phone)">'+
											'<tr>'+
												'<td class="contactCompanyLabelWidth">' + GO.lang['strWorkPhone'] + ':</td><td>{[GO.util.callToLink(values.work_phone)]}</td>'+
											'</tr>'+						
										'</tpl>'+
			
										//FAX WORK							
										'<tpl if="!GO.util.empty(work_fax)">'+
											'<tr>'+
												'<td class="contactCompanyLabelWidth">' + GO.lang['strWorkFax'] + ':</td><td>{work_fax}</td>'+
											'</tr>'+						
										'</tpl>'+
										
										'<tpl if="!GO.util.empty(homepage)">'+
											'<tr>'+
												'<td class="contactCompanyLabelWidth">' + GO.addressbook.lang['companyHomepage'] + ':</td><td><a href="{homepage}" target="_blank">{homepage}</a></td>'+
											'</tr>'+						
										'</tpl>'+
										
									'</table>'+							
								'</td>'+
							
							'</tpl>'+ //end this.isPhoneFieldset()
							
						'</tr>'+
					'</table>'+	
				'</tpl>'+
									
									

				// COMPANY DETAILS
		
				'<tpl if="this.isCompanyFieldset(values)">'+
					'{[this.collapsibleSectionHeader(GO.addressbook.lang.cmdFieldsetCompany, "companypane-"+values.panelId, "name")]}'+
				
					'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="companypane-{panelId}">'+	
//						'<tr>'+
//							'<td colspan="2" class="display-panel-heading">' + GO.addressbook.lang['cmdFieldsetCompany'] + '</td>'+
//						'</tr>'+
						
						'<tr>'+
							
							'<td valign="top" colspan="2">'+
								'<table cellpadding="0" cellspacing="0" border="0" width="100%">'+
									
									//COMPANY NAME
									'<tpl if="!GO.util.empty(company_name)">'+
										'<tr>'+
											'<td class="contactCompanyLabelWidth" colspan="2"><a href="#" onclick="GO.linkHandlers[\'GO\\\\\\\\Addressbook\\\\\\\\Model\\\\\\\\Company\'].call(this,{company_id});">{company_name}</a></td>'+
										'</tr>'+						
									'</tpl>'+
									'<tpl if="!GO.util.empty(company_name2)">'+
										'<tr>'+
											'<td colspan="2">{company_name2}</td>'+
										'</tr>'+						
									'</tpl>'+
									
									//COMPANY ADDRESS						
									'<tpl if="!GO.util.empty(company_formatted_address) || !GO.util.empty(company_formatted_post_address)">'+
										'<tr>'+
											'<tpl if="!GO.util.empty(company_formatted_address)">'+
												'<td style="width: 50%;vertical-align:top;padding:10px 0;">'+
												
													'<div class="readPanelSubHeading">'+GO.addressbook.lang['cmdFieldsetVisitAddress'] + '</div>'+
													'<tpl if="!GO.util.empty(company_google_maps_link)">'+
														'<a href="{company_google_maps_link}" target="_blank">'+
													'</tpl>'+
													'{company_formatted_address}'+
													'<tpl if="!GO.util.empty(company_google_maps_link)">'+
														'</a>'+
													'</tpl>'+												
												'</td>'+
											'</tpl>'+
											
											'<tpl if="!GO.util.empty(company_formatted_post_address)">'+
												'<td style="width: 50%;vertical-align:top;padding:10px 0;">'+												
													'<div class="readPanelSubHeading">'+GO.addressbook.lang['cmdFieldsetPostAddress'] + '</div>'+
													'<tpl if="!GO.util.empty(company_google_maps_post_link)">'+
														'<a href="{company_google_maps_post_link}" target="_blank">'+
													'</tpl>'+
													'{company_formatted_post_address}'+
													'<tpl if="!GO.util.empty(company_google_maps_post_link)">'+
														'</a>'+
													'</tpl>'+
												'</td>'+
											'</tpl>'+
											
										'</tr>'+						
									'</tpl>'+
									
									//COMPANY PHONE
									'<tpl if="!GO.util.empty(company_phone)">'+
										'<tr>'+
											'<td class="contactCompanyLabelWidth">' + GO.lang['strPhone'] + ':</td><td>{[GO.util.callToLink(values.company_phone)]}</td>'+
										'</tr>'+						
									'</tpl>'+
									
									//COMPANY EMAIL			
									'<tpl if="!GO.util.empty(company_email)">'+
										'<tr>'+
											'<td class="contactCompanyLabelWidth">' + GO.lang['strEmail'] + ':</td><td>{[this.mailTo(values.company_email, values.company_name)]}</td>'+
										'</tr>'+						
									'</tpl>'+

								'</table>'+
							'</td>'+
														
						'</tr>'+

				'</tpl>'+
									
									
			
					
					
					
					
				'<tpl if="this.isWorkFieldset(values)">'+


						//WORK DETAILS
						'<tr>'+
							'<td colspan="2" class="display-panel-heading">' + GO.addressbook.lang['cmdFieldsetWork'] + '</td>'+
						'</tr>'+
						
						'<tr>'+
							// CONTACT DETAILS+ 1e KOLOM
							'<td colspan="2" valign="top" class="contactCompanyDetailsPanelKolom60">'+
								'<table cellpadding="0" cellspacing="0" border="0">'+
								
									//FUNCTION							
									'<tpl if="!GO.util.empty(values[\'function\'])">'+
										'<tr>'+
											'<td class="contactCompanyLabelWidth">' + GO.lang['strFunction'] + ':</td><td>{function}</td>'+
										'</tr>'+						
									'</tpl>'+

									//DEPARTMENT							
									'<tpl if="!GO.util.empty(department)">'+
										'<tr>'+
											'<td class="contactCompanyLabelWidth">' + GO.lang['strDepartment'] + ':</td><td>{department}</td>'+
										'</tr>'+						
									'</tpl>'+																	

								'</table>'+							
							'</td>'+							
						'</tr>'+
					'</table>'+
				'</tpl>'+
				'</table>'+
				
				'<tpl if="!GO.util.empty(values[\'comment\'])">'+
					'{[this.collapsibleSectionHeader(GO.addressbook.lang.cmdFormLabelComment, "commentpane-"+values.panelId, "comment")]}'+
					'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="commentpane-{panelId}">'+
						'<tr>'+
							'<td colspan="2">{comment}</td>'+
						'</tr>'+
					'</table>'+
				'</tpl>';
				
				if(GO.lists)
					this.template += GO.lists.ListTemplate;
				
				if(GO.customfields)
				{
					this.template +=GO.customfields.displayPanelTemplate;
					this.template +=GO.customfields.displayPanelBlocksTemplate;
				}


			if(GO.tasks)
				this.template +=GO.tasks.TaskTemplate;
			
			if(GO.workflow){
				this.template +=GO.workflow.WorkflowTemplate;
			}

			if(GO.calendar)
				this.template += GO.calendar.EventTemplate;

			this.template +=GO.linksTemplate;
				
			
		Ext.apply(this.templateConfig, {
			replaceWithUnderscore: function(str){
				if(!GO.util.empty(str)){
					str = str.replace(/\\/g,"_");
				}
				return str;
			},
			addSlashes : function(str)
			{
				str = GO.util.html_entity_decode(str, 'ENT_QUOTES');
				str = GO.util.add_slashes(str);
				return str;
			},
			mailTo : function(email, name) {
			
				if(GO.email && GO.settings.modules.email.read_permission)
				{
					return '<a href="#" onclick="GO.email.showAddressMenu(event, \''+this.addSlashes(email)+'\',\''+this.addSlashes(name)+'\');">'+email+'</a>';
				}else
				{
					return '<a href="mailto:'+email+'">'+email+'</a>';
				}
			},

			
			isContactFieldset: function(values){
				if(!GO.util.empty(values['email']) ||
					!GO.util.empty(values['email2']) ||
					!GO.util.empty(values['email3']) ||
					!GO.util.empty(values['home_phone']) ||
					!GO.util.empty(values['fax']) ||
					!GO.util.empty(values['cellular']) ||
					!GO.util.empty(values['cellular2']) ||
					!GO.util.empty(values['work_phone']) ||
					!GO.util.empty(values['work_fax']) ||
					!GO.util.empty(values['homepage'])	)
				{
					return true;
				} else {
					return false;
				}
			},	
							
		isCompanyFieldset: function(values){
			if(!GO.util.empty(values['company_name']) ||
				!GO.util.empty(values['company_formatted_address']) ||
				!GO.util.empty(values['company_email']) ||
				!GO.util.empty(values['company_phone'])			)
			{
				return true;
			} else {
				return false;
			}
		},			
							
		isPhoneFieldset : function(values)
			{
				if(!GO.util.empty(values['home_phone']) ||
					!GO.util.empty(values['fax']) ||
					!GO.util.empty(values['cellular']) ||
					!GO.util.empty(values['cellular2']) )
				{
					return true;
				} else {
					return false;
				}
			},
			isWorkPhoneFieldset : function(values)
			{
				if(!GO.util.empty(values['work_phone']) ||
					!GO.util.empty(values['work_fax'])  ||
					!GO.util.empty(values['homepage']) )
				{
					return true;
				} else {
					return false;
				}
			},
			isWorkFieldset : function(values)
			{
				if(
					!GO.util.empty(values['function']) ||
					!GO.util.empty(values['department']))
				{
					return true;
				} else {
					return false;
				}
			},
			isSocialMediaFieldset : function(values)
			{
				if(!GO.util.empty(values['url_linkedin']) ||
					!GO.util.empty(values['url_facebook']) ||
					!GO.util.empty(values['url_twitter']) ||
					!GO.util.empty(values['skype_name']))
				{
					return true;
				} else {
					return false;
				}
			},
			GoogleMapsCityStreet : function(values)
			{
				var google_url = 'http://maps.google.com/maps?q=';
				
				if(!GO.util.empty(values['address']) && !GO.util.empty(values['city']))
				{
					if(!GO.util.empty(values['address_no']))
					{
						return '<a href="' + google_url + values['address'] + '+' + values['address_no'] + '+' + values['city'] + '" target="_blank" >' + values['address'] + ' ' + values['address_no'] + '</a>';	
					} else {
						return '<a href="' + google_url + values['address'] + '+' + values['city'] + '" target="_blank" >' + values['address'] + '</a>';						
					}
				} else {
					return values['address'] + ' ' + values['address_no'];
				}
			}
		});		
		
		Ext.apply(this.templateConfig, GO.linksTemplateConfig);
		
		
		if(GO.files)
		{
			Ext.apply(this.templateConfig, GO.files.filesTemplateConfig);
			this.template += GO.files.filesTemplate;
		}
		
		if(GO.comments)
		{
			this.template += GO.comments.displayPanelTemplate;
		}
		
		this.template += GO.createModifyTemplate;
		
		GO.addressbook.ContactReadPanel.superclass.initComponent.call(this);
		
		if(GO.tasks)
		{
			this.scheduleCallItem = new GO.tasks.ScheduleCallMenuItem();
			this.newMenuButton.menu.add(this.scheduleCallItem);
		}
		
		if (GO.smscampaigns) {
			this.newMenuButton.menu.add({
				itemId : 'sms',
				text: GO.smscampaigns.lang['singleSms'],
				iconCls: 'go-model-icon-GO_Email_Model_ImapMessage',
				handler:function(item, e){
					if (!GO.smscampaigns.singleSmsDialog)
						GO.smscampaigns.singleSmsComposer = new GO.smscampaigns.SingleSmsComposer();

					GO.smscampaigns.singleSmsComposer.show(this.model_id,this.data['last_name']);
				},
				scope: this
			});
		}
		
	},
	
	createTopToolbar : function(){
		var tbar = GO.addressbook.ContactReadPanel.superclass.createTopToolbar.call(this);
		
		if(GO.settings.modules.users.read_permission){
			tbar.splice(tbar.length-2,0,this.createUserButton = new Ext.Button({
					iconCls:'btn-add',
					text:GO.addressbook.lang.createUser,
					disabled:true,
					handler:function(){
						
						if(GO.util.empty(this.data.go_user_id)){

							var username =this.data.last_name;

							var arr = this.data.email.split('@');
							if(arr[0])
								username = arr[0];

							GO.users.showUserDialog(0, {
								loadParams:{contact_id: this.data.id, addressbook_id: this.data.addressbook_id},
								values:{
									first_name:this.data.first_name,
									middle_name:this.data.middle_name,
									last_name:this.data.last_name,
									email:this.data.email,
									username:username
								}
							});		
							
						}else
						{
							GO.users.showUserDialog(this.data.go_user_id);
						}
					},
					scope:this
				}));
		}
		
		tbar.splice(tbar.length-2,0,
			this.mergeButton = new Ext.Button({
			iconCls: 'btn-add',
			text: GO.lang.merge,
			scope:this,
			disabled:true,
			handler: function()
			{
				if(!this.selectMergeLinksWindow)
				{
					this.selectMergeLinksWindow = new GO.dialog.MergeWindow({
						displayPanel:this
					});
				}			

				this.selectMergeLinksWindow.show();
			}
		}));
		return tbar;
	},
	
	setData : function(data)
	{
		GO.addressbook.ContactReadPanel.superclass.setData.call(this, data);
		
		if(this.createUserButton){
			this.createUserButton.setDisabled(false);
			if(GO.util.empty(this.data.go_user_id))
				this.createUserButton.setText(GO.addressbook.lang.createUser);
			else
				this.createUserButton.setText(GO.addressbook.lang.editUser);
		}
		
		this.mergeButton.setDisabled(!data.write_permission)
		
		if(data.write_permission)
		{
			if(this.scheduleCallItem)
			{				
				var name = this.data.name;
				
				if(this.data.work_phone!='')
				{
					name += ' ('+this.data.work_phone+')';
				}else if(this.data.cellular!='')
				{
					name += ' ('+this.data.cellular+')';
				}else if(this.data.home_phone!='')
				{
					name += ' ('+this.data.home_phone+')';
				}
				
				this.scheduleCallItem.setLinkConfig({
					name: name,
					model_id: this.data.id, 
					model_name:"GO\\\\Addressbook\\\\Model\\\\Contact",
					callback:this.reload,
					scope: this
				});
			}
		}
		
		
		this.newMenuButton.menu.taskShowConfig= {contact_id:this.data.id};
		
	}	
});			