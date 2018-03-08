/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ManageCategoryDialog.js 14816 2013-05-21 08:31:20Z mschering $
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
	config.modal=false;
	config.resizable=false;
	config.width=500;
	config.height=300;
	config.closeAction='hide';
	config.title= GO.bookmarks.lang.administrateCategories;
	config.items= this.categoriesGrid; // grid in window
	config.buttons=[{
		text: GO.lang['cmdClose'],
		handler: function(){
			this.fireEvent('close', this);
			this.hide();
		},
		scope:this
	}
	];
		
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