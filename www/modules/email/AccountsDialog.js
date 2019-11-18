/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AccountsDialog.js 22112 2018-01-12 07:59:41Z mschering $
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
	
	config.width=700;
	config.height=600;
	config.closeAction='hide';
	config.title=t("Accounts", "email");	

	config.items=this.accountsGrid;

	config.listeners={
		render:function(){
			this.accountsGrid.store.load();
		},
		scope:this
	}

	GO.email.AccountsDialog.superclass.constructor.call(this, config);	

}
Ext.extend(GO.email.AccountsDialog, go.Window,{
	
	

});
