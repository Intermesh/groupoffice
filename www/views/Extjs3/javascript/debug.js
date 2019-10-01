GO.mainLayout.onReady(function(){
	GO.debug = false;
	var map = new Ext.KeyMap(document, {
		stopEvent:true,
		key:Ext.EventObject.F7,
		ctrl:true,
		fn:function(){
				if(!GO.debugWindow){
					GO.debugWindow =  new GO.DebugWindow();
				}

				GO.debugWindow.show();
				GO.debug = true;
				
				Ext.Ajax.defaultHeaders['X-Debug'] = "1";
			}
		});
});

GO.DebugWindow = Ext.extend(GO.Window, {
	
	baseParams : {},
	
	initComponent : function(){
		
		this.taskConfig={
			run: this.loadLog,
			scope:this,
			interval:1000
		};
		
		Ext.apply(this, {
			title:"Debug",
			maximizable:true,
			collapsible:true,
			width:600,
			height:1200,
			layout:'fit',
			items: this.tabPanel = new Ext.TabPanel({
				xtype:'tabpanel',
				items:[
					this.outputPanel = new GO.LogPanel({
						title:'Log',
						tbar:[{
								enableToggle:true,								
								pressed:false,
								text:"Debug SQL and IMAP",
								toggleHandler:function(item, pressed){
									this.baseParams.debugSql=pressed ? '1' : '0';
								},
								scope:this
						},{
								enableToggle:true,								
								pressed:false,
								text:"Pause",
								toggleHandler:function(item, pressed){
									if(pressed){
										Ext.TaskMgr.stop(this.taskConfig);
									}else
									{
										Ext.TaskMgr.start(this.taskConfig);
									}
								},
								scope:this
						},{					
						
						text: t("Copy"),
						handler: function() {
							go.util.copyTextToClipboard(this.tabPanel.getActiveTab().raw);
						},
						scope:this
				}]
					}),					
					this.infoPanel = new Ext.Panel({title:'Info',autoScroll:true, listeners:{show:this.loadInfo, scope:this}})
				],
				activeTab:0
			}),
			listeners:{
				show:function(){					
					Ext.TaskMgr.start(this.taskConfig);
					
					this.alignTo(Ext.getBody(),'tr-tr');
				},
				hide:function(){
					Ext.TaskMgr.stop(this.taskConfig);
				},
				scope:this
			}
		});
		
		GO.DebugWindow.superclass.initComponent.call(this);
	},
	
	loadInfo : function(){
		GO.request({
			url:'core/info',
			success:function(response, options, result){
							
				this.infoPanel.update(result.info);
		
			},
			scope:this
		});
	},
	loadLog : function(){
		GO.request({
			url:'core/debug',
			params:this.baseParams,
			success:function(response, options, result){			
				
				this.outputPanel.setLog(result.debugLog);				
	
			},
			fail: function(response, options, result) {
				Ext.Msg.alert(t("Error"), result.feedback);
				Ext.TaskMgr.stop(this.taskConfig);
				this.hide();
			},
			scope:this
		});
	}
});


GO.LogPanel = Ext.extend(Ext.Panel,{
	
	show : function(){
		this.scrolledToBottom=false;
		GO.LogPanel.superclass.show.call(this);
	},
	
	autoScroll:true,
	setLog : function(str){
		if(this.body){
			var d = this.body.dom;
				
			var isAtBottom = d.scrollTop >= d.scrollHeight - d.offsetHeight;
		
			this.raw = str;
			this.update("<pre>" + str + "</pre>");

	
			//scroll to bottom
			if(!this.scrolledToBottom || isAtBottom){

				d.scrollTop = d.scrollHeight - d.offsetHeight;

				this.scrolledToBottom=true;
			}
		}
	}
})
