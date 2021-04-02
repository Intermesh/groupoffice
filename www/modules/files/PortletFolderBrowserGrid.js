/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: PortletFolderBrowserGrid.js 17837 2014-01-17 14:29:56Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
GO.files.PortletFolderBrowserGrid = Ext.extend(GO.grid.GridPanel, {
	
	initComponent: function () {

		Ext.applyIf(this, {
			layout: 'fit',
			stateId: 'portlet-folder-browser-grid',
			split: true,
			paging: true,
			autoExpandColumn: 'name',
			sm: new Ext.grid.RowSelectionModel(),
			loadMask: true,
			noDelete: true,
			standardTbar: false,
			store: new GO.data.JsonStore({
				url: GO.url("files/folder/list"),
				baseParams: {
					'folder_id': this.folderId
				},
				id: 'type_id',
				remoteSort: true,
				autoDestroy: true,
				fields: ['type_id', 'id', 'name', 'type', 'size', 'mtime', 'extension', 'timestamp', 'thumb_url', 'path', 'acl_id', 'locked_user_id', 'locked', 'folder_id', 'permission_level', 'readonly', 'unlock_allowed', 'handler']
			}),
			view: new Ext.grid.GridView({
				emptyText: t("No items to display"),
				getRowClass: function (record, rowIndex, rp, ds) {
					return '';
				}
			}),
			border: false,
			cm: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true
				},
				columns: [{
						id: 'name',
						header: t("Name"),
						dataIndex: 'name',
						renderer: function (v, meta, r) {
							var cls = r.get('acl_id') > 0 && r.get('readonly') == 0 ? 'folder-shared' : 'filetype filetype-' + r.get('extension');
							if (r.get('locked_user_id') > 0)
								v = '<div class="fs-grid-locked">' + v + '</div>';

							return '<div class="go-grid-icon ' + cls + '" style="float:left;">' + v + '</div>';
						}
					}, {
						id: 'type',
						header: t("Type"),
						dataIndex: 'type',
						sortable: true,
						hidden: true,
						width: 100
					}, {
						id: 'size',
						header: t("Size"),
						dataIndex: 'size',
						renderer: function (v) {
							return  v == '-' ? v : Ext.util.Format.fileSize(v);
						},
						hidden: true,
						width: 100
					}, {
						id: 'mtime',
						header: t("Modified at"),
						dataIndex: 'mtime',
						xtype: "datecolumn"
					}]
			}),
			listeners: {
				show: function(){
					this.store.load();
				},
				rowdblclick: function (grid, rowClicked, e) {
					var selectionModel = grid.getSelectionModel();
					var record = selectionModel.getSelected();
					
					if(record.data.extension == 'folder'){
						go.Router.goto("#folder/" + record.data.id);
						
					} else {
						go.Router.goto("#file/" + record.data.id);
						
					}
				},
				scope:this
			}
			
		});
		GO.files.PortletFolderBrowserGrid.superclass.initComponent.call(this);
	}
});
