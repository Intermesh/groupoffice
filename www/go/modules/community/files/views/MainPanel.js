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
 * @author Michael de Hart <mdhart@intermesh.nl>
 */

go.modules.files.MainPanel = Ext.extend(Ext.Panel, {

	layout: 'responsive',
	layoutConfig: {
		triggerWidth: 1000
	},

	initComponent: function () {


		this.folderTree = new go.modules.files.FolderTree({
			region: 'west',
			cls: 'go-sidenav',
			width: dp(280),
			split: true
		});

		this.folderTree.getSelectionModel().on('selectionchange', function (sm) {
			this.nodeGrid.getStore().baseParams.filter = [{parentId: sm.getSelected().id}];
			this.nodeGrid.getStore().load();
		}, this);

		this.nodeGrid = new go.modules.files.NodeGrid({
			region: 'center',
			tbar: [
				{
					cls: 'go-narrow',
					iconCls: "ic-menu",
					handler: function () {
						this.folderTree.show();
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
						alert('show menu');
					},
					scope: this
				})
			],
			listeners: {
				viewready: function (grid) {
					this.folderTree.getStore().load({
						callback: function (store) {
							this.folderTree.getSelectionModel().selectRow(0);
						},
						scope: this
					});
				},

				rowdblclick: function (grid, rowIndex, e) {

					var record = grid.getStore().getAt(rowIndex);
					if (record.get('permissionLevel') < GO.permissionLevels.write) {
						return;
					}

					var fileRename = new go.modules.files.FileForm();
					fileRename.load(record.id).show();
				},

				scope: this
			}
		});

		this.nodeGrid.getSelectionModel().on('rowselect', function (sm, rowIndex, record) {
			go.Router.goto("files/" + record.id);
		}, this);

		this.nodeDetail = new go.modules.files.NodeDetail({
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
			stateId: "go-files-west",
			split: true,
			width: dp(700),
			narrowWidth: dp(400), //this will only work for panels inside another panel with layout=responsive. Not ideal but at the moment the only way I could make it work
			items: [
				this.nodeGrid, //first is default in narrow mode
				this.folderTree			]
		});

		this.items = [
			this.westPanel, //first is default in narrow mode
			this.nodeDetail
		];

		go.modules.files.MainPanel.superclass.initComponent.call(this);
	}
});

