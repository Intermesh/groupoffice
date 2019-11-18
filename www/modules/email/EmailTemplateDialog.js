/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: EmailTemplateDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */

 
GO.email.EmailTemplateDialog = function(config){
	
	if(!config)
	{
		config={};
	}
	
	this.buildForm();
	
	var focusFirstField = function(){
		this.propertiesPanel.items.items[0].focus();
	};
	
	
	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=true;
	config.width=760;
	config.height=600;
	config.closeAction='hide';
	config.title= t("E-mail template", "email");					
	config.items= this.formPanel;
	config.focus= focusFirstField.createDelegate(this);
	config.buttonAlign='left';

	
	GO.email.EmailTemplateDialog.superclass.constructor.call(this, config);
	
	
	this.addEvents({
		'save' : true
	});
}

Ext.extend(GO.email.EmailTemplateDialog, go.Window,{
//
//	inline_attachments : [],
	
	show : function (email_template_id) {
		
		if(!this.rendered)
		{
			this.render(Ext.getBody());
		}

		this.tabPanel.setActiveTab(0);

		if(!email_template_id)
		{
			email_template_id=0;			
		}
			
		this.setEmailTemplateId(email_template_id);
		
		if(this.email_template_id>0)
		{
			this.formPanel.load({
				url: GO.url('email/template/load'),
				
				success:function(form, action)
				{
					this.readPermissionsTab.setAcl(action.result.data.acl_id);
										
					GO.email.EmailTemplateDialog.superclass.show.call(this);
				},
				failure:function(form, action)
				{
					GO.errorDialog.show(action.result.feedback)
				},
				scope: this
				
			});
		}else 
		{			
			this.formPanel.form.reset();
			this.htmlEditPanel.reset();
			this.readPermissionsTab.setAcl(0);

			GO.email.EmailTemplateDialog.superclass.show.call(this);
		}
	},
	
	

	setEmailTemplateId : function(email_template_id)
	{
		this.formPanel.form.baseParams['id']=email_template_id;
		this.email_template_id=email_template_id;		
	},
	
	submitForm : function(hide){

		//won't toggle if not done twice...
		// THIS IS ALREADY DONE IN THE EMAILEDITORPANEL 
//		if(this.htmlEditPanel.getHtmlEditor().sourceEditMode){
//			this.htmlEditPanel.getHtmlEditor().toggleSourceEdit(false);
//			this.htmlEditPanel.getHtmlEditor().toggleSourceEdit(false);
//		}
		//this.htmlEditPanel.getHtmlEditor().toggleSourceEdit(false);

		this.formPanel.form.submit(
		{
			url: GO.url('email/template/submit'),
			waitMsg:t("Saving..."),
			success:function(form, action){
				
				this.fireEvent('save', this);

				if(hide)
				{
					this.hide();	
				}else
				{
					if(action.result.id)
					{
						this.setEmailTemplateId(action.result.id);						
						this.readPermissionsTab.setAcl(action.result.acl_id);
					}
				}	
			},		
			failure: function(form, action) {
				if(action.failureType == 'client')
				{					
					Ext.MessageBox.alert(t("Error"), t("You have errors in your form. The invalid fields are marked."));			
				} else {
					Ext.MessageBox.alert(t("Error"), action.result.feedback);
				}
			},
			scope: this
		});
		
	},
	
	
	buildForm : function () {
		
//		var imageInsertPlugin = new GO.plugins.HtmlEditorImageInsert();
//		imageInsertPlugin.on('insert', function(plugin, path, url,temp,id) {
//
//
//			var ia = {
//				tmp_file : path,
//				url : url,
//				temp:temp
//			};
//
//		this.inline_attachments.push(ia);
//		}, this);
		
		var autodata = [			
		['{date}',t("Date")],
		['{contact:salutation}',t("Salutation")],
		['{contact:first_name}',t("First name")],
		['{contact:middle_name}',t("Middle name")],
		['{contact:last_name}',t("Last name")],
		['{contact:initials}',t("Initials")],
		['{contact:title}',t("Title")],
		['{contact:email}',t("E-mail")],
		['{contact:home_phone}',t("Phone")],
		['{contact:fax}',t("Fax")],
		['{contact:cellular}',t("Mobile")],
		['{contact:cellular2}',t("2nd mobile")],
		['{contact:address}',t("Address")],
		['{contact:address_no}',t("Address 2")],
		['{contact:zip}',t("ZIP/Postal")],
		['{contact:city}',t("City")],
		['{contact:state}',t("State")],
		['{contact:country}',t("Country")],
		['{contact:company}',t("Company")],
		['{contact:department}',t("Department")],
		['{contact:function}',t("Function")],
		['{contact:work_phone}',t("Phone (work)")],
		['{contact:work_fax}',t("Fax (work)")],
		['{contact:work_address}',t("Address (work)")],
		['{contact:work_address_no}',t("Address 2 (work)")],
		['{contact:work_city}',t("City (work)")],
		['{contact:work_zip}',t("ZIP/Postal (work)")],
		['{contact:work_state}',t("State (work)")],
		['{contact:work_country}',t("Country (work)")],
		['{contact:work_post_address}',t("Address (post)")],
		['{contact:work_post_address_no}',t("Number of house (post)")],
		['{contact:work_post_city}',t("City (post)")],
		['{contact:work_post_zip}',t("ZIP/Postal (post)")],
		['{contact:work_post_state}',t("State (post)")],
		['{contact:work_post_country}',t("Country (post)")],
		['{contact:homepage}',t("Homepage")],
		['{user:name}',t("Name")+' ('+t("User")+')'],
		['{user:first_name}',t("First name")+' ('+t("User")+')'],
		['{user:middle_name}',t("Middle name")+' ('+t("User")+')'],
		['{user:last_name}',t("Last name")+' ('+t("User")+')'],
		['{user:initials}',t("Initials")+' ('+t("User")+')'],
		['{user:title}',t("Title")+' ('+t("User")+')'],
		['{user:email}',t("E-mail")+' ('+t("User")+')'],
		['{user:home_phone}',t("Phone")+' ('+t("User")+')'],
		['{user:fax}',t("Fax")+' ('+t("User")+')'],
		['{user:work_phone}',t("Phone (work)")+' ('+t("User")+')'],
		['{user:work_fax}',t("Fax (work)")+' ('+t("User")+')'],
		['{user:cellular}',t("Mobile")+' ('+t("User")+')'],
		['{user:cellular2}',t("2nd mobile")+' ('+t("User")+')'],
		['{user:address}',t("Address")+' ('+t("User")+')'],
		['{user:address_no}',t("Address 2")+' ('+t("User")+')'],
		['{user:zip}',t("ZIP/Postal")+' ('+t("User")+')'],
		['{user:city}',t("City")+' ('+t("User")+')'],
		['{user:state}',t("State")+' ('+t("User")+')'],
		['{user:country}',t("Country")+' ('+t("User")+')'],
		['{usercompany:name}',t("Company")+' ('+t("User")+')'],
		['{user:department}',t("Department")+' ('+t("User")+')'],
		['{user:function}',t("Function")+' ('+t("User")+')'],
		['{usercompany:phone}',t("Phone (work)")+' ('+t("User")+')'],
		['{usercompany:fax}',t("Fax (work)")+' ('+t("User")+')'],
		['{usercompany:address}',t("Address (work)")+' ('+t("User")+')'],
		['{usercompany:address_no}',t("Address 2 (work)")+' ('+t("User")+')'],
		['{usercompany:city}',t("City (work)")+' ('+t("User")+')'],
		['{usercompany:zip}',t("ZIP/Postal (work)")+' ('+t("User")+')'],
		['{usercompany:state}',t("State (work)")+' ('+t("User")+')'],
		['{usercompany:country}',t("Country (work)")+' ('+t("User")+')'],
		['{user:homepage}',t("Homepage")+' ('+t("User")+')'],
		['{unsubscribe_link}',t("Unsubscribe link", "email")],
		['%unsubscribe_href%',t("Unsubscribe href", "email")],
		['{link}',t("Link")]
		];
   	
		var items = [new Ext.Panel({
			title:t("Autodata", "email") ,
			autoScroll:true,
			items:new GO.grid.SimpleSelectList({
				store:  new Ext.data.SimpleStore({
					fields: ['value', 'name'],
					data : autodata
				}),
				listeners:{
					scope:this,
					click:function(dataview, index){
						
						this.htmlEditPanel.getHtmlEditor().insertAtCursor(dataview.store.data.items[index].data.value);
						this.htmlEditPanel.getHtmlEditor().deferFocus();
						dataview.clearSelections();
					}
				}
			})
		})];

		if(go.Modules.isAvailable("core", "customfields")){
			autodata=[];

			if(autodata.length){
				items.push(new Ext.Panel({
					autoScroll:true,
					title:t("Custom contact fields", "email"),

					items:new GO.grid.SimpleSelectList({
						store:  new Ext.data.SimpleStore({
							fields: ['value', 'name'],
							data : autodata
						}),
						listeners:{
							scope:this,
							click:function(dataview, index){
								this.htmlEditPanel.getHtmlEditor().insertAtCursor(dataview.store.data.items[index].data.value);
								this.htmlEditPanel.getHtmlEditor().deferFocus();
								dataview.clearSelections();
							}
						}
					})
				}));
			}

			autodata=[];

			if(autodata.length){
				items.push(new Ext.Panel({
					autoScroll:true,
					title:t("Custom company fields", "email"),
					items:new GO.grid.SimpleSelectList({
						store:  new Ext.data.SimpleStore({
							fields: ['value', 'name'],
							data : autodata
						}),
						listeners:{
							scope:this,
							click:function(dataview, index){
								this.htmlEditPanel.getHtmlEditor().insertAtCursor(dataview.store.data.items[index].data.value);
								this.htmlEditPanel.getHtmlEditor().deferFocus();
								dataview.clearSelections();
							}
						}
					})
				}));
			}
			
			
			
			autodata=[];

			if(autodata.length){
				items.push(new Ext.Panel({
					autoScroll:true,
					title:t("Custom user fields", "email"),
					items:new GO.grid.SimpleSelectList({
						store:  new Ext.data.SimpleStore({
							fields: ['value', 'name'],
							data : autodata
						}),
						listeners:{
							scope:this,
							click:function(dataview, index){
								this.htmlEditPanel.getHtmlEditor().insertAtCursor(dataview.store.data.items[index].data.value);
								this.htmlEditPanel.getHtmlEditor().deferFocus();
								dataview.clearSelections();
							}
						}
					})
				}));
			}
		}

		this.autoDataPanel = new Ext.Panel({
			region:'east',
			layout:'accordion',
			border:false,
			autoScroll:true,
			width: 180,
			split:true,
			resizable:true,
			items:items
		});
		

		this.propertiesPanel = new Ext.Panel({
			region:'center',
			border: false,
			layout:'border',
			items:[{
				region:'north',
				autoHeight: true,
				layout:'form',
				border: false,
				cls:'go-form-panel',			
				items:[
					{
					xtype: 'textfield',
					name: 'name',
					anchor: '100%',
					allowBlank:false,
					fieldLabel: t("Name")
				},
				{
					xtype: 'textfield',
					name: 'subject',
					anchor: '100%',
					allowBlank: true,
					fieldLabel: t("Subject")
				}
			]
			},
			this.htmlEditPanel = new GO.base.email.EmailEditorPanel({
				region:'center'
			})]
				
		});

		//{text:'Toggle HTML',handler:function(){this.htmlEditPanel.setContentTypeHtml(this.htmlEditPanel.getContentType()!='html')}, scope:this}
		var borderLayoutPanel = new Ext.Panel({
			layout:'border',
			title:t("Properties"),	
			items: [this.propertiesPanel, this.autoDataPanel]			
		});
		

		var items  = [borderLayoutPanel];
		
		this.readPermissionsTab = new GO.grid.PermissionsPanel({
			
			});

		items.push(this.readPermissionsTab);
 
		this.tabPanel = new Ext.TabPanel({
			activeTab: 0,
			deferredRender: false,
			border: false,
			items: items,
			anchor: '100% 100%'
		}) ;
    
    
		this.formPanel = new Ext.form.FormPanel({
			border: false,
			baseParams: {
				task: 'email_template'
			},
			waitMsgTarget:true,			
			items:this.tabPanel				
		});
    
		this.buttons = [this.htmlEditPanel.getAttachmentsButton(),'->',
		{
			text: t("Apply"),
			handler: function(){
				this.submitForm();
			},
			scope:this
		},{
			text: t("Save"),
			handler: function(){
				this.submitForm(true);
			},
			scope: this
		}];
	}
});
