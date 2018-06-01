/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LinksGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.grid.LinksGrid = function(config){
	
	if(!config)
	{
		config={};
	}
	
	if(!config.link_id)
	{
		config.link_id=0;
	}
	
	if(!config.link_type)
	{
		config.link_type=0;
	}
	
	if(!config.folder_id)
	{
		config.folder_id=0;
	}
	
	//was required to show the search field in the tbar
	config.hideMode='offsets';
	
	config.cls='go-white-bg';

	config['store'] = new GO.data.JsonStore({

		url: GO.url('search/links'),			
		baseParams: {
			task: "links",
			model_id: this.model_id,
			model_name: this.model_name,
			folder_id: this.folder_id,
			type_filter:'true',
			no_filter_save: config.noFilterSave
		},
		root: 'results',
		totalProperty: 'total',
		id: 'model_name_and_id',
		fields: ['icon','id', 'model_name','name','model_type_id','type','mtime','model_id','module', 'description', 'name_and_type', 'model_name_and_id','link_description'],
		remoteSort: true

	});
	config['store'].setDefaultSort('mtime', 'desc');

	if(!config.noSearchField)
	{
		this.searchField = new GO.form.SearchField({
			store: config.store,
			width:240
		});
	
		config['tbar']=[
		t("Search")+': ', ' ',this.searchField
		];
	}
	
	config.clicksToEdit = 1;

	config.enableDragDrop=true;
	config.ddGroup='LinksDD';
	
	config['columns'] = [/*{
		      header: "",
		      hideable:false,
		      width:28,
					dataIndex: 'icon',
					renderer: this.iconRenderer
		    },*/{
		header: t("Name"),
		dataIndex: 'name',
		css: 'white-space:normal;',
		sortable: true,
		renderer:function(v, meta, record){
			return '<div class="go-grid-icon  go-model-icon-'+record.data.model_name.replace(/\\/g,"_")+'">'+v+'</div>';
		}
	},{
		header: t("Description"),
		dataIndex: 'link_description',
		sortable:true,
		editor : new GO.form.LinkDescriptionField()
	},{
		header: t("Type"),
		dataIndex: 'type',
		sortable:true,
		hidden:true			    
	},{
		header: t("Modified at"),
		dataIndex: 'mtime',
		sortable:true,
		width: dp(140)
	}];
		    
		    
	
	//config.autoExpandMax=2500;
	//config.autoExpandColumn=1;	
	
	config.paging = parseInt(GO.settings['max_rows_list']);

	config.bbar = new Ext.PagingToolbar({
		cls: 'go-paging-tb',
		store: config.store,
		pageSize: parseInt(GO.settings['max_rows_list']),
		displayInfo: true,
		displayMsg: t("Total: {2}"),
		emptyMsg: t("No items to display")
	});
	      
	config['layout']='fit';
	config['view']=new Ext.grid.GridView({
		enableRowBody:true,
		showPreview:true,
		autoFill:true,
		forceFit:true,
		emptyText:t("No items to display"),	
		getRowClass : function(record, rowIndex, p, store){
			if(this.showPreview && record.data.description.length){
				p.body = '<div class="go-links-panel-description">'+record.data.description+'</div>';
				return 'x-grid3-row-expanded';
			}
			return 'x-grid3-row-collapsed';
		}
	});

	config['loadMask']={
		msg: t("Loading...")
		};
	config['sm']=new Ext.grid.RowSelectionModel({});
  

	GO.grid.LinksGrid.superclass.constructor.call(this, config);
  
	this.addEvents({
		folderOpened : true, 
		folderDrop : true
	});
  	
}

Ext.extend(GO.grid.LinksGrid, GO.grid.EditorGridPanel, {
	
	write_permission : false,
	
	afterRender : function(){
		
		GO.grid.LinksGrid.superclass.afterRender.call(this);
		
		var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody, 
		{
			ddGroup : 'LinksDD',
			copy:false,
			notifyOver : this.onGridNotifyOver,
			notifyDrop : this.onGridNotifyDrop.createDelegate(this)
		});
		
		this.on('afteredit', function(e) {
			
			GO.request({
				url:"core/updateLink",
				params:{
					model_id1: this.store.baseParams.model_id,
					model_name1: this.store.baseParams.model_name,
					model_id2:e.record.get("model_id"),
					model_name2:e.record.get("model_name"),
					description:e.record.get("link_description")
				},
				success: function(response, options)
				{
					var responseParams = Ext.decode(response.responseText);
					if(!responseParams.success)
					{	
						alert(responseParams.feedback);
					}else
					{
						this.store.commitChanges();
					}				
				},
				scope:this				
			})
			
		}, this);
		
	},
	
	
	onGridNotifyOver : function(dd, e, data){
		var dragData = dd.getDragData(e);
		if(data.grid && this.write_permission)
		{
			var dropRecord = data.grid.store.data.items[dragData.rowIndex];
			if(dropRecord)
			{
				if(dropRecord.data.link_type=='folder')
				{
					return this.dropAllowed;
				}
			}
		}
		return false;
	},

	onGridNotifyDrop : function(dd, e, data)
	{
		if(data.grid && this.write_permission)
		{
			var sm=data.grid.getSelectionModel();
			var rows=sm.getSelections();
			var dragData = dd.getDragData(e);
			
			var dropRecord = data.grid.store.data.items[dragData.rowIndex];
			
			if(dropRecord.data.link_type=='folder')
			{
				this.fireEvent('folderDrop', this, data.selections, dropRecord);
			}
		}else
		{
			return false;
		}
	},
	
	iconRenderer : function(src,cell,record){
		return '<div class=\"go-icon ' + record.data.iconCls +' \"></div>';
	}
});
