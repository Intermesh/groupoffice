/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: FileBrowser.js 22402 2018-02-19 15:59:49Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */


Ext.namespace("GO.files");

/*
 * if config.treeRootVisible == false (default) then the tree will load automatically!
 */
GO.files.FileBrowser = function(config){
	config = config || {};
	config.id = config.id || Ext.id();

	this.westPanel = new Ext.Panel({
		hideMode:"offsets",
		region: 'west',
		layout: 'border',
		cls: 'go-sidenav',
		width: dp(224),
		split: true,
		id: 'fs-tree-bookmarks-panel-'+config.id,
		items : [{	
			xtype: 'panel',
			region:'center',
			split:true,
			autoScroll:true,
			items:[
				this.treePanel = new GO.files.TreePanel({
					collapsed: config.treeCollapsed,
					animate: false,
					header:false,
					ddAppendOnly: true,
					ddGroup : 'FilesDD',
					enableDD:true
				}),
				this.bookmarksGrid = new GO.files.BookmarksGrid()
				],
			},
			this.quotaBar = new Ext.ProgressBar({
				region:'south',
				height: dp(32),
				value: quotaPercentage,
			})
		]
	});
	
	//select the first inbox to be displayed in the messages grid

	this.treePanel.getRootNode().on('load', function (node) {
		if (!this.folder_id) {
			this.folder_id = node.childNodes[0].id;
		}
		this.setFolderID(this.folder_id);

		this.ready = true;
		this.fireEvent('filebrowserready', this);
	}, this, {single: true});


	this.treePanel.on('click', function (node) {
		this.setFolderID(node.id, true);
		this.cardPanel.show();
		let showTrashBar = false;
		if (node.id === "trash") {
			showTrashBar = true;
			this.cardPanel.getLayout().setActiveItem(2);
		} else {
			this.cardPanel.getLayout().setActiveItem(this.thumbsToggle.pressed ? 1 : 0);
		}

		if (node.id !== "shared" && node.id !== "trash") {
			this.folderDetail.load(parseInt(node.id));
			this.eastPanel.getLayout().setActiveItem(this.folderDetail);
		}
		const cpt = this.cardPanel.getTopToolbar();
		if(showTrashBar) {
			cpt.items.items[0].hide();
			cpt.items.items[1].hide();
			cpt.items.items[2].show();
		} else {
			cpt.items.items[0].show();
			cpt.items.items[1].show();
			cpt.items.items[2].hide();
		}
	}, this);

	this.treePanel.on('contextmenu', function(node, e){
		e.stopEvent();

		var selModel = this.treePanel.getSelectionModel();

		if(!selModel.isSelected(node))
		{
			selModel.clearSelections();
			selModel.select(node);
		}

		var records = this.getSelectedTreeRecords();

		var coords = e.getXY();
		this.filesContextMenu.showAt(coords, records, 'tree');
	}, this);

	this.treePanel.on('beforenodedrop', function(e){

		if(e.data.selections) {
			var selections = e.data.selections;
		} else {
			var record = {};
			record.data={};
			record.data['extension']='folder';
			record.data['id']=e.data.node.id;
			record.data['type_id']='d:'+e.data.node.id;
			var selections = [record];
		}
		go.User.confirmOnMove ?
			Ext.Msg.confirm(t('Confirm'), t('Are you sure you want to move the item(s)?'), function(btn) { if(btn == 'yes') this.paste('cut', e.target.id, selections);}, this) :
			this.paste('cut', e.target.id, selections);
	},
	this);

	this.treePanel.on('nodedragover', function(dragEvent){

		if(!dragEvent.dropNode) {

			//comes from grid, don't allow it to paste it into a child
			for(var i=0;i<dragEvent.data.selections.length;i++) {
				if(dragEvent.data.selections[i].data.extension=='folder') {
					var moveid = dragEvent.data.selections[i].data.id;
					var parentid = dragEvent.data.selections[i].data.parent_id;
					var targetid = dragEvent.target.id;

					if(moveid==targetid || parentid==targetid) {
						return false;
					}

					var dragNode = this.treePanel.getNodeById(moveid);
					if(dragNode.parentNode.id == targetid || dragEvent.target.isAncestor(dragNode)) {
						return false;
					}
					return true;
				}
			}
		}else {
			var parentId = this.treePanel.getNodeById(dragEvent.dropNode.id).parentNode.id;
			if(parentId == dragEvent.target.id) {
				return false
			}
			return true;
		}
	}, this);


	const fields = {
		fields: ['type_id', 'id', 'name', 'type', 'size', 'mtime', 'ctime', 'username', 'musername', 'extension', 'timestamp', 'thumb_url', 'path', 'acl_id', 'locked_user_id', 'locked', 'folder_id', 'permission_level', 'readonly', 'unlock_allowed', 'handler', 'content_expire_date']
			.concat(go.customfields.CustomFields.getFieldDefinitions("File"))
			.concat(go.customfields.CustomFields.getFieldDefinitions("Folder")),
		columns: [{
			id: 'name',
			header: t("Name"),
			dataIndex: 'name',
			width: 300,
			renderer: function (v, meta, r) {
				var cls = r.get('acl_id') > 0 && r.get('readonly') == 0 ? 'filetype filetype-folder-shared' : 'filetype filetype-' + r.get('extension');
				if (r.get('locked_user_id') > 0)
					v = '<div class="fs-grid-locked">' + v + '</div>';

				return '<div class="go-grid-icon ' + cls + '" style="float:left;">' + v + '</div>';
			}
		}, {
			id: 'type',
			header: t("Type"),
			dataIndex: 'type',
			sortable: true,
			hidden: true,
			width: 100
		}, {
			id: 'path',
			header: t("Path"),
			dataIndex: 'path',
			sortable: true,
			hidden: true,
			width: 200
		}, {
			id: 'size',
			header: t("Size"),
			dataIndex: 'size',
			renderer: function (v) {
				return v == '-' ? v : Ext.util.Format.fileSize(v);
			},
			hidden: true,
			width: 100
		}, {
			xtype: "datecolumn",
			id: 'mtime',
			header: t("Modified at"),
			dataIndex: 'mtime'
		}, {
			xtype: "datecolumn",
			id: 'ctime',
			header: t("Created at"),
			dataIndex: 'ctime'
		}, {
			id: 'username',
			header: t("Creator"),
			dataIndex: 'username',
			sortable: true,
			hidden: true,
			width: 200
		}, {
			id: 'musername',
			header: t("Modifier"),
			dataIndex: 'musername',
			sortable: true,
			hidden: true,
			width: 200
		}, {
			id: 'id',
			header: 'ID',
			dataIndex: 'id',
			hidden: true
		}].concat(go.customfields.CustomFields.getColumns("File"))
			.concat(go.customfields.CustomFields.getColumns("Folder"))
	};


	this.gridStore = new GO.data.JsonStore({
		url:GO.url("files/folder/list"),
		baseParams: {
			'query' : ''
		},
		id: 'type_id',
		fields:fields.fields,
		remoteSort:true
	});

	this.gridStore.on('load', this.onStoreLoad, this);
	
	if(config.filesFilter) {
		this.setFilesFilter(config.filesFilter);
	}

	this.trashGridStore = new GO.data.JsonStore({
		url: GO.url("files/folder/trash"),
		id: 'type_id',
		fields: ['id', 'entity', 'name', 'fullPath', 'deletedByUser', 'deletedAt'],
		remoteSort: true
	});

	this.gridPanel = new GO.files.FilesGrid({
		id:config.id+'-fs-grid',
		store: this.gridStore,
		deleteConfig: {
			scope:this,
			success:function(){
				var activeNode = this.treePanel.getNodeById(this.folder_id);
				if(activeNode) {
					activeNode.reload();
				}
			}
		},
		cm:new Ext.grid.ColumnModel({
			defaults:{
				sortable:true
			},
			columns:fields.columns
		})
	});

	this.gridPanel.on('delayedrowselect', function (grid, rowIndex, r){
		this.fireEvent('fileselected', this, r);
	}, this);


	this.gridPanel.on('render', function(){
		//enable row sorting
		var DDtarget = new Ext.dd.DropTarget(this.gridPanel.getView().mainBody,
		{
			ddGroup : 'FilesDD',
			copy:false,
			notifyOver : this.onGridNotifyOver,
			notifyDrop : this.onGridNotifyDrop.createDelegate(this)
		});
	}, this);

	this.gridPanel.on('rowdblclick', this.onGridDoubleClick, this);


	/*
	 * Handles saving of locked state by the admin of the folder.
	 **/
	this.gridPanel.on('beforestatesave',function(grid, state){
		if(this.gridStore.reader.jsonData.lock_state){

			if (this.gridStore.reader.jsonData.may_apply_state)
				this.saveCMState(state);

			//cancel regular state save
			return false;
		}
	},this);

	// Trash has a separate grid
	this.trashGridPanel = new GO.files.TrashGrid(({
		id: config.id + '-trash-grid',
		store: this.trashGridStore,
		deleteConfig: {
			scope: this,
			success:function(){
				const activeNode = this.treePanel.getNodeById(this.folder_id);
				if (activeNode) {
					activeNode.reload();
				}
			}
		},
	}));

	this.filesContextMenu = new GO.files.FilesContextMenu();

	this.filesContextMenu.on('properties', function(menu, records){
		this.showPropertiesDialog(records[0]);
	}, this);

	this.filesContextMenu.on('cut', function(menu, records){
		this.onCutCopy('cut', records);
	}, this);

	this.filesContextMenu.on('copy', function(menu, records){
		this.onCutCopy('copy', records);
	}, this);

	this.filesContextMenu.on('delete', function(menu, records, clickedAt){
		this.onDelete(clickedAt);
	}, this);

	this.filesContextMenu.on('moveToTrash', function(menu, records, clickedAt){
		this.onMoveToTrash(clickedAt);
	}, this);


	this.filesContextMenu.on('compress', function(menu, records, clickedAt){
		this.onCompress(records);
	}, this);

	this.filesContextMenu.on('decompress', function(menu, records){
		this.onDecompress(records);
	}, this);

	this.filesContextMenu.on('download_link', function(menu, records, clickedAt, email){
		this.onDownloadLink(records,email);
	}, this);
	this.filesContextMenu.on('direct_link', function(menu, records){
		this.onDirectLink(records);
	}, this);

	this.filesContextMenu.on('email_files', function(menu, records){
		this.emailFiles(records);
	}, this);
	
	this.filesContextMenu.on('addBookmark', function(menu, folderId){
		this.bookmarksGrid.store.load();
	}, this);

	this.filesContextMenu.on('download_selected', function(menu, records, clickedAt){
		
		this.onDownloadSelected(records);
	}, this);
	this.filesContextMenu.on('batchEdit', function(menu, records, clickedAt){
		var ids = [];
		Ext.each(records, function (selected) {
			
			if(selected.get('locked')) {
				// error
				Ext.MessageBox.alert(t("Error"), t("File is locked", "files") + " :: " + selected.get('name'));
				return false;
			} else if(selected.get('type') == 'Folder') {
				// error
				Ext.MessageBox.alert(t("Error"), t("You can't edit this folder", "files") + " :: " + selected.get('name'));
				return false;
			} else {
				ids.push(selected.get('id'));
			}
		});
		
		if(ids.length > 0) {
			
			
			GO.base.model.showBatchEditModelDialog('GO\\Files\\Model\\File', ids, 'id',{}, 'id,folder_id,type_id,type,size,unlock_allowed,timestamp,thumb_url,readonly,permission_level,path,mtime,locked_user_id,locked,handler,extension,acl_id,name,status_id,muser_id,user_id,expire_time,random_code,delete_when_expired' ,t("Edit selection", "files"));
		}

	}, this);

	this.gridPanel.on('rowcontextmenu', this.onGridRowContextMenu, this);

	// todo: lower max disk space with quota, (in core)
	this.uploadFiles = new Ext.menu.Item({
		text: t('Files', 'files'),
		iconCls: 'ic-file-upload',
		handler: function() {
			let folder_id = this.folder_id;
			go.util.openFileDialog({
				multiple: true,
				directory: false,
				autoUpload: true,
				listeners: {
					uploadComplete: function(blobs) {
						blobs = this.transformBlobs(blobs);
						this.sendOverwrite({
							upload:true,
							blobs:Ext.encode(blobs),
							destination_folder_id: folder_id});
					},
					scope: this
				}
			});
		},
		scope:this
	});

	this.uploadFolders = new Ext.menu.Item({
		text: t("Folders",'files') + (Ext.isIE ? ' (Not supported by IE)': ''),
		iconCls: 'ic-file-upload',
		disabled: Ext.isIE,
		handler: function() {
			const folder_id = this.folder_id;
			go.util.openFileDialog({
				multiple: false,
				directory: true,
				autoUpload: true,
				listeners: {
					uploadComplete: function(blobs){
						blobs = this.transformBlobs(blobs);
						this.sendOverwrite({
							upload:true,
							blobs:Ext.encode(blobs),
							destination_folder_id: folder_id});
					},
					scope:this
				}
			});
		},
		scope:this
	});

	this.newMenu = new Ext.menu.Menu({
		items: []
	});


	if(!config.hideActionButtons) {
		this.newMenu.add(this.uploadFiles);
		this.newMenu.add(this.uploadFolders);
		this.newMenu.add("-");
	}

	this.newFolderBtn = new Ext.menu.Item({
		iconCls: 'ic-folder',
		text: t("Folder"),
		handler: this.promptNewFolder,
		scope: this
	});

	this.newMenu.add(this.newFolderBtn);

	this.newButton = new Ext.Button({
		tooltip:t("New"),
		iconCls: 'ic-add',
		cls: "primary",
		menu: this.newMenu
	});


   var quotaPercentage = (GO.settings.disk_quota && GO.settings.disk_quota>0) ? GO.settings.disk_usage/GO.settings.disk_quota : 0;
   this.upButton = new Ext.Button({
		iconCls: 'ic-arrow-upward',
		tooltip: t("Up"),
		handler: function(){
			if (GO.util.empty(this.gridStore.baseParams['query'])){
					this.setFolderID(this.parentID);
					this.updateLocation();
			}else{
					Ext.MessageBox.alert('',t("Can't do this when in search mode.", "files"));
			}
		},
		scope: this,
		disabled:true
	});

	this.moveToTrashButton = new Ext.menu.Item({
		iconCls: "ic-delete",
		text: t("Move to trash"),
		handler: () => {
			this.onMoveToTrash('grid');
		}
	});

	this.deleteButton = new Ext.menu.Item({
		iconCls: 'ic-delete',
		text: t("Delete"),
		overflowText:t("Delete"),
		handler: function(){
			this.onDelete('grid');
		},
		scope: this
	});

	this.cutButton= new Ext.Button({
		iconCls: 'ic-content-cut',
		tooltip: t("Cut"),
		overflowText:t("Cut"),
		handler: function(){
			var records = this.getSelectedGridRecords();
			this.onCutCopy('cut', records);
		},
		scope: this
	});
	this.copyButton = new Ext.Button({
		iconCls: 'ic-content-copy',
		tooltip: t("Copy"),
		overflowText:t("Copy"),
		handler: function(){
			var records = this.getSelectedGridRecords();
			this.onCutCopy('copy', records);
		},
		scope: this
	});
	this.pasteButton = new Ext.Button({
		iconCls: 'ic-content-paste',
		tooltip: t("Paste"),
		overflowText:t("Paste"),
		handler: this.onPaste,
		scope: this,
		disabled:true
	});
	
	this.emptyListButton = new Ext.Button({
		iconCls: 'ic-refresh',
		tooltip: t("Empty list", "files"),
		hidden:true,
		handler: function(){
			this.gridStore.baseParams.empty_new_files=true;
			this.gridStore.load();
			delete this.gridStore.baseParams.empty_new_files;
		},
		scope: this
	});

	const tbar = [
		{
			cls: 'go-narrow',
			iconCls: "ic-menu",
			handler: function () {
				this.westPanel.show();
			},
			scope: this
		},
		"->"
	];


	if(!config.hideActionButtons) {		
		tbar.push([this.cutButton,this.copyButton,this.pasteButton]);
	}
	
	this.thumbsToggle = new Ext.Button({
		tooltip: t("Thumbnails", "files"),
		iconCls: 'ic-view-comfy',
		enableToggle: true,
		toggleHandler: function(item, pressed){
			this.cardPanel.getLayout().setActiveItem(pressed?1:0);

			var thumbs = this.gridStore.reader.jsonData.thumbs=='1';
			if(thumbs!=pressed)
				GO.request({
					url:'files/folderPreference/submit',
					params: {
						folder_id: this.folder_id,
						thumbs: pressed ? '1' : '0'
					}
				});
		},
		scope:this
	});

	if(!config.hideActionButtons)
	{
		tbar.push('-',this.emptyListButton);
	}

	tbar.push(this.stateLockedButton = new Ext.Button({
		iconCls: 'ic-settings',
		text: t("Folder display locked by owner/admin", "files"),
		hidden: true,
		disabled: true,
		scope: this
	}));

	tbar.push(this.newButton);

	tbar.push(this.moreBtn = new Ext.Button({
		iconCls: 'ic-more-vert',
		tooltip: t("More"),
		menu: [
			{
				iconCls: "ic-refresh",
				text:t("Refresh"),
				overflowText:t("Refresh"),
				handler: function(){
					this.refresh(true);
				},
				scope:this
			}
		]
	}));

	if(!config.hideActionButtons) {
		this.moreBtn.menu.add(this.deleteButton, this.moveToTrashButton);
	}


	config.keys=[{
		ctrl:true,
		key: Ext.EventObject.C,
		fn:function(key, e){
			if(e.target.tagName == "INPUT") {
				return;
			}
			var records = this.getSelectedGridRecords();
			this.onCutCopy('copy', records);
		},
		scope:this
	},{
		ctrl:true,
		key: Ext.EventObject.X,
		fn:function(key,e){
			if(e.target.tagName == "INPUT") {
				return;
			}
			var records = this.getSelectedGridRecords();
			this.onCutCopy('cut', records);
		},
		scope:this
	},{
		ctrl:true,
		key: Ext.EventObject.V,
		fn:function(key,e,b){

			if(e.target.tagName == "INPUT") {
				return;
			}
			this.onPaste();

		},
		scope:this
	}];


	this.thumbsPanel = new GO.files.ThumbsPanel({
		store:this.gridStore
	});

	this.thumbsPanel.view.on('click', function(view, index,node,e){
		var record = view.store.getAt(index);
		this.fireEvent('fileselected', this, record);
	}, this);

	this.thumbsPanel.view.on('dblclick', function(view, index, node, e){

		var record = view.store.getAt(index);

		this.fireEvent('filedblclicked', this, record);

		if(record.data.extension=='folder')
		{
			this.setFolderID(record.data.id, true);
		}else
		{
			if(this.fileClickHandler)
			{				
				this.callFileClickHandler(record);
			}else
			{
				//GO.files.openFile({id:record.data.id});
				record.data.handler.call(this);
			}
		}
	}, this);

	this.thumbsPanel.view.on('contextmenu', function(view, index, node, e){

		if(!view.isSelected(index))
		{
			view.clearSelections();
			view.selectRange(index, index);
		}
		var records = view.getSelectedRecords();

		e.stopEvent();
		this.contextTreeID = node.id;

		var coords = e.getXY();
		this.filesContextMenu.showAt(coords, records);

	}, this);

	this.thumbsPanel.on('drop', function(targetID, dragRecords){
		go.User.confirmOnMove ?
			Ext.Msg.confirm(t('Confirm'), t('Are you sure you want to move the item(s)?'), function(btn) { if(btn == 'yes') this.paste('cut', targetID, dragRecords);}, this) :
			this.paste('cut', targetID, dragRecords);
	}, this);

	this.cardPanel = new Ext.Panel({
		region:'center',
		layout:'card',
		id:config.id+'-card-panel',
		tbar : {                        // configured using the anchor layout
			xtype : 'container',
			items :[ 
				new Ext.Toolbar({items: tbar, enableOverflow:true}),
				new Ext.Toolbar({
					layout:'hbox',
					layoutConfig: {
						align: 'middle',
						defaultMargins: {left: dp(4), right: dp(4),bottom:0,top:0}
					},
					items:[
						this.upButton,
						this.locationTextField = new Ext.form.TextField({
							fieldLabel:t("Location"),
							name:'files-location',
							readOnly:true,
							flex:1
						}),
						this.thumbsToggle,
						this.searchField = new go.toolbar.SearchButton({
							store: this.gridStore
					  })
					]
				}),
				new Ext.Toolbar({
					hidden: true,
					items: [
						"->",
						new Ext.Button({
							iconCls: 'ic-delete',
							tooltip: t("Delete permanently"),
							overflowText:t("Delete permanently"),
							handler: this.onDeletePermanently,
							scope: this,
						}),
						new Ext.Button({
							iconCls: 'ic-undo',
							tooltip: t("Restore"),
							overflowText:t("Restore"),
							handler: this.onRestore,
							scope: this,
						}),
					]
				})
			]
		},
		activeItem:0,
		deferredRender:false,
		border:false,
		items:[this.gridPanel, this.thumbsPanel, this.trashGridPanel]
	});

	this.cardPanel.on('afterrender', function() {
		GO.files.DnDFileUpload(function (blobs, folder_id) {

			if(this.permission_level < GO.permissionLevels.write) {
				GO.errorDialog.show(t("Access denied"));
				return
			}
			blobs = this.transformBlobs(blobs);
			this.sendOverwrite({upload: true, blobs: Ext.encode(blobs), destination_folder_id: folder_id});
		}.bind(this), this.cardPanel.body)(this);
	},this);

	this.eastPanel = new Ext.Panel({
		region:'east',
		layout:'card',
		activeItem: 0,
		collapsed:config.filePanelCollapsed,
		width:450,
		split:true,
		id: config.id+'fs-east-panel'
	});


	this.filePanel = new GO.files.FilePanel({
		id:config.id+'-file-panel',
		expandListenObject:this.eastPanel,
		hideMode: "offsets"
	});

	this.filePanel.getTopToolbar().insert(0, {
		cls: 'go-narrow',
		iconCls: "ic-arrow-back",
		handler: function () {
			this.mainContainer.show();
		},
		scope: this
	});

	this.eastPanel.add(this.filePanel);

	this.folderPanel = new GO.files.FolderPanel({
		id:config.id+'-folder-panel',
		hidden:true,
		expandListenObject:this.eastPanel,
		hideMode: "offsets"
	});
	this.folderPanel.getTopToolbar().insert(0, {
		cls: 'go-narrow',
		iconCls: "ic-arrow-back",
		handler: function () {
			this.mainContainer.show();
		},
		scope: this
	});

	this.eastPanel.add(this.folderPanel);

	config.items = [
		this.mainContainer = new Ext.Panel({
			border:false,
			region:'center',
			titlebar: false,
			layout:'responsive',
			items: [this.cardPanel,this.westPanel]
		}),
		this.eastPanel
	];

	config.layout='responsive';
	// change responsive mode on 1000 pixels
	config.layoutConfig = {
		triggerWidth: 1000
	};
	

	GO.files.FileBrowser.superclass.constructor.call(this, config);


	this.addEvents({
		fileselected: true,
		filedblclicked: true,
		refresh: true,
		folderIdSet: true,
		rootIdSet: true,
		search: true
	});

	this.on('fileselected',function(grid, r){
		if(r.data.extension!='folder'){
			this.eastPanel.show();
			this.eastPanel.getLayout().setActiveItem(this.filePanel);

			this.filePanel.load(r.id.substr(2));
		}else
		{
			if(GO.util.isMobileOrTablet()) {
				this.setFolderID(r.data.id, true);
			} else{
				this.eastPanel.show();			
			}
			// this.folderPanel.show();
			this.eastPanel.getLayout().setActiveItem(this.folderPanel);

			this.folderPanel.load(r.id.substr(2));			
		}

	}, this);

	this.bookmarksGrid.on('bookmarkClicked', function(bookmarksGrid,bookmarkRecord){
		this.setFolderID(bookmarkRecord.data['folder_id']);
		this.cardPanel.show();
	},this);
        
    this.on('beforeFolderIdSet',function(){

			if(this.gridStore.baseParams.query) {
				this.searchField.reset();
				delete this.gridStore.baseParams['query'];
			}

      // turn on buttons
      if (!GO.util.empty(this.gridStore.reader.jsonData))
        this.setWritePermission(this.gridStore.reader.jsonData.permission_level);
      this._enableFilesContextMenuButtons(true);
    },this);

    this.on('folderIdSet',function(){

      this.searchField.reset();
      delete this.gridStore.baseParams['query'];

      // turn on buttons
      if (!GO.util.empty(this.gridStore.reader.jsonData))
        this.setWritePermission(this.gridStore.reader.jsonData.permission_level);
      this._enableFilesContextMenuButtons(true);
    },this);

    this.on('refresh',function(){

      this.searchField.reset();
      delete this.gridStore.baseParams['query'];

      // turn on buttons
      if (!GO.util.empty(this.gridStore.reader.jsonData))
        this.setWritePermission(this.gridStore.reader.jsonData.permission_level);
      this._enableFilesContextMenuButtons(true);
    },this);

    this.on('search',function(){

      // turn off buttons
	  this.filesContextMenu.compressButton.setDisabled(true);
      this.filesContextMenu.decompressButton.setDisabled(true);
      //this._enableFilesContextMenuButtons(false);
      this.setWritePermission(0);

    },this);

}

Ext.extend(GO.files.FileBrowser, Ext.Panel,{
	ready:false,
	cls: 'fs-filebrowser',

	fileClickHandler : false,
	scope : this,
	path : '',

	transformBlobs : function(blobs) {
		return blobs.map(function(b)  {
			return {
				subfolder: b.subfolder,
				name: b.name,
				id: b.id
			};
		});
	},

	_enableFilesContextMenuButtons: function (enable) {
		this.filesContextMenu.cutButton.setDisabled(!enable);
		this.filesContextMenu.copyButton.setDisabled(!enable);
		this.filesContextMenu.compressButton.setDisabled(!enable);
		this.filesContextMenu.decompressButton.setDisabled(!enable);
		this.filesContextMenu.createDownloadLinkButton.setDisabled(!enable);
		this.filesContextMenu.createDirectLinkButton.setDisabled(!enable);

		if (!GO.util.empty(this.filesContextMenu.gotaButton))
			this.filesContextMenu.gotaButton.setDisabled(!enable);

		if (!GO.util.empty(this.filesContextMenu.downloadLinkButton))
			this.filesContextMenu.downloadLinkButton.setDisabled(!enable);

		if (!GO.util.empty(this.filesContextMenu.emailFilesButton))
			this.filesContextMenu.emailFilesButton.setDisabled(!enable);

		this.filesContextMenu.downloadSelectedFilesButton.setDisabled(!enable);
	},
				
	callFileClickHandler : function(record) {
		if(!this.createBlobs) {
			GO.selectFileBrowser.fileClickHandler.call(GO.selectFileBrowser.scope, record);
		} else{

			var records = this.getSelectedGridRecords(), ids = [];

			records.forEach(function(r) {
				ids.push(r.data.id);
			});

			GO.request({
				url: "files/file/createBlob",
				params: {
					ids: ids.join(',')
				},
				success: function(response, options, result) {
					GO.selectFileBrowser.fileClickHandler.call(GO.selectFileBrowser.scope, result.blobs);
				}
			});
		}
	},

	saveCMState: function(state) {
		GO.request({
			url: "files/folder/submit",
			params : {
				'id' : this.folder_id,
				'cm_state' : Ext.encode(state)
			},
			scope: this
		})
	},

	onStoreLoad : function(store){
		var state;

		if (store.reader.jsonData.lock_state && store.reader.jsonData.cm_state!='') {
			state = Ext.decode(store.reader.jsonData.cm_state);
		}else
		{
			state = Ext.state.Manager.get(this.gridPanel.id);
		}
		
		if (store.reader.jsonData.disk_usage!==null ) {
			GO.settings.disk_usage = store.reader.jsonData.disk_usage;
		} else {
			delete GO.settings.disk_usage;
		}
		
		if(store.reader.jsonData.disk_quota !== null) {
			GO.settings.disk_quota = store.reader.jsonData.disk_quota;
		} else {
			delete GO.settings.disk_quota;
		}
		
		if(typeof GO.settings.disk_usage!='undefined') {
			this.quotaBar.removeClass('warning');
			this.quotaBar.removeClass('error');
			
			var quotaPercentage = (GO.settings.disk_quota && GO.settings.disk_quota>0) ? GO.settings.disk_usage/GO.settings.disk_quota : 0;
			
			
			var text ='';
			text = '('+GO.settings.disk_usage+'MB) ';
			
			
			if(typeof GO.settings.disk_quota!='undefined') {
				
				if(quotaPercentage==0 && GO.settings.disk_quota==0) {
					quotaPercentage = 1;
				}
					
				text = Math.round(quotaPercentage*100)+'% ('+ GO.settings.disk_usage+' of '+GO.settings.disk_quota+'MB)';
				this.quotaBar.updateProgress(quotaPercentage, text);

				
					if(quotaPercentage*100 > 99) {
						this.quotaBar.addClass('error');
					} else if(quotaPercentage*100 > 75) {
						this.quotaBar.addClass('warning');
					}
				} else if(GO.settings.disk_usage) {
					this.quotaBar.updateProgress(100, text);
				} else {
					this.quotaBar.updateProgress(0, text);
				}
				
				//Tell the maximun filesize is the disk quota

				if(typeof GO.settings.disk_quota != ' undefined') {
					var remainingDiskSpace = Math.ceil((GO.settings.disk_quota-GO.settings.disk_usage)*1024*1024);
				} else {
					var remainingDiskSpace = 0
				}
			}

		if(state){
			this.gridPanel.applyStoredState(state);

			if(store.reader.jsonData.lock_state && store.reader.jsonData.cm_state==''){
				//locked state is not stored yet do it now
				this.saveCMState(state);
			}
		}


		this.stateLockedButton.setVisible(store.reader.jsonData.lock_state);

		if(!GO.util.empty(store.reader.jsonData.feedback))
		{
			alert(store.reader.jsonData.feedback);
		}

		this.path = store.reader.jsonData.path;

		this.setWritePermission(store.reader.jsonData.permission_level);

		this.thumbsToggle.toggle(store.reader.jsonData.thumbs=='1');

		if(this.folder_id=='new')
		{
			var num_files = store.reader.jsonData.num_files;
			var activeNode = this.treePanel.getNodeById('new');
			if(activeNode)
				activeNode.setText(t("New", "files") + " (" + num_files + ")");
		}

		this.emptyListButton.setVisible(this.folder_id=='new' && num_files > 0);

		if(store.reader.jsonData.refreshed)
		{
			var activeNode = this.treePanel.getNodeById(this.folder_id);
			if(activeNode)
			{
				delete activeNode.attributes.children;
				activeNode.reload();
			}
		}

		this.parentID = store.reader.jsonData.parent_id;
		var folderId = store.baseParams.folder_id;
		
		if(!this.initTreeFromGrid && this.parentID && !this.treePanel.getNodeById(folderId)) {
			
			//prevent infite loop when tree doesn't load node because of 500 node limit.
			this.initTreeFromGrid = true;
			
			this.treePanel.setExpandFolderId(folderId);
			this.treePanel.getRootNode().reload();
		}

		if(!this.parentID)  {
			this.upButton.setDisabled(true);
		} else {
			this.upButton.setDisabled(false);
		}
	},


	setFileClickHandler : function(handler, scope, createBlobs)
	{
		this.fileClickHandler = handler;
		this.createBlobs = createBlobs;
		this.scope = scope;
	},

	/**
	 * The filter parameter needs to be a comma separated string of file extensions.
	 * Example: 'jpg,png,xls,xlsx,pdf'
	 * 
	 */
	setFilesFilter : function(filter)
	{
		var old_filter = this.gridStore.baseParams['files_filter'];
		this.gridStore.baseParams['files_filter']=filter;

		if((old_filter != undefined) && old_filter != filter)
		{
			this.gridStore.reload();
		}
	},


	afterRender : function(){
		GO.files.FileBrowser.superclass.afterRender.call(this);

		GO.files.filePropertiesDialogListeners={
			scope:this,
			save:function(dlg, file_id, folder_id){
				if(this.folder_id==folder_id)
				{
					this.getActiveGridStore().load();
				}
			}
		}

		GO.files.folderPropertiesDialogListeners={
			scope:this,
			save:function(dlg, folder_id, parent_id){
				if(parent_id==this.folder_id)
				{
					this.setFolderID(parent_id, false, true);
				}
				var node = this.treePanel.getNodeById(parent_id);
				if(node)
				{
					delete node.attributes.children;
					node.reload();
				}
			}
		}
		
		if(!this.treePanel.getLoader().baseParams.root_folder_id)
			this.bookmarksGrid.store.load();
		
		this.buildNewMenu();
	},


	setRootID : function(rootID, folder_id)
	{
		
		// this.searchField.setDisabled(!!rootID);
		rootID ? this.bookmarksGrid.hide() : this.bookmarksGrid.show();
		
		this.doLayout();		
		
		if(this.treePanel.getLoader().baseParams.root_folder_id!=rootID || (folder_id>0 && this.folder_id!=folder_id)){
				
				this.folder_id=folder_id;
				this.treePanel.getLoader().baseParams.root_folder_id=rootID;
				this.treePanel.getRootNode().reload();
				
				this.treePanel.setExpandFolderId(folder_id);
				
				if(folder_id || folder_id==0) {
					this.setFolderID(this.folder_id);
				}
		}
	},

	buildNewMenu : function(){

		if(this.hideActionButtons) {
			return;
		}

		var l = this.newMenu.items.getCount();

		if(l > 3) {
			for(var i = l - 1; i > 3; i--) {
				this.newMenu.items.itemAt(i).destroy();			
			}
		}

		GO.request({
			url: 'files/template/store',
			success: function(response, options, result)
			{
			
				if(result.results.length)
				{
					this.newMenu.add('-');
					for(var i=0;i<result.results.length;i++)
					{
						var template = result.results[i];

						var menuItem = new Ext.menu.Item({
							iconCls:'filetype filetype-'+template.extension,
							text: template.name,
							template_id:template.id,
							handler: function(item){

								this.createFileFromTemplate(item.template_id);
							},
							scope:this
						});

						this.newMenu.add(menuItem);
					}
				}

				if(go.Modules.get("legacy", "files").userRights.mayManage)
				{
					this.newMenu.add('-');

					this.newMenu.add({
						iconCls: 'ic-filter-none',
						text: t("Manage templates", "files"),
						handler: function(){
							if(!this.templatesWindow)
							{
								this.templatesWindow = new GO.files.TemplateWindow();
								this.templatesWindow.gridStore.on('datachanged', function(){
									if(!this.templatesWindow.firstLoad)
									{
										this.buildNewMenu();
									}
								}, this);
							}
							this.templatesWindow.show();
						},
						scope: this
					});
				}

			},
			scope: this
		});
	},

	createFileFromTemplate : function(template_id, filename){

		if(!filename || filename == '')
		{
			Ext.Msg.prompt(t("Enter a name", "files"), t("Please enter a name", "files"),
				function(id, filename){
					if(id=='cancel')
						return false;
					else
						this.createFileFromTemplate(template_id, filename);
				},this);
		}else
		{
			var store = this.getActiveGridStore();

			GO.request({
				url: 'files/template/createFile',
				params:{
					template_id:template_id,
					folder_id:this.folder_id,
					filename: filename
				},
				success: function(response, options, result)
				{
					store.load({
						callback: function(){
							if(result.id)
							{
								GO.files.openFile({id: result.id});
							}
						},
						scope: this
					});
				},
				scope:this
			});
		}
	},

	onDecompress : function(records){

            if (GO.util.empty(this.gridStore.baseParams['query'])) {

		var decompress_sources = [];
		for(var i=0;i<records.length;i++)
		{
			decompress_sources.push(records[i].data.path);
		}

		if(decompress_sources.length)
		{
			var store = this.getActiveGridStore();
			var params = {};
			params['decompress_sources']=Ext.encode(decompress_sources);
			params.working_folder_id=this.folder_id;

			GO.request({
				timeout:300000,
				maskEl:this.getEl(),
				url:'files/folder/decompress',
				params:params,
				success:function(){
					store.load();
				}
			});
		}
               
            } else {
                Ext.MessageBox.alert('', t("Can't do this when in search mode.", "files"));
            }
	},

	onCompress : function(records, filename, utf8)
	{

  	  if (GO.util.empty(this.gridStore.baseParams['query'])) {

			var params = {
				compress_sources: [],
				working_folder_id:this.folder_id,
				destination_folder_id:this.folder_id
			};

			for(var i=0;i<records.length;i++)
			{
				if(records[i].data.parent_id)//for tree
					params.working_folder_id=records[i].data.parent_id;

				params.compress_sources.push(records[i].data.path);
			}
			

			if(!filename || filename == '')
			{
				this.compressRecords = records;
				
				if(!this.compressDialog){
					this.compressDialog = new GO.files.CompressDialog ({
						scope:this,
						handler:function(win, filename, utf8){
							this.onCompress(this.compressRecords, filename, utf8);
						}
					});
				}

				this.compressDialog.show();

			}else
			{
				params.archive_name=filename;
				params.utf8=utf8 ? '1' : '0';
				params.compress_sources=Ext.encode(params.compress_sources);
				var store = this.getActiveGridStore();

				GO.request({
					timeout:300000,
					maskEl:this.getEl(),
					url:'files/folder/compress',
					params:params,
					success:function(){
						store.load();
					}
				});
			}

		} else {
				Ext.MessageBox.alert('', t("Can't do this when in search mode.", "files"));
		}

	},
	
	onDownloadSelected : function(records, filename, utf8)	{

		var params = {
			sources: []
		};

		for(var i=0;i<records.length;i++){
			params.sources.push(records[i].data.path);
		}

		if(!filename || filename == ''){
			
			this.compressRecords = records;

			if(!this.downloadCompressedDialog){
				this.downloadCompressedDialog = new GO.files.CompressDialog ({
					scope:this,
					handler:function(win, filename, utf8){
						this.onDownloadSelected(this.compressRecords, filename, utf8);
					}
				});
			}

			this.downloadCompressedDialog.show();
			
		} else {
			
			params.archive_name=filename;
			params.utf8=utf8 ? '1' : '0';
			params.sources=Ext.encode(params.sources);
      
			GO.request({
				timeout:300000,
				maskEl:this.getEl(),
				url:'files/folder/compressAndDownload',
				params:params,
				success:function(response, options, result){
					
					if(!GO.util.empty(result.archive)){
						go.util.downloadFile(GO.url("core/downloadTempFile",{path:result.archive}));
					} else {
            			win.close();
						GO.message.alert('No archive build','error');
					}
				}
			});
		}
	},

	getSelectedTreeRecords : function(){
		var sm = this.treePanel.getSelectionModel();
		var nodes = sm.getSelectedNodes();
		var records=[];

		for(var i=0;i<nodes.length;i++)
		{
			records.push({
				data: {
					type_id:'d:'+nodes[i].id,
					id: nodes[i].id,
					extension:'folder',
					path: nodes[i].attributes.path
				}
			});
		}
		return records;
	},

	getSelectedGridRecords : function(){
		//detect grid on selModel. thumbs doesn't have that
		if(this.cardPanel.getLayout().activeItem.selModel)
		{
			const selModel = this.gridPanel.getSelectionModel();
			return selModel.getSelections();
		}else
		{
			return this.thumbsPanel.view.getSelectedRecords();
		}
	},

	getActiveGridStore : function() {
		if (this.folder_id && this.folder_id === "trash") {
			return this.trashGridStore;
		}
		return this.gridStore;
	},

	onCutCopy : function(pasteMode, records){
		GO.files.pasteSelections=records;
		GO.files.pasteMode=pasteMode;
		if(GO.files.pasteSelections.length)
		{
			this.pasteButton.setDisabled(false);
		}
	},

	onPaste : function(){
		if (GO.util.empty(this.gridStore.baseParams['query'])) {
			this.paste(GO.files.pasteMode, this.folder_id, GO.files.pasteSelections);
		} else {
			Ext.MessageBox.alert('', t("Can't do this when in search mode.", "files"));
		}
		GO.files.pasteSelections = Array();
	},

	onDelete : function(clickedAt) {
		if (clickedAt === 'tree') {
			const records = this.getSelectedTreeRecords();
			GO.deleteItems({
				url: GO.url('files/folder/delete'),
				params: {
					id: records[0].data.id
				},
				count: 1,
				callback: function (responseParams) {

					if (responseParams.success) {
						const treeNode = this.treePanel.getNodeById(records[0].data.id);
						if (treeNode) {
							//parentNode is destroyed after remove so keep it for later use
							const parentNodeId = treeNode.parentNode.id;
							treeNode.remove();

							const activeTreenode = this.treePanel.getNodeById(this.folder_id);
							if (!activeTreenode) {
								//current folder must have been removed. Let's go up.
								this.setFolderID(parentNodeId);
							}
						}
					}
				},
				scope: this
			});
		} else {
			//detect grid on selModel. thumbs doesn't have that
			if (this.cardPanel.getLayout().activeItem.id === this.gridPanel.id) {
				this.gridPanel.deleteSelected({
					callback: function () {
						const treeNode = this.treePanel.getNodeById(this.folder_id);
						if (treeNode) {
							delete treeNode.attributes.children;
							treeNode.reload();
						}
					},
					scope: this
				});
			} else {
				this.thumbsPanel.deleteSelected({
					callback: function () {
						const treeNode = this.treePanel.getNodeById(this.folder_id);
						if (treeNode) {
							delete treeNode.attributes.children;
							treeNode.reload();
						}
					},
					scope: this
				});
			}
		}
	},

	onMoveToTrash: function (clickedAt) {
		if (clickedAt === 'tree') {
			// TODO?
		} else {
			//detect grid on selModel. thumbs doesn't have that
			if (this.cardPanel.getLayout().activeItem.id === this.gridPanel.id) {
				this.gridPanel.deleteSelected({
					deleteParam: "trash_keys",
					callback: function () {
						const treeNode = this.treePanel.getNodeById(this.folder_id);
						if (treeNode) {
							delete treeNode.attributes.children;
							treeNode.reload();
						}
						this.gridStore.reload();
						this.trashGridStore.reload();
					},
					scope: this
				});
			} else {
				this.thumbsPanel.deleteSelected({
					deleteParam: "trash_keys",
					callback: function () {
						const treeNode = this.treePanel.getNodeById(this.folder_id);
						if (treeNode) {
							delete treeNode.attributes.children;
							treeNode.reload();
						}
						this.gridStore.reload();
						this.trashGridStore.reload();
					},
					scope: this
				});
			}
		}
	},

	onRestore: function (clickedAt) {
		const selected = this.trashGridPanel.getSelectionModel().getSelections();
		let strConfirm;
		switch (selected.length) {
			case 0:
				alert(t("You didn't select an item."));
				return false;
			case 1:
				strConfirm =t("Are you sure you want to restore the selected item?");
				break;

			default:
				var template = new Ext.Template(
					t("Are you sure you want to restore the {count} items?")
				);
				strConfirm = template.applyTemplate({'count': selected.length});
				break;
		}
		const ids = [];
		for (const s of selected) {
			ids.push(s.data.id);
		}
		if(confirm(strConfirm)) {
			GO.request({
				url:'files/folder/restore',
				params:{
					ids: ids.join(",")
				},
				success:function(action, response, result){
					// this.trashGridPanel.store.reload();
					const treeNode = this.treePanel.getNodeById(this.folder_id);
					if (treeNode) {
						delete treeNode.attributes.children;
						treeNode.reload();
					}
					this.gridStore.reload();
					this.trashGridStore.reload();

				},
				scope:this
			})
		}
	},
	onDeletePermanently: function() {
		const selected = this.trashGridPanel.getSelectionModel().getSelections();
		let strConfirm;
		switch (selected.length) {
			case 0:
				alert(t("You didn't select an item."));
				return false;
			case 1:
				strConfirm =t("Are you sure you want to delete the selected item permanently?");
				break;

			default:
				var template = new Ext.Template(
					t("Are you sure you want to delete the {count} items permanently?")
				);
				strConfirm = template.applyTemplate({'count': selected.length});
				break;
		}
		const ids = [];
		for (const s of selected) {
			ids.push(s.data.id);
		}
		if(confirm(strConfirm)) {
			GO.request({
				url:'files/folder/deleteFromTrash',
				params:{
					ids: ids.join(",")
				},
				success:function(action, response, result){
					this.gridStore.reload();
					this.trashGridStore.reload();
				},
				scope:this
			})
		}
	},

	emailFiles: function(records) {
		var files = [];
		Ext.each(records, function(record) {
			var folderId = record.data.folder_id;
			var id = record.data.id;

			if (!Ext.isEmpty(folderId)) {
				if(record.data.name.lastIndexOf('/') > -1) {
					record.data.name = String(record.data.name).split('/').reverse()[0];
				}
				files.push(record.data);
			} else {
				GO.email.openFolderTree(id);
			}
		});
		GO.email.emailFiles(files);
	},

	onDownloadLink : function(records,email){
		GO.files.createDownloadLink(records,email);
	},

	onDirectLink: function(records) {
		for ( const record of records ) {
			const arId = record.id.split(":");
			if(arId.length !== 2) {
				return; // We do not know what to link
			}
			let url = GO.settings.config.full_url + "#" + (arId[0] === "f" ? "file" : "folder") + "/" + arId[1];
			go.util.copyTextToClipboard(url);
			Ext.MessageBox.alert(t("Success"), t("Value copied to clipboard"));
		}
	},

	onGridNotifyOver : function(dd, e, data){
		var dragData = dd.getDragData(e);
		if(data.grid)
		{
			var dropRecord = data.grid.store.data.items[dragData.rowIndex];
			if(dropRecord)
			{
				if(dropRecord.data.extension=='folder')
				{
					for(var i=0;i<data.selections.length;i++)
					{
						if(data.selections[i].data.id==dropRecord.data.id)
						{
							return false;
						}
					}
					return this.dropAllowed;
				}
			}
		}
		return false;
	},

	onGridNotifyDrop : function(dd, e, data)
	{
		if(data.grid)
		{
			var sm=data.grid.getSelectionModel();
			var rows=sm.getSelections();
			var dragData = dd.getDragData(e);

			var dropRecord = data.grid.store.data.items[dragData.rowIndex];

			if(!dropRecord) {
				return false;
			}

			if(dropRecord.data.extension=='folder')
			{
				for(var i=0;i<data.selections.length;i++)
				{
					if(data.selections[i].data.id==dropRecord.data.id)
					{
						return false;
					}
				}
				go.User.confirmOnMove ?
					Ext.Msg.confirm(t('Confirm'), t('Are you sure you want to move the item(s)?'), function(btn) { if(btn == 'yes') this.paste('cut', dropRecord.data.id, data.selections);}, this) :
					this.paste('cut', dropRecord.data.id, data.selections);
			}
		}else
		{
			return false;
		}
	},

	onGridRowContextMenu : function(grid, rowIndex, e) {
		var selections = grid.getSelectionModel().getSelections();

		var coords = e.getXY();
		this.filesContextMenu.showAt(coords, selections, 'grid');
	},

	paste : function(pasteMode, destination, records)
	{
		var paste_sources = Array();

		for(var i=0;i<records.length;i++)
		{
			paste_sources.push(records[i].data['type_id']);
		}

		var params = {
			ids : Ext.encode(paste_sources),
			destination_folder_id : destination,
			paste_mode : pasteMode,
			id : this.folder_id
		};

		this.sendOverwrite(params);

	},


	refresh : function(syncFilesystemWithDatabase){
		
		this.getActiveGridStore().baseParams['folder_id'] = null;
		
		this.treePanel.setExpandFolderId(this.folder_id);
		
		if(syncFilesystemWithDatabase)
			this.treePanel.getLoader().baseParams.sync_folder_id=this.folder_id;

		
		this.treePanel.getRootNode().reload();
		
		this.setFolderID(this.folder_id);

		if(syncFilesystemWithDatabase)
			delete this.treePanel.getLoader().baseParams.sync_folder_id;

		this.searchField.reset();
		delete this.gridStore.baseParams['query'];

		this.filePanel.reload();
                
		this.fireEvent('refresh');
	},

	sendOverwrite : function(params) {

		if(!params.command)
			params.command='ask';

		if(!params.destination_folder_id)
			params.destination_folder_id=this.folder_id;

		this.overwriteParams = params;
		this.getEl() && this.getEl().mask(t("Saving..."));

		var url = params.upload ? GO.url('files/folder/processUploadQueue') : GO.url('files/folder/paste');

		Ext.Ajax.request({
			url: url,
			params:this.overwriteParams,
			callback: function(options, success, response){

				this.getEl() && this.getEl().unmask();

				var pasteSources = Ext.decode(this.overwriteParams.ids);
				var pasteDestination = this.overwriteParams.destination_folder_id;

				if(!success)
				{
					Ext.MessageBox.alert(t("Error"), t("Could not connect to the server. Please check your internet connection."));
				}else
				{

					var responseParams = Ext.decode(response.responseText);

					if(!responseParams.success && !responseParams.fileExists)
					{
						if(this.overwriteDialog)
						{
							this.overwriteDialog.hide();
						}
						Ext.MessageBox.alert(t("Error"), responseParams.feedback);
						this.refresh();
					}else
					{
						if(responseParams.fileExists)
						{
							if(!this.overwriteDialog)
							{

								this.overwriteDialog = new Ext.Window({
									width:500,
									autoHeight:true,
									closeable:false,
									closeAction:'hide',
									plain:true,
									border: false,
									title:t("File exists"),
									modal:false,
									buttons: [
									{
										text: t("Yes"),
										handler: function(){
											this.overwriteParams.overwrite='yes';
											this.sendOverwrite(this.overwriteParams);
										},
										scope: this
									},{
										text: t("Yes to all"),
										handler: function(){
											this.overwriteParams.overwrite='yestoall';
											this.sendOverwrite(this.overwriteParams);
										},
										scope: this
									},{
										text: t("No"),
										handler: function(){
											this.overwriteParams.overwrite='no';
											this.sendOverwrite(this.overwriteParams);
										},
										scope: this
									},{
										text: t("No to all"),
										handler: function(){
											this.overwriteParams.overwrite='notoall';
											this.sendOverwrite(this.overwriteParams);
										},
										scope: this
									},{
										text: t("Cancel"),
										handler: function(){
											this.getActiveGridStore().reload();
											this.overwriteDialog.hide();
										},
										scope: this
									}]

								});
								this.overwriteDialog.render(Ext.getBody());
							}

							var tpl = new Ext.Template(t("Do you wish to overwrite the file '{file}'?"));
							tpl.overwrite(this.overwriteDialog.body, {
								file: responseParams.fileExists
							});
							this.overwriteDialog.show();
						}else
						{
							//this.getActiveGridStore().reload();
							var store = this.getActiveGridStore();
							if(!pasteDestination || pasteDestination==this.folder_id)
							{
								store.reload();
							}else if(pasteSources)
							{
								for(var i=0;i<pasteSources.length;i++)
								{
									var record = store.getById(pasteSources[i]);
									if(record)
									{
										store.reload();
										break;
									}
								}
							}

							if(pasteSources && params.paste_mode=="cut")
							{
								//remove moved nodes if we cut and paste
								for(var i=0;i<pasteSources.length;i++)
								{
									var arr = pasteSources[i].split(':');
									if(arr[0] != 'd') {
										continue;
									}
									var node = this.treePanel.getNodeById(arr[1]);
									if(node)
										node.remove();
								}
							}

							var destinationNode = this.treePanel.getNodeById(pasteDestination);
							if(destinationNode)
							{
								delete destinationNode.attributes.children;
								destinationNode.reload();
							}

							if(this.overwriteDialog)
								this.overwriteDialog.hide();
							if(params.cb) {
								params.cb(); // run this callback after files are processed in FileDetailPanel
							}
						}
					}
				}
			},
			scope: this
		});

	},

	promptNewFolder : function(){

		if (GO.util.empty(this.gridStore.baseParams['query'])) {
	
			if(!this.newFolderWindow)
			{
				this.newFolderWindow = new GO.files.NewFolderDialog();
				this.newFolderWindow.on('save', function(){
					this.getActiveGridStore().load();

					// problem if folder didn't have a subfolder yet
					// fixed by reloading parent
					var activeNode = this.treePanel.getNodeById(this.folder_id);
					if(activeNode)
					{
						// delete preloaded children otherwise no
						// request will be sent
						delete activeNode.attributes.children;
						activeNode.reload();
					}
				},this);
			}
			this.newFolderWindow.show(this.folder_id);
            	
		} else {
			Ext.MessageBox.alert('', t("Can't do this when in search mode.", "files"));
		}
	},

	onGridDoubleClick : function(grid, rowClicked, e){
		var selectionModel = grid.getSelectionModel();
		var record = selectionModel.getSelected();

		this.fireEvent('filedblclicked', this, record);

		if(record.data.extension=='folder') {
			this.setFolderID(record.data.id, true);
		}else {
			if(this.fileClickHandler) {
				this.callFileClickHandler(record);
			}else {
				record.data.handler.call(this);
			}
		}
	},

	setWritePermission : function(permissionLevel)
	{
		this.permission_level = permissionLevel;

		var writePermission=permissionLevel>=GO.permissionLevels.write;
		var deletePermission=permissionLevel>=GO.permissionLevels.writeAndDelete;
		var createPermission=permissionLevel>=GO.permissionLevels.create;

		this.newButton.setDisabled(!createPermission);
		this.deleteButton.setDisabled(!deletePermission);
		this.moveToTrashButton.setDisabled(!deletePermission);

		this.cutButton.setDisabled(!deletePermission);
                
		this.copyButton.setDisabled(permissionLevel<=0);
                
		this.pasteButton.setDisabled(!writePermission || !GO.files.pasteSelections.length);
	},

	setFolderID : function(id, expand, forceReload) {
	    this.expandTree=expand;
		this.fireEvent('beforeFolderIdSet');
		  
		this.folder_id = id;

		if(this.dndUpload) {
			this.dndUpload.folder_id = id;
		}

		if(forceReload || this.getActiveGridStore().baseParams['folder_id'] != id) {
			
			this.getActiveGridStore().baseParams['folder_id']=id;

			this.getActiveGridStore().load({
				callback:function(){

					if(this.expandTree)
					{
						var activeNode = this.treePanel.getNodeById(id);

						if(activeNode){
							activeNode.expand();
						}else{
							this.treePanel.setExpandFolderId(id);
							this.treePanel.getRootNode().reload();	
						}
					}
					this.updateLocation();
					this.focus();
				},
				scope:this
			});
		}
		
	},
	
	updateLocation : function(){
		var activeNode = this.treePanel.getNodeById(this.folder_id);

		if(this.gridStore.reader.jsonData) {
			this.locationTextField.setValue(this.gridStore.reader.jsonData.path);
		}
		
		if(this.treePanel.getRootNode().findChild('id',this.gridStore.baseParams.folder_id)) {
			this.upButton.setDisabled(true);
		} else {
			this.upButton.setDisabled(false);
		}
		
	},

	showGridPropertiesDialog  : function(){
		var selModel = this.gridPanel.getSelectionModel();
		var selections = selModel.getSelections();

		if(selections.length==0)
		{
			GO.errorDialog.show(t("You didn't select an item."));
		}else if(selections.length>1)
		{
			GO.errorDialog.show(t("Please select only one item", "files"));
		}else
		{
			this.showPropertiesDialog(selections[0]);
		}
	},

	showPropertiesDialog : function(record)
	{
		if(record.data.extension=='folder')
		{
			GO.files.showFolderPropertiesDialog(record.data.id);
		}else
		{
			GO.files.showFilePropertiesDialog(record.data.id);
		}
	},
	
	onReady : function(fn, scope){
		if(this.ready){
			fn.call(scope, this);
		}else
		{
			this.on('filebrowserready', fn, scope);
		}
	},

	route: function(id, entity) {
		this.show();
		const entityId = (entity.name === "File" ? "f": "d")+":"+id;
		const detailViewName = entity.name.toLowerCase() + "Panel";
		this[detailViewName].on("load", function(dv){
			this.setFolderID(dv.data.folder_id || dv.data.parent_id, true);
		}, this, {single: true});
		
		this.gridStore.on("load", function(a,b,c) {
			const idx = b.findIndex( (bb) => bb.id === entityId);
			if(idx > -1 && !this.thumbsToggle.pressed) {
				const sm = this.gridPanel.getSelectionModel();
				sm.selectRow(idx);
			}
		}, this, {single: true});
		this[detailViewName].load(parseInt(id));
		this.eastPanel.getLayout().setActiveItem(this[detailViewName]);
	}
});

GO.files.createDownloadLink = function(records,email){

	if(!GO.files.expireDateDialog){
		GO.files.expireDateDialog = new GO.files.ExpireDateDialog();
	}
	
	GO.files.expireDateDialog.show(records, email, false);
}

GO.files.showFilePropertiesDialog = function(file_id){

	if(!GO.files.filePropertiesDialog)
		GO.files.filePropertiesDialog = new GO.files.FilePropertiesDialog();

	if(GO.files.filePropertiesDialogListeners){

		GO.files.filePropertiesDialog.on(GO.files.filePropertiesDialogListeners);
		delete GO.files.filePropertiesDialogListeners;
	}

	GO.files.filePropertiesDialog.show(file_id);
}

GO.files.showFolderPropertiesDialog = function(folder_id){

	if(!GO.files.folderPropertiesDialog)
		GO.files.folderPropertiesDialog = new GO.files.FolderPropertiesDialog();

	if(GO.files.folderPropertiesDialogListeners){
		GO.files.folderPropertiesDialog.on(GO.files.folderPropertiesDialogListeners);
		delete GO.files.folderPropertiesDialogListeners;
	}

	GO.files.folderPropertiesDialog.show(folder_id);
}

GO.mainLayout.onReady(function(){

	GO.checker.registerRequest("files/notification/unsent",{},function(checker, data){});
});


GO.files.FilesObservable = function(){
	GO.files.FilesObservable.superclass.constructor.call(this);

	this.addEvents({
		'beforeopenfile':true
	})
}
Ext.extend(GO.files.FilesObservable, Ext.util.Observable);

GO.files.filesObservable = new GO.files.FilesObservable();

GO.files.showImageViewer = function(imagesParams){
	if(!this.imageViewer)
	{
		this.imageViewer = new GO.files.ImageViewer({
			closeAction:'hide'
		});
	}
	
	imagesParams["thumbParams"]=Ext.encode({lw:this.imageViewer.width-20,ph:this.imageViewer.height-100});

GO.request({
		url:"files/folder/images",
		params:imagesParams,
		maskEl:Ext.getBody(),
		success:function(response, options, result){
			this.imageViewer.show(result.images, result.index);
		},
		scope:this
	});
}

/**
 * @example:
 *
 * {
				id:this.records[0].data.id,
				all:'1' //show all handlers instead of using the default
			}
 *
 * @param config
 */
GO.files.openFile = function(config)
{		
	if(!GO.files.openFileWindow){
		GO.files.openFileWindow =  new GO.files.OpenFileWindow();
	}
	GO.files.openFileWindow.show(config);
}

GO.files.openEmailAttachment = function(attachment, panel, choosehandler)
{
	Ext.getBody().mask(t("Loading..."));
	var params = {
		account_id: panel.account_id,
		mailbox: panel.mailbox,
		uid: panel.uid,
		number: attachment.number,
		uuencoded_partnumber: attachment.uuencoded_partnumber,
		encoding: attachment.encoding,
		type: attachment.type,
		subtype: attachment.subtype,
		filename: attachment.name,
		charset: attachment.charset,
		sender: panel.data.sender, //for gnupg and smime,
		tmp_file: attachment.tmp_file || ''
	}
	GO.request({
		url: "files/file/saveAttachmentToTmp",
		params: params,
		callback: function() {
			Ext.getBody().unmask();
		},
		success: function(response, options, result) {
			Ext.getBody().unmask();
			GO.files.openFile({
				id: result.data.id,
				all: choosehandler ? "1" : "0"
			});
		}
	})
}


GO.files.downloadFile = function (fileId){
	var url = GO.url("files/file/download",{id:fileId,inline:false});
	go.util.downloadFile(url);
}

//for external links
GO.files.showFolder = function(folder_id){

	var fb = GO.mainLayout.openModule("files");
	
	fb.onReady(function(){
		fb.setFolderID(folder_id, true);
	}, this);
	
	return fb;
};

GO.files.openFolder = function(id, folder_id)
{
	if(!GO.files.fileBrowser)
	{
		GO.files.fileBrowser=new GO.files.FileBrowser({
			id:'popupfb',
			border:false
			//filePanelCollapsed:true
		});
		GO.files.fileBrowserWin = new GO.Window({
			stateId: 'go-files-browser-win',
			title: t("File browser", "files"),
			height:dp(800),
			width:dp(1200),
			layout:'fit',
			maximizable:!GO.util.isMobileOrTablet(),
			collapsible:!GO.util.isMobileOrTablet(),
			closeAction:'hide',
			items: GO.files.fileBrowser
		});
	}
	
	if(!folder_id) {
		folder_id = id;
	}
	
	GO.files.fileBrowser.setRootID(id, folder_id);
	GO.files.fileBrowserWin.show();

	return GO.files.fileBrowser;
}

GO.files.createSelectFileBrowser = function(){
	if(!GO.selectFileBrowser)
	{
		GO.selectFileBrowser= new GO.files.FileBrowser({});

		GO.selectFileBrowserWindow = new GO.Window({
			title: t("Select files"),
			height:dp(800),
			width:dp(1200),
			modal:true,
			layout:'fit',
			border:false,
			collapsible:true,
			maximizable:true,
			closeAction:'hide',
			items: GO.selectFileBrowser,
			buttons:[
			{
				text: t("Ok"),
				handler: function(){
					var records = GO.selectFileBrowser.getSelectedGridRecords();

					GO.selectFileBrowser.callFileClickHandler(records[0]);
				},
				scope: this
			},{
				text: t("Close"),
				handler: function(){
					GO.selectFileBrowserWindow.hide();
				},
				scope:this
			}
			]

		});
	}
}

GO.files.isContentExpired = function(contentExpireDateString){
	
	if(GO.util.empty(contentExpireDateString)){
		return false;
	}
	
	var contentExpireDate = Date.parseDate(contentExpireDateString,GO.settings.date_format);

	if(Date.now() >= contentExpireDate.getTime()){
		return true;
	} else {
		return false;
	}
};

GO.files.MainPanel = Ext.extend(GO.files.FileBrowser, {
	id: "files",
	title: t("Files")
})

go.Modules.register("legacy", 'files', {
	initModule: function() {
		if (go.Modules.get("legacy", "files").userRights.mayAccessMainPanel)
		{
			this.addPanel(GO.files.MainPanel);
		}
	},

	title: t("Files", "files"),
	iconCls: 'go-tab-icon-files',
	customFieldTypes: [
		"go.modules.community.files.customfield.File"
	],
	entities: [{
			name: "File",
			links: [
				
				{
					iconCls: 'entity File pink',
					entity: "File",
					linkDetail: function () {
						return new GO.files.FilePanel();
					}
				}

			]
		}, {
			name: "Folder",

			links: [
				{
					iconCls: 'entity Folder pink',
					entity: "Folder",
					linkDetail: function () {
						return new GO.files.FolderPanel();
					}
				}
			]
		}]

});

GO.files.pasteSelections = new Array();
GO.files.pasteMode = 'copy';


GO.files.launchFile = function(config) {
	GO.request({
		url: 'files/file/open',
		params: config,
		success: function(response, options, result) {
			result.handler.call();
		},
		scope: this
	})
};
