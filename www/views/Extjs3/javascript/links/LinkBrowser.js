/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LinkBrowser.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.LinkBrowser = function(config){
	
	Ext.apply(this, config);


	this.linksPanel = new GO.grid.LinksPanel({
		noFilterSave:'true'
	});

	if(!this.id)
		this.id='go-link-browser';
	
	GO.LinkBrowser.superclass.constructor.call(this, {
   	layout: 'fit',
		modal:false,
		minWidth:300,
		minHeight:300,
		height:500,
		width:950,
		border:false,
		plain:true,
		maximizable:true,
		collapsible:true,
		closeAction:'hide',
		title:GO.lang.cmdBrowseLinks,
		items: this.linksPanel
    });
    
   this.addEvents({'link' : true});
};

Ext.extend(GO.LinkBrowser, GO.Window, {
	
	show : function(config)
	{
		this.linksPanel.loadLinks(config.model_id, config.model_name);
		
		if(config.folder_id)
		{
			this.linksPanel.setFolder(config.folder_id);
		}
		
		GO.LinkBrowser.superclass.show.call(this);
	}
});