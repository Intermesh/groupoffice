/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ReadPanelCompany.js 22345 2018-02-08 15:24:09Z mschering $
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

			this.template = '<tpl if="values.photo_url"><figure style="background-image: url({[this.photo_link(values)]});" \
					onClick="GO.addressbook.showCompanyDialog({id}, \\{activeTab:1\\} );"></figure>'+
				'</tpl>'+
				
				'{[this.collapsibleSectionHeader(t("Company", "addressbook")+": "+ values.name, "companypane2-"+values.panelId, "name")]}'+
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="companypane2-{panelId}">'+
				

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
								'<td colspan="2" class="readPanelSubHeading">' + t("Visit address", "addressbook") + '</td>'+
							'</tr>'+
							'</tpl>'+

							// LEGE REGEL
							'<tr>'+
								'<td>'+
								'<h4>{name}</h4><tpl if="!GO.util.empty(name2)"><h5>{name2}</h5></tpl>'+
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
									'<td colspan="3" class="readPanelSubHeading">' + t("Post address", "addressbook") + '</td>'+
								'</tr>'+

								// LEGE REGEL
								'<tr>'+
									'<td>'+

								//ADDRESS
								'<h4>{name}</h4>'+
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
										'<td class="contactCompanyLabelWidth">' + t("Phone") + ':</td><td>{[GO.util.callToLink(values.phone)]}</td>'+
									'</tr>'+						
								'</tpl>'+

								//FAX							
								'<tpl if="!GO.util.empty(fax)">'+
									'<tr>'+
										'<td class="contactCompanyLabelWidth">' + t("Fax") + ':</td><td>{fax}</td>'+
									'</tr>'+						
								'</tpl>'+								
								
								//EMAIL							
								'<tpl if="!GO.util.empty(email)">'+
									'<tr>'+
										'<td class="contactCompanyLabelWidth">' + t("E-mail") + ':</td><td>{[this.mailTo(values.email, values.full_name)]}</td>'+
									'</tr>'+						
								'</tpl>'+		
								
								//HOMEPAGE
								'<tpl if="!GO.util.empty(homepage)">'+
									'<tr>'+
										'<td class="contactCompanyLabelWidth">' + t("Homepage") + ':</td><td><a href="{homepage}" target="_blank">{homepage}</a></td>'+
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
											'<td>' + t("Bank number", "addressbook") + ':</td><td>{bank_no}</td>'+
										'</tr>'+						
									'</tpl>'+
									
									'<tpl if="!GO.util.empty(iban)">'+
										'<tr>'+
											'<td>' + t("IBAN", "addressbook")+ ':</td><td>{iban}</td>'+
										'</tr>'+						
									'</tpl>'+
									
									'<tpl if="!GO.util.empty(crn)">'+
										'<tr>'+
											'<td>' + t("Company Reg. No.", "addressbook")+ ':</td><td>{crn}</td>'+
										'</tr>'+						
									'</tpl>'+

									//VAT_NO							
									'<tpl if="!GO.util.empty(vat_no)">'+
										'<tr>'+
											'<td>' + t("VAT number", "addressbook") + ':</td><td>{vat_no}</td>'+
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
							'<td class="display-panel-heading">' + t("Remark", "addressbook") + '</td>'+
						'</tr>'+
						'<tr>'+
							'<td>{comment}</td>'+
						'</tr>'+
						'</table>'+
					'</tpl>'+		
					
					
					'<tpl if="employees.length">'+
					'{[this.collapsibleSectionHeader("'+t("Employees", "addressbook")+'","employees")]}'+
						'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="employees">'+
						//LINK DETAILS
//						'<tr>'+
//							'<td colspan="4" class="display-panel-heading">'+t("Employees", "addressbook")+'</td>'+
//						'</tr>'+
						
						'<tr>'+							
							'<td class="table_header_links">' + t("Name") + '</td>'+
							'<td class="table_header_links">' + t("Function") + '</td>'+
							'<td class="table_header_links">' + t("E-mail") + '</td>'+							
						'</tr>'+	
											
						'<tpl for="employees">'+
							'<tr>'+								
								'<td><a href="#contact/{id}">{name}</a></td>'+
								'<td>{function}</td>'+
								'<td>{[this.mailTo(values.email, values.name)]}</td>'+
							'</tr>'+							
						'</tpl>'+
						'</table>'+
		
						'</tpl>';

			this.template +=GO.customfields.displayPanelTemplate;
			this.template +=GO.customfields.displayPanelBlocksTemplate;
		

			if(go.Modules.isAvailable("legacy", "lists"))
				this.template += GO.lists.ListTemplate;

			
			
			if(go.Modules.isAvailable("legacy", "workflow")){
				this.template +=GO.workflow.WorkflowTemplate;
			}
								
								
			
			
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
					return '<a onclick="GO.email.showAddressMenu(event, \''+this.addSlashes(email)+'\',\''+this.addSlashes(name)+'\');">'+email+'</a>';
				}else
				{
					return '<a href="mailto:'+email+'">'+email+'</a>';
				}
			},
			photo_link: function(values) {
				if(values.photo_url) {
					return values.photo_url.split('&w=120&h=160&zc=1').join('&w=280');
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
		
			
		GO.addressbook.CompanyReadPanel.superclass.initComponent.call(this);

	},
	createTopToolbar : function(){
		var tbar = GO.addressbook.CompanyReadPanel.superclass.createTopToolbar.call(this);
		

		tbar.splice(tbar.length-2,0,
			this.mergeButton = new Ext.Button({
			iconCls: 'ic-merge-type',
			text: t("Merge"),
			scope:this,
			disabled:true,
			handler: function()
			{
				if(!this.selectMergeLinksWindow)
				{
					this.selectMergeLinksWindow = new GO.dialog.MergeWindow({
						entity: "Company",
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
					
		this.newMenuButton.menu.taskShowConfig= {company_id:this.data.id};
	}
});
