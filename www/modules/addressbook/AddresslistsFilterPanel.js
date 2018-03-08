/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: AddresslistsFilterPanel.js 14816 2013-05-21 08:31:20Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.AddresslistsFilterPanel = function(config)
{
	if(!config)
	{
		config = {};
	}
	
	config.autoScroll=true;
	config.title= GO.addressbook.lang.filterMailings;
	
	var checkColumn = new GO.grid.CheckColumn({
		header: '&nbsp;',
		dataIndex: 'checked',
		width: 30
	});
	
	
	Ext.apply(config,{
		cls:'go-grid3-hide-headers',
		disableSelection:true,
		border:true,
		loadMask:true,
		store: GO.addressbook.readableAddresslistsStore,		
		columns: [
				checkColumn,
				{
					header: GO.lang.strName, 
					dataIndex: 'name',
					id:'name'
				}				
			],
		plugins: [checkColumn],
		autoExpandColumn:'name',
		viewConfig: {emptyText:GO.addressbook.lang.noAddressLists}
	});	

	config.layout= 'fit';

	
	var applyButton = new Ext.Button({
		text:GO.lang.cmdApply,
		handler:function(){			
			var mailings = [];
			
			for (var i = 0; i < GO.addressbook.readableAddresslistsStore.data.items.length;  i++)
			{
				var checked = GO.addressbook.readableAddresslistsStore.data.items[i].get('checked');
				if(checked=="1")
				{
					mailings.push(GO.addressbook.readableAddresslistsStore.data.items[i].get('id'));	
				}				
			}
			
			this.fireEvent('change', this, mailings);
			
			GO.addressbook.readableAddresslistsStore.commitChanges();			
		},
		scope: this
	});    
	
	var resetButton = new Ext.Button({
		text:GO.lang.cmdReset,
		handler:function(){			
			
			var mailings = [];
			for (var i = 0; i < GO.addressbook.readableAddresslistsStore.data.items.length;  i++)
			{
				var checked = GO.addressbook.readableAddresslistsStore.data.items[i].set('checked', '0');								
			}
						
			this.fireEvent('change', this, mailings);			
			GO.addressbook.readableAddresslistsStore.commitChanges();		
		},
		scope: this
	});    
	
	
	config.buttons=[applyButton,resetButton];
	config.buttonAlign='left';

	GO.addressbook.AddresslistsFilterPanel.superclass.constructor.call(this, config);	
	this.addEvents({change : true});
}

Ext.extend(GO.addressbook.AddresslistsFilterPanel, GO.grid.GridPanel,{
	afterRender : function(){
		
		
		
		GO.addressbook.readableAddresslistsStore.load();
		
		GO.addressbook.AddresslistsFilterPanel.superclass.afterRender.call(this);
	}
});