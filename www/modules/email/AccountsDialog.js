/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AccountsDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.email.AccountsDialog = function(config){
	
	
	if(!config)
	{
		config={};
	}
	
	this.accountsGrid = new GO.email.AccountsGrid({
		region:'center'
	});
	
	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=true;
	
	config.width=600;
	config.height=400;
	config.closeAction='hide';
	config.title=GO.email.lang.accounts;	


	
	config.buttons=[{
		text: GO.lang.cmdClose,
		handler: function(){this.hide();},
		scope: this
	}];

	config.items=this.accountsGrid;

	

	config.listeners={
		render:function(){
			this.accountsGrid.store.load();
		},
		scope:this
	}

	GO.email.AccountsDialog.superclass.constructor.call(this, config);	

}
Ext.extend(GO.email.AccountsDialog, Ext.Window,{
	
	

});