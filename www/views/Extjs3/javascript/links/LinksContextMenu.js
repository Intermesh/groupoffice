/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LinksContextMenu.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.LinksContextMenu = function(config)
{
	if(!config)
	{
		config = {};
	}
	config['shadow']='frame';
	config['minWidth']=180;
					

//	this.deleteButton = new Ext.menu.Item({
//					iconCls: 'btn-delete',
//					text: t("Delete"),
//					cls: 'x-btn-text-icon',
//					handler: function(){
//						this.fireEvent('delete', this, this.selected);
//					},
//					scope: this
//				});
				
	this.unlinkButton = new Ext.menu.Item({
					iconCls: 'btn-unlink',
					text: t("Unlink"),
					cls: 'x-btn-text-icon',
					handler: function(){
						this.fireEvent('unlink', this, this.selected);
					},
					scope: this
				});
				
	this.propertiesButton = new Ext.menu.Item({
					iconCls: 'btn-properties',
					text: t("Properties"), 
					handler: function(){
							this.fireEvent('properties', this, this.selected);
					},
					scope:this					
				});
	
	
	config['items']=[
				this.propertiesButton,
				//this.deleteButton,
				this.unlinkButton
				];
				
	GO.LinksContextMenu.superclass.constructor.call(this, config);
	
	this.addEvents({
		
		'properties' : true,
		'unlink' : true,
		'delete' : true		
	});
	
}

Ext.extend(GO.LinksContextMenu, Ext.menu.Menu,{
	/*tree or grid */
	clickedAt : 'grid',
	
	showAt : function(xy, selected)
	{ 	
		this.link_type = link_type;
		this.selected = selected;
		
		if(this.selected.length>1)
		{
			link_type='mixed';
		}else
		{
			var colonPos = selected[0].indexOf(':');
			var link_type = selected[0].substr(0, colonPos);
		}
 	
		switch(link_type)
	 	{
	 		case 'folder':
	 			this.propertiesButton.show();
	 			//this.deleteButton.show();
	 			this.unlinkButton.hide();
	 		break;
	 		
	 		default:
	 			this.propertiesButton.hide();
	 			//this.deleteButton.show();
	 			this.unlinkButton.show();
	 		break;	 		
	 	}	
 	
		GO.LinksContextMenu.superclass.showAt.call(this, xy);
	}
	
});
