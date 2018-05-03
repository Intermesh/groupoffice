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
	path: [],
	currentRootNode: 'my-files', // (my-files, shared-with-me, bookmarks, etc..)
	store: null, // only used by grid
	rootNodes: [{
		text: t('My files'),
		iconCls:'ic-home',
		entityId:'my-files',
		draggable:false,
		params:{}
	},{
		text: t('Shared with me'),
		iconCls:'ic-group',
		entityId:'shared-with-me',
		draggable:false,
		params: {
			filter: {
				isHome: false
			}
		}
	},{
		text: t('Bookmarks'),
		iconCls:'ic-bookmark',
		entityId:'bookmarks',
		draggable:false,
		params: {
			filter: {
				isBookmarked: true
			}
		}
	}],

	/**
	 * Call open() will change route, will call nav()
	 * @param {type} config
	 * @returns {undefined}
	 */
	constructor: function(config){
		this.store = config.store;
		this.addEvents({
			"pathchanged" : true // post browsing
		});
		this.listeners = config.listeners;
		
		go.modules.community.files.Browser.superclass.constructor.call(this, config);
		
		// Add route to routers used by open()
		var me = this;
		go.Router.add(/files\/([\w\-]+)\/([0-9\/]*)/, function(root, path) {
			me.currentRootNode = root;
			me.path = [];
			me.nav(path);
		});
	},
	
	/**
	 * Set the current Path for this browser
	 * 
	 * @param array Path
	 */
	setPath : function(path){
		
		if(path.length == 1){
			this.currentRootNode = path[0];
			this.path = [];
		} else {
			this.currentRootNode = path.shift();
			this.path = path;
		}
	},
	
	/**
	 * Get the rootnode based on entityId
	 * 
	 * @param string rootNodeEntityId
	 * @return array
	 */
	getRootNode : function(rootNodeEntityId){
		var rootNodes = this.rootNodes.filter(function(node){
			return node.entityId === rootNodeEntityId;
		});
		return (rootNodes.length >= 1)?rootNodes[0]:false;
	},
	
	/**
	 * Get the path that is currently set in this browser
	 * @param boolean withRoot
	 */
	getPath : function(withRoot){
		
		if(withRoot){
			return [this.currentRootNode].concat(this.path);
		}
		
		return this.path;
	},
	
	/**
	 * Get the currentRootNode that is currently set in this browser
	 */
	getCurrentRootNode : function(){
		return this.currentRootNode;
	},
	
	/**
	 * Go to the given path
	 * 
	 * @param array path
	 */
	goto : function(path){
		this.setPath(path);
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
		go.Router.goto("files/"+this.currentRootNode+"/" + strPath);
	},
//	
//	/**
//	 * Find the given id in the full path and cut off the path when it's found
//	 */
//	cleanRecursiveness : function(path, id){
//		for(var i in path) {
//			if(path[i] === id) {
//				path = path.slice(0,i);
//			}
//		}
//		return path;
//	},
	
	// private
	nav: function(path) {
		var ids = path.replace(/\/$/g, '').split('/');
		if(ids[0] === '') {
			ids = [];
		}
		this.path = this.path.concat(ids);
		
		var filter = Ext.isEmpty(this.path) && this.rootNodes[this.currentRootNode] && this.rootNodes[this.currentRootNode].params ? this.rootNodes[this.currentRootNode].params.filter : {parentId:ids[ids.length-1]} 
		
		this.store.setBaseParam('filter',filter);
		this.store.load();
		this.fireEvent('pathchanged', this);
	}
	
});