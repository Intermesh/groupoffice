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

	width: dp(500),

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
		if(target.tagName == "A" && target.attributes.href && target.attributes.href.value && target.attributes.href.value.substring(0,4) == "http") {
			window.open(target.attributes.href.value);
			e.preventDefault();
		}
	},
	
	onChanges : function(entityStore, added, changed, destroyed) {

		if(this.loading) {
			return;
		}

		if(entityStore.entity.name === this.entityStore.entity.name) {

			if(changed.indexOf(this.currentId) > -1) {
				this.reload();
			} else if (destroyed.indexOf(this.currentId) > -1) {
				this.reset();
			}
			return;
		}
		if(!this.watchRelations[entityStore.entity.name]) {
			return;
		}

		changed.forEach((id) => {
			if(this.watchRelations[entityStore.entity.name].indexOf(id) > -1) {
				this.internalLoad(this.data);
				return;
			}
		});
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
		const id = this.currentId;
		this.currentId = null;
		this.load(id);
	},
	
	internalLoad : function(data) {

		//in case user destroys panel while loading
		if(this.isDestroyed) {
			return Promise.resolve(data);
		}

		this.watchRelations = {};
		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(false);
		}

		this.data = data;

		if(!this.relations.length) {
			this.onLoad();
			this.fireEvent('load', this);
			return Promise.resolve(data);
		}	
		
		return go.Relations.get(this.entityStore, data, this.relations).then((result) => {
			this.watchRelations = result.watch;
			return data;
		}).catch((result) => {
			console.warn("Failed to fetch relation", result);
		}).finally(() => {
			this.onLoad();
			this.fireEvent('load', this);
		});
		
	},

	load: function (id) {

		if(this.loading) {
			return this.loading.then(() => {
				return this.load(id);
			});
		}

		if(this.currentId == id) {
			return Promise.resolve(this.data);
		}

		this.currentId = id;
		this.loading = this.entityStore.single(id).then((entity) => {
			try {
				return this.internalLoad(entity);
			} catch (e) {
				Ext.MessageBox.alert(t("Error"), t("Sorry, an error occurred") + ": " + e.message);
				console.error(e);
				return Promise.reject(e);
			}
		}).catch((e) => {
			console.error(e);
			Ext.MessageBox.alert(t("Error"), e.error);
		}).finally(() => {
			this.loading = false;
		});

		return this.loading;
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
	},
	addHistory : function() {
		if (go.Modules.isAvailable("community", "history")) {
			this.add(new go.modules.community.history.HistoryDetailPanel());
		} else
		{
			this.add(new go.detail.CreateModifyPanel());
		}
	},
});

Ext.reg("detailview", go.detail.Panel);
