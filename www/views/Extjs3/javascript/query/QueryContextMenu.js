/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

GO.query.QueryContextMenu = function(config)
{
	if(!config)
	{
		config = {};
	}
	config['shadow']='frame';
	config['minWidth']=180;
	
	config.items=[this.deleteBtn = new Ext.menu.Item({
		iconCls: 'btn-delete',
		text: GO.lang['cmdDelete'],
		cls: 'x-btn-text-icon',
		handler: function(){						
			this.callingGrid.deleteSelected();
		},
		scope: this
	}), this.permissionsButton = new Ext.menu.Item({
		iconCls: 'btn-edit',
		text: GO.lang['cmdEdit'],
		cls: 'x-btn-text-icon',
		handler: function(button,item) {
			var record = this.callingGrid.getSelectionModel().getSelected();
			this.callingGrid.showSavedQueryDialog(record.data.id);
		},
		scope: this
	})];
					
	GO.query.QueryContextMenu.superclass.constructor.call(this, config);	
}

Ext.extend(GO.query.QueryContextMenu, Ext.menu.Menu,{
	attachment : false,
	
	callingGrid : false,
	
	showAt : function(xy, attachment)
	{ 	
		this.attachment = attachment;
		GO.query.QueryContextMenu.superclass.showAt.call(this, xy);
	}
});