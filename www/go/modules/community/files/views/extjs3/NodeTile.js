/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
go.modules.community.files.NodeTile = Ext.extend(Ext.DataView, {
	autoHeight:true,
	autoScroll:true,
	multiSelect: true,
	cls: 'x-view-tiles',
	overClass:'x-view-over',
	selectedClass:'x-view-selected',
	itemSelector:'div.tile',
	tpl: new Ext.XTemplate('<tpl for=".">',
		'<div class="tile">',
			'<tpl if="values.blobId"><div class="fs-thumb" style="background-image:url({[go.Jmap.downloadUrl(values.blobId)]});"></div></tpl>',
			'<tpl if="!values.blobId"><div class="fs-thumb folder"></div></tpl>',
			'<span class="x-editable">{name}</span>'+
		'</div>',				
	'</tpl>'),
	initComponent : function(){

     this.addEvents({drop:true});
        
     go.modules.community.files.NodeTile.superclass.initComponent.call(this);
	},
	
	onNotifyDrop : function(dd, e, data) {
		var dragData = dd.getDragData(e);

		if(dd.dragData)
		{	
			var dropRecord = this.view.store.getAt(dragData.ddel.viewIndex);
					
			if(dropRecord && dropRecord.data.extension=='folder')
			{
				this.fireEvent('drop', dropRecord.data.id, data.selections);
				return true;
			}
		}
	}
	
});
