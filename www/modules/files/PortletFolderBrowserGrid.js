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
				fields: ['type_id', 'id', 'name', 'type', 'size', 'mtime', 'extension', 'timestamp', 'thumb_url', 'path', 'acl_id', 'locked_user_id', 'locked', 'folder_id', 'permission_level', 'readonly', 'unlock_allowed', 'handler']
			}),
			view: new Ext.grid.GridView({
				emptyText: GO.lang['strNoItems'],
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
						header: GO.lang['strName'],
						dataIndex: 'name',
						renderer: function (v, meta, r) {
							var cls = r.get('acl_id') > 0 && r.get('readonly') == 0 ? 'folder-shared' : 'filetype filetype-' + r.get('extension');
							if (r.get('locked_user_id') > 0)
								v = '<div class="fs-grid-locked">' + v + '</div>';

							return '<div class="go-grid-icon ' + cls + '" style="float:left;">' + v + '</div>';
						}
					}, {
						id: 'type',
						header: GO.lang.strType,
						dataIndex: 'type',
						sortable: true,
						hidden: true,
						width: 100
					}, {
						id: 'size',
						header: GO.lang.strSize,
						dataIndex: 'size',
						renderer: function (v) {
							return  v == '-' ? v : Ext.util.Format.fileSize(v);
						},
						hidden: true,
						width: 100
					}, {
						id: 'mtime',
						header: GO.lang.strMtime,
						dataIndex: 'mtime',
						width: 40
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
						GO.linkHandlers["GO\\Files\\Model\\Folder"].call(this, record.data.id);
					} else {
						GO.linkHandlers["GO\\Files\\Model\\File"].call(this, record.data.id);
					}
				},
				scope:this
			}
			
		});
		GO.files.PortletFolderBrowserGrid.superclass.initComponent.call(this);
	}
});