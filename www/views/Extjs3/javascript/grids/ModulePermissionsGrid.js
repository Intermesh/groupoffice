/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ModulePermissionsGrid.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */
 
GO.grid.ModulePermissionsGrid = function(config)
{
    if(!config)
    {
        config={};
    }
	
		var missingParams = '';
	
		if (GO.util.empty(config.title))
			missingParams = missingParams + '- title<br />';
		if (GO.util.empty(config.storeUrl))
			missingParams = missingParams + '- storeUrl<br />';
		if (GO.util.empty(config.paramIdType))
			missingParams = missingParams + '- paramIdType<br />';

		if (!GO.util.empty(missingParams))
			Ext.alert('',GO.lang['gridMissingParams']+'<br />');

		
		config.width = '100%';
		config.height = '100%';
		config.loadMask=true;

    var radioPermissionNoneColumn = new GO.grid.RadioColumn({
			horizontal:true,
			value:0,
			header: GO.lang['permissionNone'],
			dataIndex: 'permissionLevel',
			disabled_field: 'disable_none',
			width: 40
    });
		
		var radioPermissionUseColumn = new GO.grid.RadioColumn({
			horizontal:true,
			value:GO.permissionLevels.read,
			header: GO.lang['permissionUse'],
			dataIndex: 'permissionLevel',
			disabled_field: 'disable_use',
			width: 40
    });
		
		var radioPermissionManageColumn = new GO.grid.RadioColumn({
			horizontal:true,
			value:GO.permissionLevels.manage,
			header: GO.lang['permissionManage'],
			dataIndex: 'permissionLevel',
			disabled_field: 'never',
			width: 40
    });
	
    config.store = new GO.data.JsonStore({
			url: config.storeUrl,
			baseParams: {
				groupId : -1
			},
			fields: ['id','name', 'permissionLevel','disable_none','disable_use'],
			root: 'results',
			menuDisabled:true
    });
		
		config.cm = new Ext.grid.ColumnModel({
			defaults:{
				sortable:true
			},
			columns:[
				{
						id:'name',
						header: GO.lang.strName,
						dataIndex: 'name',
						renderer: function(name, cell, reader) {
							return '<div class="go-module-icon-'+reader.data.id+'" style="height:16px;padding-left:22px;background-repeat:no-repeat;">'+name+'</div>';
						}
				},
				radioPermissionNoneColumn,
				radioPermissionUseColumn,
				radioPermissionManageColumn
			]
		});
		
		config.view=new Ext.grid.GridView({
			autoFill: true,
			forceFit: true,
			emptyText: GO.lang['strNoItems']		
		});
		config.sm=new Ext.grid.RowSelectionModel();
		
		config.plugins = [
			radioPermissionNoneColumn,
			radioPermissionUseColumn,
			radioPermissionManageColumn
		];

    GO.grid.ModulePermissionsGrid.superclass.constructor.call(this, config);
		
		this.on('show',function(){
			this.store.load();
		},this);
		
}


Ext.extend(GO.grid.ModulePermissionsGrid, GO.grid.GridPanel,{
	
	setIdParam : function(id) {
		
		
		
		this.store.baseParams['id'] = id;
		this.store.baseParams['paramIdType'] = this.paramIdType;
		this.store.commitChanges();
	},
	
	getPermissionData : function(){
		if(this.store.getModifiedRecords().length){
			this.store.commitChanges();
			return Ext.encode(this.getGridData());			
		}else
		{
			return null;
		}
	}
	
});