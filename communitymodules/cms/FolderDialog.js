/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: FolderDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.FolderDialog = function(config){
	
	
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
	config.resizable=false;
	config.width=400;
	config.height=500;
	config.closeAction='hide';
	config.title= GO.cms.lang.folderProperties;
	config.items= this.formPanel;
	config.focus= focusFirstField.createDelegate(this);
	config.buttons=[{
			text: GO.lang['cmdOk'],
			handler: function(){
				this.submitForm(true);
			},
			scope: this
		},{
			text: GO.lang['cmdClose'],
			handler: function(){
				this.hide();
			},
			scope:this
		}					
	];
	
	GO.cms.FolderDialog.superclass.constructor.call(this, config);
	this.addEvents({'save' : true});	
}
Ext.extend(GO.cms.FolderDialog, Ext.Window,{
	
	nodeAttributes : [],
	
	show : function (folder_id, parent_id, site_id) {
		if(!this.rendered)
		{
			this.render(Ext.getBody());
		}
		
		this.tabPanel.setActiveTab(0);
		
		
		if(parent_id)
		{
			this.formPanel.baseParams.parent_id=parent_id;
		}else
		{
			delete this.formPanel.baseParams.parent_id;
		}
		
		if(site_id)
		{
			this.formPanel.baseParams.site_id=site_id;
		}else
		{
			delete this.formPanel.baseParams.site_id;
		}
		
		if(!folder_id)
		{
			folder_id=0;			
		}
			
		this.setFolderId(folder_id);
		
		if(this.folder_id==0)
		{
			this.formPanel.form.reset();
			this.writePermissionsTab.setAcl(0);
		}
		
		//if(this.folder_id>0)
		//{
			this.formPanel.load({
				url : GO.settings.modules.cms.url+'json.php',
				
				success:function(form, action)
				{
					
					this.formPanel.baseParams.parent_id=action.result.data.parent_id;
					this.writePermissionsTab.setAcl(action.result.data.acl);
					
					this.optionsPanel.loadConfig(
							action.result.data.config, 
							action.result.data.option_values,
							action.result.data.type,
							action.result.data.default_template);
					
					GO.cms.FolderDialog.superclass.show.call(this);
				},
				failure:function(form, action)
				{
					GO.errorDialog.show(action.result.feedback)
				},
				scope: this
				
			});
		/*}else 
		{
			
			this.formPanel.form.reset();
			this.writePermissionsTab.setAcl(0);
			GO.cms.FolderDialog.superclass.show.call(this);
		}*/
	},
	
	
		
		
	
	
	setFolderId : function(folder_id)
	{
		this.formPanel.form.baseParams['folder_id']=folder_id;
		this.folder_id=folder_id;
		
	},
	
	submitForm : function(hide){
		this.formPanel.form.submit(
		{
			url:GO.settings.modules.cms.url+'action.php',
			params: {'task' : 'save_folder'},
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action){
				
				if(action.result.folder_id)
				{
					this.setFolderId(action.result.folder_id);
				}
				if(hide)
				{
					this.hide();	
				}else
				{				
					if(action.result.folder_id)
					{
						this.writePermissionsTab.setAcl(action.result.acl);
					}
				}				
				this.fireEvent('save',this.folder_id, this.formPanel.form.getValues(), this.formPanel.baseParams.parent_id);									
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
	
	
	buildForm : function () {
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',waitMsgTarget:true,			
			layout:'form',
			autoScroll:true,
			items:[{
				xtype: 'textfield',
			  name: 'name',
				anchor: '-20',
			  allowBlank:false,
			  fieldLabel: GO.lang.strName
			},{
				xtype: 'checkbox',
			  name: 'disabled',
				anchor: '-20',
			  allowBlank:false,
			  boxLabel: GO.cms.lang.disabled,
			  hideLabel: true
			},{
				xtype: 'checkbox',
			  name: 'authentication',
				anchor: '-20',
			  allowBlank:false,
			  boxLabel: GO.cms.lang.authentication,
			  hideLabel: true
			},{
				xtype: 'checkbox',
			  name: 'feed',
				anchor: '-20',
			  allowBlank:false,
			  boxLabel: GO.cms.lang.feed,
			  hideLabel: true
			},
			new Ext.form.FieldSet({
				title: GO.cms.lang.defaultTemplateOptions,
				autoHeight:true,
				items:[this.optionsPanel = new GO.cms.TemplateOptionsPanel({
					isFolder : true
					})]
			})]
				
		});
		var items  = [this.propertiesPanel];
		
    
    this.writePermissionsTab = new GO.grid.PermissionsPanel({
			title: GO.lang['strWritePermissions']
		});
    
    items.push(this.writePermissionsTab);
    
		
		
		
 
    this.tabPanel = new Ext.TabPanel({
      activeTab: 0,      
      deferredRender: false,
    	border: false,
      items: items,
      anchor: '100% 100%'
    }) ;    
    
    
    this.formPanel = new Ext.form.FormPanel({
    	waitMsgTarget:true,
			url: GO.settings.modules.cms.url+'action.php',
			border: false,
			baseParams: {task: 'folder'},				
			items:this.tabPanel				
		});
    
    
	}
});