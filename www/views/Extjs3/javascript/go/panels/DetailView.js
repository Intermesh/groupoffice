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



go.panels.DetailView = Ext.extend(Ext.Panel, {

	cls: 'go-detail-view',
	autoScroll: true,

	store: null,
	data: {},
	currentId: null,
	basePanels: [],

	entityStore: null,

	initComponent: function () {

		go.flux.Dispatcher.register(this);

		go.panels.DetailView.superclass.initComponent.call(this, arguments);
		
		this.on('render', function() {
			this.reset();
		}, this);
	},

	receive: function (action) {
		//console.log(action.type, this.entityStore.entity.methods.get.responseName);
		switch (action.type) {
			case this.entityStore.entity.name + "Updated":

				//reload if data in entity store was updated
				for (var i = 0, l = action.payload.list.length; i < l; i++) {
					if (this.currentId == action.payload.list[i].id) {
						this.reload();
					}
				}
				break;

			case this.entityStore.entity.name + "Destroyed":
				console.log(action.payload.list, this.currentId);
				if (action.payload.list.indexOf(this.currentId) > -1) {
					this.reset();
				}
				break;
		}
	},

//
//	printHandler: function () {
//		this.body.print({title: this.getTitle()});
//	},

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

	load: function (pk) {
		this.currentId = pk;
		this.entityStore.get([pk], function (entities) {
			if (entities[0]) {
				if(this.getTopToolbar()) {
					this.getTopToolbar().setDisabled(false);
				}
				this.data = entities[0];
				this.onLoad();				

				this.fireEvent('load', this);

			}
		}.createDelegate(this));
	}
});
