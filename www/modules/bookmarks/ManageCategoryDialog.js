/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ManageCategoryDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */


GO.bookmarks.ManageCategoriesDialog = function(config){
		
	if(!config)
	{
		config={};
	}
		
	this.categoriesGrid = new GO.bookmarks.ManageCategoriesGrid();

	config.maximizable=true;
	config.layout='fit';
	config.resizable=false;
	config.width=600;
	config.height=400;
	config.closeAction='hide';
	config.title= t("Administrate categories", "bookmarks");
	config.items= this.categoriesGrid; // grid in window
		
	//constructor
	GO.bookmarks.ManageCategoriesDialog.superclass.constructor.call(this, config);

	this.on('hide', function(){
		if(this.categoriesGrid.changed)
		{
			this.fireEvent('change');
			this.categoriesGrid.changed=false;

			if(GO.bookmarks.comboCategoriesStore.loaded)
				GO.bookmarks.comboCategoriesStore.reload();
		}
	}, this);
	
	this.addEvents({
		'change':true
	});
}

Ext.extend(GO.bookmarks.ManageCategoriesDialog, Ext.Window,{

	});
