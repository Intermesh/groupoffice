/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: DevicesGrid.js 16399 2013-07-23 13:55:30Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.bookmarks.BookmarkColumnView = Ext.extend(Ext.DataView,{

	initComponent : function(){
		
		Ext.applyIf(this,{
			autoScroll: true,
			store: GO.bookmarks.groupingStore,
			tpl: new Ext.XTemplate(
				'<tpl for=".">',
					'<tpl if="this.is_new_category(values.category_name)">', // Show category name column (Only when category changes)
						'<tpl if="xindex &gt; 1"><br/><br/></div></tpl>', // Close previous category column (Don't do this the first time)
						'<div class="bookmark-column">',
							'<span class="title">{category_name}</span>',
					'</tpl>',
						
					'<span class="link" id="{id}" href="{content}" target="_blank">',
						'<span class="thumb" style="background-image:url({thumb})">{name}</span>',
					'</span>',
        '</tpl>',
				'<br/><br/></div>',
				{
					is_new_category : function(category_name){

						if(!this.lastcategory || category_name!=this.lastcategory){
							this.lastcategory=category_name;
							return true;
						}else
						{
							return false;
						}
					}
					
				}
			),
			autoHeight:true,
      multiSelect: false,
			singleSelect:false,
			simpleSelect:false,
			trackOver:true,
			overClass:'x-view-over',
			itemSelector:'.link',
			emptyText: GO.bookmarks.lang.noEmployeesToDisplay
			,
			listeners: {
				contextmenu: function(dv, index, node, e){
					e.preventDefault();

					var XY = new Array(e.getPageX(),e.getPageY());

					if (!GO.bookmarks.bookmarkContextMenu) {
						GO.bookmarks.bookmarkContextMenu = new GO.bookmarks.BookmarkContextMenu();
					}

					// Very Important !! to get the record and the XY data of the mouse
					var record = this.getRecord(node);
					GO.bookmarks.bookmarkContextMenu.setRecord(record);
					GO.bookmarks.bookmarkContextMenu.showAt(XY);
				},
				render:function(){
					this.store.load();
					this.refresh();
				},
				click: function( DdvV, index, node, e) {
					var record = this.getRecord(node); // waar hebben we op geklikt?
					GO.bookmarks.openBookmark(record);
				}
			}
		});
		
		GO.bookmarks.BookmarkColumnView .superclass.initComponent.call(this);
	}
});