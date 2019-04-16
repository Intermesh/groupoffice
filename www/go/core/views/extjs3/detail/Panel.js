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
	/**
	 * string[] relation names defined in entity store
	 * When specified the Detailview will listen to these store and fetch the related entities
	 */
	relations: [],
	
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
		if(entityStore.entity.name === this.entityStore.entity.name) {
			var entity = added[this.currentId] || changed[this.currentId] || false;

			if(entity) {
				this.internalLoad(entity);
			}

			if (destroyed.indexOf(this.currentId) > -1) {
				this.reset();
			}
			return;
		}
		for(let i = 0,relName; relName = this.relations[i]; i++) {
			var relation = this.entityStore.entity.relations[relName];
			if(entityStore.entity.name === relation.store && changed[this.data[relation.fk]]) {
				this.data[relName] = changed;
				this.internalLoad(this.data);
			}
		}
	},
	
	// listen to relational stores as well
	initEntityStore: Ext.Panel.prototype.initEntityStore.createSequence(function () {
		
		//for(let i = 0,relName; relName = this.relations[i]; i++) {
		this.relations.forEach(function(relName) {
			var relation = this.entityStore.entity.relations[relName],
				entityStore = go.Stores.get(relation.store);
				
			if(entityStore) {
				this.on("afterrender", function() {
					entityStore.on('changes',this.onChanges, this);		
				}, this);

				this.on('beforedestroy', function() {
					entityStore.un('changes', this.onChanges, this);
				}, this);
			}
		}, this);
	}),

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
				//debugger;
				item.update(this.data);
			}
			if (item.onLoad) {
				item.onLoad.call(item, this);
			}
			
		}, this);
		
		
		this.doLayout();
		this.body.scrollTo('top', 0);		
	},
	
	resolve : function() {
		return null;
	},

	reload: function () {
		this.load(this.currentId);
	},
	
	internalLoad : function(data) {
		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(false);
		}
		this.data = data;
		var me = this;

		var resolveRelations = [];
		this.relations.forEach(function(relName) {
			var relation = me.entityStore.entity.relations[relName];
			if(me.data[relation.fk]) {
				resolveRelations.push(
					go.Stores.get(relation.store).get([me.data[relation.fk]]).then(function(record){
						me.data[relName] = record[0];
					})
				);
			}
		});
		
		//used for sony
		//var promises = this.resolve();
		if(!resolveRelations) {
			this.onLoad();
			this.fireEvent('load', this);
			return;
		}
		
		
		Promise.all(resolveRelations).then(function() {		
			me.onLoad();
			me.fireEvent('load', me);
		}).catch(function(result) {
			throw result;
		});
		
	},

	load: function (id) {
		this.currentId = id;
		this.entityStore.get([id], function(entities) {
			this.internalLoad(entities[0]);
		}, this);
	}
});

Ext.reg("detailview", go.detail.Panel);