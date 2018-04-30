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
	constructor: function(config){
		this.store = config.store;
		this.addEvents({"pathchanged" : true});
		this.listeners = config.listeners;
		go.modules.community.files.Browser.superclass.constructor.call(this, config);
	},
//	open: function(node) {
//		if(!node.isDirectory){
//			return;
//		}
//		this.path.push(node.id);
//		this.store.setBaseParam('filter',{parentId:node.id});
//		this.store.load();
//		this.fireEvent('pathchanged', this);
//	},
	nav: function(ids) {
		if(!Ext.isArray(ids)){
			ids = [ids];
		}
		for(var i in this.path) {
			if(this.path[i] === ids[0]) {
				this.path.slice(0,i);
			}
		}
		this.path = this.path.concat(ids);
		this.store.setBaseParam('filter',{parentId:ids[ids.length-1]});
		this.store.load();
		this.fireEvent('pathchanged', this);
	}
	
});