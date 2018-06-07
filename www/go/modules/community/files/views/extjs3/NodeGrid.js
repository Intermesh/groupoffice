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
						if(record.data.bookmarked) {
							value += ' <i class="icon small">bookmark</i>';
						}
						if(record.data.internalShared || record.data.externalShared) {
							value += ' <i class="icon small">group</i>';
						}
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
			viewConfig:{
				emptyText: '<i class="icon">cloud_upload</i><br>'+
					 '<h3>'+t('Drop files here')+'</h3>'+
					 '<small>'+t('Or use the \'+\' button')+'</small>'
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
			copy:false,
			notifyOver : this.onNotifyOver,
			notifyDrop: this.onNotifyDrop
		})
		  
	},
	
	onNotifyDrop : function(ddSource, e, data){
		var records = ddSource.dragData.selections,
			i = ddSource.getDragData(e).rowIndex,
			droppedAt = ddSource.grid.store.getAt(i);
		if(!droppedAt) {
			return false;
		}
		if(droppedAt.data.isDirectory) {
			Ext.each(records, function(record) {
				record.set('parentId', droppedAt.data.id);
			}, ddSource.grid.store);
			ddSource.grid.store.commitChanges();
			//Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
		}
		return true
	},
	
	onNotifyOver : function(dd, e, data){
		var dragData = dd.getDragData(e),
			dropRecord = data.grid && data.grid.store.data.items[dragData.rowIndex];
		if(!dropRecord) {
			return false;
		}
		if(!dropRecord.data.isDirectory) {
			return false;
		}
		for(var i=0;i<data.selections.length;i++) {
			if(data.selections[i].data.id==dropRecord.data.id) {
				return false;
			}
		}
		return this.dropAllowed;
	}
});

