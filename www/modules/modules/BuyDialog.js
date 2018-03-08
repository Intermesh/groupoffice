GO.modules.BuyDialog = Ext.extend(GO.Window, {
	width: 500,
	height: 500,
	title: 'Buy',
	initComponent: function() {
		
		var store = new GO.data.JsonStore({
			url : GO.url('modules/license/users'),
			fields : ['name', 'username', 'id','checked'],
			remoteSort : true,
			id:'username',
			baseParams:{
				module:''
			}
		});
		
		store.on('load', function(){
			
			this.licenseIdField.setValue(store.reader.jsonData.license_id);
			this.hostnameField.setValue(store.reader.jsonData.hostname);
			
		}, this);

		this.usersGrid = new GO.grid.MultiSelectGrid({
			loadMask:true,
			autoExpandColumn: 'name',
			noSingleSelect:true,
			extraColumns:[{
				header: GO.lang['strUsername'],
				dataIndex: "username",
				id: "username",
				width: 200
			}],
			store:store,
			allowNoSelection:true
		});
		
		
		this.formPanel = new Ext.FormPanel({
			
			url:'https://www.group-office.com/buylicense',
			standardSubmit:true,
			items:[
				this.usernamesField = new Ext.form.Hidden({
					name:'usernames',
					value:''
				}),
				this.moduleField = new Ext.form.Hidden({
					name:'module',
					value:''
				}),
				this.licenseIdField = new Ext.form.Hidden({
					name:'license_id',
					value:''
				}),
				this.hostnameField = new Ext.form.Hidden({
					name:'hostname',
					value:''
				})
			],
			hidden:true
		});
		
		


		this.layout = 'fit';
		this.items = [this.usersGrid, this.formPanel];
		
		this.buttons=[{
			text:GO.lang.cmdCancel,
			handler:function(){
				this.hide();
				
				this.usersGrid.store.removeAll();
			},
			scope:this
		},{
				text:GO.modules.lang.addToCart,
				scope:this,
				handler:function(){
					
					this.formPanel.form.getEl().dom.target='groupoffice-shop';
					
					var usernames = this.usersGrid.getSelected();					
					
					this.usernamesField.setValue(Ext.encode(usernames));
					this.formPanel.form.submit();		
					
					
					this.usersGrid.store.removeAll();
					
					this.hide();
					
				}
		}];


		GO.modules.BuyDialog.superclass.initComponent.call(this);
	},
	setModule: function(record) {
		
		this.setTitle(GO.modules.lang.selectUsersForPackage.replace('{package}',record.data.package));
		
		this.usersGrid.store.baseParams.module=record.id;
		this.moduleField.setValue(record.id);
		this.usersGrid.store.load();
	}
});

GO.modules.showBuyDialog = function(record) {
	if (!GO.modules.buyDialog) {
		GO.modules.buyDialog = new GO.modules.BuyDialog();
	}

	GO.modules.buyDialog.show();
	GO.modules.buyDialog.setModule(record);

	
};
