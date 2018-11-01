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

go.modules.community.notes.MainPanel = Ext.extend(Ext.Panel, {

	layout: 'responsive',
	layoutConfig: {
		triggerWidth: 1000
	},

	initComponent: function () {

//		debugger;
		this.noteBookGrid = new go.modules.community.notes.NoteBookGrid({
			region: 'west',
			cls: 'go-sidenav',
			width: dp(280),
			split: true,
			tbar: [{
					xtype: 'tbtitle',
					text: t('Notebooks')
				}, '->', {
					disabled: go.Modules.get("community", 'notes').permissionLevel < GO.permissionLevels.write,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var noteBookForm = new go.modules.community.notes.NoteBookForm();
						noteBookForm.show();
					}
				}, {
					cls: 'go-narrow',
					iconCls: "ic-arrow-forward",
					tooltip: t("Notes"),
					handler: function () {
						this.noteGrid.show();
					},
					scope: this
				}],
			listeners: {
				rowclick: function(grid, row, e) {
					if(e.target.className != 'x-grid3-row-checker') {
						//if row was clicked and not the checkbox then switch to grid in narrow mode
						this.noteGrid.show();
					}
				},
				scope: this
			}
		});

		this.noteBookGrid.getSelectionModel().on('selectionchange', function (sm) {
			var ids = [];

			this.addNoteBookId = false;

			Ext.each(sm.getSelections(), function (r) {
				ids.push(r.id);
				if (!this.addNoteBookId && r.get('permissionLevel') >= GO.permissionLevels.write) {
					this.addNoteBookId = r.id;
				}
			}, this);

			this.addButton.setDisabled(!this.addNoteBookId);

			this.noteGrid.getStore().baseParams.filter.noteBookId = ids;
			this.noteGrid.getStore().load();
		}, this, {buffer: 1}); //add buffer because it clears selection first


		this.noteGrid = new go.modules.community.notes.NoteGrid({
			region: 'center',
			tbar: [
				{
					cls: 'go-narrow',
					iconCls: "ic-menu",
					handler: function () {
//						this.westPanel.getLayout().setActiveItem(this.noteBookGrid);
						this.noteBookGrid.show();
					},
					scope: this
				},
				'->',
				{
					xtype: 'tbsearch'
				},
				this.addButton = new Ext.Button({
					disabled: true,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (btn) {
						var noteForm = new go.modules.community.notes.NoteForm({
							formValues: {
								noteBookId: this.addNoteBookId
							}
						});
						noteForm.show();
					},
					scope: this
				}),{
				iconCls: 'ic-more-vert',
				menu: [
					{
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							this.noteGrid.deleteSelected();
						},
						scope: this
					}
				]
			}
				
//				,{
//					disabled: go.Modules.get("community", 'notes').permissionLevel < GO.permissionLevels.write,
//					iconCls: 'ic-add',
//					tooltip: t('Add test'),
//					handler: function (e, toolEl) {
//						var store = this.noteGrid.store;
//						var myRecordDef = Ext.data.Record.create(store.fields);
//
//						store.insert(0, new myRecordDef({
//							name: "New",
//							content: "Testing",
//							noteBookId: this.addNoteBookId
//						}));
//						
//						store.commitChanges();
//					},
//					scope: this
//				}
			],
			listeners: {
				viewready: function (grid) {
					//load note books and select the first
					this.noteBookGrid.getStore().load({
						callback: function (store) {
							this.noteBookGrid.getSelectionModel().selectRow(0);
						},
						scope: this
					});
				},

				rowdblclick: function (grid, rowIndex, e) {

					var record = grid.getStore().getAt(rowIndex);
					if (record.get('permissionLevel') < GO.permissionLevels.write) {
						return;
					}

					var noteEdit = new go.modules.community.notes.NoteForm();
					noteEdit.load(record.id).show();
				},

				scope: this
			}
		});

		this.noteGrid.getSelectionModel().on('rowselect', function (sm, rowIndex, record) {
			go.Router.goto("note/" + record.id);
		}, this);

		this.noteDetail = new go.modules.community.notes.NoteDetail({
			region: 'center',
			split: true,
			tbar: [{
					cls: 'go-narrow',
					iconCls: "ic-arrow-back",
					handler: function () {
						this.westPanel.show();
					},
					scope: this
				}]
		});

		this.westPanel = new Ext.Panel({
			region: "west",
			layout: "responsive",
			stateId: "go-notes-west",
			split: true,
			width: dp(700),
			narrowWidth: dp(400), //this will only work for panels inside another panel with layout=responsive. Not ideal but at the moment the only way I could make it work
			items: [
				this.noteGrid, //first is default in narrow mode
				this.noteBookGrid
			]
		});

		this.items = [
			this.westPanel, //first is default in narrow mode
			this.noteDetail
		];

		go.modules.community.notes.MainPanel.superclass.initComponent.call(this);
	}
});

