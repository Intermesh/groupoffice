go.modules.community.files.NodeGrid = Ext.extend(go.grid.GridPanel, {
	paging: true,
	enableDragDrop:true,
	multiselect:true,
	browser: null,
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
					 '<small>'+t('Or use the \'+\' button')+'</small>',
				enableRowBody:true,
				getRowClass: function(record, rowIndex, rp, ds){ // rp = rowParams
					if(record.data.status=='queued'){
						rp.body = '<progress max="100" value="'+record.data.progress+'"></progress>'
						return 'x-grid3-row-expanded';
					}
					return 'x-grid3-row-collapsed';
			  }
			},
			autoExpandColumn: 'name',
			// config options for stateful behavior
			stateful: true,
			stateId: 'files-grid'
		});
		
		this.store.entityStore.on('changes', function(store, added, changed, destroyed){
			var stale = false;
			for(var i = 0; i < changed.length; i++) {
				if(store.get(changed[i]).parentId !== this.store.baseParams.filter.parentId) {
					stale = true;
				}
			}
			if(stale) {
				this.store.reload();
			}

		},this);

		go.modules.community.files.NodeGrid.superclass.initComponent.call(this);
	},
	
	afterRender: function() {
		go.modules.community.files.NodeGrid.superclass.afterRender.call(this);

		var el =  this.getView().scroller.dom;
		new Ext.dd.DropTarget(el, {
			ddGroup: 'files-center-dd',
			copy:false,
			browser: this.browser,
			notifyOver : this.onNotifyOver,
			notifyDrop: this.onNotifyDrop
		});
	},
	
	onNotifyDrop : function(ddSource, e, data){
		var records = ddSource.dragData.selections,
			i = ddSource.getDragData(e).rowIndex,
			droppedAt = ddSource.grid.store.getAt(i);
		if(!droppedAt) {
			return false;
		}
		if(droppedAt.data.isDirectory) {
			this.browser.receive(records, droppedAt.data.id, 'move');
			//Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
		}
		return true
	},
	
	onNotifyOver : function(dd, e, data){
		var dragData = dd.getDragData(e),
			dropRecord = data.grid && data.grid.store.data.items[dragData.rowIndex];
	  console.log(dropRecord);
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

