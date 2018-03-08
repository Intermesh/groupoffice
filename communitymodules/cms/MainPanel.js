/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.MainPanel = function(config){

	if(!config)
	{
		config = {};//GO.cms.CommentsGrid;
	}	
	
	this.folderDialog = new GO.cms.FolderDialog();
	this.folderDialog.on('save', function(folder_id, formValues, parent_id){

		var folderNode = this.treePanel.getNodeById('folder_'+folder_id);
		 
		if(folderNode){
			folderNode.setText(formValues.name);
		}else
		{
			var folderNode = this.treePanel.getNodeById('folder_'+parent_id);
		 	
			var newNode = new Ext.tree.AsyncTreeNode({
				text: formValues.name,
				id: 'folder_'+folder_id,
				iconCls: 'filetype-folder',
				fstype:'folder',
				site_id: folderNode.attributes.site_id,
				file_id: 0,
				folder_id: folder_id,
				template: folderNode.attributes.template,
				expanded:true,
				children:[]
			});
		 	
		 	
			folderNode.appendChild(newNode);
		}
	}, this);
			
				
	this.treePanel = GO.cms.treePanel = new GO.cms.TreePanel({
		region:'west',
		title:GO.lang.menu,
		autoScroll:true,				
		width: 250,
		split:true,
		selModel: new Ext.tree.MultiSelectionModel()
	});
	
	this.treePanel.on('beforeclick', function(node){
		if(node.attributes.file==0)
			return false;
	}, this);
	
	this.treePanel.on('click', function(node, e)
	{		
		if(!e.ctrlKey)
		{
			if(!node.expanded)
				node.expand();

			if(node.attributes.site_id)
				GO.cms.site_id=node.attributes.site_id;
				
			if(node.attributes.file_id>0)
			{
				this.checkChanges.defer(100, this, [function(){
					this.getEl().mask(GO.lang.waitMsgLoad);
					this.editorPanel.loadFile(node.attributes.file_id, node.attributes.template);	
				}, this]);				
				
			}
		}				
	}, this);

	this.treeContextMenu = new Ext.menu.Menu({

		items: [
		this.addFolderButton = new Ext.menu.Item({
			iconCls: 'btn-add',
			text: GO.cms.lang.newFolder,
			handler: function(){
				var selModel = this.treePanel.getSelectionModel();

				this.folderDialog.show(0, selModel.selNodes[0].attributes.folder_id, selModel.selNodes[0].attributes.site_id);
			},
			scope: this
		}),

		this.folderPropertiesButton = new Ext.menu.Item({
			iconCls: 'cms-folder-properties',
			text: GO.cms.lang.folderProperties,
			handler: function(){
				var selModel = this.treePanel.getSelectionModel();

				this.folderDialog.show(selModel.selNodes[0].attributes.folder_id);
			},
			scope:this
		}),
		this.openFileButton = new Ext.menu.Item({
			iconCls: 'btn-edit',
			text: GO.lang.cmdEdit,
			handler: function(){
				var selModel = this.treePanel.getSelectionModel();

				this.checkChanges.defer(100, this, [function(){
					this.getEl().mask(GO.lang.waitMsgLoad);
					this.editorPanel.loadFile(selModel.selNodes[0].attributes.file_id, selModel.selNodes[0].attributes.template);
				}, this]);
			},
			scope:this
		})
		,'-',{
			iconCls: 'btn-delete',
			text: GO.lang.cmdDelete,
			handler: function(){
				this.deleteSelected();
			},
			scope:this
		}]
	});

	this.treePanel.on('contextmenu', function(node, e){
		e.stopEvent();

		var selModel = this.treePanel.getSelectionModel();

		if(!selModel.isSelected(node))
		{
			selModel.clearSelections();
			selModel.select(node);
		}
		var coords = e.getXY();

		var nodes = 	selModel.getSelectedNodes();

		this.openFileButton.setDisabled(nodes[0].attributes.fstype=='folder');
		this.folderPropertiesButton.setDisabled(nodes[0].attributes.fstype!='folder');
		this.addFolderButton.setDisabled(nodes[0].attributes.fstype!='folder');
		this.treeContextMenu.showAt([coords[0], coords[1]]);
	}, this);

	
	this.editorPanel = new GO.cms.EditorPanel();
	GO.cms.editorPanel = this.editorPanel;
	
	this.editorPanel.on('disabled', function(disabled){
		this.saveButton.setDisabled(disabled);
		this.viewButton.setDisabled(disabled);
		if(disabled)
			this.filesButton.setDisabled(true);
		
	}, this);
	
	this.editorPanel.on('save', function(file_id, formValues, folder_id){
		
		var fileNode = this.treePanel.getNodeById('file_'+file_id);
		 
		if(fileNode){
			fileNode.setText(formValues.name);
		}else
		{
		 	
			var folderNode = this.treePanel.getNodeById('folder_'+folder_id);
		 	
			var newNode = new Ext.tree.AsyncTreeNode({
				text: formValues.name,
				id: 'file_'+file_id,
				iconCls: 'filetype-html',
				site_id: folderNode.attributes.site_id,
				file_id: file_id,
				folder_id: folderNode.attributes.folder_id,
				template: folderNode.attributes.template,
				leaf: true
			});
		 	
			folderNode.appendChild(newNode);
			this.treePanel.getSelectionModel().select(newNode);
		}
		
	}, this);
	
	config.items=[
	this.treePanel,
	this.editorPanel
	];	
	
	config.layout='border';
	
	var tbar = [
	
	{
		iconCls: 'btn-add',
		text: GO.cms.lang.newPage,
		handler: function(){
			
			var selModel = this.treePanel.getSelectionModel();

			if(selModel.selNodes[0]==null)
			{
				Ext.MessageBox.alert(GO.lang.strError, GO.cms.lang.selectFolderAdd);
			}else
			{
				this.checkChanges.defer(100, this, [function(){
					this.editorPanel.newFile(selModel.selNodes[0].attributes.folder_id, selModel.selNodes[0].attributes.template);	
				}, this]);				
			}
		},
		scope: this
	},{
		iconCls: 'btn-add',
		text: GO.cms.lang.newFolder,
		handler: function(){
			
			var selModel = this.treePanel.getSelectionModel();

			if(selModel.selNodes[0]==null)
			{
				Ext.MessageBox.alert(GO.lang.strError, GO.cms.lang.selectFolderAdd);
			}else
			{					
				
				this.folderDialog.show(0, selModel.selNodes[0].attributes.folder_id, selModel.selNodes[0].attributes.site_id);
			}
		},
		scope: this
	},{
		iconCls: 'cms-btn-copy',
		text: GO.lang.copy,
		handler: function(){
			
			var selModel = this.treePanel.getSelectionModel();

			if(selModel.selNodes[0]==null)
			{
				Ext.MessageBox.alert(GO.lang.strError, GO.cms.lang.selectFolderAdd);
			}else
			{		
				this.copyFolders=[];
				this.copyFiles=[];				
				for(var i=0;i<selModel.selNodes.length;i++)
				{
					if(selModel.selNodes[i].attributes.file_id>0)
					{
						this.copyFiles.push(selModel.selNodes[i].attributes.file_id);
					}else
					{
						this.copyFolders.push(selModel.selNodes[i].attributes.folder_id);
					}
				}

				this.pasteButton.setDisabled(false);		
				
				
			}
		},
		scope: this
	},this.pasteButton = new Ext.Button({
		disabled:true,
		iconCls: 'cms-btn-paste',
		text: GO.lang.paste,
		handler: function(){
			var selModel = this.treePanel.getSelectionModel();

			if(selModel.selNodes[0]==null)
			{
				Ext.MessageBox.alert(GO.lang.strError, GO.cms.lang.selectFolderAdd);
			}else
			{		
				
				var destination_folder_id=selModel.selNodes[0].attributes.folder_id;
				
				var params = {
					task: 'copy',
					copy_folders: Ext.encode(this.copyFolders),
					copy_files: Ext.encode(this.copyFiles),
					destination_folder_id: selModel.selNodes[0].attributes.folder_id					
				};
				
				Ext.Ajax.request({
					url: GO.settings.modules.cms.url+'action.php',
					params: params,
					callback: function(options, success, response)
					{	
						if(!success)
						{
							Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strRequestError']);
						}else
						{
							var responseParams = Ext.decode(response.responseText);
							if(!responseParams.success)
							{
								Ext.MessageBox.alert(GO.lang['strError'], responseParams.feedback);
							}else
							{				
								this.treePanel.rootNode.reload();			
								
								this.copyFolders=[];
								this.copyFiles=[];	
								this.pasteButton.setDisabled(true);
							}	
						}
					},
					scope:this
				});
				
			}			
		},
		scope: this
	}),{
		text: GO.lang.cmdDelete,
		iconCls: 'btn-delete',
		handler:function(){
			this.deleteSelected();
		},
		scope: this
	},'-',this.saveButton = new Ext.Button({
		iconCls: 'btn-save',
		text: GO.lang.cmdSave,
		handler:function(){
			this.editorPanel.saveFile();			
		},
		scope:this,
		disabled:true
	}),this.viewButton = new Ext.Button({
		iconCls: 'cms-btn-view',
		text: GO.cms.lang.view,
		handler:function(){
			window.open(GO.settings.modules.cms.url+'run.php?file_id='+this.editorPanel.baseParams.file_id);
		},
		scope:this,
		disabled:true
	}),'-',{
		iconCls: 'cms-folder-properties',
		text: GO.cms.lang.folderProperties,
		handler: function(){
			var selModel = this.treePanel.getSelectionModel();

			if(selModel.selNodes[0]==null)
			{
				Ext.MessageBox.alert(GO.lang.strError, GO.cms.lang.selectFolder);
			}else
			{
				this.folderDialog.show(selModel.selNodes[0].attributes.folder_id);
			}
		},
		scope: this
	}
	];
	
	
	if(GO.files)
	{		
		tbar.push(this.filesButton = new Ext.Button({
			iconCls: 'cms-folder-properties',
			text: GO.cms.lang.files,
			disabled:true,
			handler:function(){				
				GO.cms.createFileBrowser(GO.cms.editorPanel.root_folder_id, '', false, GO.cms.editorPanel.files_folder_id);
			},
			scope:this
		}));

		this.editorPanel.on('load', function(){
			this.filesButton.setDisabled(this.editorPanel.files_folder_id==0);
		},this);

		this.editorPanel.on('save', function(){
			this.filesButton.setDisabled(this.editorPanel.files_folder_id==0);
		},this);
	}

	this.editorPanel.on('load', function(){
		this.getEl().unmask();
	},this);


	if(GO.settings.modules.cms.write_permission)
	{
		tbar.push('-');
		tbar.push({
			iconCls: 'cms-btn-sites',
			text: GO.cms.lang.sites,
			handler:function(){
				if(!this.sitesDialog)
				{
					this.sitesDialog = new GO.cms.SitesDialog();
					this.sitesDialog.sitesGrid.store.on('load', function(){
						this.sitesDialog.sitesGrid.store.on('load', function(){
							this.treePanel.rootNode.reload();
						}, this);
					}, this);
				}
				
				this.sitesDialog.show();
			},
			scope:this
		});
	}
	
	
	if(GO.webshop)
	{
		tbar.push({
			iconCls: 'ws-btn-webshop',
			text: GO.webshop.lang.webshops,
			handler:function(){
				if(!this.webshopsDialog)
				{
					this.webshopsDialog = new Ext.Window({
						layout:'fit',
						width:500,
						height:400,
						closeAction:'hide',
						title: GO.webshop.lang.webshops,
						items:new GO.webshop.WebshopsGrid(),
						buttons:[{
							text: GO.lang.cmdClose,
							handler: function(){
								this.webshopsDialog.hide();
							},
							scope:this
						}]
					});
					
				}
					
				this.webshopsDialog.show();
			},
			scope:this
		});
	}

	if(GO.mailings)
	{
		tbar.push({
			iconCls: 'ml-btn-mailings',
			text: GO.addressbook.lang.sendMailing,
			cls: 'x-btn-text-icon',
			handler: function(){
				if(!this.selectMailingGroupWindow)
				{
					this.selectMailingGroupWindow=new GO.mailings.SelectMailingGroupWindow();
					this.selectMailingGroupWindow.on("select", function(win, mailing_group_id){
						GO.email.showComposer({
							loadUrl: GO.settings.modules.mailings.url+'json.php',
							loadParams:{
								task:'sendcmsfile',
								file_id: this.editorPanel.baseParams.file_id,
								mailing_group_id:mailing_group_id
							},
							mailing_group_id:mailing_group_id
						});
					}, this);
				}
				this.selectMailingGroupWindow.show();
			},
			scope: this
		});
	}
	
	config.tbar=new Ext.Toolbar({		
		cls:'go-head-tb',
		items: tbar
	});
	
	config.border=false;
	
	GO.cms.MainPanel.superclass.constructor.call(this, config);	
};

Ext.extend(GO.cms.MainPanel, Ext.Panel, {
	copyFolders : [],
	copyFile : [],

	deleteSelected : function(){
		var selModel = this.treePanel.getSelectionModel();

		var deleteItems = [];
		if(selModel.selNodes)
		{
			for(var i=0;i<selModel.selNodes.length;i++)
			{
				deleteItems.push(selModel.selNodes[i].id);
			}
		}
		GO.deleteItems({
			count: deleteItems.length,
			url: GO.settings.modules.cms.url+'action.php',
			params: {
				task: 'delete',
				delete_items: Ext.encode(deleteItems)
			},
			callback:function(responseParams){
				if(responseParams.deleted_nodes)
				{
					for(var i=0;i<responseParams.deleted_nodes.length;i++)
					{
						var node = this.treePanel.getNodeById(responseParams.deleted_nodes[i]);
						if(node)
						{
							if(node.attributes.file_id==this.editorPanel.baseParams.file_id)
							{
								this.editorPanel.form.reset();
								this.editorPanel.setDisabled(true);
							}
							node.remove();
						}
					}
				}
			},
			scope:this
		});
	},
	
	checkChanges : function(callback, scope){
		var dirty = this.editorPanel.isDirty();
		if(dirty && confirm(GO.cms.lang.saveChanges))
		{
			this.editorPanel.saveFile();
			
			this.editorPanel.on('save', callback, scope, {
				single:true
			});
		}else
		{		
			callback.call(scope);
		}
	}
	
	
});
 

/*if(Ext.isIE)
{
	GO.moduleManager.addModule('cms', GO.panel.IFrameComponent, {
		title : GO.cms.lang.cms,
		iconCls : 'go-tab-icon-cms',
		url:'modules/cms/index.php',
		border:false
	});
	
}else
{*/
GO.moduleManager.addModule('cms', GO.cms.MainPanel, {
	title : GO.cms.lang.cms,
	iconCls : 'go-tab-icon-cms'
});
//}
