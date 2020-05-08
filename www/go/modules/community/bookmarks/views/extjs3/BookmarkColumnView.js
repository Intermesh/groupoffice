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

go.modules.community.bookmarks.BookmarkColumnView = Ext.extend(Ext.DataView,{
	

	initComponent : function(){
		Ext.applyIf(this,{
			autoScroll: true,
			store: go.modules.community.bookmarks.groupingStore,
			tpl: new Ext.XTemplate(
				'<tpl for=".">',
					'<tpl if="this.is_new_category(values.category.id,xindex,xcount)">', // Show category name column (Only when category changes)
						'<tpl if="xindex &gt; 1"><br/><br/></div></tpl>', // Close previous category column (Don't do this the first time)
						'<div class="bookmark-column">',
						'<tpl for="category">',
								'<h3 class="categorie">{name}</h3>',
						'</tpl>',
					'</tpl>',
						
					'<span class="link" id="{id}" href="{content}" target="_blank">',
						'<span class="thumb" style="background-image:url(' + go.Jmap.downloadUrl('{logo}') + ');white-space:nowrap; overflow:hidden;text-overflow: ellipsis;display:block;">{name}</span>',
					'</span>',
        		'</tpl>',
				'<br/><br/></div>',
				{
                    is_new_category: function(id,index,count){
                        var result = false;
                        if(!this.lastid || id != this.lastid){
                            this.lastid = id;
                            result = true;
                        }
                        // check if it is the last id if it is reset it
                        if(index == count) {
                            this.lastid = null;
                        }
                        return result;
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
			emptyText: t("No items to display")
			,
			listeners: {
				contextmenu: function(dv, index, node, e){
					e.preventDefault();

					var XY = new Array(e.getPageX(),e.getPageY());

					if (!this.contextMenu) {
						this.contextMenu = new go.modules.community.bookmarks.BookmarkContextMenu();
					}
					
					// Very Important !! to get the record and the XY data of the mouse
					var record = this.getRecord(node);
					this.contextMenu.setRecord(record);
					this.contextMenu.showAt(XY);
				},
				render:function(){
					this.store.load();
					this.refresh();
				},
				click: function( DdvV, index, node, e) {
					var record = this.getRecord(node); // waar hebben we op geklikt?
					go.modules.community.bookmarks.openBookmark(record);
				}
			}
		});
		
		go.modules.community.bookmarks.BookmarkColumnView.superclass.initComponent.call(this);
	}
});
