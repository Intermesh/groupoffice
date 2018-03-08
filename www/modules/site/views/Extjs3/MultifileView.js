/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MultifileView.js 10767 2012-06-12 13:31:03Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.site.MultifileView = Ext.extend(Ext.DataView,{
	
	maxSize:0,
	
	initComponent : function(){
				
		var tpl = new Ext.XTemplate('<tpl for=".">',
        '<div class="fs-thumb-wrap" id="{name}">',
		    '<div class="fs-thumb" style="background-image:url({thumb_url});">',
				'</div>',
		    '<span class="x-editable">{shortName}</span></div>',				
        '</tpl>',
        '<div class="x-clear"></div>');	
	
		Ext.apply(this,{
			store: GO.site.multifileStore,
			tpl: tpl,
//			autoHeight:true,
			autoScroll:true,
			multiSelect: true,
			overClass:'fs-view-over',
			selectedClass:'fs-view-selected',
			itemSelector:'div.fs-thumb-wrap',
			emptyText: 'No images to display',
			plugins: new net.drasill.plugins.SortableDataView({
				listeners: {
					drop : function(origIdx,lastIdx, record){

						//save sort order							
						var records = [];
						for (var i = 0; i < this.store.data.items.length;  i++){
							records.push({
								file_id: this.store.data.items[i].get('id'), 
								sort_index : i,
								model_id : this.store.data.items[i].get('model_id'),
								field_id : this.store.data.items[i].get('field_id')
							});
						}
						
						GO.request({
							url:'site/multifile/saveSort',
							params:{
								sort:Ext.encode(records)
							}
						});
					},
					scope:this
				}
			})
		});
		
		GO.site.MultifileView.superclass.initComponent.call(this);
		
		this.addEvents({attachmentschanged:true});
		
		this.on('contextmenu',this.onAttachmentContextMenu, this);
		this.on('dblclick',this.onAttachmentDblClick, this);
		this.on('render',function(){
			this.getEl().tabIndex=0;
			var map = new Ext.KeyMap(this.getEl(),{
				key: Ext.EventObject.DELETE,
				fn: function(key, e){
					this.removeSelectedAttachments();
				},
				scope:this
			});
		}, this);		
	},

	prepareData: function(data){
		data.shortName = Ext.util.Format.ellipsis(data.name, 20);
		return data;
	},
	
	maxSizeExceeded : function(){
		return this.maxSize && this.maxSize<this.getTotalSize();
	},
	
	getMaxSizeExceededErrorMsg : function(){
		return GO.lang.maxAttachmentsSizeExceeded
			.replace('{max}',Ext.util.Format.fileSize(this.maxSize))
			.replace('{total}',Ext.util.Format.fileSize(this.getTotalSize()));
	},
	
	getTotalSize : function(){
		var records = this.store.getRange();
		var totalSize = 0;
		for(var i=0;i<records.length;i++){
			totalSize+=records[i].get('size');
		}
		
		return totalSize;
	},
	
	afterUpload : function(loadParams){
		var params = {add:true, params:loadParams};
		this.store.load(params);
	},
	removeSelectedAttachments : function(){
		var records = this.getSelectedRecords();
		for(var i=0;i<records.length;i++)
		{
			this.store.remove(records[i]);
		}
		this.setVisible(this.store.data.length);
		this.fireEvent('attachmentschanged', this);
		
	},
	onAttachmentDblClick : function(view, index, node, e){
		
//		var record = this.store.getAt(index);	
//		if(record.data.from_file_storage){
//			window.open(GO.url("files/file/download",{path:record.data.tmp_file}));
//		}else
//		{
//			window.open(GO.url("core/downloadTempFile",{path:record.data.tmp_file}));
//		}		
	},
	
	onAttachmentContextMenu : function(dv, index, node, e)
	{
//		if(!this.menu)
//		{
//			this.menu = new Ext.menu.Menu({
//				items: [
//				{
//					iconCls:'btn-delete',
//					text:GO.lang.cmdDelete,
//					scope:this,
//					handler: function()
//					{
//						this.removeSelectedAttachments();
//					}
//				}]
//			});
//		}
//
//		if(!this.isSelected(node))
//			this.select(node);	
//
//		e.preventDefault();
//		this.menu.showAt(e.getXY());		
	},
	
	deleteSelected : function(){
		
		var selectedRecords = this.getSelectedRecords();
		var ids = [];
		for(var i=0; i<selectedRecords.length;i++){
			var record = selectedRecords[i];
			ids.push(record.data.id);
		}
		
		GO.deleteItems({
			store:this.store,
			params: {
				delete_keys:Ext.encode(ids)
			},
			count: ids.length
		});
	}
});