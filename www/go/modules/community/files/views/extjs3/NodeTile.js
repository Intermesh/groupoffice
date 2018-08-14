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
		'<div class="tile x-unselectable">',
			'<tpl if="values.metaData && values.metaData.thumbnail">\
				<div class="thumb" style="background-image:url({[go.Jmap.downloadUrl(values.metaData.thumbnail)]})"></div>\
			</tpl>',
			'<tpl if="!values.metaData || !values.metaData.thumbnail">{[this.icon(values)]}</tpl>',
			'<div class="text">{name}\
				<tpl if="values.bookmarked"> <i class="icon small">bookmark</i></tpl>\
				<tpl if="values.internalShared || values.externalShared"> <i class="icon small">group</i></tpl>\
			</div>'+
			'<tpl if="values.status==\'queued\'"><progress max="100" value="{progress}"></progress></tpl>'+
		'</div>',
	'</tpl>',{
		icon: function(values) {
			if(values.contentType === 'image/svg+xml') {
				return '<div class="thumb" style="background-image:url('+go.Jmap.downloadUrl(values.blobId)+')"></div>';
			}
			return '<div class="filetype '+ go.util.contentTypeClass(values.contentType, values.name)+ '"></div>';
		}
	}),
	emptyText: '<div class="empty-state"><i class="icon">cloud_upload</i><br>'+
		 '<h3>'+t('Drop files here')+'</h3>'+
		 '<small>'+t('Or use the \'+\' button')+'</small></div>',
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
