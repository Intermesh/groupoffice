/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 */
 
GO.comments.ManageCategoriesDialog = function(config){
	
	
	if(!config)
	{
		config={};
	}
		
	this.categoriesGrid = new GO.comments.ManageCategoriesGrid();

	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=true;
	config.width=600;
	config.height=400;
	config.closeAction='hide';
	config.title= GO.comments.lang.manageCategories;					
	config.items= [this.categoriesGrid];
	config.buttons=[{
			text: GO.lang['cmdClose'],
			handler: function(){				
				this.hide();				
			},
			scope:this
		}					
	];
	
	GO.comments.ManageCategoriesDialog.superclass.constructor.call(this, config);

	this.addEvents({'change':true});

	this.on('hide', function(){
		if(this.categoriesGrid.changed)
		{
			this.fireEvent('change');
			this.categoriesGrid.changed=false;
		}
	}, this);
	
}

Ext.extend(GO.comments.ManageCategoriesDialog, GO.Window,{
	show : function() {
		this.categoriesGrid.store.load();
		GO.comments.ManageCategoriesDialog.superclass.show.call(this);
	}
});