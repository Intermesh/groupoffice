/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: AddresslistsFilterPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.AddresslistsFilterPanel = function(config)
{
	if(!config)
	{
		config = {};
	}
	
	config.autoScroll=true;
	config.title= t("Address list filter", "addressbook");
	
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
					header: t("Name"), 
					dataIndex: 'name',
					id:'name'
				}				
			],
		plugins: [checkColumn],
		autoExpandColumn:'name',
		viewConfig: {emptyText:t("No address lists", "addressbook")}
	});	

	config.layout= 'fit';

	
	var applyButton = new Ext.Button({
		text:t("Apply"),
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
		text:t("Reset"),
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
