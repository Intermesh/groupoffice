/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SearchPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.grid.SearchPanel = function(config){

	config = config || {};	
	
	if(!this.query)
	{
		this.query='';
	}

	if(!config.id){
		config.id=Ext.id();
	}
	
	config.border=false;
	if(!config.noTitle)
		config.title=t("Search")+': "'+Ext.util.Format.htmlEncode(this.query)+'"';
  	
	config.iconCls='go-search-icon-tab';
	config.layout='border';
	
	config.cls='go-white-bg';

	if(!config.filesupport) // Load only the models that can handle files then set to true else false
		config.filesupport = false;
	
	if(!config.for_links) // Load only the models that can handle files then set to true else false
		config.for_links = false;

	if(!config.filter_model_type_ids)
		config.filter_model_type_ids = [];

	this.filterPanel = new GO.LinkTypeFilterPanel({
		for_links: config.for_links,
		filesupport:config.filesupport,
		filter_model_type_ids:config.filter_model_type_ids,
		region:'west',		
		split:true,
		border:true,
		width:160
	});
	
	this.filterPanel.on('change', function(grid, types){		
		
		//Make compatible with the config.filter_model_type_ids
		if(this.filter_model_type_ids.length > 0 && types.length == 0){
			types = this.filter_model_type_ids;
		}
		
		this.searchGrid.store.baseParams.types = Ext.encode(types);
		this.searchGrid.store.load();
		//delete this.searchGrid.store.baseParams.types;
	}, this);
	
	
	this.store = new GO.data.JsonStore({
		//url: BaseHref+'json.php',			
		url: GO.url('search/store'),
		baseParams: {
			filesupport:config.filesupport,
			link_id: this.link_id,
			link_type: this.link_type,
			for_links: config.for_links,
			folder_id: this.folder_id,
			type_filter:'true',
			minimumWritePermission: config.minimumWritePermission || false
		},
		root: 'results',
		totalProperty: 'total',
		id: 'model_name_and_id',
		fields: ['icon','id', 'model_name','name','model_type_id','type','mtime','model_id','module', 'description', 'name_and_type', 'model_name_and_id'],
		remoteSort: true
	});
	
	
	
	this.searchField = new GO.form.SearchField({
		store: this.store,
		width:320
	});
	
	var gridConfig = {
		border:true,
		region:'center',
		bbar:new GO.BlindPagingToolbar({
			cls: 'go-paging-tb',
			store: this.store,
			pageSize: GO.settings.max_rows_list,
			displayInfo: true,
			displayMsg: t("Displaying items {0} - {1} of {2}"),
			emptyMsg: t("No items to display")
		}),
		tbar:[
		t("Search")+': ', ' ',this.searchField,
		'-',{
			iconCls: 'btn-delete',
			text: t("Delete"),
			cls: 'x-btn-text-icon',
			handler: function(){
				this.searchGrid.deleteSelected();
			},
			scope: this
		}],
		store:this.store,
		columns:[{
			id:'name',
			header: t("Name"),
			dataIndex: 'name',
			css: 'white-space:normal;',
			sortable: true,
			renderer:function(v, meta, record){
				return '<div class="go-grid-icon go-model-icon-'+record.data.model_name.replace(/\\/g,"_")+'">'+v+'</div>';
			}
		},{
			header: t("Type"),
			dataIndex: 'type',
			sortable:true,
			width:100
		},{
			header: t("Modified at"),
			dataIndex: 'mtime',
			sortable:true,
			width: dp(140)
		}],
		autoExpandMax:2500,
		autoExpandColumn:'name',
		
		layout:'fit',
		view:new Ext.grid.GridView({
			enableRowBody:true,
			showPreview:true,			
			emptyText:t("No items to display"),	
			applyEmptyText : function() {
					if (this.emptyText && !this.hasRows()) {

							this.emptyText = this.ds.baseParams.query=='' ? t("Please enter a search term") : t("No items to display");

							this.mainBody.update('<div class="x-grid-empty">' + this.emptyText + '</div>');
					}
			},
			getRowClass : function(record, rowIndex, p, store){
				if(this.showPreview && record.data.description.length){
					p.body = '<div class="go-links-panel-description">'+record.data.description+'</div>';
					return 'x-grid3-row-expanded';
				}
				return 'x-grid3-row-collapsed';
			}
		}),
		loadMask:{
			msg: t("Loading...")
			},
		sm:new Ext.grid.RowSelectionModel({singleSelect:config.singleSelect})
	};
	
//	if(config.noOpenLinks)
//	{
		this.store.baseParams.dont_calculate_total=1;
		this.store.baseParams.limit=GO.settings.max_rows_list;
		this.store.baseParams.start=0;
		gridConfig.paging=false;
//	}else
//	{
//		gridConfig.paging=true;
//	}
		
	this.searchGrid = new GO.grid.GridPanel(gridConfig);
	
	this.searchGrid.store.setDefaultSort('mtime', 'desc');
	if(!config.noTitle)
	{
		this.searchGrid.store.on('load', function(){
			this.setTitle(t("Search")+': "'+Ext.util.Format.htmlEncode(this.searchGrid.store.baseParams.query)+'"');
		}, this);
	}

	config.items=[this.filterPanel, this.searchGrid];
 
	if(!config.noOpenLinks)
	{
		this.searchGrid.on('rowdblclick', function(grid, rowClicked, e) {
			this.previewPanel.getLayout().activeItem.editHandler();
			/*var selectionModel = grid.getSelectionModel();
			var record = selectionModel.getSelected();
			
			if(GO.linkHandlers[record.data.link_type])
			{
				GO.linkHandlers[record.data.link_type].call(this, record.data.id, record);
			}else
			{
				GO.errorDialog.show('No handler definded for link type: '+record.data.link_type);
			}*/
		}, this);

		this.linkPreviewPanels["search_pp_0"]=new Ext.Panel({
			bodyStyle:'padding:5px'
		});

		this.previewPanel = new Ext.Panel({
			id: config.id+'_preview',
			region:'east',
			width:420,
			split:true,
			layout:'card',
			items:[this.linkPreviewPanels["search_pp_0"]]
		});

		config.items.push(this.previewPanel);

		this.searchGrid.on("delayedrowselect", this.rowClicked, this);

	}
	

	if(config.noOpenLinks && !config.hideDescription)
	{
		config.items.push({
			region:'south',
			height:34,
			layout:'form',
			cls:'go-form-panel',
			split:true,
			items:this.linkDescriptionField = new GO.form.LinkDescriptionField({
				name:'description',
				fieldLabel:t("Description"),
				anchor:'100%'
			})
		});
	}


		
	GO.grid.SearchPanel.superclass.constructor.call(this, config);
}

Ext.extend(GO.grid.SearchPanel, Ext.Panel, {

	linkPreviewPanels : [],
	
	rowClicked : function(grid, rowClicked, record){

		this.previewPanel.getLayout().setActiveItem(0);

        var jsModelName = record.data.model_name;//.replace(/\\/g,"_");

		var panelId = 'search_pp_'+jsModelName;

		if(record.data.link_type!='folder'){

			if(!GO.linkPreviewPanels[jsModelName]){
				this.linkPreviewPanels["search_pp_0"].body.update('Sorry, the preview of this type not implemented yet.');
			}else
			{
				if(!this.linkPreviewPanels[panelId]){
					this.linkPreviewPanels[panelId] = GO.linkPreviewPanels[jsModelName].call(this, {id:panelId});
					this.previewPanel.add(this.linkPreviewPanels[panelId]);
				}
				
				this.previewPanel.getLayout().setActiveItem(panelId);				
				this.linkPreviewPanels[panelId].load(record.data.model_id);
			}
		}
	},
	
	afterRender : function()
	{
		GO.grid.SearchPanel.superclass.afterRender.call(this);

		if(!this.dontLoadOnRender)
			this.load();
	},
	
	load : function(){
//		if(this.query) {

		if(!this.for_links || !this.searchGrid.store.loaded) {
			this.searchField.setValue(this.query);
			this.searchGrid.store.baseParams.query=this.query;
			this.searchGrid.store.load();
		}
//		}
	},	
	
	iconRenderer : function(src,cell,record){
		return '<div class=\"go-icon ' + record.data.iconCls +' \"></div>';
	},
	
	setFileSupport : function(filesupport){
		this.filterPanel.setFileSupport(filesupport);
		this.store.baseParams.filesupport = filesupport;
	}
});
