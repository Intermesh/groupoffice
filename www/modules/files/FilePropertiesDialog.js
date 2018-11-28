/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: FilePropertiesDialog.js 22441 2018-03-01 11:13:36Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.files.FilePropertiesDialog = function(config){	
	
	if(!config)
		config={};

	this.goDialogId='file';
	
	this.contentExpireDate = new Ext.form.DateField({
		name : 'content_expire_date',
		width : 140,
		format : GO.settings['date_format'],
		allowBlank : true
	});
	
	this.clearExpireDateButton = new Ext.Button({
		text:GO.files.lang.clear,
		listeners: {
			click: function() {
				this.contentExpireDate.setValue(null);
			},
			scope:this
		}
	});
	
	this.propertiesPanel = new Ext.Panel({
		layout:'form',
		title:GO.lang['strProperties'],
		cls:'go-form-panel',
		waitMsgTarget:true,
		labelWidth: 120,
		defaultType: 'textfield',
		items: [
		{
			xtype: 'compositefield',
			anchor: '100%',
			items: [this.nameField = new Ext.form.TextField({
				fieldLabel: GO.lang['strName'],
				name: 'name',
				flex: 1,
				validator:function(v){
					return !v.match(/[\/\*\"<>|\\]/);
				}
			}),{
				xtype: 'textfield',
				fieldLabel: GO.lang.strExtension,
				name: 'extension',
				width: 45
			}]
		},{
			xtype: 'plainfield',
			fieldLabel: GO.lang.strLocation,
			name: 'path'
		},
		new GO.form.HtmlComponent({
			html:'<hr />'
		}),
		{
			xtype: 'plainfield',
			fieldLabel: GO.lang.strCtime,
			name: 'ctime'
		},
		{
			xtype: 'plainfield',
			fieldLabel: GO.lang.strMtime,
			name: 'mtime'
		},
		{
			xtype: 'plainfield',
			fieldLabel: GO.lang.strUser,
			name: 'username'
		},
		{
			xtype: 'plainfield',
			fieldLabel: GO.lang.mUser,
			name: 'musername'
		},{
			xtype: 'plainfield',
			fieldLabel: GO.files.lang.lockedBy,
			name: 'locked_user_name'
		},
		new GO.form.HtmlComponent({
			html:'<hr />'
		}),
		{
			xtype: 'plainfield',
			fieldLabel: GO.lang.strType,
			name: 'type'
		},
		{
			xtype: 'plainfield',
			fieldLabel: GO.lang.strSize,
			name: 'size'
		},this.selectHandler = new GO.form.ComboBoxReset({
			xtype:'comboboxreset',
			emptyText:GO.lang.strDefault,
			store:new GO.data.JsonStore({
				url:GO.url('files/file/handlers'),
				fields:['name','cls','handler','iconCls','extension'],
				baseParams:{
					id:0,
					all:1
				}
			}),
			displayField:'name',
			valueField:'cls',
			mode:'remote',
			triggerAction:'all',
			hiddenName:'handlerCls',
			fieldLabel:GO.files.lang.openWith
		}),
		new GO.form.HtmlComponent({
			html:'<hr />'
		}),{
			xtype: 'compositefield',
			border: false,
			anchor: '100%',
			fieldLabel: GO.files.lang.contentExpiresAt,
			items: [
				this.contentExpireDate,
				this.clearExpireDateButton
			]
		}
	]
	});
		
	this.commentsPanel = new Ext.Panel({
		layout:'form',
		labelWidth: 70,
		title: GO.files.lang.comments,
		border:false,
		items: new Ext.form.TextArea({
			name: 'comment',
			fieldLabel: '',
			hideLabel: true,
			anchor:'100% 100%'
		})
		
	});
	
	this.versionsGrid = new GO.files.VersionsGrid();
	
	var items = [this.propertiesPanel, this.commentsPanel, this.versionsGrid];

//	
//	if(GO.workflow)
//	{
//		this.workflowPanel = new GO.workflow.FilePropertiesPanel();
//		items.push(this.workflowPanel);
//	}


	if(GO.customfields && GO.customfields.types["GO\\Files\\Model\\File"])
	{
		for(var i=0;i<GO.customfields.types["GO\\Files\\Model\\File"].panels.length;i++)
		{
			items.push(GO.customfields.types["GO\\Files\\Model\\File"].panels[i]);
		}
	}

	
	this.tabPanel =new Ext.TabPanel({
		activeTab: 0,
		deferredRender:false,
		doLayoutOnTabChange:true,
		enableTabScroll:true,
		border:false,
		anchor:'100% 100%',
		hideLabel:true,
		items:items
	});
		
	this.formPanel = new Ext.form.FormPanel(
	{
		waitMsgTarget:true,
		border:false,
		defaultType: 'textfield',
		items:this.tabPanel
	});

		
	GO.files.FilePropertiesDialog.superclass.constructor.call(this,{
		title:GO.lang['strProperties'],
		layout:'fit',
		width:650,
		height:550,
		closeAction:'hide',
		items:this.formPanel,
		maximizable:true,
		collapsible:true,
		buttons:[
		{
			text:GO.lang['cmdOk'],
			handler: function(){
				this.save(true)
				},
			scope: this
		},
		{
			text:GO.lang['cmdApply'],
			handler: function(){
				this.save(false)
				},
			scope: this
		},
		{
			text:GO.lang['cmdClose'],
			handler: function(){
				this.hide()
				},
			scope: this
		},
		{
			text:t("Apply"),
			handler: function(){
				this.save(false)
				},
			scope: this
		}]
	});
	
	this.addEvents({
		'rename' : true,
		'save':true
	});
}

Ext.extend(GO.files.FilePropertiesDialog, GO.Window, {
	folder_id : 0,
	show : function(file_id, config)
	{
		config = config || {};
		
		this.setFileID(file_id);
		
		if(!this.rendered)
			this.render(Ext.getBody());
			
		this.formPanel.form.reset();
		this.tabPanel.setActiveTab(0);
		
		var params = {
			id: file_id
		};
			
		if(config.loadParams)
		{
			Ext.apply(params, config.loadParams);
		}
		
		
		
		this.formPanel.form.load({
			url: GO.url("files/file/load"), 
			params: params,			
			success: function(form, action) {				
				this.setWritePermission(action.result.data.write_permission);		

				this.fireEvent('fileCommentsEdit',this);
				
				if(action.result.data.id)
				{
					this.setFileID(action.result.data.id);
				}
				
				this.folder_id=action.result.data.folder_id;
				
				if(GO.customfields)
					GO.customfields.disableTabs(this.tabPanel, action.result);	
				
				
				this.selectHandler.store.baseParams.id=action.result.data.id;
				this.selectHandler.clearLastSearch();
				this.selectHandler.setRemoteText(action.result.data.handlerName);
				
				
				GO.files.FilePropertiesDialog.superclass.show.call(this);
			},
			failure: function(form, action) {
				Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
			},
			scope: this
		});
	},
	
	setFileID : function(file_id)
	{
		this.file_id = file_id;
		this.versionsGrid.setFileID(file_id);
		//this.linkBrowseButton.setDisabled(file_id < 1);
	},
	
	setWritePermission : function(writePermission)
	{
		var form = this.formPanel.form;
		this.nameField.setDisabled(!writePermission);
	},
	
	save : function(hide)
	{
		this.formPanel.form.submit({
						
			url: GO.url("files/file/submit"),
			params: {
				id: this.file_id
			},
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action){
				if(action.result.path)
				{
					this.formPanel.form.findField('path').setValue(action.result.path);
					this.fireEvent('rename', this, this.folder_id);					
				}
				
				this.fireEvent('save', this, this.file_id, this.folder_id);
				
				GO.dialog.TabbedFormDialog.prototype.refreshActiveDisplayPanels.call(this);

				if(hide)
				{
					this.hide();
				}
			},	
			failure: function(form, action) {
				var error = '';
				if(action.failureType=='client')
				{
					error = GO.lang['strErrorsInForm'];
				}else
				{
					error = action.result.feedback;
				}
				
				Ext.MessageBox.alert(GO.lang['strError'], error);
			},
			scope:this			
		});			
	}
	
	
});
