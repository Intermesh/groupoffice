/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SimpleSelectList.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
 
GO.grid.SimpleSelectList = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	
	if(!config.tpl)
	{
		var tpl = '';
		
		if(config.title)
		{
			tpl += '<b>'+config.title+':</b>';
		}
		
		
		tpl+='<tpl for=".">'+
			'<div id="{dom_id}" class="go-item-wrap">{name}</div>'+
			'</tpl>';
		
		config.tpl = new Ext.XTemplate( 
			tpl		
		);
	}
	
	config.singleSelect=true;
	config.autoHeight=true;
	config.overClass='go-view-over';
	config.itemSelector='div.go-item-wrap';
	config.selectedClass='go-view-selected';
	
	GO.grid.SimpleSelectList.superclass.constructor.call(this, config);	
}

Ext.extend(GO.grid.SimpleSelectList,Ext.DataView, {
   onRender : function(ct, position){
			this.el = ct.createChild({
  	  	tag: 'div', 
       	cls:'go-select-list'
      });      
      GO.grid.SimpleSelectList.superclass.onRender.apply(this, arguments);
   }

});