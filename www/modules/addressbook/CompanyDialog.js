/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: CompanyDialog.js 22335 2018-02-06 16:25:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.CompanyDialog = function(config)
{
	var config = config || {};
	Ext.apply(this, config);

	this.goDialogId = 'company';
	this.personalPanel = new GO.addressbook.CompanyProfilePanel();
	this.photoPanel = new GO.addressbook.PicturePanel({
		getName: function(){
			var f= this.companyForm.form;
			var n2 = f.findField('name2').getValue();

			if(n2)
				n2 = ' '+n2;
			else
				n2 = '';

			return f.findField('name').getValue()+n2;
		}.createDelegate(this)
	});	
				
	this.commentPanel = new Ext.Panel({
		title: t("Remarks", "addressbook"), 
		layout: 'fit',
		border:false,
		items: [
		new Ext.form.TextArea({
			name: 'comment',
			fieldLabel: '',
			hideLabel: true
		})
		]
	});

	this.commentPanel.on('show', function(){
		this.companyForm.form.findField('comment').focus();
	}, this);

	/* employees Grid */
	this.employeePanel = new GO.addressbook.EmployeesPanel();

  
	var items = [
		this.personalPanel,
		this.photoPanel,
		this.commentPanel
	];

					
	this.selectAddresslistsPanel = new GO.addressbook.SelectAddresslistsPanel();
					
	items.push(this.selectAddresslistsPanel);
	items.push(this.employeePanel);
  
	if(GO.customfields && GO.customfields.types["GO\\Addressbook\\Model\\Company"])
	{
		for(var i=0;i<GO.customfields.types["GO\\Addressbook\\Model\\Company"].panels.length;i++)
		{
			items.push(GO.customfields.types["GO\\Addressbook\\Model\\Company"].panels[i]);
		}
	}

	
	this.companyForm = this.formPanel = new Ext.FormPanel({
		fileUpload : true,
		waitMsgTarget:true,		
		border: false,
		baseParams: {},
		items: [
		this.tabPanel = new Ext.TabPanel({
			border: false,
			activeTab: 0,
			enableTabScroll:true,
			deferredRender: false,
			hideLabel: true,
			anchor:'100% 100%',
			items: items
		})
		]
	});				
  
	this.stateId= 'addressbook-window-new-company';
	this.layout= 'fit';
	this.modal= false;
	this.shadow= false;
	this.border= false;
	this.height= 640;
	this.width= 820;
	this.plain= true;
	this.closeAction= 'hide';
	this.collapsible=true;
	this.title= t("Edit company", "addressbook");
	this.items= this.companyForm;
	this.buttonAlign = 'left';
	this.buttons = [
	this.moveEmployeesButton = new Ext.Button({
		text:t("Move employees", "addressbook"),
		iconCls: 'ic-compare-arrows',
		handler:function(){
			if(!this.moveEmpWin){

				this.moveEmpForm = new Ext.FormPanel({
					cls:'go-form-panel',
					url: GO.url('addressbook/company/moveEmployees'),
					baseParams:{
						from_company_id:0
					},
					waitMsgTarget:true,
					items:new GO.addressbook.SelectCompany({
						allowBlank:false,
						anchor:'100%',
						hiddenName:'to_company_id'
					})
				});

				this.moveEmpWin = new GO.Window({
					title:t("Move employees", "addressbook"),
					closable:true,
					modal:true,
					width:400,
					autoHeight:true,
					items:this.moveEmpForm,
					buttons:[{
						text:t("Ok"),
						handler:function(){
							this.moveEmpForm.form.submit({
								waitMsg:t("Saving..."),
								success:function(form, action){
									this.moveEmpWin.hide();
								},
								failure: function(form, action) {
									Ext.MessageBox.alert(t("Error"), 
										action.failureType == 'client' ? t("You have errors in your form. The invalid fields are marked.") : action.result.feedback
									);	
								},
								scope: this
							})
						},
						scope:this
					}]
				});
			}
			this.moveEmpForm.baseParams.from_company_id=this.company_id;
			this.moveEmpWin.show();
		},
		scope:this
	}),'->',{
		text: t("Save"),
		handler: function(){
			this.saveCompany(true);
		},
		scope: this
	}
	];
		
	var focusFirstField = function(){
		this.companyForm.form.findField('name').focus(true);
	};
	this.focus= focusFirstField.createDelegate(this);

		this.personalPanel.formAddressBooks.on({
					scope:this,
					change:function(sc, newValue, oldValue){
						var record = sc.store.getById(newValue);
						GO.customfields.disableTabs(this.tabPanel, record.data,'companyCustomfields');	
					}
				});

	GO.addressbook.CompanyDialog.superclass.constructor.call(this);
	
	this.addEvents({'save' : true});
}
	
Ext.extend(GO.addressbook.CompanyDialog, GO.Window, {

	show : function(company_id, config) {
		
		var config = config || {};
		
		if(!GO.addressbook.writableAddressbooksStore.loaded) {
			
			GO.addressbook.writableAddressbooksStore.load({
				callback: function(){
					this.show(company_id, config);
				},
				scope:this
			});
		} else	if(!GO.addressbook.writableAddresslistsStore.loaded) {
			GO.addressbook.writableAddresslistsStore.load({
				callback:function(){
					this.show(company_id, config);
				},
				scope:this
			});
		} else {
			this.companyForm.form.reset();

			if(!this.rendered) {
				this.render(Ext.getBody());
			}			

			this.company_id = company_id || 0;
			this.moveEmployeesButton.setDisabled(true);
			this.tabPanel.setActiveTab(0);
			this.loadCompany(company_id, config);				
	
		}
	},	

	updateCfTabs : function(allowed_cf_categories) {},

	loadCompany : function(id, config) {
		this.beforeLoad();
		
		var params = config.values || {};
		params.id = id;
		
		this.companyForm.form.load({
			url:GO.url('addressbook/company/load'),
			params: params,
			success: function(form, action) {
				
				this.employeePanel.setCompanyId(action.result.data['id']);
				this.employeePanel.setAddressbookId(action.result.data['addressbook_id']);
				
				this.personalPanel.setCompanyId(action.result.data['id']);
				this.moveEmployeesButton.setDisabled(false);
				
				if(!GO.util.empty(action.result.data.photo_url)) {
					this.photoPanel.setPhoto(action.result.photo_url, action.result.original_photo_url);
				}
				
				if(go.Modules.isAvailable("core", "customfields"))
					GO.customfields.disableTabs(this.tabPanel, action.result);	
				
				
				GO.dialog.TabbedFormDialog.prototype.setRemoteComboTexts.call(this, action);
				
				//this.personalPanel.formAddressBooks.setRemoteText(action.result.remoteComboTexts.addressbook_id);
	

				this.afterLoad(action);

				GO.addressbook.CompanyDialog.superclass.show.call(this);
						
			},
			failure: function(form, action) {
				Ext.MessageBox.alert(t("Error"), 
					action.failureType == 'client' ? t("You have errors in your form. The invalid fields are marked.") : action.result.feedback
				);			 		
			},
			scope: this
		});			
	},
	
	afterLoad  : function(action){},
	
	beforeLoad  : function(){},
	
	saveCompany : function(hide)
	{	
		this.companyForm.form.submit({
			url:GO.url('addressbook/company/submit'),
			params:{
				id : this.company_id
			},
			waitMsg:t("Saving..."),
			success:function(form, action){
				if(action.result.id) {
					this.company_id = action.result.id;				
					this.employeePanel.setCompanyId(action.result.id);
					this.moveEmployeesButton.setDisabled(false);
				}				
				this.fireEvent('save', this, this.company_id);
				
				this.photoPanel.setPhoto(action.result.photo_url, action.result.original_photo_url);
				
				GO.dialog.TabbedFormDialog.prototype.refreshActiveDisplayPanels.call(this);

				if (hide) {
					this.hide();
				}			
			},
			failure: function(form, action) {					
				Ext.MessageBox.alert(t("Error"), 
					action.failureType == 'client' ? t("You have errors in your form. The invalid fields are marked.") : action.result.feedback
				);
			},
			scope: this
		});			
	}
	
});
