/* global go, Ext */

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: DisplayPanel.js 19345 2015-08-25 10:11:22Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */



/**
 * 
 * Detail view panel
 * 
 * All panel items are updated automatically if they have a "tpl" (Ext.XTemplate) property or an "onLoad" function. The panel is passed as argument.
 * @type |||
 */
go.detail.Panel = Ext.extend(Ext.Panel, {

	cls: 'go-detail-view',
	autoScroll: true,

	store: null,
	data: {},
	currentId: null,
	basePanels: [],

	entityStore: null,

	initComponent: function () {
		go.detail.Panel.superclass.initComponent.call(this, arguments);		
		
		if (go.Modules.isAvailable("community", "comments")) {
			this.add(new go.modules.comments.CommentsDetailPanel());
		}
		
		this.cls += " go-detail-view-" + this.entityStore.entity.name.toLowerCase();
		
		this.on('afterrender', function() {
			this.reset();			
		}, this);
	},
	
	onChanges : function(entityStore, added, changed, destroyed) {		
		var entity = added[this.currentId] || changed[this.currentId] || false;
			
		if(entity) {
			this.internalLoad(entity);
		}

		if (destroyed.indexOf(this.currentId) > -1) {
			this.reset();
		}

	},

	reset: function () {

		this.data = {};
		this.currentId = null;

		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(true);
		}
		
		this.items.each(function (item, index, length) {
			item.hide();
		}, this);
		
		this.fireEvent('reset', this);
	},

	onLoad: function () {
		
		go.Translate.setModule(this.package, this.module);
		
		this.items.each(function (item, index, length) {
			
			item.show();
			
			if (item.tpl) {
				item.update(this.data);
			}
			if (item.onLoad) {
				item.onLoad.call(item, this);
			}
			
		}, this);
		
		
		this.doLayout();
		this.body.scrollTo('top', 0);		
	},

	reload: function () {
		this.load(this.currentId);
	},
	
	internalLoad : function(data) {
		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(false);
		}
		this.data = data;
		this.onLoad();				

		this.fireEvent('load', this);
	},

	load: function (id) {
		this.currentId = id;
		this.entityStore.get([id], function(entities) {
			this.internalLoad(entities[0]);
		}, this);
	}
});

Ext.reg("detailview", go.detail.Panel);