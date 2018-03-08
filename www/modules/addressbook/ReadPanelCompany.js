/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ReadPanelCompany.js 19324 2015-08-18 10:17:13Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.CompanyReadPanel = Ext.extend(GO.DisplayPanel,{
	
	model_name : "GO\\Addressbook\\Model\\Company",

	stateId : 'ab-company-panel',

	editGoDialogId : 'company',
	
	editHandler : function(){
		GO.addressbook.showCompanyDialog(this.model_id);		
	},	
	
	initComponent : function(){
		
		this.loadUrl = ("addressbook/company/display");
 
			this.template = ''+
				'{[this.collapsibleSectionHeader(GO.addressbook.lang.company+": "+ values.name, "companypane2-"+values.panelId, "name")]}'+
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="companypane2-{panelId}">'+
//				'<tr>'+
//						'<td colspan="3" class="display-panel-heading">'+GO.addressbook.lang.company+': {name}</td>'+
//				'</tr>'+
					/*'<tr>'+
						'<tpl if="this.isCompanySecondColumn(values)">'+
							'<td colspan="2" valign="top" class="display-panel-heading">'+
						'</tpl>'+

						'<tpl if="this.isCompanySecondColumn(values) == false">'+
							'<td valign="top" class="display-panel-heading">'+
						'</tpl>'+
						
							GO.addressbook.lang['cmdCompanyDetailsFor'] + ' <b>{name}</b>'+
						'</td>'+
					'</tr>'+*/

				// CONTACT DETAILS+ 1e KOLOM

				'<tr>'+
					'<tpl if="this.isCompanySecondColumn(values)">'+
						'<td colspan="2" style="vertical-align:top;">'+
					'</tpl>'+
					'<tpl if="this.isCompanySecondColumn(values) == false">'+
						'<td>'+
					'</tpl>'+
					'<table><tr><td>ID:</td><td>{id}</td>'+
						'<td colspan="2" style="vertical-align:top;">'+GO.addressbook.lang.addressbook+':</td><td>{addressbook_name}</td>'+'</tr>'+
					'</table>'+
					'</td>'+					
					
					'<tpl if="photo_url">'+
						'<td align="right">' +

							'<tpl if="write_permission">'+
								'<img src="{photo_url}" class="ab-photo" style="cursor:pointer;" onClick="GO.addressbook.showCompanyDialog({id}, \\{activeTab:1\\} );"/>' +
							'</tpl>'+

							'<tpl if="!write_permission">'+
								'<a href="{original_photo_url}" target="_blank">'+
								'<img src="{photo_url}" class="ab-photo" />' +
								'</a>'+
							'</tpl>'+

						'</td>' +
					'</tpl>'+
					
				'</tr>'+
				'</table>'+
				
				'{[this.collapsibleSectionHeader(GO.addressbook.lang.cmdFieldsetContact, "contactpane2-"+values.panelId, "name")]}'+
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="contactpane2-{panelId}">'+
				

				'<tr>'+

					'<tpl if="this.isCompanySecondColumn(values)">'+
						'<tpl if="this.isAddressPost(values)">'+
							'<td valign="top" class="contactCompanyDetailsPanelKolom">'+
						'</tpl>'+

						'<tpl if="this.isAddressPost(values) == false">'+
							'<td colspan="2" valign="top" class="contactCompanyDetailsPanelKolom100">'+
						'</tpl>'+
					'</tpl>'+

					'<tpl if="this.isCompanySecondColumn(values) == false">'+
						'<td valign="top" class="contactCompanyDetailsPanelKolom100">'+
					'</tpl>'+

						'<table cellpadding="0" cellspacing="0" border="0">'+
							'<tpl if="this.isAddressPost(values) != false">'+
							'<tr>'+
								'<td colspan="2" class="readPanelSubHeading">' + GO.addressbook.lang['cmdFieldsetVisitAddress'] + '</td>'+
							'</tr>'+
							'</tpl>'+

							// LEGE REGEL
							'<tr>'+
								'<td>'+
								'<b>{name}</b><tpl if="!GO.util.empty(name2)"><br />{name2}</tpl><br />'+
							//ADDRESS
							'<tpl if="!GO.util.empty(google_maps_link)">'+
								'<a href="{google_maps_link}" target="_blank">'+
							'</tpl>'+
							'{formatted_address}'+
							'<tpl if="!GO.util.empty(google_maps_link)">'+
								'</a>'+
							'</tpl>'+

						'</table>'+
					'</td>'+


					// CONTACT DETAILS+ 2e KOLOM
					'<tpl if="this.isAddressPost(values)">'+
						'<tpl if="this.isAddressVisit(values)">'+
							'<td colspan="2" valign="top" class="contactCompanyDetailsPanelKolom">'+
						'</tpl>'+

						'<tpl if="this.isAddressVisit(values) == false">'+
							'<td colspan="3" valign="top" class="contactCompanyDetailsPanelKolom100">'+
						'</tpl>'+

							'<table cellpadding="0" cellspacing="0" border="0">'+

								'<tr>'+
									'<td colspan="3" class="readPanelSubHeading">' + GO.addressbook.lang['cmdFieldsetPostAddress'] + '</td>'+
								'</tr>'+

								// LEGE REGEL
								'<tr>'+
									'<td>'+

								//ADDRESS
								'<b>{name}</b><br />'+
								'<tpl if="!GO.util.empty(post_google_maps_link)">'+
									'<a href="{post_google_maps_link}" target="_blank">'+
								'</tpl>'+
								'{post_formatted_address}'+
								'<tpl if="!GO.util.empty(post_google_maps_link)">'+
									'</a>'+
								'</tpl>'+
							'</table>'+
						'</td>'+
					'</tpl>'+
				'</tr>'+
				
				
					'<tr>'+	
						// COMPANY DETAILS+ 1e KOLOM
						'<tpl if="this.isCompanySecondColumn(values)">'+
							'<tpl if="this.isBankVat(values)">'+
								'<td valign="top" class="contactCompanyDetailsPanelKolom">'+
							'</tpl>'+
							
							'<tpl if="this.isBankVat(values) == false">'+
								'<td colspan="2" valign="top" class="contactCompanyDetailsPanelKolom100">'+
							'</tpl>'+							
						'</tpl>'+
						
						'<tpl if="this.isCompanySecondColumn(values) == false">'+
							'<td valign="top" class="contactCompanyDetailsPanelKolom100">'+
						'</tpl>'+
																		
							'<table cellpadding="0" cellspacing="0" border="0">'+						
								
								//PHONE							
								'<tpl if="!GO.util.empty(phone)">'+
									'<tr>'+
										'<td class="contactCompanyLabelWidth">' + GO.lang['strPhone'] + ':</td><td>{[GO.util.callToLink(values.phone)]}</td>'+
									'</tr>'+						
								'</tpl>'+

								//FAX							
								'<tpl if="!GO.util.empty(fax)">'+
									'<tr>'+
										'<td class="contactCompanyLabelWidth">' + GO.lang['strFax'] + ':</td><td>{fax}</td>'+
									'</tr>'+						
								'</tpl>'+								
								
								//EMAIL							
								'<tpl if="!GO.util.empty(email)">'+
									'<tr>'+
										'<td class="contactCompanyLabelWidth">' + GO.lang['strEmail'] + ':</td><td>{[this.mailTo(values.email, values.full_name)]}</td>'+
									'</tr>'+						
								'</tpl>'+		
								
								//HOMEPAGE
								'<tpl if="!GO.util.empty(homepage)">'+
									'<tr>'+
										'<td class="contactCompanyLabelWidth">' + GO.lang['strHomepage'] + ':</td><td><a href="{homepage}" target="_blank">{homepage}</a></td>'+
									'</tr>'+
								'</tpl>'+
											
																										
							'</table>'+
						'</td>'+
						
						'<tpl if="this.isBankVat(values)">'+
							// COMPANY DETAILS+ 2e KOLOM
							'<td colspan="2" valign="top" class="contactCompanyDetailsPanelKolom">'+
								'<table cellpadding="0" cellspacing="0" border="0">'+												
									
									//BANK_NO
									'<tpl if="!GO.util.empty(bank_no)">'+
										'<tr>'+
											'<td>' + GO.addressbook.lang['cmdFormLabelBankNo'] + ':</td><td>{bank_no}</td>'+
										'</tr>'+						
									'</tpl>'+
									
									'<tpl if="!GO.util.empty(iban)">'+
										'<tr>'+
											'<td>' + GO.addressbook.lang.iban+ ':</td><td>{iban}</td>'+
										'</tr>'+						
									'</tpl>'+
									
									'<tpl if="!GO.util.empty(crn)">'+
										'<tr>'+
											'<td>' + GO.addressbook.lang.crn+ ':</td><td>{crn}</td>'+
										'</tr>'+						
									'</tpl>'+

									//VAT_NO							
									'<tpl if="!GO.util.empty(vat_no)">'+
										'<tr>'+
											'<td>' + GO.addressbook.lang['cmdFormLabelVatNo'] + ':</td><td>{vat_no}</td>'+
										'</tr>'+						
									'</tpl>'+

									
								'</table>'+
							'</td>'+
						'</tpl>'+					
					'</tr>'+
					
					
					
					
					'</table>'+		

					'<tpl if="!GO.util.empty(comment)">'+						
						'<table cellpadding="0" cellspacing="0" border="0" class="display-panel">'+
						'<tr>'+
							'<td class="display-panel-heading">' + GO.addressbook.lang['cmdFormLabelComment'] + '</td>'+
						'</tr>'+
						'<tr>'+
							'<td>{comment}</td>'+
						'</tr>'+
						'</table>'+
					'</tpl>'+		
					
					
					'<tpl if="employees.length">'+
					'{[this.collapsibleSectionHeader("'+GO.addressbook.lang.cmdPanelEmployees+'","employees")]}'+
						'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="employees">'+
						//LINK DETAILS
//						'<tr>'+
//							'<td colspan="4" class="display-panel-heading">'+GO.addressbook.lang.cmdPanelEmployees+'</td>'+
//						'</tr>'+
						
						'<tr>'+
							'<td width="16" class="display-panel-links-header">&nbsp;</td>'+
							'<td class="table_header_links">' + GO.lang['strName'] + '</td>'+
							'<td class="table_header_links">' + GO.lang['strFunction'] + '</td>'+
							'<td class="table_header_links">' + GO.lang['strEmail'] + '</td>'+							
						'</tr>'+	
											
						'<tpl for="employees">'+
							'<tr>'+
								'<td><div class="go-icon go-model-icon-GO_Addressbook_Model_Contact"></div></td>'+
								'<td><a href="#" onclick="GO.linkHandlers[\'GO\\\\\\\\Addressbook\\\\\\\\Model\\\\\\\\Contact\'].call(this, {id});">{name}</a></td>'+
								'<td>{function}</td>'+
								'<td>{[this.mailTo(values.email, values.name)]}</td>'+
							'</tr>'+							
						'</tpl>'+
						'</table>'+
					'</tpl>';

			if(GO.lists)
				this.template += GO.lists.ListTemplate;

			if(GO.customfields)
			{
				this.template +=GO.customfields.displayPanelTemplate;
				this.template +=GO.customfields.displayPanelBlocksTemplate;
			}
			
			if(GO.workflow){
				this.template +=GO.workflow.WorkflowTemplate;
			}
								
			if(GO.tasks)
				this.template +=GO.tasks.TaskTemplate;

			if(GO.calendar)
				this.template += GO.calendar.EventTemplate;

			this.template +=GO.linksTemplate;
			
	  Ext.apply(this.templateConfig,{
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
			
			isCompanySecondColumn : function(values)
			{
				if(
					this.isBankVat(values) ||
					this.isAddressPost(values) ||
					!GO.util.empty(values['homepage'])
				)
				{
					return true;
				} else {
					return false;
				}
			},
			isBankVat : function(values)
			{
				if(
					!GO.util.empty(values['bank_no']) ||
					!GO.util.empty(values['vat_no']) 	||
					!GO.util.empty(values['iban']) 	||
					!GO.util.empty(values['crn'])
					
				)
				{
					return true;
				} else {
					return false;
				}
			},	
			isAddress : function(values)
			{
				if(
					!GO.util.empty(values['address']) ||
					!GO.util.empty(values['address_no']) ||
					!GO.util.empty(values['zip']) ||
					!GO.util.empty(values['city']) ||
					!GO.util.empty(values['state']) ||
					!GO.util.empty(values['country']) ||
					!GO.util.empty(values['post_address']) ||
					!GO.util.empty(values['post_address_no']) ||
					!GO.util.empty(values['post_zip']) ||
					!GO.util.empty(values['post_city']) ||
					!GO.util.empty(values['post_state']) ||
					!GO.util.empty(values['post_country'])
				)
				{
					return true;
				} else {
					return false;
				}
			},	
			isAddressVisit : function(values)
			{
				if(
					!GO.util.empty(values['address']) ||
					!GO.util.empty(values['address_no']) ||
					!GO.util.empty(values['zip']) ||
					!GO.util.empty(values['city']) ||
					!GO.util.empty(values['state']) ||
					!GO.util.empty(values['country'])
				)
				{
					return true;
				} else {
					return false;
				}
			},
			isAddressPost : function(values)
			{
				if(
					!GO.util.empty(values['post_address']) ||
					!GO.util.empty(values['post_address_no']) ||
					!GO.util.empty(values['post_zip']) ||
					!GO.util.empty(values['post_city']) ||
					!GO.util.empty(values['post_state']) ||
					!GO.util.empty(values['post_country'])					
				)
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
				
//		this.template+='</div>';		
			
		GO.addressbook.CompanyReadPanel.superclass.initComponent.call(this);
		
//		if(GO.tasks)
//		{
//			this.scheduleCallItem = new GO.tasks.ScheduleCallMenuItem();
//			this.newMenuButton.menu.add(this.scheduleCallItem);
//		}
	},
	createTopToolbar : function(){
		var tbar = GO.addressbook.ContactReadPanel.superclass.createTopToolbar.call(this);
		

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
		GO.addressbook.CompanyReadPanel.superclass.setData.call(this, data);
		
		if(this.mergeButton)
			this.mergeButton.setDisabled(!data.write_permission)
					
//		if(data.write_permission)
//		{
//			if(this.scheduleCallItem)
//			{				
//				var name = this.data.name;
//				
//				if(this.data.phone!='')
//				{
//					name += ' ('+this.data.phone+')';
//				}
//				
//				this.scheduleCallItem.setLinkConfig({
//					name: name,
//					model_id: this.data.id, 
//					model_name:"GO\\Addressbook\\Model\\Company",
//					callback:this.reload,
//					scope: this
//				});
//			}
//		}
		this.newMenuButton.menu.taskShowConfig= {company_id:this.data.id};
	}
});