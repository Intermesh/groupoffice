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

		this.watchRelations = {};

		this.cls += " go-detail-view-" + this.entityStore.entity.name.toLowerCase();
		
		this.on('afterrender', function() {
			this.reset();

			this.body.on("click", this.onBodyClick, this);
		}, this);
	},

	onBodyClick : function (e, target) {

		//prevent navigating away.
		if(target.tagName == "A" && target.attributes.href && target.attributes.href.value) {
			window.open(target.attributes.href.value);
			e.preventDefault();
		}
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
		if(!this.watchRelations[entityStore.entity.name]) {
			return;
		}

		for(var id in changed) {
			if(this.watchRelations[entityStore.entity.name].indexOf(changed[id].id) > -1) {
				this.internalLoad(this.data);
				return;
			}
		}
	},
	
	// listen to relational stores as well
	initEntityStore: Ext.Panel.prototype.initEntityStore.createSequence(function () {
		
		//for(let i = 0,relName; relName = this.relations[i]; i++) {
		this.relations.forEach(function(relName) {
			var relation = this.entityStore.entity.findRelation(relName),
				entityStore = go.Db.store(relation.store);
				
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

		this.applyTemplateToItems(this.items);
		
		this.doLayout();
		this.body.scrollTo('top', 0);		
	},

	/**
	 * Helper function to apply data on all items of this panel. It can also be used to apply it to other items.
	 *
	 * @param items
	 */
	applyTemplateToItems : function(items) {
		items.each(function (item, index, length) {

			item.show();

			if (item.tpl) {
				//debugger;
				item.update(this.data);
			}
			if (item.onLoad) {
				item.onLoad.call(item, this);
			}

		}, this);
	},

	reload: function () {
		this.load(this.currentId);
	},
	
	internalLoad : function(data) {
		this.watchRelations = {};
		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(false);
		}
		this.data = data;

		var me = this;
		
		if(!this.relations.length) {
			this.onLoad();
			this.fireEvent('load', this);
			return;
		}	
		
		go.Relations.get(me.entityStore, data, this.relations).then(function(result) {
			me.watchRelations = result.watch;					
		}).catch(function(result) {
			console.warn("Failed to fetch relation", result);
		}).finally(function() {
			me.onLoad();
			me.fireEvent('load', me);
		});
		
	},

	load: function (id) {
		this.currentId = id;
		var me = this;
		this.entityStore.single(id).then(function(entity) {
			try {
				me.internalLoad(entity);
			} catch (e) {
				Ext.MessageBox.alert(t("Error"), t("Sorry, an unexpected error occurred: ") + e.message);
				console.error(e);
			}
		}).catch(function(e) {
			console.error(e);
			Ext.MessageBox.alert(t("Not found"), "The requested page was not found");
		});
	},

	addCustomFields : function() {
		return this.add(go.customfields.CustomFields.getDetailPanels(this.entityStore.entity.name));
	},

	addLinks : function(sortFn) {
		return this.add(new go.links.getDetailPanels(sortFn));
	},

	addComments : function() {
		if (go.Modules.isAvailable("community", "comments")) {
			this.add(new go.modules.comments.CommentsDetailPanel());
		}
	},
	addFiles : function() {
		if (go.Modules.isAvailable("legacy", "files")) {
			return this.add(new go.modules.files.FilesDetailPanel());
		}
	}
});

Ext.reg("detailview", go.detail.Panel);
