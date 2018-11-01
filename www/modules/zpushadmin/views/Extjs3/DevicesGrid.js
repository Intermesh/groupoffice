/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: DevicesGrid.js 22949 2018-01-12 08:01:31Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.zpushadmin.DevicesGrid = Ext.extend(GO.grid.GridPanel,{
	changed : false,
	
	// These 2 parameters are used in the render function of the comments column
	maxLength : 20,
	cutWholeWords : true,

	initComponent : function(){
		
		Ext.apply(this,{
			standardTbar:false,
			store: GO.zpushadmin.deviceStore,
			border: false,
			paging:true,
			view:new Ext.grid.GridView({
				emptyText: t("No items to display"),
				getRowClass: this.rowRenderer
			}),
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
				{
					header: t("Status", "zpushadmin"),
					dataIndex: 'new',
					sortable: true,
					renderer: this.statusRenderer,
					width:100
				},
				{
					header: t("User", "zpushadmin"),
					dataIndex: 'username',
					sortable: true,
					width:180
				},
				{
					header: t("Can connect", "zpushadmin"),
					dataIndex: 'can_connect',
					sortable: true,
					renderer: GO.grid.ColumnRenderers.yesNo,
					width:100,
					hidden:true
				},
				{
					header: t("Device ID", "zpushadmin"),
					dataIndex: 'device_id',
					sortable: true,
					width:200
				},
				{
					header: t("Device Type", "zpushadmin"),
					dataIndex: 'device_type',
					sortable: true,
					width:200
				},
				{
					header: t("Activesync version", "zpushadmin"),
					dataIndex: 'as_version',
					sortable: true,
					width:120
				},
				{
					header: t("Ip-Address", "zpushadmin"),
					dataIndex: 'remote_addr',
					sortable: true,
					width:100
				},
				{
					header: t("First synchronisation attempt", "zpushadmin"),
					dataIndex: 'ctime',
					sortable: true,
					width:180
				},{
					header: t("Last synchronisation attempt", "zpushadmin"),
					dataIndex: 'mtime',
					sortable: true,
					width:180
				},
				{
					header: t("Comments", "zpushadmin"),
					dataIndex: 'comment',
					sortable: false,
					renderer: {
						fn: GO.grid.ColumnRenderers.Text,
						scope: this
					},
					width:180
				}
				]
			})
		});
		
		GO.zpushadmin.DevicesGrid.superclass.initComponent.call(this);
		
		this.on("afterrender", function() {
			GO.zpushadmin.deviceStore.load();
		}, this);
	},
	
	dblClick : function(grid, record, rowIndex){
		this.showDeviceDialog(record.id);
	},
//	
//	btnAdd : function(){				
//		this.showDeviceDialog();	  	
//	},
	showDeviceDialog : function(id){
		if(!this.deviceDialog){
			this.deviceDialog = new GO.zpushadmin.DeviceDialog();

			this.deviceDialog.on('save', function(){   
				this.store.load();
				this.changed=true;	    			    			
			}, this);	
		}
		this.deviceDialog.show(id);	  
	},
	showSettingsDialog : function(){
		if(!this.settingsDialog){
			this.settingsDialog = new GO.zpushadmin.SettingsDialog();

			this.settingsDialog.on('save', function(){   
				this.store.load();
				this.changed=true;	    			    			
			}, this);	
		}
		this.settingsDialog.show();	  
	},
	deleteSelected : function(){
		GO.zpushadmin.DevicesGrid.superclass.deleteSelected.call(this);
		this.changed=true;
	},
	statusRenderer : function(value, metaData, record, rowIndex, colIndex, store){
		if(record.data['new']==true)
			return t("New", "zpushadmin");
		else if(record.data['can_connect']==true)
			return t("Enabled", "zpushadmin");
		else
			return t("Disabled", "zpushadmin");		
	},
	rowRenderer : function(record, index){
		if(record.data['new']==true)
			return 'zpushadmin-new-device';
		else if(record.data['can_connect']==true)
			return 'zpushadmin-enabled-device';
		else
			return 'zpushadmin-disabled-device';
	}
	
});
