/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: FilePropertiesDialog.js 22467 2018-03-07 08:42:50Z mschering $
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
		text:t("Clear", "files"),
		listeners: {
			click: function() {
				this.contentExpireDate.setValue(null);
			},
			scope:this
		}
	});
	
	this.propertiesPanel = new Ext.Panel({
		layout:'form',
		title:t("Properties"),
		waitMsgTarget:true,
		labelWidth: dp(128),
		defaultType: 'textfield',
		autoScroll: true,
		items: [
			{xtype:'fieldset', 
				items:[
				{
				xtype: 'compositefield',
				anchor: '100%',
				items: [this.nameField = new Ext.form.TextField({
					fieldLabel: t("Name"),
					name: 'name',
					flex: 1,
					validator:function(v){
						return !v.match(/[\/\*\"<>|\\]/);
					}
				}),{
					xtype: 'textfield',
					fieldLabel: t("Extension"),
					name: 'extension',
					width: 45
				}]
			}					
		,
		this.pathField = new Ext.form.DisplayField({
				fieldLabel: t("Location"),
				name: 'path'
			})
		]},
		{xtype:'fieldset', items:[{
		
			xtype: 'plainfield',
			fieldLabel: t("Created at"),
			name: 'ctime'
		},
		{
			xtype: 'plainfield',
			fieldLabel: t("Modified at"),
			name: 'mtime'
		},
		{
			xtype: 'plainfield',
			fieldLabel: t("User"),
			name: 'username'
		},
		{
			xtype: 'plainfield',
			fieldLabel: t("Modified by"),
			name: 'musername'
		},{
			xtype: 'plainfield',
			fieldLabel: t("Locked by", "files"),
			name: 'locked_user_name'
		}]}
		,
		{xtype:'fieldset', items:[
			{
				xtype: 'plainfield',
				fieldLabel: t("Type"),
				name: 'type'
			},
			{
				xtype: 'plainfield',
				fieldLabel: t("Size"),
				name: 'size'
			},this.selectHandler = new GO.form.ComboBoxReset({
				xtype:'comboboxreset',
				emptyText:t("Default"),
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
				fieldLabel:t("Open with", "files")
			})
		]},
		{xtype:'fieldset', items:[		
			{
				xtype: 'compositefield',
				border: false,
				anchor: '100%',
				fieldLabel: t("Content expires at", "files"),
				items: [
					this.contentExpireDate,
					this.clearExpireDateButton
				]
			}
		]}
	]
	});
		
	this.commentsPanel = new Ext.Panel({
		layout:'form',
		labelWidth: 70,
		title: t("Comments", "files"),
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
	
	go.customfields.CustomFields.getFormFieldSets("File").forEach(function(fs) {
		//console.log(fs);
		if(fs.fieldSet.isTab) {
			fs.title = null;
			fs.collapsible = false;
			var pnl = new Ext.Panel({
				autoScroll: true,
				hideMode: 'offsets', //Other wise some form elements like date pickers render incorrectly.
				title: fs.fieldSet.name,
				items: [fs]
			});
			this.tabPanel.add(pnl);
		}else
		{			
			this.propertiesPanel.add(fs);
		}
	}, this);
		
	this.formPanel = new Ext.form.FormPanel(
	{
		waitMsgTarget:true,
		border:false,
		defaultType: 'textfield',
		items:this.tabPanel
	});

		
	GO.files.FilePropertiesDialog.superclass.constructor.call(this,{
		title:t("Properties"),
		layout:'fit',
		width:dp(700),
		height:dp(700),
		closeAction:'hide',
		items:this.formPanel,
		maximizable:true,
		collapsible:true,
		buttons:[
		{
			text:t("Save"),
			handler: function(){
				this.save(true)
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
			
				
				this.selectHandler.store.baseParams.id=action.result.data.id;
				this.selectHandler.clearLastSearch();
				this.selectHandler.setRemoteText(action.result.data.handlerName);
				
				
				GO.files.FilePropertiesDialog.superclass.show.call(this);
			},
			failure: function(form, action) {
				Ext.MessageBox.alert(t("Error"), action.result.feedback);
			},
			scope: this
		});
	},
	
	setFileID : function(file_id) {
		
		this.file_id = file_id;
		this.versionsGrid.setFileID(file_id);
	},
	
	setWritePermission : function(writePermission) {		
		this.nameField.setDisabled(!writePermission);
	},
	
	save : function(hide) {
		
		this.formPanel.form.submit({
						
			url: GO.url("files/file/submit"),
			params: {
				id: this.file_id
			},
			waitMsg:t("Saving..."),
			success:function(form, action){
				if(action.result.path)
				{
					this.pathField.setValue(action.result.path);
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
					error = t("You have errors in your form. The invalid fields are marked.");
				}else
				{
					error = action.result.feedback;
				}
				
				Ext.MessageBox.alert(t("Error"), error);
			},
			scope:this			
		});			
	}
	
	
});
