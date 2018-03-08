/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: CompanyDialog.js 20952 2017-03-20 07:59:18Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.CompanyDialog = function(config)
{
	Ext.apply(this, config);

	this.goDialogId = 'company';
	
	this.personalPanel = new GO.addressbook.CompanyProfilePanel();	    
		    
	
				
	GO.addressbook.CompanyPhoto = Ext.extend(Ext.BoxComponent, {
		autoEl : {
				tag: 'img',
				cls:'ab-photo',
				src:Ext.BLANK_IMAGE_URL
			},
	
		setPhotoSrc : function(url)
		{
			if (this.el)
				this.el.set({
					src: GO.util.empty(url) ? Ext.BLANK_IMAGE_URL : url
				});
			this.setVisible(true);
		}
	});

	this.companyPhoto = new GO.addressbook.CompanyPhoto();

	this.deleteImageCB = new Ext.form.Checkbox({
		boxLabel: GO.addressbook.lang.deleteImage,
		labelSeparator: '',
		name: 'delete_photo',
		allowBlank: true,
		hideLabel:true,
		disabled:true
	});

	this.uploadFile = new GO.form.UploadFile({
		inputName : 'image',
		max: 1
	})

	this.fullImageButton = new Ext.Button({
			text:GO.addressbook.lang.downloadFullImage,
			disabled:false,
			handler:function(){
				window.open(this.originalPhotoUrl,'_blank');
			},
			scope:this
		});

	this.photoPanel = new Ext.Panel({
		title : GO.addressbook.lang.photo,
		layout: 'form',
		border:false,
		cls : 'go-form-panel',		
		autoScroll:true,
		labelAlign:'top',
		items:[	{
				style:'margin-bottom:15px',
				xtype:'button',
				text:GO.addressbook.lang.searchForImages,
				scope:this,
				handler:function(){
					var f= this.companyForm.form;
					var n2 = f.findField('name2').getValue();
					
					if(n2)
						n2 = ' '+n2;
					else
						n2 = '';
					
					var name = f.findField('name').getValue()+n2;
					var sUrl = 'http://www.google.com/search?tbm=isch&q="'+encodeURIComponent(name)+'"';
					window.open(sUrl);
				}
			},
			{
				
				xtype:'textfield',
				fieldLabel:GO.addressbook.lang.downloadPhotoUrl,
				name:'download_photo_url',
				anchor:'100%'
			},{
				style:'margin-top:15px;margin-bottom:10px;',
				html:GO.addressbook.lang.orBrowseComputer+':',
				xtype:'htmlcomponent'
			},
			this.uploadFile,
			{
				style:'margin-top:15px',
				html:GO.addressbook.lang.currentImage+':',
				xtype:'htmlcomponent'
			},
			this.companyPhoto,
			this.deleteImageCB,
			this.fullImageButton
		]
	});
	
				
				
	this.commentPanel = new Ext.Panel({
		title: GO.addressbook.lang['cmdPanelComments'], 
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
	this.commentPanel];

	// Remove the original comment panel if it is set in the settings of the user.
	if(GO.comments && GO.comments.hideOriginalTab('company')){
		items.pop();
	}		
					
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
	
	if(GO.comments){
		this.commentsGrid = new GO.comments.CommentsGrid({title:GO.comments.lang.comments});
		items.push(this.commentsGrid);
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
    


	this.id= 'addressbook-window-new-company';
	this.layout= 'fit';
	this.modal= false;
	this.shadow= false;
	this.border= false;
	this.height= 640;
	this.width= 820;
	this.plain= true;
	this.closeAction= 'hide';
	this.collapsible=true;
	this.title= GO.addressbook.lang['cmdCompanyDialog'];
	this.items= this.companyForm;
	this.buttons=  [
	{
		text: GO.lang['cmdOk'],
		handler: function(){
			this.saveCompany(true);
		},
		scope: this
	},
	/*{
		text: GO.lang['cmdApply'],
		handler: function(){
			this.saveCompany();
		},
		scope: this
	},*/
	{
		text: GO.lang['cmdClose'],
		handler: function()
		{
			this.hide();
		},
		scope: this
	}
	];
		
	var focusFirstField = function(){
		this.companyForm.form.findField('name').focus(true);
	};
	this.focus= focusFirstField.createDelegate(this);



	this.tbar = [this.moveEmployeesButton = new Ext.Button({
		text:GO.addressbook.lang.moveEmployees,
		handler:function(){
			if(!this.moveEmpWin){

				this.moveEmpForm = new Ext.FormPanel({
					cls:'go-form-panel',
//					url:GO.settings.modules.addressbook.url+'action.php',
					url: GO.url('addressbook/company/moveEmployees'),
					baseParams:{
//						task:'move_employees',
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
					title:GO.addressbook.lang.moveEmployees,
					closable:true,
					modal:true,
					width:400,
					autoHeight:true,
					items:this.moveEmpForm,
					buttons:[{
						text:GO.lang.cmdOk,
						handler:function(){
							this.moveEmpForm.form.submit({
								waitMsg:GO.lang['waitMsgSave'],
								success:function(form, action){
									this.moveEmpWin.hide();
								},
								failure: function(form, action) {

									if(action.failureType == 'client')
									{
										Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);
									} else {
										Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
									}
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
	})];

		this.personalPanel.formAddressBooks.on({
					scope:this,
					change:function(sc, newValue, oldValue){
						var record = sc.store.getById(newValue);
						GO.customfields.disableTabs(this.tabPanel, record.data,'companyCustomfields');	
					}
				});

	GO.addressbook.CompanyDialog.superclass.constructor.call(this);
	
	this.addEvents({
		'save':true
	});
	
//	if (GO.customfields) {
//		this.personalPanel.formAddressBooks.on('select',function(combo,record,index){
//			var allowed_cf_categories = record.data.allowed_cf_categories.split(',');
//			this.updateCfTabs(allowed_cf_categories);
//		},this);
//		this.companyForm.form.on('actioncomplete',function(form, action){
//			if(action.type=='load'){
//				
//			}
//		},this);
//	}
}
	
Ext.extend(GO.addressbook.CompanyDialog, GO.Window, {

	show : function(company_id, config)
	{
		if(!GO.addressbook.writableAddressbooksStore.loaded)
		{
			GO.addressbook.writableAddressbooksStore.load(
			{
				callback: function(){
					this.show(company_id, config);
				},
				scope:this
			});
		}else	if(!GO.addressbook.writableAddresslistsStore.loaded)
		{
			GO.addressbook.writableAddresslistsStore.load({
				callback:function(){
					this.show(company_id, config);
				},
				scope:this
			});
		}else
		{
			this.companyForm.form.reset();

			
			if(!this.rendered)
			{
				this.render(Ext.getBody());
			}			
			
			if(company_id)
			{
				this.company_id = company_id;
			} else {
				this.company_id = 0;
			}	
			
			this.moveEmployeesButton.setDisabled(true);
		
			this.tabPanel.setActiveTab(0);
			
//			if(this.company_id > 0)
//			{
				this.loadCompany(company_id, config);				
//			} else {
//				this.employeePanel.setCompanyId(0);
//				var tempAddressbookID = this.personalPanel.formAddressBooks.getValue();
//				
//				this.companyForm.form.reset();
//
//				if(tempAddressbookID>0 && this.personalPanel.formAddressBooks.store.getById(tempAddressbookID))
//					this.personalPanel.formAddressBooks.setValue(tempAddressbookID);
//				else
//					this.personalPanel.formAddressBooks.selectFirst();
//				
//				this.personalPanel.setCompanyId(0);
//
//				var abRecord = this.personalPanel.formAddressBooks.store.getById(this.personalPanel.formAddressBooks.getValue());
//			
//				if (GO.customfields) {
//					var allowed_cf_categories = abRecord.data.allowed_cf_categories.split(',');
//					this.updateCfTabs(allowed_cf_categories);
//				}
//
//				GO.addressbook.CompanyDialog.superclass.show.call(this);
//			}		
		}
	},	

	updateCfTabs : function(allowed_cf_categories) {
//		for (var i=0; i<this.tabPanel.items.items.length; i++) {
//			if (typeof(this.tabPanel.items.items[i].category_id)!='undefined') {
//				this.tabPanel.hideTabStripItem(this.tabPanel.items.items[i]);
//				if(allowed_cf_categories.indexOf(this.tabPanel.items.items[i].category_id.toString())>=0)
//					this.tabPanel.unhideTabStripItem(this.tabPanel.items.items[i]);
//				else
//					this.tabPanel.hideTabStripItem(this.tabPanel.items.items[i]);
//			}
//		}
	},

	loadCompany : function(id, config)
	{
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
				
				if(!GO.util.empty(action.result.data.photo_url))
					this.setPhoto(action.result.data.photo_url);
				if(!GO.util.empty(action.result.data.original_photo_url))
					this.setOriginalPhoto(action.result.data.original_photo_url);
				
				if(GO.customfields)
					GO.customfields.disableTabs(this.tabPanel, action.result);	
				
				
				GO.dialog.TabbedFormDialog.prototype.setRemoteComboTexts.call(this, action);
				
				//this.personalPanel.formAddressBooks.setRemoteText(action.result.remoteComboTexts.addressbook_id);
	
				if(GO.comments){	
					if(action.result.data['id'] > 0){
						if (!GO.util.empty(action.result.data['action_date'])) {
							this.commentsGrid.actionDate = action.result.data['action_date'];
						} else {
							this.commentsGrid.actionDate = false;
						}
						this.commentsGrid.setLinkId(action.result.data['id'], 'GO\\Addressbook\\Model\\Company');
						this.commentsGrid.store.load();
						this.commentsGrid.setDisabled(false);
					}else {
						this.commentsGrid.setDisabled(true);
					}
				}

				this.afterLoad(action);

				GO.addressbook.CompanyDialog.superclass.show.call(this);
						
			},
			failure: function(form, action)
			{
				if(action.failureType == 'client')
				{					
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);			
				} else {
					Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
				}			 		
			},
			scope: this
		});			
	},
	
	afterLoad  : function(action){
		
	},
	
	beforeLoad  : function(){
		
	},
	
	saveCompany : function(hide)
	{	
		this.companyForm.form.submit({
			url:GO.url('addressbook/company/submit'),
			params:
			{
				id : this.company_id
			},
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action){
				if(action.result.id)
				{
					this.company_id = action.result.id;				
					this.employeePanel.setCompanyId(action.result.id);
					this.moveEmployeesButton.setDisabled(false);
				}				
				this.fireEvent('save', this, this.company_id);
				
				this.uploadFile.clearQueue();
				
				GO.dialog.TabbedFormDialog.prototype.refreshActiveDisplayPanels.call(this);
				
				if(!GO.util.empty(action.result.photo_url))
					this.setPhoto(action.result.photo_url);
				else
					this.setPhoto("");
				
				if(!GO.util.empty(action.result.original_photo_url))
					this.setOriginalPhoto(action.result.original_photo_url);
				else
					this.setOriginalPhoto("");	
				
				if (hide)
				{
					this.hide();
				}			
			},
			failure: function(form, action) {					

				if(action.failureType == 'client')
				{					
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);			
				} else {
					Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
				}
			},
			scope: this
		});			
	},
	
	setOriginalPhoto : function(url){
		this.originalPhotoUrl = url;
	},
	setPhoto : function(url)
	{
		this.companyPhoto.setPhotoSrc(url);
		this.deleteImageCB.setValue(false);
		this.deleteImageCB.setDisabled(url=='');
	}
	
});
