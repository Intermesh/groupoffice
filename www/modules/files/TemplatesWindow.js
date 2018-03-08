/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: TemplatesWindow.js 15954 2013-10-17 12:04:36Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.files.TemplateWindow = function(config){	
	this.gridStore = new GO.data.JsonStore({
		url: GO.url('files/template/store'),
		baseParams: {
			'permissionLevel': GO.permissionLevels.write
		},
		fields:['id','name', 'type', 'extension'],
		remoteSort:true
	});
	
	this.gridStore.on('load', function(){
		this.firstLoad=false;
	}, this, {
		single:true
	});
	
	this.gridStore.load();	
	
	this.gridPanel = new GO.grid.GridPanel( {
		region:'center',
		layout:'fit',
		split:true,
		paging:true,
		store: this.gridStore,
		columns:[{
			header:GO.lang['strName'],
			dataIndex: 'name',
			renderer:function(v, metaData,record){
				return '<div class="go-grid-icon filetype filetype-'+record.get("extension")+'">'+v+'</div>';
			},
			sortable:true
		},{
			header:GO.lang.strType,
			dataIndex: 'type',
			sortable:false
		}],
		view:new  Ext.grid.GridView({
			autoFill:true,
			forceFit:true
		}),
		sm: new Ext.grid.RowSelectionModel(),
		loadMask: true	,
		tbar: [{
			iconCls: 'btn-add',
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			scope: this,
			handler:function(){
				this.showTemplate();
			}
		},{
			iconCls: 'btn-delete',
			text: GO.lang['cmdDelete'],
			cls: 'x-btn-text-icon',
			scope: this,
			handler:function(){
				this.gridPanel.deleteSelected();
			}
		}]
	});
		
	this.gridPanel.on('rowdblclick', function(grid){
		this.showTemplate(grid.selModel.selections.keys[0]);
	}, this);	
	
	GO.files.TemplateWindow.superclass.constructor.call(this,{
		title:GO.files.lang.manageTemplates,
		layout:'fit',
		width:500,
		height:600,
		closeAction:'hide',
		items:this.gridPanel,
		buttons:[
		{
			text:GO.lang['cmdClose'],
			handler: function(){
				this.hide()
				},
			scope: this
		}]
	});
}

Ext.extend(GO.files.TemplateWindow,Ext.Window, {
	
	firstLoad : true,
	
	showTemplate : function(template_id)
	{								
		if(!this.templateDialog)
		{			
			this.uploadFile = new GO.form.UploadFile({
				max: 1
			});
			
			this.downloadButton = new Ext.Button({
				handler: function(){
					document.location.href = GO.url('files/template/download&id='+this.template_id);
				},
				disabled: true,
				text: GO.files.lang.downloadTemplate,
				scope: this
			});				
			
			this.formPanel = new Ext.form.FormPanel({
				title: GO.lang['strProperties'],
				cls:'go-form-panel',
				waitMsgTarget:true,
				labelWidth: 85,
				defaultType: 'textfield',
				fileUpload: true,
				items:[
				{
					fieldLabel: GO.lang['strName'],
					name: 'name',
					id: 'template-name',
					anchor: '100%',
					allowBlank: false
				},		     
				
				new GO.form.HtmlComponent({
					html: '<br />'
				}),
				this.uploadFile,
				new GO.form.HtmlComponent({
					html: '<br />'
				}),
				this.downloadButton
				]
			});
			
			var buttons = [			
			{
				text: GO.lang['cmdOk'],
				handler: function(){
					this.saveTemplate(true)
					},
				scope: this
			},

			{
				text: GO.lang['cmdApply'],
				handler: function(){
					this.saveTemplate(false)
					},
				scope: this
			},

			{
				text: GO.lang['cmdClose'],
				handler:
				function()
				{
					this.templateDialog.hide();

				},
				scope: this
			}
			];				
			
			this.templateDialog = new Ext.Window({
				layout: 'fit',
				modal:false,
				height: 400,
				width: 400,
				closeAction: 'hide',
				title: GO.files.lang.template,
				items: [this.templateTabPanel = new Ext.TabPanel({
					activeTab: 0,
					border:false,
					items:[
					this.formPanel,
					this.readPermissionsTab = new GO.grid.PermissionsPanel({
									
						})						
					]
				})],
				buttons: buttons,
				focus: function(){
					Ext.get('template-name').focus();
				}									
			});								
		}
		
		this.template_id=template_id;
		
		this.templateTabPanel.setActiveTab(0);				
		
		if(this.template_id > 0)
		{
			//update
			this.readPermissionsTab.setDisabled(false);
			
			this.loadTemplate();				 			
		} else {
			// insert
			
			this.formPanel.form.reset();
			this.readPermissionsTab.setAcl(0);
			this.downloadButton.setDisabled(true);						
		}
		
		this.templateDialog.show();
	},
	loadTemplate : function()
	{
		this.formPanel.form.load({
			url: GO.url('files/template/load'), 
			params: {
				id: this.template_id
			},
			
			success: function(form, action) {
				//this.selectUser.setRemoteText(action.result.data.user_name);
				this.readPermissionsTab.setAcl(action.result.data.acl_id);
				this.downloadButton.setDisabled(false);										
			},
			scope: this
		});
	},
	
	saveTemplate : function(hide)
	{
		this.formPanel.form.submit({
			waitMsg:GO.lang.waitMsgSave,
			url:GO.url('files/template/submit'),
			params:
			{
				id: this.template_id
			},
			success:function(form, action){
				this.template_id = action.result.id;
				this.gridStore.reload();

				this.uploadFile.clearQueue();
				
				if(this.template_id && !hide)
				{
					this.readPermissionsTab.setAcl(action.result.acl_id);			
				}					
				
				if(hide)
				{
					this.templateDialog.hide();
				}					
			},
			failure: function(form, action) {					
				
				if(action.failureType != 'client')
				{					
					Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);			
				}
			},
			scope: this				
		});
	}	
});