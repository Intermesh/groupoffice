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
go.modules.community.files.NodeTile = Ext.extend(go.grid.TilePanel, {
	tpl: new Ext.XTemplate('<tpl for=".">',
		'<div class="tile">',
			'<div class="filetype {[this.icon(values)]}"></div>',
			'<div class="text">{name}</div>'+
			'<tpl if="values.status==\'queued\'"><progress max="100" value="{progress}"></progress></tpl>'+
		'</div>',
	'</tpl>',{
		icon: function(values) {
			//todo: find thumb in metadata
			return go.util.contentTypeClass(values.contentType, values.name);
		}
	}),
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
