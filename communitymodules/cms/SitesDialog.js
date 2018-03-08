/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SitesDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.SitesDialog = function(config){
	
	
	if(!config)
	{
		config={};
	}
	

	this.sitesGrid = new GO.cms.SitesGrid();	

		
	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=500;
	config.height=400;
	config.closeAction='hide';
	config.title= GO.cms.lang.sites;					
	config.items= this.sitesGrid;
	config.buttons=[{
		text: GO.lang.cmdClose,
		handler: function(){this.hide();},
		scope:this
	}];
	
	GO.cms.SitesDialog.superclass.constructor.call(this, config);	
}
Ext.extend(GO.cms.SitesDialog, Ext.Window,{
	show : function(){
		if(!this.sitesGrid.store.loaded)
			this.sitesGrid.store.load();
			
		GO.cms.SitesDialog.superclass.show.call(this);
	}

});