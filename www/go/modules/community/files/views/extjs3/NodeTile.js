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
	browser: null, // passed from centerpanel
	tpl: new Ext.XTemplate('<tpl for=".">',
		'<div class="tile x-unselectable<tpl if="values.isDirectory"> dir</tpl>">',
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
	
	afterRender: function() {
		go.modules.community.files.NodeTile.superclass.afterRender.call(this);
		
		var view = this;
		view.dragZone = new Ext.dd.DragZone(view.getEl(), {
			ddGroup: 'files-center-dd',
			getDragData: function(e) {
				var sourceEl = e.getTarget(view.itemSelector, 10);
				if(sourceEl) {
					var d = sourceEl.cloneNode(true);
					d.id = Ext.id();
					return view.dragData = {
						sourceEl: sourceEl,
						repairXY: Ext.fly(sourceEl).getXY(),
						ddel: d,
						node: view.getRecord(sourceEl).data
				  }
				}
			},
			getRepairXY: function() {
				return this.dragData.repairXY;
		  }
		});

		view.dropZone = new Ext.dd.DropZone(view.getEl(), {
			ddGroup: 'files-center-dd',
			copy:false,
			getTargetFromEvent: function(e) {
				return e.getTarget('.tile.dir');
			},
			onNodeOver : this.onNotifyOver,
			onNodeDrop: this.onNotifyDrop.createDelegate(this)
		});
		
		console.log(view.dragZone);
	},
	
	onNotifyDrop : function(target, dd, e, data){
		var records = dd.dragData.selections,
			i = this.indexOf(target);
			droppedAt = this.store.getAt(i);
			console.log(droppedAt);
		if(!droppedAt) {
			return false;
		}
		if(droppedAt.data.isDirectory) {
			this.browser.receive([data.node], droppedAt.data.id, 'move');
			//Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
		}
		return true
	},
	
	onNotifyOver : function(target, dd, e, data){
		return Ext.dd.DropZone.prototype.dropAllowed;
	}
	
});
