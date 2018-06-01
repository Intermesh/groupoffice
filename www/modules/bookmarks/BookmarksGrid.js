/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: BookmarksGrid.js 22333 2018-02-06 15:42:39Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */


GO.bookmarks.BookmarksGrid = function(config){

	config = config || {};
	config.hideHeaders=true;
	config.hideMode='offsets'; // hack
	config.autoScroll=true;

	// de kolommen in de Grid
	config.cm = new Ext.grid.ColumnModel([{
		id: 'name',
		header: t("Title", "bookmarks"),
		dataIndex: 'name',
		width: 175
	}, {

		header: "URL",
		dataIndex: 'content',
		width: 250,
		renderer:  this.urlrenderer // om url in cel klikbaar te maken
	}, {
		header: t("Category", "bookmarks"),
		dataIndex: 'category_name',
		width: 125
	}, {
		header: t("Website description.", "bookmarks"),
		dataIndex: 'description',
		id:'description'
	}
	]);

	config.sm= new Ext.grid.RowSelectionModel({
		
	});
	
	config.autoExpandColumn='description';


	// GroupingView 
	config.view=  new Ext.grid.GroupingView({
		scrollOffset: 2,
		hideGroupedColumn:true,
		groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "'+t("items")+'" : "'+t("item")+'"]})',
		showGroupName:false,
		forceFit:false

	});


	Ext.apply(config, {
		listeners:{
			render:function(){
				config.store.load();
			}
		}
	});


	GO.bookmarks.BookmarksGrid.superclass.constructor.call(this, config);

	

	// rechtermuisknop, edit bookmark ** show context menu with edit delete 
	//this.on('rowcontextmenu', function(grid, rowIndex){
		//var rec = grid.getStore().getAt(rowIndex).data;
		//GO.bookmarks.showBookmarksDialog({
		//	record:rec,
		//	edit:1
		//});
		
		
	//},this)


	// dubbelklik, edit bookmark
	this.on('rowdblclick', function(grid, rowIndex){
		var rec = grid.getStore().getAt(rowIndex).data;
		GO.bookmarks.showBookmarksDialog({
			record:rec,
			edit:1
		});
	},this)


}


Ext.extend(GO.bookmarks.BookmarksGrid, Ext.grid.GridPanel,{

	// Cel met url roept functie in MainPanel aan hoe
	// een bookmark geopend moet worden, intern of extern (javascript:GO.bookmarks.openBookmark)

	urlrenderer: function(value, css, record, row, column,ds) {
                return ('<a class="normal-link" onclick="GO.bookmarks.openBookmark(\''+ record +'\');" target="">' + value + "</a>") ;
	}


})
