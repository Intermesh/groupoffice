GO.addressbook.SentMailingsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.tbar = [{
				iconCls: 'ic-mail',
				text: t("Send mailing", "addressbook"),
				handler: function(){
					if(!this.selectAddresslistWindow)
					{
						this.selectAddresslistWindow=new GO.addressbook.SelectAddresslistWindow();
						this.selectAddresslistWindow.on("select", function(win, addresslist_id){
							var composer = GO.email.showComposer({addresslist_id:addresslist_id, campaign_id: this._campaignId});
							composer.on('hide', function(){
								this.store.load();
							}, this, {single:true});
						}, this);
					}
					this.selectAddresslistWindow.show();
				},
				scope: this
			},'-',{
			iconCls: 'ic-delete',
			text: t("Delete"),
			handler: function(){
				this.deleteSelected();
			},
			scope: this
		}];
	
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
	    url: GO.url('addressbook/sentMailing/store'),
	    baseParams: {
//	    	task: 'sent_mailings',
//	    	addresslist_id: 0	    	
					campaign_id: 0
	    	},
	    root: 'results',
	    id: 'id',
	    totalProperty:'total',
	    fields: ['id', 'addresslist','subject','user_name', 'ctime','status','sent','total','errors', 'hide_pause', 'hide_play', 'message_path','opened'],
	    remoteSort: true
	});

	if (!config.action) {
		var action = new Ext.ux.grid.RowActions({
			header:'',
			hideMode:'display',
			keepSelection:true,
			actions:[{
				iconCls:'ic-pageview',
				qtip:t("View message", "addressbook")
			},{
				iconCls:'ic-receipt',
				qtip:t("View log", "addressbook")
			},{
				callback: function(grid,record, iconCls) { Ext.select('.ux-row-action-item.'+iconCls).hide(); },
				iconCls:'ic-pause',
				hideIndex:'hide_pause',
				qtip:t("Pause sending", "addressbook")
			},{
				callback: function(grid,record, iconCls) { Ext.select('.ux-row-action-item.'+iconCls).hide(); },
				iconCls:'ic-play-arrow',
				hideIndex:'hide_play',
				qtip:t("Resume sending", "addressbook")
			}],
			width: 50
		});
		
		action.on('beforeaction', function (grid, record, action, row, col) {
//			console.log('beforeaction')
			
			grid.setDisabled(true);
			grid.task = false;
			var task = {
				run: function(){
//					console.log('run')
					if(grid.task == true) {
//						console.log('setDisabled')
						grid.task = false;
						grid.setDisabled(false);
					}
					grid.task = true;
				},
				interval: 3000,
				scope: grid,
				repeat:2
			};

			Ext.TaskMgr.start(task);
				
		})
		
		action.on({
			action:function(grid, record, action, row, col) {
//				this.setDisabled(true);
//				
				
				
				
				switch(action){
					case 'ic-pause':
						grid.store.baseParams.pause_mailing_id=record.id;
						grid.store.load();
						delete grid.store.baseParams.pause_mailing_id;

						break;
					case 'ic-play-arrow':
						grid.store.baseParams.start_mailing_id=record.id;
						grid.store.load();
						delete grid.store.baseParams.start_mailing_id;

						break;
					case 'ic-pageview':
						GO.linkHandlers["GO\\Savemailas\\Model\\LinkedEmail"].call(this, 0, {action: "path", path:record.get('message_path')});
						break;
					case 'ic-receipt':
						window.open(GO.url("addressbook/sentMailing/viewLog",{'mailing_id': record.id}));
						break;
				}
				
				
				
			}
		});
		
	} else {
		
		var action = config.action;
	}

	if (!config.plugins)
		config.plugins=action;
	
	config.paging=true;
	
	if (!config.columns)
		config.columns = [
	   		{
					header: t("Address list", "addressbook"),
					dataIndex: 'addresslist'
				},	{
					header: t("Subject", "addressbook"), 
					dataIndex: 'subject'
				},	{
					header: t("Owner"), 
					dataIndex: 'user_name'
				},	{
					header: t("Created at"), 
					dataIndex: 'ctime',
					width: dp(140)
				},		{
					header: t("Status", "addressbook"), 
					dataIndex: 'status',
					renderer:function(v){
						return t("mailingStatus", "addressbook")[v];
					}
				},		{
					header: t("Sent", "addressbook"), 
					dataIndex: 'sent',
					align:'center',
					width:60
				},		{
					header: t("Total", "addressbook"), 
					dataIndex: 'total',
					align:'center',
					width:60
				},		{
					header: t("Errors", "addressbook"), 
					dataIndex: 'errors',
					align:'center',
					width:60
				},
				action
			];
	
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:false
		},
		columns: config.columns
});
		
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")		
	}),
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=false;

	
	GO.addressbook.SentMailingsGrid.superclass.constructor.call(this, config);
	
};

Ext.extend(GO.addressbook.SentMailingsGrid, GO.grid.GridPanel,{
//	setMailingId : function(addresslist_id){
//		this.store.baseParams.addresslist_id=addresslist_id;
//		this.store.loaded=false;
//	},
					
	_campaignId : 0,
					
	setCampaignId : function(campaignId) {
		this._campaignId = campaignId;
		this.store.baseParams['campaign_id']=campaignId;
	}
});
