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
	at: 'mine', // (mine,shared,etc..)
	store: null, // only used by grid
	rootNames: {
		mine: t('My Files'),
		shared: t('Shared with me'),
		bookmarked: t('Bookmarked')
	},
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
		go.Router.add(/files\/(\w+)\/([0-9/]*)/, function(root, path) {
			me.at = root;
			me.path = [];
			me.nav(path);
		});
	},
	open: function(id) {
		// find Id in current path and slide it there
		for(var i in this.path) {
			if(this.path[i] == id) {
				this.path = this.path.slice(0,i);
			}
		}
		var strPath = Ext.isEmpty(this.path) ? '' : this.path.join('/')+'/';
		if(id){
			strPath += (id+'/');
		}
		go.Router.goto("files/"+this.at+"/" + strPath);
	},
	// private
	nav: function(path) {
		var ids = path.replace(/\/$/g, '').split('/');
		if(ids[0] === '') {
			ids = [];
		}
		this.path = this.path.concat(ids);
		var filter = Ext.isEmpty(this.path) ? {isHome:true} : {parentId:ids[ids.length-1]} 
		this.store.setBaseParam('filter',filter);
		this.store.load();
		this.fireEvent('pathchanged', this);
	}
	
});