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
	
	// These 2 parameters are used in the render function of the comments column
	maxLength : 20,
	cutWholeWords : true,

	standardTbar:false,
	border: false,
	paging:true,

	initComponent(){
		
		Ext.apply(this,{
			store: GO.zpushadmin.deviceStore,
			view:new Ext.grid.GridView({
				emptyText: t("No items to display"),
				getRowClass: r => {
					if(r.data.new)
						return 'zpushadmin-new-device';
					return r.data.can_connect ? 'zpushadmin-enabled-device' : 'zpushadmin-disabled-device';
				}
			}),
			cm:new Ext.grid.ColumnModel({
				defaults: { sortable:true, width:100 },
				columns:[
					{header: t("Status"), dataIndex: 'can_connect', renderer: (v, meta, r) => {
						if(r.data.new)
							return t("New");
						return t(r.data.can_connect ? "Enabled" : "Disabled");
					}},
					{header: t("User"), dataIndex: 'username', width:180},
					{header: t("Can connect"), dataIndex: 'can_connect', hidden:true, renderer: GO.grid.ColumnRenderers.yesNo},
					{header: t("Device ID"), dataIndex: 'device_id', width:200},
					{header: t("Device Type"), dataIndex: 'device_type', width:200},
					{header: t("Activesync version"), dataIndex: 'as_version', width:120},
					{header: t("Ip-Address"), dataIndex: 'remote_addr'},
					{header: t("First synchronisation attempt"), dataIndex: 'ctime'},
					{header: t("Last synchronisation attempt"), dataIndex: 'mtime'},
					{header: t("Comments"), dataIndex: 'comment', sortable: false, renderer: {
						fn: GO.grid.ColumnRenderers.Text,
						scope: this
					}}
				]
			})
		});
		
		this.supr().initComponent.call(this);
		
		this.on("afterrender", () => {
			GO.zpushadmin.deviceStore.load();
		});
	},
	
	dblClick(grid, record, rowIndex){
		this.showDeviceDialog(record.id);
	},

	showDeviceDialog(id){
		if(!this.deviceDialog){
			this.deviceDialog = new GO.zpushadmin.DeviceDialog();

			this.deviceDialog.on('save', function(){   
				this.store.load();
			}, this);	
		}
		this.deviceDialog.show(id);	  
	}
	
});
