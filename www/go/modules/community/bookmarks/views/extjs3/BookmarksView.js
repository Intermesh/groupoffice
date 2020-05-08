/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: BookmarksView.js 22345 2018-02-08 15:24:09Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */

go.modules.community.bookmarks.BookmarksView = Ext.extend(Ext.Panel,{
        initComponent: function() {
            this.autoScroll = true;
            Ext.QuickTips.init();
         
            /* 
             * De template die de bookmarks per categorie indeelt (left float)
             * zonder (!index) laat ie geen categorienaam zien als er maar 1 categorie is
             *
             */
            
            this.bookmarkthumbs  = new Ext.XTemplate(
                '<tpl for=".">',
                '<tpl if="this.is_new_category(values.category.id,xindex,xcount)">', 
                    '<div style="clear:both"></div><h3 class="categorie">{values.category.name} </h3>',
                '</tpl>',
                '<div class="thumb-wrap">',
                '<div class="thumb">',
                '<div class="thumb-name" style="background-image:url(' + go.Jmap.downloadUrl('{logo}') + ')"><h4>{name}</h4>{[Ext.util.Format.nl2br(values.description)]}</div>',
                '</div>',	'</div>',
                '</tpl>',
                {
                    // switchen van categorie
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
                );
        
            /*
           * Dataview met bovenstaande template
           */
        
            this.DV = new Ext.DataView({
                store: this.store,
                tpl: this.bookmarkthumbs,
                cls: 'thumbnails',
                itemSelector:'div.thumb-wrap',
                multiSelect: false,
                singleSelect: false,
                trackOver:true
            });
        
        
        
            /*
           *  linkermuisknop, roept globale functie openBookmark aan
             *  link wordt in GO tab of in browsertab getoond (open_extern)
           */
        
            this.DV.on('click',function( DV, index, node, e) {
                var record = this.DV.getRecord(node); // waar hebben we op geklikt?
                go.modules.community.bookmarks.openBookmark(record);
            },this)
        
            /*
             * rechtermuisknop, edit bookmark
             */
            
            this.DV.on('contextmenu',function( DV, index, node, e) {
                e.preventDefault();
        
                if (!this.contextMenu) {
                    this.contextMenu = new go.modules.community.bookmarks.BookmarkContextMenu();
                }
                
                //Verry Important !! to get the record and the XY data of the mouse
                var record = this.DV.getRecord(node);
                this.contextMenu.setRecord(record);
                this.contextMenu.showAt([e.getPageX(),e.getPageY()]);

            }, this);
        
        //     /*
        //   * Mouseover
        //   */
            
        //     this.DV.on('mouseenter',function( DV, index, node, e) {
        //         this.mouseOver=true;
        //     },this);
        
        //     this.DV.on('mouseenter',function( DV, index, node, e) {
        //         }, this, {
        //             delay:600,
        //             buffer:200
        //         })
        
            Ext.apply(this, {
                items: [this.DV]
            });
            go.modules.community.bookmarks.BookmarksView.superclass.initComponent.call(this);

            this.store.on("remove",function() {
                this.DV.tpl.lastcategory = "";
                this.store.load();
                this.DV.refresh();
            },this);

            this.store.on("update",function() {
                this.DV.tpl.lastcategory = "";
                this.store.load();
                this.DV.refresh();
            },this);

            this.on("render", function() {
                this.store.load();
            }, this);
        }
});