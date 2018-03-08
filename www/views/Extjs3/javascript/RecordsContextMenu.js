/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: RecordsContextMenu.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.menu.RecordsContextMenu=Ext.extend(Ext.menu.Menu,{
	
	records : [],	
	
	/**
	 * 
	 * @param {} xy
	 * @param {} records pass Ext.Records. If there are more then one records 
	 * 	a menu item will be disabled if it doesn't have the multiple property set.
	 */
	showAt : function(xy, records)
	{ 	
		this.records = records;
		
		var multiple = this.records.length>1;

		for(var i=0;i<this.items.getCount();i++)
		{			
			var item = this.items.get(i);
			item.setDisabled(!item.multiple && multiple);
		}
		
		GO.menu.RecordsContextMenu.superclass.showAt.call(this, xy);
	}	
});