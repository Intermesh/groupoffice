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
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.notes.ManageCategoriesDialog = function(config){
	
	
	if(!config)
	{
		config={};
	}
		
	this.categoriesGrid = new GO.notes.ManageCategoriesGrid();

	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=true;
	config.width=600;
	config.height=400;
	config.closeAction='hide';
	config.title= GO.notes.lang.manageCategories;					
	config.items= this.categoriesGrid;
	config.buttons=[{
			text: GO.lang['cmdClose'],
			handler: function(){				
				this.hide();				
			},
			scope:this
		}					
	];
	
	GO.notes.ManageCategoriesDialog.superclass.constructor.call(this, config);

	this.on('hide', function(){
		if(this.categoriesGrid.changed)
		{
			this.fireEvent('change');
			this.categoriesGrid.changed=false;
		}
	}, this);
	
	this.addEvents({'change':true});
}

Ext.extend(GO.notes.ManageCategoriesDialog, GO.Window,{

});