GO.grid.notifyDrop = function(dd, e, data)
{	
	var sm=data.grid.getSelectionModel();
	var rows=sm.getSelections();
	var dragData = dd.getDragData(e);
	var cindex=dragData.rowIndex;
	
	var store = data.grid.store;
	
	if(typeof(cindex)=='undefined')
		cindex=store.data.length-1;

	//var dropRowData = this.store.getAt(cindex);

	

	for(i = 0; i < rows.length; i++) 
	{								
		var rowData=store.getById(rows[i].id);

		if(!this.copy)
			store.remove(store.getById(rows[i].id));

		store.insert(cindex,rowData);
	}

	//save sort order							
	var records = [];

	for (var i = 0; i < store.data.items.length;  i++)
	{			    	
		records.push({id: store.data.items[i].get('id')});
	}

	GO.request({
		url:data.grid.notifyDropSubmitUrl,
		params:{
			records:Ext.encode(records)
		}
	})		
}