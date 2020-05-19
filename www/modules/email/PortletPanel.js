GO.email.PortletPanel = Ext.extend(Ext.Panel, {

	height:400,
	//autoHeight:true,	
		
	initComponent : function(){	
		
		Ext.applyIf(this, {
				// Configuration for this Panel
				layout:"border"
		});
		
		this.tabPanel = new Ext.TabPanel({
			region:'north',
			title:'test',
			border:false,
			items:[{title:"dummy"}]
		});
		
		this.folderStore = new GO.data.JsonStore({
			url:GO.url('email/portlet/portletFoldersByUser'),
			root: 'results',
			totalProperty: 'total',
			fields:['account_id','folder_name','user_id','mtime','name','email'],
			remoteSort: true
		});
		
		this.messageStore = new GO.data.GroupingStore({
			suppressError: true,
			url: GO.url("email/message/store"),
			root: 'results',
			totalProperty: 'total',
			remoteSort: true,
			reader: new Ext.data.JsonReader({
				root: 'results',
				totalProperty: 'total',
				fields:['uid','icon','deleted','flagged','labels','has_attachments','seen','subject','from','to','sender','size','udate','internal_udate', 'x_priority','answered','forwarded','account_id','mailbox','mailboxname'],
				id: 'uid'
			}),
			sortInfo: {
				field: 'udate',
				direction: 'DESC'
			},
			groupField: 'udate'
		});

		this.messagesGrid = new GO.email.MessagesGrid({
			id:'emp-messagesgrid',
			store:this.messageStore,
			hideSearch:true,
			region:"center"
		});
		
		this.messagesGrid.on('rowdblclick', function(grid, rowIndex)
		{
			var record = grid.getStore().getAt(rowIndex);

			GO.email.showMessageDialog(record.id, record.store.baseParams.mailbox, record.store.baseParams.account_id);
			
		}, this);
		
		this.folderStore.on('load', function()
		{
			// Remove all tabs
			this.tabPanel.removeAll(true);
//			
			if(!this.folderStore.data.length || this.folderStore.data.length == 0)
			{
//				// Add an empty tab to the panel
				this.tabPanel.add(new Ext.Panel({
					title:t("No folders have been added.", "email")
				}));
				
				this.messagesGrid.store.removeAll();
				this.messagesGrid.hide();
			}
			else
			{
				this.messagesGrid.show();
				for(var i=0; i<this.folderStore.data.length; i++)
				{
					
					var folder = this.folderStore.data.items[i].data;
//					console.log(folder);
					var panel = new Ext.Panel({
						id:'account_'+folder.account_id+':'+folder.name,
						account_id:folder.account_id,
						folder_id:folder.folder_name,
						title:folder.name,
						tabTip:folder.email,
						mailbox:folder.folder_name,
						layout:'fit',
						closable:true
					});
					
					panel.on('show', function(p)
					{
						this.loadMessagepanel(p);
					},this);

					panel.on('close', function(p)
					{
						var record = this.folderStore.getAt(p.index);
						this.folderStore.remove(record);
						
						GO.request({
							url : 'email/portlet/disablePortletFolder',
							params : {
								account_id : p.account_id,
								mailbox : p.mailbox
							},
							fail: function(response, options, result) {
								Ext.Msg.alert(t("Error"), result.feedback);
								this.folderStore.reload();
							},
							success:function(){
								this.folderStore.reload();
							},
							scope : this
						});

					},this);

					Ext.TaskMgr.start({
						run: function(){
							this.messagesGrid.store.load();
						},
						scope:this,
						interval:60*15*1000
					});

					this.tabPanel.add(panel);
				}				
			}

			
			this.tabPanel.setActiveTab(0);
			this.tabPanel.doLayout();
			
		}, this);
				
		
		
		// Add the tabpanel and messageGrid to the Portlet panel
		this.items=[this.tabPanel,this.messagesGrid];

		GO.email.PortletPanel.superclass.initComponent.call(this);
		
		this.on("render",function(){
			this.folderStore.load();
		}, this);
		
		
	},
	loadMessagepanel : function(e){
		this.messagesGrid.store.baseParams.account_id = e.account_id;
		this.messagesGrid.store.baseParams.folder_name = e.folder_name;
		this.messagesGrid.store.baseParams.mailbox = e.mailbox;

		this.messagesGrid.store.load();
	}
	
	
});
