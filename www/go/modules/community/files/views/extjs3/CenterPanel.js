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
go.modules.community.files.CenterPanel = Ext.extend(Ext.Panel, {
	layout:'card',
	activeItem: 0,
	deferredRender: false,
	border: false,
	browser: null, // set from mainpanel
	detailView: null, // from mainpanel (for click in centerpanel)
	previewTypes: {
      'image/png': true,
      'image/jpeg': true,
      'image/gif': true
   },
	
	pendingDuplicates: {},
		
	initComponent: function () {
		
		if(!this.browser){
			throw "Parameter 'browser' is required!";
		}
		
		this.store = new go.data.Store({
				fields: [
					'id', 
					'name',
					'bookmarked',
					'internalShared',
					'externalShared',
					'storageId',
					{name: 'touchedAt', type: 'date'},
					{name: 'contentType', submit: false},
					{name: 'metaData', submit: false},
					{name: 'size', submit: false},
					{name: 'progress', submit: false},
					{name: 'status', submit: false},
					'isDirectory', 
					{name: 'createdAt', type: 'date'}, 
					{name: 'modifiedAt', type: 'date'}, 
					'aclId'
				],
				entityStore: go.Stores.get("Node")
			});

		var contextMenu = new go.modules.community.files.ContextMenu({
			store: this.store
		});
		
		this.browser.on('pathchanged', function(browser, path, filter) {
			this.store.setBaseParam('filter',filter);
			this.store.load();
		}, this);
		
		this.nodeGrid = new go.modules.community.files.NodeGrid({
			store:this.store,
			browser:this.browser,
			listeners: {
				rowcontextmenu: function(grid, index, event){
					event.stopEvent();
					var selections = grid.getSelectionModel().getSelections();
					var records = [];
					for(var i=0; i < selections.length; i++){
						records.push(selections[i].json);
					};
					
					contextMenu.showAt(event.xy, records);
				},
				rowdblclick: function (grid, rowIndex, e) {
					var record = grid.getStore().getAt(rowIndex);
					if (record.data.isDirectory) {
						this.browser.descent(record.data.id);
						return;
					} else {
						go.Preview(record.json);
					}
				},
				scope:this
			},
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: false,
				listeners: {
					selectionchange: function(sm){
						var records = sm.getSelections();
						if(records.length === 1) {
							this.detailView.getLayout().setActiveItem(0);
							this.detailView.items.itemAt(0).load(parseInt(records[0].data.id));
						} else if (records.length > 1) {
							this.detailView.getLayout().setActiveItem(1);
							var ids = [];
							for(var i = 0; i < records.length; i++) {
								ids.push(records[i].data.id);
							}
							this.detailView.items.itemAt(1).load(ids);
						}
					},
					scope: this
				}
			})
		});
		
		this.nodeTile = new go.modules.community.files.NodeTile({
			store:this.store,
			browser:this.browser,
			singleSelect: false,
			listeners: {
				selectionchange: function(view, records){
					if(records.length === 1) {
						this.detailView.getLayout().setActiveItem(0);
						var record = view.getStore().getAt(records[0].viewIndex);
						this.detailView.items.itemAt(0).load(parseInt(record.data.id));
					} else if (records.length > 1) {
						this.detailView.getLayout().setActiveItem(1);
						var ids = [];
						for(var i = 0; i < records.length; i++) {
							var record = view.getStore().getAt(records[i].viewIndex);
							ids.push(record.data.id);
						}
						this.detailView.items.itemAt(1).load(ids);
					}
				},
				contextmenu: function(view, index, node, event){
					event.stopEvent();
					var selections = view.getSelectedRecords();
					if(Ext.isEmpty(selections)) {
						view.select(index);
						selections = view.getSelectedRecords();
					}
					var records = [];
					for(var i=0; i < selections.length; i++){
						records.push(selections[i].json);
					};
					contextMenu.showAt(event.xy, records);
				},
				dblclick(view, index, node, e) {
					var record = view.getStore().getAt(index);
					if(record.data.isDirectory) {
						this.browser.descent(record.data.id);
					} else {
						go.Preview(record.json);
					}
					
				},
				scope:this
			}
		});
		
		this.items = [this.nodeGrid, this.nodeTile];
		
		this.breadCrumbs = new go.modules.community.files.BreadCrumbBar({
			browser: this.browser,
			style: {'background-color': 'white'}
		});
		
		this.tbar =  {// configured using the anchor layout
			xtype: 'container',
			items: [new Ext.Toolbar({
				items: [
//							{
//								cls: 'go-narrow',
//								iconCls: "ic-menu",
//								handler: function () {
//									this.sideNav.show();
//								},
//								scope: this
//							},
					'->',
						this.addButton = new Ext.Button({
							iconCls: 'ic-add',
							tooltip: t('Add'),
							menu: new Ext.menu.Menu({
								items: [{
										iconCls: 'ic-create-new-folder',
										text: t("Create folder") + '&hellip;',
										handler: function () {
											var nodeDialog = new go.modules.community.files.NodeDialog();
											nodeDialog.setTitle(t("Create new folder"));
											nodeDialog.show(this.browser.getCurrentDir());
										},
										scope: this
									}, '-', {
										iconCls: 'ic-file-upload',
										text: t("Upload files") + '&hellip;',
										handler: function () {
											if (!this.uploadDialog) {
												var input = document.createElement("input"),
												me = this;
												input.setAttribute("type", "file");
												input.setAttribute('multiple', true);
												input.onchange = function (e) {
													for (var i = 0; i < this.files.length; i++) {
														me.fileUpload(this.files[i]);
													}
												};
												this.uploadDialog = input;
											}
											this.uploadDialog.click(); // opening dialog
										},
										scope: this
									}, {
										iconCls: 'ic-folder',
										text: t("Upload folder") + '&hellip;',
										handler: function () {
											if (!this.uploadDialog) {
												var input = document.createElement("input"),
																me = this;
												input.setAttribute("type", "file");
												input.setAttribute('multiple', true);
												input.setAttribute('webkitdirectory', true);
												input.setAttribute('directory', true);

												input.onchange = function (e) {
													for (var i = 0; i < this.files.length; i++) {
														var path = this.files[i].webkitRealtivePath.split('/');
														var record = new this.store.recordType({
															name: file.name,
															isDirectory: 0,
															parentId: this.store.baseParams.filter.parentId,
															size: file.size,
															status: 'queued'
														});
														console.log(this.files[i]);
														//me.centerCardPanel.fileUpload(this.files[i]);
													}
												};
												this.uploadDialog = input;
											}
											this.uploadDialog.click(); // opening dialog
										},
										scope: this
									}, '-', {
										disabled: true,
										text: t('File from template') + '&hellip;',
										icon: 'ic-insert-drive-file'
									}]
							}),
							scope: this
						}), {
							xtype: 'tbsearch',
							store: this.store,
							listeners: {
								open: function () {
									this.breadCrumbs.setVisible(false);
									this.advancedSearchBar.setVisible(true);
									this.doLayout();
								},
								close: function () {
									this.breadCrumbs.setVisible(true);
									this.advancedSearchBar.setVisible(false);
									this.doLayout();
								},
								scope: this
							}
						}, {
								xtype:'buttongroup',
								items:[{
									tooltip: t("List", "files"),
									iconCls: 'ic-view-list',
									enableToggle:true,
									allowDepress:false,
									pressed : true,
									toggleGroup:'files-view-switcher',
									handler: function(item){
										this.getLayout().setActiveItem(0);
									},
									scope:this
								},{
									tooltip: t("Thumbnails", "files"),
									toggleGroup:'files-view-switcher',
									enableToggle:true,
									allowDepress:false,
									iconCls: 'ic-view-comfy',
									handler: function(item){
										this.getLayout().setActiveItem(1);
									},
									scope:this
								}]
							}]
				}),
				this.advancedSearchBar = new Ext.Toolbar({
					hidden: true,
					updateCurrentFolder: function () {
						var id = this.browser.getCurrentDir();
						if (!id) {
							return;
						}
						var node = go.Stores.get('Node').get([id])[0];
						if (node) {
							var btnCurrentFolder = this.advancedSearchBar.items.get(2);
							btnCurrentFolder.setText(node.name);
							btnCurrentFolder.parentId = node.id;
						}
						;
					},
					listeners: {
						afterrender: function (me) {
							this.browser.on('pathchanged', me.updateCurrentFolder, this);
						},
						show: function (me) {
							me.updateCurrentFolder.apply(this);
						},
						scope: this
					},
					style: {
						'border-bottom': 0,
						'background-color': 'white'
					},
					defaults: {toggleGroup: 'file-search-filter', enableToggle: true},
					items: [
						t('Search in') + ':',{
							text: t('All folders'),
							toggleHandler: function (btn, state) {
								if (state) {
									delete this.store.baseParams.filter.parentId;
									this.store.reload();
								}
							},
							scope: this
						},{
							text: '',
							pressed: true, //default
							toggleHandler: function (btn, state) {
								if(!state) {
									delete this.store.baseParams.filter.parentId
								}
								if (state) {
									this.store.baseParams.filter.parentId = btn.parentId;
									this.store.reload();
								}
							},
							scope: this
						},{
							text: t('Shared with me'),
							toggleHandler: function(btn, state) {
								if(!state) {
									delete this.store.baseParams.filter.sharedWithMe
								}
								if(state) {
									this.store.baseParams.filter.sharedWithMe = true;
									this.store.reload();
								}
							},
							scope: this
						},{
							text: t('Bookmarks'),
							toggleHandler: function(btn, state) {
								if(!state) {
									delete this.store.baseParams.filter.bookmarked
								}
								if(state) {
									this.store.baseParams.filter.bookmarked = true;
									this.store.reload();
								}
							},
							scope: this
						}
					]
				}),
				this.breadCrumbs
			]};
	
		go.modules.community.files.CenterPanel.superclass.initComponent.call(this, arguments);

	},
	
	afterRender : function(view) {
		var childCount = 0;
		this.body.dom.addEventListener('dragenter', function(e) {
			e.preventDefault();
			e.stopPropagation();
			childCount++;
			this.body.addClass('x-dd-over');
		}.bind(this));
		
		this.body.dom.addEventListener('dragleave', function(e) {
			e.preventDefault();
			childCount--;
			if (childCount === 0) {
				this.body.removeClass('x-dd-over');
			}
		}.bind(this));
		
		this.body.dom.addEventListener('dragover', function(e) {
			e.preventDefault(); // THIS IS NEEDED
			e.stopPropagation();
		});
		
		this.body.dom.addEventListener('drop', function(e) {
			e.stopPropagation();
			e.preventDefault();
			this.body.removeClass('x-dd-over');
			this.browser.receive(e.dataTransfer.files, this.store);
		}.bind(this));
		
		go.modules.community.files.CenterPanel.superclass.afterRender.call(this, arguments);
	}

});
