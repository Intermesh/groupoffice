go.modules.community.files.NodeGrid = Ext.extend(go.grid.GridPanel, {
	paging: true,
	enableDragDrop:true,
	multiselect:true,
	ddGroup: 'files-center-dd',
	initComponent: function () {

		Ext.apply(this, {
			columns: [
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: 40,
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'name',
					header: t('Name'),
					renderer: function(value, store, record) {
						var icon = go.util.contentTypeClass(record.data.contentType, record.data.name);
						return '<i class="icon filetype '+icon+'"></i><span>'+value+'</span>';
					},
					width: 75,
					sortable: true,
					dataIndex: 'name'
				},{
					header: t('Size'),
					width: 120,
					sortable: true,
					dataIndex: 'size',
					renderer: function(v) {
						return v ? Ext.util.Format.fileSize(v) : '';
					}
				},{
					xtype:"datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: 160,
					sortable: true,
					dataIndex: 'createdAt',
					hidden: true
				},
				{					
					xtype:"datecolumn",
					hidden: false,
					id: 'modifiedAt',
					header: t('Modified at'),
					width: 160,
					sortable: true,
					dataIndex: 'modifiedAt'
				}
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
			},
			autoExpandColumn: 'name',
			// config options for stateful behavior
			stateful: true,
			stateId: 'files-grid'
		});

		go.modules.community.files.NodeGrid.superclass.initComponent.call(this);
	},
	
	afterRender: function() {
		go.modules.community.files.NodeGrid.superclass.afterRender.call(this);
		
		var el =  this.getView().scroller.dom;
		new Ext.dd.DropTarget(el, {
			ddGroup: 'files-center-dd',
			notifyDrop: function(ddSource, e, data){
				var records = ddSource.dragData.selections,
					cindex = ddSource.getDragData(e).rowIndex,
					droppedAt = ddSource.grid.store.getAt(cindex);
				if(droppedAt.data.isDirectory) {
					Ext.each(records, function(record) {
						record.set('parentId', droppedAt.data.id);
					}, ddSource.grid.store);
					ddSource.grid.store.commitChanges();
					Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
				}
				return true
			}
		})
		  
	}
});

