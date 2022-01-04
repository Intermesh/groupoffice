GO.files.OpenFileWindow = Ext.extend(GO.Window, {
	initComponent : function(){
		this.title=t("Select application to open this file...", "files");
		
		this.list = new GO.grid.SimpleSelectList({
			store: new GO.data.JsonStore({
				url:GO.url('files/file/handlers'),
				fields:['name','cls','handler','iconCls','extension']
			}),
			tpl:new Ext.XTemplate( '<tpl for=".">'+
			'<div id="{dom_id}" class="go-item-wrap fs-handler-icon {iconCls}">{name}</div>'+
			'</tpl>')
		});
		
		this.list.on('click', function(dataview, index){			
				
			var record = dataview.store.getAt(index);			
			eval(record.data.handler);
			
			if(this.rememberCB.getValue()){
				GO.request({
					url:'files/file/saveHandler',
					params:{
						cls:record.data.cls,
						extension:record.data.extension
					}
				});
			}
			
			this.list.clearSelections();
			this.hide();
				
		}, this);
		
		this.layout='border';
		this.modal=true;
		this.height=400;			
		this.width=400;
		this.closable=true;
		this.closeAction='hide';	
		
		this.items= [this.panel = new Ext.Panel({
			region:'center',
			autoScroll:true,
			items: this.list,
			cls: 'go-form-panel'
		}),{
			region:'south',
			xtype:'form',
			height:40,
			cls:'go-form-panel',
			items:[this.rememberCB = new Ext.form.Checkbox({
				xtype:'checkbox',
				hideLabel:true,
				boxLabel:t("Remember my decision for this file type", "files")
			})]
		}];
		this.buttons=[{
				text: t("Close"),
				handler: function(){
					this.hide();
				},
				scope:this
			}];
		
		GO.files.OpenFileWindow.superclass.initComponent.call(this);
	},
	show : function(config){	
		

		Ext.getBody().mask(t("Loading..."));
		this.list.store.load({
			params:{
				id:config.id,
				path:config.path,
				all:config.all
			},
			callback:function(){
				Ext.getBody().unmask();

				if(this.list.store.getCount()==1)
				{
					var record = this.list.store.getAt(0);			
					eval(record.data.handler);

				}else{				
					GO.files.OpenFileWindow.superclass.show.call(this);
					this.rememberCB.setValue(false);
					
					this.returnedHandler=true;
				}
			},
			scope:this
		});	

	}	
});
