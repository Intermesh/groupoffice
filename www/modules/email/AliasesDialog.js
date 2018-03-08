/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AliasesDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.email.AliasesDialog = function(config){	
	if(!config)
	{
		config={};
	}
	
	this.aliasesGrid = new GO.email.AliasesGrid();
		
	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=500;
	config.height=400;
	config.closeAction='hide';
	config.title= GO.email.lang.aliases;					
	config.items=this.aliasesGrid;
	config.buttons=[{
		text: GO.lang.cmdClose,
		handler: function(){this.hide();},
		scope:this
	}];
	
	GO.email.AliasesDialog.superclass.constructor.call(this, config);	
}
Ext.extend(GO.email.AliasesDialog, Ext.Window,{
	show : function(account_id){		
		
		this.aliasesGrid.setAccountId(account_id);
		
		GO.email.AliasesDialog.superclass.show.call(this);
	}

});