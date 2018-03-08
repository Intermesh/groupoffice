/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SiteDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.SiteDialog = function(config){
	
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
	config.width=500;
	config.height=400;
	config.closeAction='hide';
	config.title= GO.cms.lang.site;					
	config.items= this.formPanel;
	config.focus= focusFirstField.createDelegate(this);
	config.buttons=[
//	{
//		text: GO.lang['cmdOk'],
//		handler: function(){
//			this.submitForm(true);
//		},
//		scope: this
//	},{
//		text: GO.lang['cmdApply'],
//		handler: function(){
//			this.submitForm();
//		},
//		scope:this
//	},
	{
		text: GO.lang['cmdClose'],
		handler: function(){
			this.hide();
		},
		scope:this
	}
	];
	
	GO.cms.SiteDialog.superclass.constructor.call(this, config);
	
	this.addEvents({
		'save' : true
	});
}
Ext.extend(GO.cms.SiteDialog, Ext.Window,{
	
	show : function (site_id) {
		if(!this.rendered)
		{
			this.render(Ext.getBody());
		}
		
		this.tabPanel.setActiveTab(0);		
		
		if(!site_id)
		{
			site_id=0;			
		}
			
		this.setSiteId(site_id);
		
		if(this.site_id>0)
		{
			this.formPanel.load({
				url : GO.settings.modules.cms.url+'json.php',
				
				success:function(form, action)
				{
					this.writePermissionsTab.setAcl(action.result.data.acl_write);
					this.writePermissionsTab.aclUsersGrid.store.load();
					this.writePermissionsTab.aclGroupsGrid.store.load();
					GO.cms.SiteDialog.superclass.show.call(this);
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
			this.writePermissionsTab.setAcl(0);
			
			GO.cms.SiteDialog.superclass.show.call(this);
		}
	},
	
	setSiteId : function(site_id)
	{
		this.formPanel.form.baseParams['site_id']=site_id;
		this.site_id=site_id;
		GO.cms.foldersDialog.site_id=site_id;
	},
	
	submitForm : function(hide){
		this.formPanel.form.submit(
		{
			url:GO.settings.modules.cms.url+'action.php',
			params: {
				'task' : 'save_site'
			},
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action){
				
				this.fireEvent('save', this);
				
				if(hide)
				{
					this.hide();	
				}else
				{
				
					if(action.result.site_id)
					{
						this.setSiteId(action.result.site_id);

						this.writePermissionsTab.setAcl(action.result.acl_write);
											
					}
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
	
	
	buildForm : function () {
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',
			waitMsgTarget:true,
			layout:'form',
			autoScroll:true,
			items:[{
				xtype: 'textfield',
				name: 'name',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: GO.lang.strName
			},{
				xtype: 'textfield',
				name: 'domain',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: GO.cms.lang.domain
			},{
				xtype: 'textfield',
				name: 'webmaster',
				anchor: '-20',
				allowBlank:false,
				fieldLabel: GO.cms.lang.webmaster
			},new Ext.form.ComboBox({
				fieldLabel: GO.cms.lang.template,
				hiddenName:'template',
				store: new GO.data.JsonStore({
					fields: ['name'],
					url: GO.settings.modules.cms.url+'json.php',
					root: 'results',
					baseParams: {
						'task':'templates'
					}
				}),
				valueField:'name',
				displayField:'name',
				mode: 'remote',
				triggerAction: 'all',
				editable: false,
				forceSelection: true,
				anchor:'-20',
				allowBlank:false
			}),
			new Ext.form.ComboBox({
				fieldLabel: GO.lang.strLanguage,
				name: 'language',
				store:  new Ext.data.SimpleStore({
					fields: ['id', 'language'],
					data : GO.Languages
				}),
				displayField:'language',
				valueField: 'id',
				hiddenName:'language',
				mode:'local',
				triggerAction:'all',
				editable: false,
				selectOnFocus:true,
				forceSelection: true,
				value: GO.settings.language,
				anchor:'-20'
			}),{
				xtype:'checkbox',
				name:'enable_categories',
				hideLabel:true,
				boxLabel:GO.cms.lang.enableCategories
			},{
				xtype:'checkbox',
				name:'enable_rewrite',
				hideLabel:true,
				boxLabel:GO.cms.lang.enableRewrite
			},{
				xtype:'textfield',
				name:'rewrite_base',
				value:'/',
				anchor:'-20',
				fieldLabel:GO.cms.lang.rewriteBase
			},
			{
				xtype: 'plainfield',
				fieldLabel: GO.cms.lang.siteId,
				name: 'id'
			}]
				
		});
		var items  = [this.propertiesPanel];
		
		this.writePermissionsTab = new GO.grid.PermissionsPanel({
			title: GO.lang['strWritePermissions']
		});
    
		items.push(this.writePermissionsTab);

		GO.cms.writingUsersPanel = this.writingUsersPanel = new GO.cms.WritingUsersPanel(
		{
			'permissionsTab':this.writePermissionsTab
		});

		items.push(GO.cms.writingUsersPanel);

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
			baseParams: {
				task: 'site'
			},
			items:this.tabPanel				
		});
    
    
	}
});

//GO.cms.SiteDialog = Ext.extend(GO.dialog.TabbedFormDialog,{
//	
//	initComponent : function(){	
//		
//		Ext.apply(this, {
//			titleField: 'name',
//			title: GO.cms.lang.site,
//			formControllerUrl: 'cms/site',
//			collapsible:true,
//			layout:'fit',
//			modal:false,
//			resizable:false,
//			maximizable:true,
//			width:500,
//			height:400,
//			closeAction:'hide'
//		});
//		
//		GO.cms.SiteDialog.superclass.initComponent.call(this);
//		
//		this.addEvents({
//			'save' : true
//		});
//	},
//	
//	buildForm : function () {
//		this.propertiesPanel = new Ext.Panel({
//			title:GO.lang['strProperties'],			
//			cls:'go-form-panel',
//			waitMsgTarget:true,
//			layout:'form',
//			autoScroll:true,
//			items:[{
//				xtype: 'textfield',
//				name: 'name',
//				anchor: '-20',
//				allowBlank:false,
//				fieldLabel: GO.lang.strName
//			},{
//				xtype: 'textfield',
//				name: 'domain',
//				anchor: '-20',
//				allowBlank:false,
//				fieldLabel: GO.cms.lang.domain
//			},{
//				xtype: 'textfield',
//				name: 'webmaster',
//				anchor: '-20',
//				allowBlank:false,
//				fieldLabel: GO.cms.lang.webmaster
//			},new Ext.form.ComboBox({
//				fieldLabel: GO.cms.lang.template,
//				hiddenName:'template',
//				store: new GO.data.JsonStore({
//					fields: ['name'],
//					url: GO.settings.modules.cms.url+'json.php',
//					root: 'results',
//					baseParams: {
//						'task':'templates'
//					}
//				}),
//				valueField:'name',
//				displayField:'name',
//				mode: 'remote',
//				triggerAction: 'all',
//				editable: false,
//				forceSelection: true,
//				anchor:'-20',
//				allowBlank:false
//			}),
//			new Ext.form.ComboBox({
//				fieldLabel: GO.lang.strLanguage,
//				name: 'language',
//				store:  new Ext.data.SimpleStore({
//					fields: ['id', 'language'],
//					data : GO.Languages
//				}),
//				displayField:'language',
//				valueField: 'id',
//				hiddenName:'language',
//				mode:'local',
//				triggerAction:'all',
//				editable: false,
//				selectOnFocus:true,
//				forceSelection: true,
//				value: GO.settings.language,
//				anchor:'-20'
//			}),{
//				xtype:'checkbox',
//				name:'enable_categories',
//				hideLabel:true,
//				boxLabel:GO.cms.lang.enableCategories
//			},{
//				xtype:'checkbox',
//				name:'enable_rewrite',
//				hideLabel:true,
//				boxLabel:GO.cms.lang.enableRewrite
//			},{
//				xtype:'textfield',
//				name:'rewrite_base',
//				value:'/',
//				anchor:'-20',
//				fieldLabel:GO.cms.lang.rewriteBase
//			},
//			{
//				xtype: 'plainfield',
//				fieldLabel: GO.cms.lang.siteId,
//				name: 'id'
//			}]
//				
//		});
//		this.addPanel(this.propertiesPanel);
//		    
//		this.addPermissionsPanel(new GO.grid.PermissionsPanel({
//			title: GO.lang['strWritePermissions']
//		}));
//
//		GO.cms.writingUsersPanel = this.writingUsersPanel = new GO.cms.WritingUsersPanel(
//		{
//			'permissionsTab':this.permissionsPanel
//		});  
//    
//	},
//	
//	setRemoteModelId : function(remoteModelId)
//	{
//		GO.cms.SiteDialog.superclass.setRemoteModelId.call(this,remoteModelId);
////		this.site_id=site_id;
//		GO.cms.foldersDialog.site_id=remoteModelId;
//	}
//	
//});