GO.form.TreeSelect = Ext.extend(Ext.form.TriggerField,{

	triggerClass : 'go-form-tree-select',

	windowConfig : {},
	
	onTriggerClick : function(){
		if(!this.treeSelectWindow)
		{
			this.windowConfig.textfield=this;
			this.treeSelectWindow = new GO.form.TreeSelectWindow(this.windowConfig);
		}
		this.treeSelectWindow.show();
	}
	
});

GO.form.TreeSelectWindow = Ext.extend(function(config){

	config = config || {};
	
	config.title=config.textfield.windowTitle || '';
	
	this.treePanel = new Ext.tree.TreePanel({
		layout:'fit',
		loader:config.textfield.treeLoader,
		containerScroll:true,
		autoScroll:true,
		rootVisible:false,
		root: {
        nodeType: 'async',
        id:'root'
    }
	});
	config.layout='fit';
	
	config.items=[this.treePanel];
	
	config.modal=true;
	config.width=640;
	config.height=400;
	config.closeAction='hide';
	config.closable=true;
	
	config.buttons=[{
    text: GO.lang.cmdOk,
    handler: function(){
        var newids = [], selNodes = this.treePanel.getChecked();        
        var oldv = this.textfield.getValue();
        var oldids = oldv.split(',');
        
      	for(var i=0;i<oldids.length;i++)
				{
					if(oldids[i]!='')
					{
						var node = this.treePanel.getNodeById(oldids[i]);
						if(!node){
							newids.push(oldids[i])
						}
					}
				}
        
        Ext.each(selNodes, function(node){            
            newids.push(node.id);
        });
        
        this.textfield.setValue(newids.join(','));
        this.hide();
    },
    scope:this	
	}];
	
	GO.form.TreeSelectWindow.superclass.constructor.call(this, config);
	
	this.treePanel.on('load',function(node){		
		var v = this.textfield.getValue();
		var ids = v.split(',');		
		
		Ext.each(node.childNodes, function(child){
			
			var indexOf =ids.indexOf(child.id);			
			if(indexOf>-1)
			{
				child.attributes.checked=true;
			}
		});
		
	}, this);
	
	this.on('show', function(){
		var v = this.textfield.getValue();	
		
		var ids = v.split(',');
		
		this.clearChecked();
		
		for(var i=0;i<ids.length;i++)
		{
			var node = this.treePanel.getNodeById(ids[i]);
			if(node){
				node.getUI().toggleCheck(true);
			}
		}
		
	}, this);
}, GO.Window, {
	clearChecked : function(){
		var selNodes = this.treePanel.getChecked();
		
		Ext.each(selNodes, function(node){
        node.getUI().toggleCheck(false);
    });
	}
});

Ext.reg('treeselect', GO.form.TreeSelect);