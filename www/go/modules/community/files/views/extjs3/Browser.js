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
	rootLoaded : false,
	_rootNodes:[],
	
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
	 * 
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

		var ids = path.replace(/\/$/g, '').split('/');
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
