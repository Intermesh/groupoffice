/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: FolderPropertiesDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.files.FolderPropertiesDialog = function(config){
	
	if(!config)
		config={};

	this.goDialogId='folder';

	this.propertiesPanel = new Ext.Panel({
		layout:'form',
		title:t("Properties"),
		waitMsgTarget:true,		
		autoScroll: true,
		labelWidth:100, 
		border:false, 
		items: [{
				xtype:"fieldset",
				defaultType: 'textfield',
				labelWidth:100, 
				items: [
				{
					fieldLabel: t("Name"),
					name: 'name',
					anchor: '100%',
					validator:function(v){
						return !v.match(/[&\/:\*\?"<>|\\]/);
					}
				},{
					xtype: 'plainfield',
					fieldLabel: t("Location"),
					name: 'path'
				},
				{
					xtype: 'plainfield',
					fieldLabel: "URL",
					name: 'url'
				},
				new GO.form.HtmlComponent({
					html:'<hr />'
				}),
				{
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
					fieldLabel: t("Created by"),
					name: 'username'
				},
				{
					xtype: 'plainfield',
					fieldLabel: t("Modified by"),
					name: 'musername'
				},
				{
					xtype: 'htmlcomponent',
					html:'<hr />'
				},
				{
					xtype:'xcheckbox',
					boxLabel: t("Activate sharing", "files"),
					name: 'share',
					listeners: {
						check: function(cb, checked) {
							if(!this.suspendCheckEvent) {
								this.save(false);
							}
						},
						scope:this
					},
					checked: false,
					hideLabel:true
				},
				this.notifyCheckBox = new Ext.ux.form.XCheckbox({
					boxLabel: t("Notify me about changes in this folder", "files"),
					name: 'notify',
					checked: false,
					hideLabel:true
				}),
				this.applyStateCheckbox = new Ext.ux.form.XCheckbox({
					boxLabel: t("Apply the folder's display settings for everyone.", "files"),
					name: 'apply_state',
					checked: false,
					hideLabel:true
				})
				]
			}]
	});

	this.readPermissionsTab = new GO.grid.PermissionsPanel({
							
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
	
	this.tabPanel =new Ext.TabPanel({
		activeTab: 0,
		enableTabScroll:true,
		deferredRender:false,
		border:false,
		anchor:'100% 100%',
		hideLabel:true,
		items:[this.propertiesPanel, this.commentsPanel, this.readPermissionsTab]
	});
	
	go.customfields.CustomFields.getFormFieldSets("Folder").forEach(function(fs) {
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

//	if(go.Modules.isAvailable("core", "customfields")){
//		this.disableCategoriesPanel = new GO.customfields.DisableCategoriesPanel();
//		
//		this.recursivePanel = new Ext.Panel({
//			region:'south',
//			items: [
//				{
//					xtype: 'button',
//					name: 'recursiveApplyCustomFieldCategories',
//					text: 'Apply',
//					listeners: {
//						click: function() {
//							this.formPanel.baseParams.recursiveApplyCustomFieldCategories = true;
//							this.save();
//							//this.formPanel.baseParams.recursiveApplyCustomFieldCategories = false;
//						},
//						scope:this
//					}
//				},{
//					type:'displayfield',
//					html: t("Apply these custom field settings to current folder and it's sub folders recursively", "files")
//				}
//			]
//		});
//
//		this.disableCategoriesPanel.add(this.recursivePanel);
//		
//		
//		this.tabPanel.add(this.disableCategoriesPanel);
//		
//		
//		if(GO.customfields && GO.customfields.types["GO\\Files\\Model\\Folder"])
//		{
//			for(var i=0;i<GO.customfields.types["GO\\Files\\Model\\Folder"].panels.length;i++)
//			{
//				this.tabPanel.add(GO.customfields.types["GO\\Files\\Model\\Folder"].panels[i]);
//			}
//		}
//	}

//	if(go.Modules.isAvailable("legacy", "workflow"))
//	{
//		this.workflowPanel = new GO.workflow.FolderPropertiesPanel();
//		this.tabPanel.insert(2,this.workflowPanel);
//	}
		
	this.formPanel = new Ext.form.FormPanel(
	{
		waitMsgTarget:true,
		border:false,
		defaultType: 'textfield',
		items:this.tabPanel,
		baseParams:{
			notifyRecursive:false
		}
	});

	GO.files.FolderPropertiesDialog.superclass.constructor.call(this,{
		title:t("Properties"),
		layout:'fit',
		width:600,
		height:600,
		closeAction:'hide',
		items:this.formPanel,
		buttons:[{
			text:t("Apply"),
			handler: function(){
				this.save(false)
				},
			scope: this
		},{
			text:t("Save"),
			handler: function(){
				this.save(true)
				},
			scope: this
		}
		]		
	});

	this.addEvents({
		'rename' : true,
		'onNotifyChecked' : true
	});
}

Ext.extend(GO.files.FolderPropertiesDialog, GO.Window, {
	parent_id : 0,
	show : function(folder_id)
	{
		//this.folder_id = folder_id;
		
		this.setFolderId(folder_id);
		
		this.notifyCheckBox.removeListener('check',this.onNotifyChecked,this);
		
		this.formPanel.baseParams.notifyRecursive=false;
		this.formPanel.baseParams.recursiveApplyCustomFieldCategories=false;
		if(!this.rendered)
			this.render(Ext.getBody());

		this.suspendCheckEvent = true;

		this.formPanel.form.load({
			url: GO.url('files/folder/load'),
			params: {
				id: folder_id,
				permissionLevel: go.permissionLevels.read
			},			
			success: function(form, action) {

				var shareField = this.formPanel.form.findField('share');
				shareField.setValue(action.result.data.acl_id>0);
				
				this.parent_id=action.result.data.parent_id;
								
				this.readPermissionsTab.setAcl(action.result.data.acl_id);
				
				this.setPermission(action.result.data.is_someones_home_dir, action.result.data.permission_level, action.result.data.readonly);

				this.tabPanel.setActiveTab(0);
//				if(go.Modules.isAvailable("core", "customfields"))
//					this.disableCategoriesPanel.setModel(folder_id,"GO\\Files\\model\\File");
				
				this.notifyCheckBox.addListener('check',this.onNotifyChecked,this);
				
				GO.dialog.TabbedFormDialog.prototype.setRemoteComboTexts.call(this, action);
				
				GO.files.FolderPropertiesDialog.superclass.show.call(this);


				this.suspendCheckEvent = false;
			},
			failure: function(form, action) {
				if(action.result.exceptionClass === 'GO\\Base\\Exception\\AccessDenied') {
					this.formPanel.form.load({
						url: GO.url('files/folder/display'),
						params: {
							id: folder_id
						},			
						success: function(form, action) {
							this.parent_id=action.result.data.parent_id;
							this.setPermission(action.result.data.is_someones_home_dir, action.result.data.permission_level, action.result.data.readonly);
							
							this.tabPanel.setActiveTab(0);
							GO.dialog.TabbedFormDialog.prototype.setRemoteComboTexts.call(this, action);
							GO.files.FolderPropertiesDialog.superclass.show.call(this);
							this.readPermissionsTab.setDisabled(true);
							this.commentsPanel.setDisabled(true);

	
							if(go.Modules.isAvailable("core", "customfields") && GO.customfields.types["GO\\Files\\Model\\Folder"]){
								this.tabPanel.items.each(function(item, i) {
									if(item.customfields) {
										item.setDisabled(true);
									}
								});
							}
							
							if(GO.workflow) {
								this.folderPanel.setDisabled(true);
							}

						},
						scope:this
					});
				} else {				
					Ext.MessageBox.alert(t("Error"), action.result.feedback);

				}
			},
			scope: this
		});		
	},
	
	setFolderId : function(id){
		this.folder_id=id;
	},
	
	onNotifyChecked : function(checkbox,checked) {
		Ext.Msg.show({
			width:400,
			title: checked  ? t("Set notification on subfolders?", "files") :  t("Remove notification from subfolders?", "files"),
			msg: t("Do you want to apply this to all the subfolders?", "files"),
			buttons: Ext.Msg.YESNO,
			fn: function (btn){
				this.formPanel.baseParams['notifyRecursive'] = btn=='yes';
			},
			scope: this
		});
	},
	
	setPermission : function(is_someones_home_dir, permission_level, readonly)
	{
		//readonly flag is set for project, contact, company etc. folders.
			
		var form = this.formPanel.form;
		form.findField('name').setDisabled(is_someones_home_dir || readonly || permission_level<GO.permissionLevels.write);
		form.findField('share').setDisabled(is_someones_home_dir || readonly || permission_level<GO.permissionLevels.manage);
		form.findField('apply_state').setDisabled(permission_level<GO.permissionLevels.manage && !GO.settings.has_admin_permission);
		if(!this.readPermissionsTab.disabled)
			this.readPermissionsTab.setDisabled(!is_someones_home_dir && readonly);

		this.commentsPanel.setDisabled(readonly || permission_level<GO.permissionLevels.write);
		
	},
	
	save : function(hide)
	{
		this.formPanel.form.submit({
						
			url: GO.url('files/folder/submit'),
			params: {
				id: this.folder_id
			},
			waitMsg:t("Saving..."),
			success:function(form, action){

				if(typeof(action.result.acl_id) != 'undefined')
				{
					this.readPermissionsTab.setAcl(action.result.acl_id);
				}
				
				if(action.result.new_path)
				{
					this.formPanel.form.findField('path').setValue(action.result.new_path);
					this.fireEvent('rename', this, this.parent_id);				
				}
				this.fireEvent('save', this, this.folder_id, this.parent_id);
				
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
