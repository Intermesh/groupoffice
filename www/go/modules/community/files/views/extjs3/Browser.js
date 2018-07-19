/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
go.modules.community.files.Browser = Ext.extend(Ext.Component, {
	useRouter:null,
	path: null,
	rootConfig: {
		filters: [],
		nodeId: null,
		storages: false
	},
	targetStore: null,
	rootLoaded : false,
	_rootNodes:[],
	
	activeUploads: 0,
	unprocessedFiles: 0,
	
	/**
	 * Call open() will change route, will call nav()
	 * @param {type} config
	 * @returns {undefined}
	 */
	initComponent: function () {
		this.path = [];
		this.addEvents({
			"pathchanged" : true, // post browsing
			"myfilesnodeidchanged" : true,
			"rootNodesChanged": true
		});
		
		if(this.rootConfig.storages) {
			this.loadStorages();
		}
		// When processing file into a folder we need to load it
		// otherwise we can't solve duplicated localy
		this.targetStore = new go.data.Store({
			fields: [
				'id', 
				'name',
				'bookmarked',
				'internalShared',
				'externalShared',
				'storageId',
				{name: 'touchedAt', type: 'date'},
				{name: 'contentType', submit: false},
				{name: 'metaData', submit: false},
				{name: 'size', submit: false},
				{name: 'progress', submit: false},
				{name: 'status', submit: false},
				'isDirectory', 
				{name: 'createdAt', type: 'date'}, 
				{name: 'modifiedAt', type: 'date'}, 
				'aclId'
			],
			entityStore: go.Stores.get("Node")
		});
		
		this.initRootNodes();
		go.Stores.get('Node').on('changes', this.initRootNodes,this);
		go.Stores.get('Storage').on('changes', function(store, added, changed, deleted) {
			
			this.storages = added.concat(changed);
			this.initRootNodes();
		} ,this);
		
		go.modules.community.files.Browser.superclass.initComponent.call(this);
	},
	
	loadStorages : function() {
		var callId = go.Jmap.request({
			method: 'Storage/query',
			params: {
				filter: {
					ownedBy: go.User.id
				}
			}
		});
		go.Jmap.request({
			method: 'Storage/get',
			params: {
				"#ids": {
					resultOf: callId,
					name: "Storage/query",
					path: "ids"
				}
			}
		});
	},
	
	initRootNodes : function(){
		if(this.rootLoaded) {
			return;
		}
		this._rootNodes = [];
		
		if(this.rootConfig.storages){
			// Retreive storages and add
			if(!this.storages) {
				return;
			}
			
			Ext.each(this.storages, function(storage){
				//var rootNode = this.fireEvent('parseStorage', this, storage);
				//if(!rootNode) {
				storage = go.Stores.get('Storage').get(storage);
				var	rootNode = {
						filter: {
							parentId: storage.rootFolderId
						},
						text: t('My files'),
						iconCls: 'ic-home',
						entity: storage,
						entityId: storage.rootFolderId,
						type:'storage'
					};
				//}
				this._rootNodes.push(rootNode);
			},this);
				
		}
		
		if(this.rootConfig.nodeId){
			// Retreive nodeId and add
			var node = go.Stores.get('Node').get(this.rootConfig.nodeId);
			if(!node) {
				return;
			}
			this._rootNodes.push({
				filter: {
					parentId: node.id
				},
				text: node.name,
				iconCls: 'ic-folder',
				entity: node,
				entityId: node.id,
				type:'node'
			});
		}
		
		if(this.rootConfig.filters){
			// Retreive filters and add
			this._rootNodes = this._rootNodes.concat(this.rootConfig.filters);
		}
		this.rootLoaded = true;
		this.fireEvent('rootNodesChanged', this, this._rootNodes);
		
	},	
	
	getRootNodes: function(){
		return this._rootNodes;
	},
	
	/**
	 * Add a rootNode to the browser
	 * 
	 * @param nodeEntity object
	 * @param boolean clear the current rootNode array
	 */
	addRootNodeEntity : function(nodeEntity, clearExisting){
		
		var nodeConfig = {
			iconCls:'ic-folder',
			text: nodeEntity.name,
			entityId:nodeEntity.id,
			draggable:false,
			params:{
				filter: {
					parentId: nodeEntity.id
				}
			}
		};

		this.addRootNode(nodeConfig,clearExisting);
	},
	
	/**
	 * Add a rootNode to the browser
	 * 
	 * @param {} config object of a rootNode
	 * @param boolean clear the current rootNode array
	 * 
	 */
	addRootNode : function(nodeConfig, clearExisting){
		
		if(clearExisting){
			this.rootNodes = [];
		}
		
		this.rootNodes.push(nodeConfig);
	},
	
	/**
	 * Get the rootnode based on entityId
	 * 
	 * @param string rootNodeEntityId
	 * @return array
	 */
	getRootNode : function(){
		if(this.path.length == 0) {
			return {text: 'loading...'};
		}
		var entityId = this.path[0];
		var rootNodes = this._rootNodes.filter(function(node){
			return node.entityId == entityId;
		});
		return (rootNodes.length >= 1)?rootNodes[0]:false;
	},
	
	/**
	 * Get the path that is currently set in this browser
	 * @param boolean withRoot
	 */
	getPath : function(){
		return this.path;
	},
	
	getCurrentDir : function() {
		
		var path = this.getPath(true);
		if(!path.length) {
			return null;
		}
		var currentDir = path[path.length-1];
		
		return currentDir;
	},
	
	/**
	 * Go to the given path
	 * 
	 * @param array path
	 */
	goto : function(path){
		if(Number(path) !== 'NaN') {
			for(var i = 0; i < this.path.length; i++) {
				if(this.path[i] == path) {
					path = this.path.slice(0,i+1);
					break;
				}
			}
		}
		this.path = path;
		this.open();
	},
	
	receive: function(files, targetId, action, callback) {
		action = action || 'fileUpload';
		
		function internalReceive() {
			for (var i = 0; i < files.length; i++) {
				var node = files[i].data || files[i];
				var index = this.targetStore.find('name', node.name);
				if(index === -1) { // not found
					this[action](node, false, node.name, callback);
				} else { // already exist
					this._solveDuplicate(node, index, action, callback);
				}
			}
		}
		
		this.unprocessedFiles = files.length;
		
		if(targetId && this.targetStore.baseParams.filter && this.targetStore.baseParams.filter.parentId != targetId) {
			this.targetStore.setBaseParam('filter',{parentId: targetId});
			this.targetStore.load({callback:internalReceive,scope:this});
		} else if(this.targetStore.isLoaded) {
			internalReceive();
		}
	},
	
	pendingDuplicates : {},
	
	_solveDuplicate : function(file, index, action, callback) {
		this.pendingDuplicates[index] = file;
		var count = Object.keys(this.pendingDuplicates).length,
			msg = (count < 2) ? 'A file named <b>' + file.name + '</b>' : '<b>' + count + '</b> files';
		Ext.Msg.show({
			title: t('Duplicate file(s)'),
			msg: t(msg+' already exists. <br>What would you like to do?'),
			buttons: {yes:t('Keep both'), no:t('Replace'), cancel:t('Cancel')},
			icon: Ext.MessageBox.QUESTION,
			fn: function(btnId, text) {
				for (var i in this.pendingDuplicates) {
					if(btnId === 'no') {
						this[action](this.pendingDuplicates[i], i, false, callback);
						continue;
					} else if(btnId === 'yes') {
						var newName, nameCount = 0, index = i,
							nameExt = this.pendingDuplicates[i].name.split('.'),
							name, extension = nameExt.pop();
						if(nameExt.length === 0) {
							name = extension;
							extension = null;
						} else {
							name = nameExt.join('.');
						}
						while(index !== -1) {
							nameCount++;
							newName = name + '('+nameCount+')';
							index = this.targetStore.find('name', newName, callback);
						}
						if(extension !== null) {
							newName += ('.'+extension);
						}
						this[action](this.pendingDuplicates[i], false, newName, callback);
					}
				}
				this.pendingDuplicates = {};
			},
			scope:this
		});

	},
	
	move: function(nodes, replaceIndex, newName){
		var items = {};
		Ext.each(nodes, function(record) {
			items[record.id] = {parentId:this.targetStore.baseParams.filter.parentId};
			if(newName) {
				items[record.id].name = newName;
			}
		}, this);
		// TODO: Change in store and commit all at once
		go.Stores.get('Node').set({update:items});
		
		this.unprocessedFiles--;
		if(this.unprocessedFiles === 0) {
			this.targetStore.commitChanges();
		}
	},
	
	fileUpload: function(file, replaceIndex, newName) {
		var targetStore = this.targetStore;
		if(replaceIndex || replaceIndex === 0) {
			var record = targetStore.getAt(replaceIndex);
			record.set('status', 'queued');
		} else {
			var record = new targetStore.recordType({
				name: newName || file.name,
				isDirectory: 0,
				parentId: targetStore.baseParams.filter.parentId, 
				size: file.size,
				status: 'queued'
			});
			targetStore.add(record);
		}
		this.activeUploads++;
		go.Jmap.upload(file, {
		  progress: function(e) {
				if (e.lengthComputable) {
					var complete = (e.loaded / e.total * 100 | 0);
					record.set('progress', complete);
				}
		  },
		  success: function(data) {
			  this.activeUploads--;
			  record.set('status', 'done');
			  record.set('blobId', data.blobId);
			  if(this.activeUploads === 0) {
					targetStore.commitChanges();
			  }
		  },
		  failure: function(e) {
			  record.set('progress', 0);
			  record.set('status', 'failed');
		  },
		  scope:this
	  });
	},
	
	/**
	 * Descent into the folder of the given id
	 * 
	 * @param int id
	 */
	descent : function(id){
		this.path.push(id);
		this.open();
	},
	
	/**
	 * Process the path and open it
	 */
	open: function(){
		var strPath = Ext.isEmpty(this.path) ? '' : this.path.join('/')+'/';
		if(this.useRouter){		
			go.Router.goto("files/"+strPath);
		} else {
			this.path = [];
			this.nav(strPath);
		}
	},

	/**
	 * 
	 * @param {string} path path of id's eg 1/2/3
	 * @return {undefined}
	 */
	nav: function(path) {
		if(path == 'undefined' || path == '') {
			path = "/"+this._rootNodes[0].entityId;
		}
		var ids = path.substr(1).replace(/\/$/g, '').split('/');
		if(ids[0] === '') {
			ids = [];
		}
		//ids = ids.map(Number);
		this.path = ids;

		var rootNode = this.getRootNode();
		var filter = this.path.length === 1 && rootNode.filter ? rootNode.filter : {parentId:ids[ids.length-1]};

		this.fireEvent('pathchanged', this, this.path, filter);
	}
});
