/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LinksPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.grid.LinksPanel = function(config){
	
	if(!config)
	{
		config={};
	}
	
	if(!this.model_id)
	{
		this.model_id=0;
	}
	
	if(!this.model_name)
	{
		this.model_name=0;
	}
	
	if(!this.folder_id)
	{
		this.folder_id=0;
	}

	if(!config.id){
		config.id='go-links-panel';
	}

	this.linksDialog = new GO.dialog.LinksDialog({
		linksStore: config['store']
		});
	this.linksDialog.on('link', function(){
		this.linksGrid.store.reload();
	}, this);
	
	this.linksTree = new GO.LinksTree({
		id:config.id+'_tree',
		region:'center',
		split:true
	});
	
	this.linksTree.on('click', function(node)	{
		this.setFolder(node.id.substr(10));
	}, this);
	
	this.linksTree.on('contextmenu', function(node, e){
		e.stopEvent();

		var selModel = this.linksTree.getSelectionModel();

		if(!selModel.isSelected(node))
		{
			selModel.clearSelections();
			selModel.select(node);
		}

		var folder_id = node.id.substr(10);
		
		if(folder_id!='')
		{
			var coords = e.getXY();
			this.linksContextMenu.showAt([coords[0], coords[1]], ['GO\\Base\\Model\\LinkFolder:'+folder_id], 'folder');	
		}		
	}, this);
	
	this.linksTree.on('beforenodedrop', function(e){
		
		if(!this.write_permission)
		{
			return false;
		}
		
		var target = {
			folder_id: e.target.id.substr(10),
			model_id: this.model_id,
			model_name: this.model_name
		};
		
		var selections = [];		
		if(e.data.selections)
		{
			//dropped from grid
			for(var i=0;i<e.data.selections.length;i++)
			{
				//				if(e.data.selections[i].data.link_and_type.substr(0,6)=='folder')
				//				{					
				//					var id = e.data.selections[i].data.link_and_type.substr(7);
				//					var movedNode = this.linksTree.getNodeById('lt-folder-'+id);
				//					var targetNode = this.linksTree.getNodeById('lt-folder-'+target.folder_id);
				//					targetNode.appendChild(movedNode);
				//				}
				selections.push(e.data.selections[i].data.model_name_and_id);
			}
		}else
		{
			//dropped from tree		  
			selections = ['GO\\Base\\Model\\LinkFolder:'+e.data.node.id.substr(10)];
		}
		
		this.moveSelections(selections, target);
		
	},
	this);
	
	
	this.linksGrid = new GO.grid.LinksGrid({
		region:'center',
		id: config.id+'_grid',
		noFilterSave:config.noFilterSave,		
		deleteConfig:{
			scope:this,
			success:this.onDelete
		}
	});

	this.linksGrid.store.on('load', function(){
		var sm = this.linksTree.getSelectionModel();

		var activeNode = this.linksTree.getNodeById('lt-folder-'+this.folder_id);
		if(activeNode)
			sm.select(activeNode);
		else
			sm.select(this.linksTree.getRootNode());
	}, this);
	
	this.linksGrid.on('folderDrop', function(grid, selections, dropRecord){
		var target = {
			folder_id: dropRecord.data.id,
			model_id: this.model_id,
			model_name: this.model_name
		};
		var selectedKeys=[]
		for(var i=0;i<selections.length;i++)
		{
			selectedKeys.push(selections[i].data.link_and_type);
		}
		
		this.moveSelections(selectedKeys, target);
		
	}, this);

	
	this.linksGrid.on('rowcontextmenu', function(grid, rowIndex,e){

		e.stopEvent();
		
		var sm =grid.getSelectionModel();
		if(sm.isSelected(rowIndex) !== true) {
			sm.clearSelections();
			sm.selectRow(rowIndex);
		}


		var coords = e.getXY();
		this.linksContextMenu.showAt([coords[0], coords[1]], sm.selections.keys);

	}, this)
	
	
	this.linksGrid.store.on('load', function(){
		
		this.setWritePermission(this.linksGrid.store.reader.jsonData.permissionLevel>=GO.permissionLevels.write);
		
	}, this);
	
	this.folderWindow = new GO.LinkFolderWindow();
	this.folderWindow.on('save', function(folderWin){
		this.linksGrid.store.reload();
		
		var activeNode = this.linksTree.getNodeById('lt-folder-'+this.folder_id);

		if(folderWin.folder_id==this.folder_id && activeNode){
			activeNode = activeNode.parentNode;
		}
		
		if(activeNode)
		{
			//delete preloaded children otherwise no request will be sent
			delete activeNode.attributes.children;
			activeNode.reload();
		}else
		{
			this.linksTree.rootNode.reload();
		}

	}, this);

	this.linkPreviewPanels[0]=new Ext.Panel({
		bodyStyle:'padding:5px'
	});

	this.previewPanel = new Ext.Panel({
		id: config.id+'_preview',
		region:'east',
		width:420,
		split:true,
		layout:'card',
		items:[this.linkPreviewPanels[0]]
	});

	this.linkTypeFilter = new GO.LinkTypeFilterPanel({
		for_links: true,
		region:'south',
		height:300
	//		region:'west',
	//		width:160,
	//		layout:'border',
	//		id:config.id+'_west'
	//		store:new GO.data.JsonStore({
	//			root: 'results',
	//			data: {"results":GO.linkTypes}, //defined in /default_scripts.inc.php
	//			fields: ['id','name', 'checked'],
	//			id:'id'
	//		})
	});
	this.linkTypeFilter.on('change', function(grid, types){
		this.linksGrid.store.baseParams.types = Ext.encode(types);
		this.linksGrid.store.load();
	//delete this.linksGrid.store.baseParams.types;
	}, this);
	
	config.items=[
	{
		region:'west',
		width:160,
		layout:'border',
		id:config.id+'_west',
		split:true,
		items:[
		this.linksTree,
		this.linkTypeFilter
		]
	},
	//			this.linkTypeFilter,
	this.linksGrid,
	this.previewPanel
	];
	
	this.linksContextMenu = new GO.LinksContextMenu();
	
	this.linksContextMenu.on('properties', function(menu,selections){
		
		var colonPos = selections[0].indexOf(':');
		var folder_id = selections[0].substr(colonPos+1);		
		
		this.folderWindow.show({
			folder_id: folder_id
		});
	
	}, this);
	
	this.linksContextMenu.on('delete', function(menu,selections){

		if(selections.indexOf('GO\\Base\\Model\\LinkFolder:'+this.folder_id)>-1){
			this.setFolder(0,true);
		}
		
		var deleteConfig = {
			store:this.linksGrid.store,
			params:{
				delete_keys:Ext.encode(selections)
			},
			count:selections.length,
			callback:this.onDelete,
			scope:this
		};
		GO.deleteItems(deleteConfig);
		
	}, this);
	
	this.linksContextMenu.on('unlink', function(menu,selections){
		this.unlinkSelected();
	}, this);
	
	config['layout']='border';
	config.border=false;
	
	//was required to show the search field in the tbar
	config.hideMode='offsets';
		
	config['tbar'] = [
	this.linkButton = new Ext.Button({
		iconCls: 'btn-link',
		text: t("Link"),
		cls: 'x-btn-text-icon',
		handler: function(){				
			this.linksDialog.show();					
		},
		scope: this
				
	}),
	this.newFolderButton = new Ext.Button({
		iconCls: 'btn-add',
		text: t("New folder"),
		cls: 'x-btn-text-icon',
		handler: function() {
					
			this.folderWindow.show({
				model_id : this.model_id,
				model_name : this.model_name,
				parent_id : this.folder_id
			});
		},
		scope: this
	}),
	this.unlinkButton = new Ext.Button({
		iconCls: 'btn-unlink',
		text: t("Unlink"),
		cls: 'x-btn-text-icon',
		handler: function() {

			this.unlinkSelected();
		},
		scope: this
	}),'-',{
		iconCls: 'btn-refresh',
		text: t("Refresh"),
		cls: 'x-btn-text-icon',
		handler: function(){
			this.linksGrid.store.load();
			this.linksTree.getRootNode().reload();
		},
		scope: this

			
	}
	//			,'-',this.deleteButton = new Ext.Button({
	//				iconCls: 'btn-delete',
	//				text: t("Delete"),
	//				cls: 'x-btn-text-icon',
	//				handler: function(){
	//					this.linksGrid.deleteSelected();
	//				},
	//				scope: this
	//			})
	];
		
	if(GO.links && GO.links.SettingsDialog)
	{
		config.tbar.push('-');
		
		config.tbar.push({
			text: t("Settings"),
			scope:this,
			iconCls:'btn-settings',
			handler:function(){
				if(!this.settingsWindow)
				{
					this.settingsWindow = new GO.links.SettingsDialog();
				}
				this.settingsWindow.show();				
			}
		});
	}
		
		
	this.linksGrid.on("rowdblclick", this.rowDoulbleClicked, this);
	this.linksGrid.on("delayedrowselect", this.rowClicked, this);

	
	
	GO.grid.LinksPanel.superclass.constructor.call(this, config);
	
}

Ext.extend(GO.grid.LinksPanel, Ext.Panel, {

	linkPreviewPanels : [],

	onDelete : function(deleteConfig){
		var selections = Ext.decode(deleteConfig.params.delete_keys);
		var colonPos, folder_id, deletedNode;
		for(var i=0;i<selections.length;i++){
			colonPos = selections[i].indexOf(':');
			folder_id = selections[i].substr(colonPos+1);

			deletedNode = this.linksTree.getNodeById('lt-folder-'+folder_id);
			if(deletedNode)
				deletedNode.remove();
		}

		var model_names = {};
		for(var i=0;i<selections.length;i++){
			var arr = selections[i].split(':');
			if(!model_names[arr[0]])
			{
				model_names[arr[0]]=[];
			}
			model_names[arr[0]].push(arr[1]);
		}

		GO.mainLayout.fireEvent('linksDeleted', deleteConfig, model_names);
	},
	
	afterRender : function(){
		
		GO.grid.LinksPanel.superclass.afterRender.call(this);
		
		if(this.isVisible())
		{
			this.onShow();
		}
	},

	unlinkSelected : function(){

		var selectionModel = this.linksGrid.getSelectionModel();
		var records = selectionModel.getSelections();

		if(records.length>0)
		{
			this.linksGrid.store.baseParams['unlinks']=Ext.encode(selectionModel.selections.keys);
			this.linksGrid.store.reload();
			delete this.linksGrid.store.baseParams['unlinks'];
		}
	},
	
	
	moveSelections : function(selections, target)
	{
		GO.request({
			url: "linkFolder/moveLinks",
			params: {
				selections : Ext.encode(selections),
				target : Ext.encode(target)
			},
			success: function(options,  response, result){				
				this.linksGrid.store.reload();
					
			//				if(responseParams.moved_links)
			//				{
			//					for(var i=0;i<responseParams.moved_links.length;i++)
			//					{
			//						var record = this.linksGrid.store.getById(responseParams.moved_links[i]);
			//						if(record)
			//						{
			//							this.linksGrid.store.remove(record);
			//						}
			//					}
			//				}					
				
			},
			scope:this								
			
		});
		
		
	},
	
	
	rowDoulbleClicked : function(grid, rowClicked, e) {
			
		var record = grid.store.getAt(rowClicked);
		
		if(record.data.model_name=='folder')
		{
			this.setFolder(record.data.id);
		}else
		{			
			this.previewPanel.getLayout().activeItem.editHandler();
		}

	/*else	if(GO.linkHandlers[record.data.model_name])
		{
			GO.linkHandlers[record.data.model_name].call(this, record.data.id);

		}else
		{
			GO.errorDialog.show('No handler definded for link type: '+record.data.model_name);
		}*/
	},

	rowClicked : function(grid, rowClicked, record){
		this.previewPanel.getLayout().setActiveItem(0);

		var panelId = 'link_pp_'+record.data.model_name;

		if(record.data.model_name!='folder'){

			if(!GO.linkPreviewPanels[record.data.model_name]){
				this.linkPreviewPanels[0].body.update('Sorry, the preview of this type not implemented yet.');
			}else
			{
				if(!this.linkPreviewPanels[record.data.model_name]){
					this.linkPreviewPanels[record.data.model_name] = GO.linkPreviewPanels[record.data.model_name].call(this, {
						id:panelId
					});
					this.previewPanel.add(this.linkPreviewPanels[record.data.model_name]);
				}
				this.previewPanel.getLayout().setActiveItem(panelId);
				this.linkPreviewPanels[record.data.model_name].load(record.data.model_id);
			}
		}
	},
	
	
	onShow : function(){
		GO.grid.LinksPanel.superclass.onShow.call(this);

		this.previewPanel.getLayout().setActiveItem(0);
		
		if(!this.loaded && this.model_id>0)
		{
			this.linksGrid.store.baseParams.types = Ext.encode(this.linkTypeFilter.getSelected());
			this.linksGrid.store.load();
			//delete this.linksGrid.store.baseParams.types;
			
			var rootNode = this.linksTree.getRootNode();
			
			if(rootNode.isExpanded()){
				rootNode.reload();
			}else
			{
				rootNode.expand();
			}
			this.loaded=true;
		}
	},
	
	setWritePermission : function(writePermission){
		this.linkButton.setDisabled(!writePermission);
		this.unlinkButton.setDisabled(!writePermission);
		this.newFolderButton.setDisabled(!writePermission);
		//this.deleteButton.setDisabled(!writePermission);
		
		this.write_permission=writePermission;
		this.linksGrid.write_permission=writePermission;
	},
	
	setFolder : function(folder_id, noload)
	{
		var activeNode = this.linksTree.getNodeById('lt-folder-'+folder_id);
		if(activeNode)
		{
			activeNode.expand();			
		}
		
		this.linksDialog.folder_id=folder_id;
		
		this.folder_id=folder_id;
		this.linksGrid.store.baseParams["folder_id"]=folder_id;
		if(!noload){
			this.linksGrid.store.baseParams.types = Ext.encode(this.linkTypeFilter.getSelected());
			this.linksGrid.store.load();
		//delete this.linksGrid.store.baseParams.types;
		}
	},
	
	loadLinks : function (model_id, model_name, folder_id)
	{
		if(model_id>0)
		{
			this.setDisabled(false);
		}else
		{
			this.setDisabled(true);
		}
		
		if(this.model_id!=model_id || this.model_name!=model_name)
		{	
			this.model_id=this.linksGrid.store.baseParams["model_id"]=model_id;
			this.model_name=this.linksGrid.store.baseParams["model_name"]=model_name;			
			this.linksGrid.store.baseParams["folder_id"]=folder_id;
			
			this.linksTree.loadLinks(model_id, model_name);

			this.linksDialog.setSingleLink(this.model_id, this.model_name);
			this.loaded=false;

			//reset all preview panels
			this.previewPanel.items.each(function(p){
				if(p.reset){
					p.reset();
				}
			});

		}
	}

});

