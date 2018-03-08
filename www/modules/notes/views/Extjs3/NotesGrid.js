/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: NotesGrid.js 20553 2016-10-25 09:57:14Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.notes.NotesGrid = function(config){
	
	if(!config)
	{
		config = {};
	}



	
	config.title = GO.notes.lang.notes;
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
		url: GO.url('notes/note/store'),		
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields: ['id','category_id','user_name',{name:'mtime'},{name:'ctime'},'name','content'],
		remoteSort: true,
		model:"GO\\Notes\\Model\\Note"
	});

	config.store.on('load', function()
	{
		if(config.store.reader.jsonData.feedback)
		{
			alert(config.store.reader.jsonData.feedback);
		}
	},this);

	config.paging=true;

	
	config.columns=[
		{
			header: GO.lang.strName,
			dataIndex: 'name',
			sortable: true
		},
		{
			header: GO.lang.strOwner,
			dataIndex: 'user_name',
			sortable: false,
			hidden:true
		},		{
			header: GO.lang.strCtime,
			dataIndex: 'ctime',
			format: GO.settings.date_format+" "+GO.settings.time_format,			
			hidden:true,
			sortable: true,
			width:110
		},		{
			header: GO.lang.strMtime,
			dataIndex: 'mtime',
			sortable: true,
			width:110
		}
		];
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: GO.lang['strNoItems']		
	});
	
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	this.searchField = new GO.form.SearchField({
		store: config.store,
		width:320
	});
		    	
	config.tbar = [GO.lang['strSearch'] + ':', this.searchField];
	
	GO.notes.NotesGrid.superclass.constructor.call(this, config);
};


Ext.extend(GO.notes.NotesGrid, GO.grid.GridPanel,{
	

	});