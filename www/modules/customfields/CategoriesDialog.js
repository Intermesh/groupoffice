/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: CategoriesDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

 
GO.customfields.CategoriesDialog = function(config){
	
	
	if(!config)
	{
		config={};
	}
		
	this.categoriesPanel = new GO.customfields.CategoriesPanel({
	});
	

	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=500;
	config.height=300;
	config.closeAction='hide';
	config.title= GO.customfields.lang.categories;					
	config.items= this.categoriesPanel;
	config.buttons=[{
			text: GO.lang['cmdClose'],
			handler: function(){
				if(this.categoriesPanel.changed)
				{
					this.fireEvent('change');
					this.categoriesPanel.changed=false;
				}
				this.hide();
				
			},
			scope:this
		}					
	];
	

	
	GO.customfields.CategoriesDialog.superclass.constructor.call(this, config);
	
	this.addEvents({'change':true});
	
}

Ext.extend(GO.customfields.CategoriesDialog, Ext.Window,{
	
	
	show : function (link_type) {		
		if(!this.rendered)
			this.render(Ext.getBody());
			
		if(this.categoriesPanel.store.baseParams['link_type']=link_type)
		{
			this.setLinkType(link_type);
			this.categoriesPanel.store.load();
		}
		GO.customfields.FieldDialog.superclass.show.call(this);
	},
	
	setLinkType : function(link_type)
	{
		this.categoriesPanel.store.baseParams['link_type']=link_type;
		
	}	
});