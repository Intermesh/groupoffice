/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SavedQueriesGrid.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.advancedquery.SavedQueriesGrid = function(config)
	{
		if(!config)
		{
			config = {};
		}

		config.title = GO.lang.savedQueries;
		//config.paging=true;
		config.border=true;


		config.store = new GO.data.JsonStore({
			url: BaseHref+'json.php',
			baseParams: {
				task: "saved_advanced_queries",
				type:config.type
			},
			root: 'results',
			id: 'id',
			fields: ['id','name','sql'],
			remoteSort: true,
			autoLoad:true
		});

		var cm =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
			header: GO.lang.strName,
			dataIndex: 'name'
		}]
		});

		config.cm=cm;

		config.view=new Ext.grid.GridView({
			autoFill: true,
			forceFit: true,
			emptyText: GO.lang.strNoItems
		}),
		config.sm=new Ext.grid.RowSelectionModel();
		config.loadMask=true;

		config.tbar = [{
			iconCls: 'btn-delete',
			text: GO.lang['cmdDelete'],
			cls: 'x-btn-text-icon',
			handler: function(){
				this.deleteSelected();
			},
			scope: this
		}];

		GO.advancedquery.SavedQueriesGrid.superclass.constructor.call(this, config);

		this.on("rowdblclick",function(grid,row,e) {
			this.ownerCt.searchQueryPanel.queryField.setValue(grid.store.data.items[row].data.sql);
//			GO.advancedquery.advancedSearchWindow.fireEvent('ok', GO.advancedquery.advancedSearchWindow);
//			GO.advancedquery.advancedSearchWindow.hide();
		});
	}

Ext.extend(GO.advancedquery.SavedQueriesGrid, GO.grid.GridPanel, {

});