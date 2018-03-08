/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ThumbsPanel.js 22436 2018-03-01 07:55:07Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.files.ThumbsPanel = Ext.extend(Ext.Panel, {
	store : false,
	cls: 'card',
	initComponent : function(){
		this.bbar = new Ext.PagingToolbar({
  					cls: 'go-paging-tb',
	          store: this.store,
	          pageSize: parseInt(GO.settings['max_rows_list']),
	          displayInfo: true,
	          displayMsg: t("Displaying items {0} - {1} of {2}"),
	          emptyMsg: t("No items to display")
	      });
		
    var tpl = new Ext.XTemplate('<tpl for=".">',
            '<div class="fs-thumb-wrap" id="{name}">',
						
				
		    '<div class="fs-thumb" style="background-image:url({thumb_url});">',
				'<tpl if="locked_user_id&gt;0"><div class="fs-thumb-locked"></tpl>',
				'&nbsp;',
				'<tpl if="locked_user_id&gt;0"></div></tpl>',
				'</div>',
				
				'<tpl if="GO.files.isContentExpired(content_expire_date) == false">'+
					'<span class="x-editable">{shortName}</span>'+
				'</tpl>'+
				'<tpl if="GO.files.isContentExpired(content_expire_date)">'+
					'<span class="x-editable content-expired">{shortName}</span>'+
				'</tpl>'+

		    '</div>',				
        '</tpl>',
        '<div class="x-clear"></div>');
     
        
     this.items=[this.view = new Ext.DataView({
            store: this.store,
            tpl: tpl,
            autoHeight:true,
            multiSelect: true,
            overClass:'fs-view-over',
            selectedClass:'fs-view-selected',
            itemSelector:'div.fs-thumb-wrap',
           /* plugins: [
                new Ext.DataView.DragSelector()
                //new Ext.DataView.LabelEditor({dataIndex: 'name'})
            ],*/

            prepareData: function(data){
                data.shortName = Ext.util.Format.ellipsis(data.name, 20);
                return data;
            }
        })];
        
     this.autoScroll=true;
     
		 this.view.on('render', function(){
     	var dragZone = new GO.files.ThumbsDragZone(this.view, {containerScroll:true,
        ddGroup: 'FilesDD'});
       var dropZone = new GO.files.ThumbsDropZone(this.view, {
       	notifyDrop: this.onNotifyDrop.createDelegate(this)
       });        
     }, this);
     
     this.addEvents({drop:true});
        
     GO.files.ThumbsPanel.superclass.initComponent.call(this);
	},
	

	onBeforeLoad : function(){		
    this.body.mask(t("Loading..."));     
	},
	
	onStoreLoad : function(){		
    this.body.unmask();     
	},
	
	setStore : function(store){
		if(this.store)
		{
			this.store.un("beforeload", this.onBeforeLoad, this);
			this.store.un("load", this.onStoreLoad, this);
			this.store=false;
		}
		
		if(store)
		{
			this.store=store;
			this.store.on("beforeload", this.onBeforeLoad, this);
			this.store.on("load", this.onStoreLoad, this);
		}
		
		this.view.bindStore(this.store);
		
	},
	/**
	 * Sends a delete request to the remote store. It will send the selected keys in json 
	 * format as a parameter. (delete_keys by default.)
	 * 
	 * @param {Object} options An object which may contain the following properties:<ul>
     * <li><b>deleteParam</b> : String (Optional)<p style="margin-left:1em">The name of the
     * parameter that will send to the store that holds the selected keys in JSON format.
     * Defaults to "delete_keys"</p>
     * </li>
	 * 
	 */
	deleteSelected : function(config){	  
		
		if(!config)
		{
			config=this.deleteConfig;
		}
		
		if(!config['deleteParam'])
		{
			config['deleteParam']='delete_keys';
		}
		
		var selectedRows = [];
		
		var records = this.view.getSelectedRecords();
		for(var i=0;i<records.length;i++)
		{
			selectedRows.push(records[i].data.type_id);
		}
		
		var params={}
		params[config.deleteParam]=Ext.encode(selectedRows);
		
		var deleteItemsConfig = {
			store:this.store,
			params: params,
			count: selectedRows.length	
		};
		
		if(config.callback)
		{
		  deleteItemsConfig['callback']=config.callback;		
		}
		if(config.success)
		{
		  deleteItemsConfig['success']=config.success;		
		}
		if(config.failure)
		{
		  deleteItemsConfig['failure']=config.failure;		
		}
		if(config.scope)
		{
		  deleteItemsConfig['scope']=config.scope;
		}
			
		GO.deleteItems(deleteItemsConfig);		
	},
	
	onNotifyDrop : function(dd, e, data)
	{
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


GO.files.ThumbsDropZone = function(view, config)
{
	this.view = view;
  GO.files.ThumbsDropZone.superclass.constructor.call(this, view.getEl(), config);
};

Ext.extend(GO.files.ThumbsDropZone, Ext.dd.DropTarget,{
	ddGroup: 'FilesDD',
	copy:false,
	notifyOver : function(dd, e, data){
		var target = e.getTarget('.fs-thumb-wrap');
		
		if(target)
		{		
			var dropRecord = this.view.store.getAt(target.viewIndex);		
			if(dropRecord)
			{
				if(dropRecord.data.extension=='folder')
				{
					return this.dropAllowed;
				}
			}
		}		
		return false;
	}
});




/**
 * Create a DragZone instance for our JsonView
 */
GO.files.ThumbsDragZone = function(view, config){
    this.view = view;
    GO.files.ThumbsDragZone.superclass.constructor.call(this, view.getEl(), config);
};
Ext.extend(GO.files.ThumbsDragZone, Ext.dd.DragZone, {
		ddGroup: 'FilesDD',
    // We don't want to register our image elements, so let's 
    // override the default registry lookup to fetch the image 
    // from the event instead
    getDragData : function(e){
    	if(e.ctrlKey)
    	{
    		return false;
    	}
      var target = e.getTarget('.fs-thumb-wrap');
      if(target){
          var view = this.view;
          if(!view.isSelected(target)){
              view.onClick(e);
          }
          var selNodes = view.getSelectedNodes();
          var records = view.getSelectedRecords();
          
          var dragData = {
              nodes: selNodes,
              selections: records
          };
          if(selNodes.length == 1){
              dragData.ddel = target;
              dragData.single = true;
          }else{
              var div = document.createElement('div'); // create the multi element drag "ghost"
              div.className = 'multi-proxy';
              for(var i = 0, len = selNodes.length; i < len; i++){
                  div.appendChild(selNodes[i].firstChild.firstChild.cloneNode(true)); // image nodes only
                  if((i+1) % 3 == 0){
                      div.appendChild(document.createElement('br'));
                  }
              }
              var count = document.createElement('div'); // selected image count
              count.innerHTML = i + ' images selected';
              div.appendChild(count);
              
              dragData.ddel = div;
              dragData.multi = true;
          }
          return dragData;
      }
      return false;
    },

    // this method is called by the TreeDropZone after a node drop
    // to get the new tree node (there are also other way, but this is easiest)
  /*  getTreeNode : function(){
        var treeNodes = [];
        var nodeData = this.view.getRecords(this.dragData.nodes);
        for(var i = 0, len = nodeData.length; i < len; i++){
            var data = nodeData[i].data;
            treeNodes.push(new Ext.tree.TreeNode({
                text: data.name,
                icon: '../view/'+data.url,
                data: data,
                leaf:true,
                cls: 'image-node'
            }));
        }
        return treeNodes;
    },*/
    
    // the default action is to "highlight" after a bad drop
    // but since an image can't be highlighted, let's frame it 
    afterRepair:function(){
        for(var i = 0, len = this.dragData.nodes.length; i < len; i++){
            Ext.fly(this.dragData.nodes[i]).frame('#8db2e3', 1);
        }
        this.dragging = false;    
    },
    
    // override the default repairXY with one offset for the margins and padding
    getRepairXY : function(e){
        if(!this.dragData.multi){
            var xy = Ext.Element.fly(this.dragData.ddel).getXY();
            xy[0]+=3;xy[1]+=3;
            return xy;
        }
        return false;
    }
});

