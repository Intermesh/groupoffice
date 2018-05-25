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

GO.bookmarks.BookmarksView = function(config){

	config = config || {};
	config.autoScroll=true;


	Ext.QuickTips.init();
 
	/* 
	 * De template die de bookmarks per categorie indeelt (left float)
	 * zonder (!index) laat ie geen categorienaam zien als er maar 1 categorie is
	 *
	 */
	
	this.bookmarkthumbs  = new Ext.XTemplate(
		'<tpl for=".">',
		'<tpl if="this.is_new_category(category_name)">', 
		'<h1 class="categorie">{category_name}</h1>',
		'</tpl>',
		'<div class="thumb-wrap"  >',
		'<div class="thumb">',
		'<div class="thumb-name" style="background-image:url({thumb})"><h4>{name}</h4>{[Ext.util.Format.nl2br(values.description)]}</div>',
		'</div>',	'</div>',	'</tpl>',
		'<div style="clear:both"></div>',
		{
			// switchen van categorie
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
		);

	/*
   * Dataview met bovenstaande template
   */

	this.DV = new Ext.DataView({
		store: config.store,
		tpl: this.bookmarkthumbs,
		cls: 'thumbnails',
		itemSelector:'div.thumb',
		multiSelect: false,
		singleSelect: false,
		trackOver:true
		
	});

	/*
   * Close-button om node in Dataview te verwijderen
   */

	//	if (!this.closeButton)
	//	{
	//		this.closeButton = new GO.bookmarks.CloseButton({
	//			autoEl: {
	//				tag: 'img',
	//				src: 'modules/bookmarks/themes/Default/images/close.gif',
	//				cls: 'closebutton'
	//			}
	//		});
	//	}
	//	this.closeButton.hide();
	//	this.closeButton.on('remove_bookmark', GO.bookmarks.removeBookmark, this);


	/*
   *  linkermuisknop, roept globale functie openBookmark aan
	 *  link wordt in GO tab of in browsertab getoond (open_extern)
   */

	this.DV.on('click',function( DV, index, node, e) {
		var record = this.DV.getRecord(node); // waar hebben we op geklikt?
		GO.bookmarks.openBookmark(record);
	},this)

	/*
	 * rechtermuisknop, edit bookmark
	 */

	
	this.DV.on('contextmenu',function( DV, index, node, e) {
		e.preventDefault();

		var XY = new Array(e.getPageX(),e.getPageY());

		if (!GO.bookmarks.bookmarkContextMenu) {
			GO.bookmarks.bookmarkContextMenu = new GO.bookmarks.BookmarkContextMenu();
		}
		
		// Verry Important !! to get the record and the XY data of the mouse
		var record = this.DV.getRecord(node);
		GO.bookmarks.bookmarkContextMenu.setRecord(record);
		GO.bookmarks.bookmarkContextMenu.showAt(XY);
		
				

	//	if (this.DV.getRecord(node).data.permissionLevel>=GO.permissionLevels.write) // users kunnen niet rechts klikken op public bookmarks
	//{
	//	GO.bookmarks.showBookmarksDialog({
	//		record:record,
	//		edit:1
	///);
	//}
 
	}, this);

	/*
  * Mouseover
  */
	
	this.DV.on('mouseenter',function( DV, index, node, e) {
		this.mouseOver=true;
	},this);

	this.DV.on('mouseenter',function( DV, index, node, e) {
		
		//		if((this.mouseOver)&&(this.DV.getRecord(node)!=undefined)){
		//			if (this.DV.getRecord(node).data.permissionLevel>GO.permissionLevels.write) // users zien geen kruisje bij een public bookmark
		//			{
		//				this.closeButton.show();
		//				this.closeButton.record=this.DV.getRecord(node);
		//				this.closeButton.getEl().alignTo(node, 'tr', [-21,6]);
		//			}
		//		}
		}, this, {
			delay:600,
			buffer:200
		})

	//	this.DV.on('mouseleave',function( DV, index, node, e) {
	//		this.mouseOver=false;
	//		this.closeButton.hideIfNotOver.defer(100, this.closeButton);
	//	}, this);

	Ext.apply(config, {
		listeners:{
			render:function(){
				config.store.load();
			}
		},
		items: [this.DV]
	});

	GO.bookmarks.BookmarksView.superclass.constructor.call(this, config);
}

Ext.extend(GO.bookmarks.BookmarksView, Ext.Panel,{

});
