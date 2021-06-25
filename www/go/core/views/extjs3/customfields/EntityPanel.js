/* global go, Ext */

/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

go.customfields.EntityPanel = Ext.extend(go.grid.GridPanel, {

	entity: null,

	createFieldSetDialog: function () {
		
		var e = go.Entities.get(this.entity)
		
		if(e.customFields && e.customFields.fieldSetDialog) {
			var cls = eval(e.customFields.fieldSetDialog);
			console.log(cls);
			return new cls;
		} 

		return new go.customfields.FieldSetDialog();
	},

	initComponent: function () {
	
		this.plugins = [new go.grid.plugin.Sortable(this.onSort, this, this.isDropAllowed)];

		this.store = new Ext.data.ArrayStore({
			fields: [
				'name',
				'databaseName',
				'type',
				'fieldId',
				'fieldSetId',				
				{name: 'isFieldSet', type: "boolean"},
				{name: 'sortOrder', type: "int"},
				"aclId"
			]
		});

		go.Db.store("FieldSet").on("changes", this.onFieldSetChanges, this);
		go.Db.store("Field").on("changes", this.onFieldChanges, this);
		
		this.on('destroy', function() {
			go.Db.store("FieldSet").un("changes", this.onFieldSetChanges, this);
			go.Db.store("Field").un("changes", this.onFieldChanges, this);
		}, this);

		var types = go.customfields.CustomFields.getTypes();
		Ext.apply(this, {
			//plugins: [actions],
			tbar: [
				"->",
				{
					iconCls: 'ic-cloud-upload',
					tooltip: t('Import fieldsets from JSON-file'),
					handler: function() {
						go.util.openFileDialog({
							multiple: false,
							accept: ".json",
							directory: false,
							autoUpload: true,
							scope: this,
							listeners: {
								upload: function (response) {

									this.getEl().mask(t("Importing..."));

									go.Jmap.request({
										method: 'FieldSet/importFromJson',
										params: {
											entity: this.entity,
											blobId: response.blobId
										},
										callback: function(request, tmp, response, callId) {
											this.getEl().unmask();
											GO.errorDialog.show(response.feedback, t('Import messages'));
											this.load();

										},
										scope: this
									})
								},
								scope: this
							}
						});
					},
					scope: this
				}, {
					iconCls: 'ic-cloud-download',
					tooltip: t('Export fieldsets to JSON-file'),
					handler: function() {
						var dlg = new go.customfields.ExportDialog();
						dlg.setEntity(this.entity);
						dlg.show();
					},
					scope: this
				}, {
					iconCls: 'ic-add',
					cls: "primary",
					text: t('Add field set'),
					handler: function (e, toolEl) {
						var dlg = this.createFieldSetDialog();
						dlg.setValues({entity: this.entity});
						dlg.show();
					},
					scope: this
				}
			],
			columns: [
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: false,
					dataIndex: 'name',
					renderer: function (v, meta, record) {
						return record.data.isFieldSet ? "<h5>" + v + "</h5>" : v;
					}
				},{
					header: t('Database name'),
					width: dp(200),
					sortable: false,
					dataIndex: 'databaseName'
				},{
					header: t('Type'),
					width: dp(100),
					sortable: false,
					dataIndex: 'type',
					renderer: function(v) {
						return types[v] ? types[v].label : types[v];
					}
				},
				{
					width: dp(80),
					menuDisabled: true,
					draggable: false,
					hidable: false,
					align: "right",
					sortable: false,
					dataIndex: "databaseName",
					renderer: function (v, meta, record) {

						if (record.data.isFieldSet) {
							return '<div class="x-toolbar"><button class="go-button primary" title="' + t("Add field") + '"><i class="icon">add</i></button><button class="go-button"><i class="icon">more_vert</i></button></div>';
						} else
						{
							return '<div class="x-toolbar"><button class="go-button"><i class="icon">more_vert</i></button></div>';
						}
					}
				}
			],
			viewConfig: {
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true
			},
			listeners: {
				scope: this,
				rowclick: function (grid, rowIndex, e) {
					if (e.target.tagName !== "I") {
						return false;
					}

					var record = this.store.getAt(rowIndex);

					switch (e.target.innerHTML) {
						case 'more_vert':
							this.showMoreMenu(record, e);
							break;

						case 'add':
							this.showAddFieldMenu(record, e);
							break;
					}
				}
			}
			// config options for stateful behavior
//			stateful: true,
//			stateId: 'apikeys-grid'
		});

		go.customfields.SystemSettingsPanel.superclass.initComponent.call(this);

		this.on('render', function () {
			this.load();
		}, this);
		
		this.on('rowdblclick', this.onRowDblClick, this);
	},
	
	onFieldSetChanges : function (store, added, changed, destroyed) {
			if (this.loading || !this.rendered) {
				return;
			}

			let all = added.concat(changed);

			store.get(all).then((result) => {
				result.entities.forEach((e) => {

					//change for another entity. Skip it.
					if(e.entity !== this.entity) {
						return;
					}

					const record = this.store.getAt(me.store.findBy((record) => {
						if (record.data.isFieldSet && record.data.fieldSetId === e.id) {
							return true;
						}
					}));

					if (!record) {
						this.load();
					} else
					{
						record.beginEdit();
						record.set("name", e.name);
						record.set("sortOrder", e.sortOrder);
						record.endEdit();
						record.commit();
					}
				});
			});
			
			if(destroyed.length) {
				this.store.remove(this.store.getRange().filter(function(r) {
					return destroyed.indexOf(r.data.fieldSetId) > -1;
				}));
			}
		},
	
	onFieldChanges : function (store, added, changed, destroyed) {
			if (this.loading || !this.rendered) {
				return;
			}

			let all = added.concat(changed);

			store.get(all).then((result) => {
				result.entities.forEach((e) => {

					if (this.store.findBy(function (record) {
						if (record.data.isFieldSet && record.data.fieldSetId === e.fieldSetId) {
							return true;
						}
					}) === -1) {
						//fieldset not part of this panel
						return;
					}

					const record = this.store.getAt(me.store.findBy(function (record) {
						if (record.data.fieldId === e.id) {
							return true;
						}
					}));

					if (!record) {
						this.load();
					} else {
						record.beginEdit();
						record.set("name", e.name);
						record.set("databaseName", e.databaseName);
						record.set("sortOrder", e.sortOrder);
						record.endEdit();
						record.commit();
					}
				});
			});
			
			if(destroyed.length) {
				this.store.remove(this.store.getRange().filter(function(r) {
					return destroyed.indexOf(r.data.fieldId) > -1;
				}));
			}

		},
	
	onRowDblClick : function(grid, rowIndex, e) {
		this.edit(this.store.getAt(rowIndex));
	},

	onSort: function (sortable, selections, dragData, dd) {
		var isFieldSet = !!selections.find(function (r) {
			if (r.data.isFieldSet) {
				return true;
			}
		});

		if (isFieldSet) {
			var fieldSetRecords = this.store.getRange().filter(function (r) {
				return r.data.isFieldSet;
			});

			var update = {};
			for (var i = 0, l = fieldSetRecords.length; i < l; i++) {
				update[fieldSetRecords[i].data.fieldSetId] = {sortOrder: i};
			}

			go.Db.store("FieldSet").set({
				update: update
			}, function() {
				//Quick and dirty way: reload on fieldset reorder so fields are moved.
				this.load();
			}, this);
			
			
		} else
		{
			var fieldRecords = this.store.getRange().filter(function (r) {
				return !r.data.isFieldSet;
			});

			var update = {};
			for (var i = 0, l = fieldRecords.length; i < l; i++) {
				update[fieldRecords[i].data.fieldId] = {sortOrder: i};
				if (selections.column("id").indexOf(fieldRecords[i].id) > -1) {
					update[fieldRecords[i].data.fieldId].fieldSetId = dragData.dropRecord.data.fieldSetId;
				}
			}

			go.Db.store("Field").set({
				update: update
			});
		}
	},

	isDropAllowed: function (selections, overRecord) {

		var isFieldSet = !!selections.find(function (r) {
			if (r.data.isFieldSet) {
				return true;
			}
		});

		var isField = !!selections.find(function (r) {
			if (!r.data.isFieldSet) {
				return true;
			}
		});

		//Don't allow mix
		if (isField && isFieldSet) {
			return false;
		}

		if (isFieldSet && !overRecord.data.isFieldSet) {
			//Only allow fieldsets to be dropped on other fieldsets
			return false;
		}

//		if(isField && overRecord.data.isFieldSet) {
//			return false;
//		}

		return true;
	},

	showMoreMenu: function (record, e) {
		if (!this.moreMenu) {
			this.moreMenu = new Ext.menu.Menu({
				items: [
					{
						itemId: "edit",
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function () {
							this.edit(this.moreMenu.record);
						},
						scope: this
					}, {
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							this.getSelectionModel().selectRecords([this.moreMenu.record]);
							this.deleteSelected();
						},
						scope: this
					}
				]
			});
		}

		this.moreMenu.record = record;

		this.moreMenu.showAt(e.getXY());
	},
	
	edit: function (record) {
		if (record.data.isFieldSet) {
			var dlg = this.createFieldSetDialog();
			dlg.load(record.data.fieldSetId).show();
		} else {
			var dlg = go.customfields.CustomFields.getType(record.data.type).getDialog();
			dlg.load(record.data.fieldId).show();
		}
	},

	showAddFieldMenu: function (record, e) {
		if (!this.addFieldMenu) {

			var items = [], types = go.customfields.CustomFields.getTypes();

			for (var name in types) {
				items.push({
					iconCls: types[name].iconCls,
					text: types[name].label,
					type: types[name],
					handler: function (item) {
						console.warn(item);
						var dlg = item.type.getDialog();
						dlg.setValues({
							fieldSetId: this.addFieldMenu.record.data.fieldSetId,
							type: item.type.name,
							typeLabel: item.text
						});

						dlg.show();
					},
					scope: this
				});
			}

			items = items.columnSort('text');

			this.addFieldMenu = new Ext.menu.Menu({
				items: items
			});
		}

		this.addFieldMenu.record = record;

		this.addFieldMenu.showAt(e.getXY());
	},

	doDelete: function (selectedRecords) {

		var fieldSetIds = [], fieldIds = [], me = this;
		selectedRecords.forEach(function (r) {
			if (r.data.isFieldSet) {
				fieldSetIds.push(r.data.fieldSetId);
			} else
			{
				fieldIds.push(r.data.fieldId);
			}
		});

		if (fieldSetIds.length) {
			me.getEl().mask(t("Deleting..."));

			go.Db.store("FieldSet").set({
				destroy: fieldSetIds
			}).finally(function() {
				me.getEl().unmask();
			});
		}

		if (fieldIds.length) {
			me.getEl().mask(t("Deleting..."));

			go.Db.store("Field").set({
				destroy: fieldIds
			}).finally(function() {
				me.getEl().unmask();
			});
		}
	},

	load: function () {

		this.loading = true;
		this.store.removeAll();

		var fsSortOrderMap = {};

		go.Db.store("FieldSet").query({
			filter: {
				entities: [this.entity]
			}
		}, function (response) {
			
			if(!response.ids.length) {
				this.store.loadData([], false);
				this.loading = false;
				return;
			}

			go.Db.store("FieldSet").get(response.ids, function (fieldSets) {
				fieldSetsLoaded = true;

				var storeData = [], lastSortOrder = -1;
				fieldSets.forEach(function (fs) {
					fs.sortOrder = lastSortOrder + 1;
					lastSortOrder = fs.sortOrder;
					storeData.push([
						fs.name,
						null,
						null,
						null,
						fs.id,
						true,
						fs.sortOrder * 100000,
						fs.aclId
					]);

					fsSortOrderMap[fs.id] = fs.sortOrder * 100000;

				});

				this.store.loadData(storeData, true);

			}, this);

			go.Db.store("Field").query({
				filter: {
					fieldSetId: response.ids
				}
			}, function (response) {
				go.Db.store("Field").get(response.ids, function (fields) {
					var storeData = [], lastSortOrder = -1;
					fields.forEach(function (f) {
						f.sortOrder = lastSortOrder + 1;
						lastSortOrder = f.sortOrder;
						storeData.push([
							f.name,
							f.databaseName,
							f.type,
							f.id,
							f.fieldSetId,
							false,
							f.sortOrder + fsSortOrderMap[f.fieldSetId],
							null
						]);
					});
					this.store.loadData(storeData, true);

					this.store.multiSort([
//						//{field: 'fieldSetId', direction: 'ASC'},
//						{field: 'isFieldSet', direction: 'DESC'},
						{field: 'sortOrder', direction: 'ASC'}
					]);
					this.loading = false;
				}, this);
			}, this);

		}, this);



	}
});



