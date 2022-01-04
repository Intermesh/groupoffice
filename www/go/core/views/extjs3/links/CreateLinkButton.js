/* global Ext, go */

go.links.CreateLinkButton = Ext.extend(Ext.Button, {
	//iconCls: 'ic-link',
	text: t("Links"),
	cls: "go-create-link-btn",
	totalCount: 0,
	cancelAdd: false,
	addLink : function(entity, entityId) {


		
		var me = this;
		me.cancelAdd = false;
		//We need to query the ID of the search cache so the "to" relation can be resolved.
		go.Db.store("Search").query({
			filter: {
				entities: [{name: entity}],
				entityId: entityId
			}
		}).then(function(response) {

			if(me.cancelAdd) {
				return;
			}
			var newLink = {
				"toId": entityId,
				"toEntity": entity,
				"toSearchId": response.ids[0]
			};

			console.log(newLink, me.linkGrid.store.setFilter("link"));

			me.newLinks.push(newLink);
			me.linkGrid.store.loadData({"records" :[newLink]}, true);
			me.setCount(++me.totalCount);
		});		
		
	},


	cancelAddLink : function() {
		this.cancelAdd = true;
	},
					
	initComponent: function () {

		this.newLinks = [];
		
		this.searchField = new go.search.SearchField({
			
			anchor: "100%",
			hideLabel: true,
			listeners: {
				scope: this,
				select: function (cmb, record, index) {
					this.linkGrid.store.loadData({"records" :[{
						"toId": record.get('entityId'),
						"toEntity": record.get('entity'),
						"toSearchId": record.get('id')
					}]}, true);
					// this.searchField.reset();
					
					this.newLinks.push({						
						toEntity: record.get('entity'),
						toId: record.get('entityId')
					});
					this.setCount(++this.totalCount);
				}
			},
			getListParent: function () {
				//this avoids hiding the menu on click in the list
				return this.el.up('.x-menu');
			}
		});

		// this.searchField = new go.search.SearchCombo({
		// 	anchor: "100%",
		// 	hideLabel: true,
		// 	listeners: {
		// 		scope: this,
		// 		select: function (cmb, record, index) {					
		// 			this.linkGrid.store.loadData({"records" :[{
		// 				"toId": record.get('entityId'),
		// 				"toEntity": record.get('entity'),
		// 				"toSearchId": record.get('id')
		// 			}]}, true);
		// 			this.searchField.reset();
					
		// 			this.newLinks.push({						
		// 				toEntity: record.get('entity'),
		// 				toId: record.get('entityId')
		// 			});
		// 			this.setCount(++this.totalCount);
		// 		}
		// 	},
		// 	getListParent: function () {
		// 		//this avoids hiding the menu on click in the list
		// 		return this.el.up('.x-menu');
		// 	}
		// });
		this.store = new go.data.Store({
			autoDestroy: true,
			fields: ['id', 'toId', 'toEntity', {name: "to", type: "relation"}, 'description', {name: 'modifiedAt', type: 'date'}],
			entityStore: "Link",
			sortInfo: {
				field: 'modifiedAt',
				direction: 'DESC'
			},
			baseParams: {
				filter: {}
			}
		});

		this.disableEditableDescription = this.disableEditableDescription || false;
		this.linkGrid = new go.grid.EditorGridPanel({
			name: 'linkGrid',
			clicktToEdit: 1,
			trackMouseOver: true,
			loadMask: true,
			columns: [
				{
					id: 'name',
					header: t('Name'),					
					sortable: true,
					editable: false,
					dataIndex: 'to',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {						
						var linkIconCls = go.Entities.getLinkIcon(record.data.toEntity, record.data.to.filter);

						return '<i class="entity ' + linkIconCls + '"></i> <a>' + record.data.to.name + '</a>';
					}
				},
				{
					id: 'description',
					header: t('Description'),
					sortable: false,
					dataIndex: "description",
					width: dp(200),
					editable: !this.disableEditableDescription,
					hidable: false,
					editor: new Ext.form.TextField({
						allowBlank: true,
						anchor: '100%',
						maxLength: 190
					}),
					renderer: function(value, metaData, record, rowIndex, colIndex, store) {
						var desc = '';record.data.description;
						if(record.data.description && record.data.description.length > 0) {
							desc = record.data.description;
							if(desc.length > 15) {
								desc = desc.substring(0,15) + '...';
							}
						}
						return desc;
					},
					listeners: {
						scope: this,
						blur: function() {

						}
					}
				},
				{
					width: dp(60),
					menuDisabled: true,
					draggable: false,
					hideable: false,
					align: "right",
					sortable: false,
					dataIndex: "entityId",
					renderer: function (v, meta, record) {						
						return "<button class='icon'>delete</button>";						
					}
				}
			],
			autoExpandColumn: 'name',
			store: this.store,
			tbar: new Ext.Toolbar({
				layout: "fit",
				items: [{
						xtype: "fieldset",
						items: [this.searchField]
					}]
			}),
			listeners: {
				scope: this,
				rowclick: function (grid, rowIndex, e) {
					if (e.target.tagName === "BUTTON" && e.target.innerHTML === 'delete') {
						var record = grid.store.getAt(rowIndex);
						this.removeLink(record.data.toEntity, record.data.toId, record);
					} else {
						grid.startEditing(rowIndex,1); // The description field
					}
				},
				rowdblclick: function(grid,rowIndex,e) {
					if (e.target.tagName !== "BUTTON") {
						var record = this.store.getAt(rowIndex);

						var win = new go.links.LinkDetailWindow({
							entity: record.data.toEntity
						});

						win.load(record.data.toId);
						return false;
					}
				},
				afteredit: function(e)
				{
					e.record.store.save();
				}
			},
			width: dp(800),
			height: dp(400)
		}
		);

		var me = this;

		this.menu = new Ext.menu.Menu({
			items: [this.linkGrid],
			doFocus: function () {
				me.searchField.focus();
			}
//			listeners: {
//				scope: this,	
//				show: function() {
//					if(this.linkGrid.store.baseParams.filter.entityId) {
//						
//					}
//				}
//			}
		});

		go.links.CreateLinkButton.superclass.initComponent.call(this);
		
		this.origText = this.text;

	},

	removeLink: function(entity, entityId, record) {
		if(!record) {
			var storeIndex = this.store.findBy(function (r) {
				return r.data.toId === entityId && r.data.toEntity === entity;
			})
			if (storeIndex !== -1) {
				record = this.store.getAt(storeIndex);
			}
		}
		if(record) {
			this.store.remove(record);
		}

		this.setCount(--this.totalCount);

		var i = this.newLinks.findIndex(function (l) {
			return l.toId === entityId && l.toEntity === entity;
		});

		if (i > -1) {
			this.newLinks = this.newLinks.splice(i, 1);
		} else if(record) {
			go.Db.store("Link").set({
				destroy: [record.id]
			});
		}
	},
	
	setCount : function(count) {		
		this.totalCount = count;
		this.setText(this.origText + " <span class='badge'>" + (this.totalCount) + "</span>");
	},
	
	setEntity : function(entity, entityId) {
		
		var f = this.linkGrid.store.getFilter("link");
		if(!f) {
			f = {};
		}
		
		if(f.entity === entity && f.entityId === entityId) {
			return;
		}	
		
		f.entity = entity;
		f.entityId = entityId;		
		
		if(!entityId) {
			this.reset();
			return;
		}

		this.linkGrid.store.setFilter("link", f);

		this.linkGrid.store.load({
			scope: this,
			callback: function() {
				this.setCount(this.linkGrid.store.getTotalCount());			
			}
		});
		//this.menu.on("show", this.load, this, {single: true});
	},
	
	reset : function() {

		console.log("reset",this.linkGrid.store.setFilter("link"));
		
		// Clear the new attached links list
		this.newLinks = [];		
		this.linkGrid.store.removeAll();
		this.linkGrid.store.setFilter("link", null);
		this.linkGrid.store.loaded = false; //otherwise changes will reload with all links in request when this button is
		//hidden and reused.
		this.setCount(0);		
		//this.menu.un("show", this.load);
	},

	
	load: function() {
		this.linkGrid.store.load();		
	},	
	
	getNewLinks : function() {
		var links = {}, i = 0, id;

		var f = this.linkGrid.store.getFilter("link");
		
		this.newLinks.forEach(function(l) {
			id = "new" + (i++);
			if(f) {
				l.fromEntity = f.entity;
				l.fromId = f.entityId;
			}
			//comes from store record relation
			delete l.to;
			links[id] = l;
		}, this);
		
		return links;
	},
	
	save : function() {
		
		if(this.newLinks.length === 0) {
			return;
		}

		var me = this;
		
		go.Db.store("Link").set({
			create: this.getNewLinks()
		})
		.then(function(result) {
			if(result.notCreated) {

				//ignore if it already existed
				for(var id in result.notCreated) {
					var ve = result.notCreated[id].validationErrors;
					if(!ve) {
						Ext.MessageBox.alert(t("Error"), t("Sorry, the link could not be created."));
						return;
					}
					for(var prop in ve) {
						if(ve[prop].code != 11) { //unique error
							Ext.MessageBox.alert(t("Error"), t("Sorry, the link could not be created."));
						}
					}
				}

			}
		})
		
		.finally(function() {
			if(!me.isDestroyed) {
				var f = me.linkGrid.store.getFilter("link");

				me.reset();
				if(f) {
					me.setEntity(f.entity, f.entityId);
				}
			}
		});
	}
});


Ext.reg("createlinkbutton", go.links.CreateLinkButton);