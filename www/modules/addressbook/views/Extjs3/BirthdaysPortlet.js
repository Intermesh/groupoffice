GO.addressbook.BirthdaysPanel = function(config)
	{
		if(!config)
		{
			config = {};
		}

		config.id='su-birthdays-grid';
		
		var reader = new Ext.data.JsonReader({
			totalProperty: 'total',
			root: 'results',
			fields:['addressbook_id','photo_url', 'name','birthday','age'],
			id: 'name'
		});
	
		config.store = new Ext.data.GroupingStore({
			url: GO.url('addressbook/portlet/birthdays'),
			reader: reader,
			sortInfo: {
				field: 'addressbook_id',
				direction: 'ASC'
			},
			groupField: 'addressbook_id',
			remoteGroup:true,
			remoteSort:true
		});

		config.store.on('load', function(){
			//do layout on Startpage
			if(this.rendered)
				this.ownerCt.ownerCt.ownerCt.doLayout();
		}, this);

	
		config.paging=false,
		config.autoExpandColumn='birthday-portlet-name-col';
		config.autoExpandMax=2500;
		config.enableColumnHide=false;
		config.enableColumnMove=false;
		config.columns=[
		{
			header: '',
			dataIndex: 'photo_url',
			renderer: function (value, metaData, record) {
				return '<img src="'+value+'" height="50"/>';
			}
		},{
			id:'birthday-portlet-name-col',
			header:GO.lang['strName'],
			dataIndex: 'name',
			sortable:true
		},{
			header:GO.addressbook.lang['addressbook'],
			dataIndex: 'addressbook_id',
			sortable:true
		},{
			header:GO.addressbook.lang['contactBirthday'],
			dataIndex: 'birthday',
			width:100,
			sortable:true
		},{
			header:GO.lang['age'],
			dataIndex: 'age',
			width:100
		}];
		config.view=new Ext.grid.GroupingView({
			scrollOffset: 2,
			hideGroupedColumn:true
		}),
		config.sm=new Ext.grid.RowSelectionModel();
		config.loadMask=true;
		config.autoHeight=true;
	
		GO.addressbook.BirthdaysPanel.superclass.constructor.call(this, config);
	
	};

Ext.extend(GO.addressbook.BirthdaysPanel, GO.grid.GridPanel, {
	
	saveListenerAdded : false,
		
	afterRender : function()
	{
		GO.addressbook.BirthdaysPanel.superclass.afterRender.call(this);

		Ext.TaskMgr.start({
			run: function(){
				this.store.load();
			},
			scope:this,
			interval:960000
		});
	}
	
});


GO.mainLayout.onReady(function(){
	if(GO.summary)
	{
		var birthdaysGrid = new GO.addressbook.BirthdaysPanel();
		
		GO.summary.portlets['portlet-birthdays']=new GO.summary.Portlet({
			id: 'portlet-birthdays',
			//iconCls: 'go-module-icon-tasks',
			title: GO.addressbook.lang['upcomingBirthdays'],
			layout:'fit',
			tools: [{
				id: 'gear',
				handler: function(){
					if(!this.selectAddressbookWin)
					{
						this.selectAddressbookWin = new GO.base.model.multiselect.dialog({
							url:'addressbook/portlet',
							columns:[{ header: GO.lang['strName'], dataIndex: 'name', sortable: true }],
							fields:['id','name'],
							title:GO.addressbook.lang['birthdays'],
							model_id:GO.settings.user_id,
							listeners:{
								hide:function(){
									birthdaysGrid.store.reload();
								},
								scope:this
							}
						});
					}
					this.selectAddressbookWin.show();
				}
			},{
				id:'close',
				handler: function(e, target, panel){
					panel.removePortlet();
				}
			}],
			items: birthdaysGrid,
			autoHeight:true
		});
	}
});