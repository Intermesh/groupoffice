GO.files.FilesGrid = function(config) {
	
	config = config || {};
	config.layout = 'fit';
	config.split  = true;
	config.paging  = true;
	config.autoExpandColumn = 'name';
	config.sm = new Ext.grid.RowSelectionModel();
	config.loadMask = true;
	config.enableDragDrop = true;
	config.ddGroup = 'FilesDD';
	
	config.viewConfig = {
		emptyText: '<div class="go-dropzone">'+t('Drop files here')+'</div>',
		getRowClass: function(record, rowIndex, rowParams, store) {

			if(GO.files.isContentExpired(record.json.content_expire_date)){
				return 'content-expired';
			} else {
				return '';
			}
		}
	};
	
//	config.viewConfig = {'forceFit':true};

	GO.files.FilesGrid.superclass.constructor.call(this,config);
};

Ext.extend(GO.files.FilesGrid, GO.grid.GridPanel, {
	applyStoredState : function(state){
		delete state.sort;
		//this.stateful=false;

		GO.files.FilesGrid.superclass.applyState.call(this, state);
		if (this.rendered){
			this.reconfigure(this.store,this.getColumnModel());
			this.getColumnModel().setColumnWidth(0,this.getColumnModel().getColumnWidth(0));
		}

		//this.enableState.defer(500,this);
	}

//	enableState : function(){
//		this.stateful=true;
//	}
});
