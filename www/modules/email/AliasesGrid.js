/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AliasesGrid.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.email.AliasesGrid = function(config){
	if(!config)
	{
		config = {};
	}
	config.layout='fit';
	config.autoScroll=true;
	config.store = new GO.data.JsonStore({
	    url: GO.url("email/alias/store"),
	    fields: ['id','account_id','name','email','signature'],
	    remoteSort: true
	});

	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
			header: GO.lang.strName, 
			dataIndex: 'name'
		},{
			header: GO.email.lang.email, 
			dataIndex: 'email'
		}]
	});
	
	config.cm=columnModel;
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: GO.lang['strNoItems']		
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	this.aliasDialog = new GO.email.AliasDialog();
		this.aliasDialog.on('save', function(){   
			this.store.reload();	  
			if(GO.email.aliasesStore.loaded)
			{
				GO.email.aliasesStore.reload();
			}
		}, this);
	config.tbar=[{
			iconCls: 'btn-add',							
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			handler: function(){
				this.aliasDialog.formPanel.baseParams.account_id=this.account_id;
				this.aliasDialog.show();
			},
			scope: this
		},{
			iconCls: 'btn-delete',
			text: GO.lang['cmdDelete'],
			cls: 'x-btn-text-icon',
			handler: function(){
				this.deleteSelected({
					callback: function(){										 
						if(GO.email.aliasesStore.loaded)
						{
							GO.email.aliasesStore.reload();
						}
						this.fireEvent('delete', this);
					},
					scope: this
				});
			},
			scope: this
		}];
	GO.email.AliasesGrid.superclass.constructor.call(this, config);
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		this.aliasDialog.show(record.data.id);
		}, this);
};
Ext.extend(GO.email.AliasesGrid, GO.grid.GridPanel,{
	setAccountId : function(account_id)
	{
		if(this.store.baseParams.account_id != account_id)
		{
			this.store.baseParams.account_id=account_id;
			if(account_id==0)
			{
				this.store.removeAll();
			}else
			{
				this.store.load();
			}
		}
		
		this.setDisabled(account_id==0);		
		this.account_id=account_id;
	}
});
