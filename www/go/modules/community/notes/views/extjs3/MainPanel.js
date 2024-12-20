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

go.modules.community.notes.MainPanel = Ext.extend(go.modules.ModulePanel, {
	id: "notes",
	title: t("Notes"),

	layout: 'responsive',
	layoutConfig: {
		triggerWidth: 1000
	},

	initComponent: function () {

		this.createNoteGrid();

		this.sidePanel = new Ext.Panel({
			autoScroll: true,
			layout: "anchor",
			defaultAnchor: '100%',
			width: dp(300),
			cls: 'go-sidenav',
			region: "west",
			split: true,
			items: [
				this.createNoteBookGrid(),
				{xtype:'filterpanel', store: this.noteGrid.store, entity: 'Note'}
			]
		});

		this.noteDetail = new go.modules.community.notes.NoteDetail({
			region: 'center',
			split: true,
			tbar: [{
					cls: 'go-narrow', //will only show on small devices
					iconCls: "ic-arrow-back",
					handler: function () {
						//this.westPanel.show();
						go.Router.goto("notes");
					},
					scope: this
				}]
		});

		// route to main grid when details resets / is deleted for mobile view
		this.noteDetail.on("reset", () => {
			go.Router.goto("notes");
		})

		this.westPanel = new Ext.Panel({
			region: "west",
			layout: "responsive",
			stateId: "go-notes-west",
			split: true,
			width: dp(700),
			narrowWidth: dp(400), //this will only work for panels inside another panel with layout=responsive. Not ideal but at the moment the only way I could make it work
			items: [
				this.noteGrid, //first is default in narrow mode
				this.sidePanel
			]
		});

		this.items = [
			this.westPanel, //first is default in narrow mode
			this.noteDetail
		];

		go.modules.community.notes.MainPanel.superclass.initComponent.call(this);
		
		//use viewready so load mask can show
		this.noteBookGrid.on("viewready", this.runModule, this);
	},
	
	runModule : function() {
		//load note books and select the first
		this.setDefaultSelection();
		this.noteBookGrid.getStore().load();
		this.noteGrid.getStore().load();
	},

	setDefaultSelection : function() {
		let selectedListIds = [];
		if(go.User.notesSettings.rememberLastItems && go.User.notesSettings.lastNoteBookIds) {
			selectedListIds = go.User.notesSettings.lastNoteBookIds;
		}
		if(!selectedListIds.length && go.User.notesSettings.defaultNoteBookId) {
			selectedListIds.push(go.User.notesSettings.defaultNoteBookId);
		}

		this.noteBookGrid.setDefaultSelection(selectedListIds);
		this.checkCreateNoteBook();
	},
	
	createNoteBookGrid : function() {
		this.noteBookGrid = new go.modules.community.notes.NoteBookGrid({
			region: "north",
			filterName: "noteBookId",
			filteredStore: this.noteGrid.store,
			showMoreLoader: true,

			tbar: [
				{
					xtype: 'tbtitle',
					text: t('Notebooks')
				}, '->', {
					xtype: "tbsearch"
				},{
					hidden: !go.Modules.get("community", 'notes').userRights.mayChangeNoteBooks,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.community.notes.NoteBookDialog();
						dlg.show();
					}
				}, 
				{
					cls: 'go-narrow',
					iconCls: "ic-arrow-forward",
					tooltip: t("Notes"),
					handler: function () {
						this.noteGrid.show();
					},
					scope: this
				}],
			listeners: {
				afterrender: function(grid) {
					new Ext.dd.DropTarget(grid.getView().mainBody, {
						ddGroup : 'NotebooksDD',
						notifyDrop :  (source, e, data) => {
							const selections = source.dragData.selections,
								dropRowIndex = grid.getView().findRowIndex(e.target),
								noteBookId = grid.getView().grid.store.data.items[dropRowIndex].id;


							const update = {};
							selections.forEach((r) => {
								update[r.id] = {noteBookId: noteBookId};
							})

							go.Db.store("Note").set({update: update});

						}
					});
				},
				rowclick: function(grid, row, e) {
					if(e.target.className != 'x-grid3-row-checker') {
						//if row was clicked and not the checkbox then switch to grid in narrow mode
						this.noteGrid.show();
					}
				},
				scope: this
			}
		});

		this.noteBookGrid.on('selectionchange', this.onNoteBookSelectionChange, this);

		return this.noteBookGrid;
	},
	
	
	createNoteGrid : function() {
		this.noteGrid = new go.modules.community.notes.NoteGrid({
			region: 'center',
			ddGroup: "NotebooksDD",
			enableDrag: true,
			multiSelectToolbarItems: [
				{
					hidden: go.customfields.CustomFields.getFieldSets('Note').length == 0,
					iconCls: 'ic-edit',
					tooltip: t("Batch edit"),
					handler: function() {
						var dlg = new go.form.BatchEditDialog({
							entityStore: "Note"
						});
						dlg.setIds(this.noteGrid.getSelectionModel().getSelections().column('id')).show();
					},
					scope: this
				}
			],
			tbar: [
				{
					cls: 'go-narrow', //Shows on mobile only
					iconCls: "ic-menu",
					handler: function () {
						this.sidePanel.show();
					},
					scope: this
				},
				'->',
				{
					xtype: 'tbsearch',
					filters: [
						'text',
						'name', 
						'content',
						{name: 'modified', multiple: false},
						{name: 'created', multiple: false}						
					]
				},
				this.addButton = new Ext.Button({
					disabled: true,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					cls: "primary",
					handler: function (btn) {
						var noteForm = new go.modules.community.notes.NoteDialog();
						noteForm.show();
						noteForm.setValues({
								noteBookId: this.addNoteBookId
							});
					},
					scope: this
				}),
				this.moreMenu = new Ext.Button({
					iconCls: 'ic-more-vert',
					menu: [{
						iconCls: 'ic-cloud-upload',
						text: t("Import"),
						handler: function() {
							go.util.importFile(
								'Note',
								'.csv, .xlsx, .json',
								{noteBookId: this.addNoteBookId},
								{}
							);
						},
						scope: this
					},{
						iconCls: 'ic-cloud-download',
						text: t("Export"),
						menu: [{
							text: 'Microsoft Excel',
							iconCls: 'filetype filetype-xls',
							handler: function() {
								go.util.exportToFile(
									'Note',
									Object.assign(go.util.clone(this.noteGrid.store.baseParams), this.noteGrid.store.lastOptions.params, {
										limit: 0,
										position: 0
									}),
									"xlsx");
							},
							scope: this
						},{
							text: 'Comma Separated Values',
							iconCls: 'filetype filetype-csv',
							handler: function () {
								go.util.exportToFile(
									'Note',
									Object.assign(go.util.clone(this.noteGrid.store.baseParams), this.noteGrid.store.lastOptions.params, {
										limit: 0,
										position: 0
									}),
									'csv');
							},
							scope: this
						},{
							text: t("Web page") + " (HTML)",
							iconCls: 'filetype filetype-html',
							handler: function() {
								go.util.exportToFile(
									'Note',
									Object.assign(go.util.clone(this.noteGrid.store.baseParams), this.noteGrid.store.lastOptions.params, {
										limit: 0,
										position: 0
									}),
									'html');
							},
							scope: this
						},

							{
							iconCls: 'filetype filetype-json',
							text: 'JSON',
							handler: function() {
								go.util.exportToFile(
									'Note',
									Object.assign(go.util.clone(this.noteGrid.store.baseParams), this.noteGrid.store.lastOptions.params, {
										limit: 0,
										position: 0
									}),
									'json');
							},
							scope: this
						}],
						scope: this

					}]
				})
			],
			listeners: {				
				rowdblclick: this.onNoteGridDblClick,
				scope: this,				
				keypress: this.onNoteGridKeyPress
			}
		});

		this.noteGrid.on('navigate', function (grid, rowIndex, record) {
			go.Router.goto("note/" + record.id);
		}, this);
		
		return this.noteGrid;
	
	},

	checkCreateNoteBook: function() {

		this.addNoteBookId = false;

		go.Db.store("NoteBook").get(this.noteBookGrid.getSelectedIds()).then((result) => {

			result.entities.forEach((notebook) => {
				if (!this.addNoteBookId && notebook.permissionLevel >= go.permissionLevels.create) {
					this.addNoteBookId = notebook.id;
				}
			});

			this.addButton.setDisabled(!this.addNoteBookId);
		});
	},
	
	onNoteBookSelectionChange : function (ids, sm) {

		this.checkCreateNoteBook();

		if(go.User.notesSettings.rememberLastItems && go.User.notesSettings.lastNoteBookIds.join(",") != ids.join(",")) {

			go.Db.store("User").save({
				notesSettings: {
					lastNoteBookIds: ids
				}
			}, go.User.id);
		}

	},
	
	onNoteGridDblClick : function (grid, rowIndex, e) {

		var record = grid.getStore().getAt(rowIndex);
		if (record.get('permissionLevel') < go.permissionLevels.write) {
			return;
		}

		var dlg = new go.modules.community.notes.NoteDialog();
		dlg.load(record.id).show();
	},
	
	onNoteGridKeyPress : function(e) {
		if(e.keyCode != e.ENTER) {
			return;
		}
		var record = this.noteGrid.getSelectionModel().getSelected();
		if(!record) {
			return;
		}

		if (record.get('permissionLevel') < go.permissionLevels.write) {
			return;
		}

		var dlg = new go.modules.community.notes.NoteDialog();
		dlg.load(record.id).show();

	}
});

