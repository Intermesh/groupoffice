/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddresslistsGroupedMultiSelectGrid.js 21434 2017-09-14 12:59:40Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
//GO.addressbook.AddresslistsGroupedMultiSelectGrid = Ext.extend(GO.grid.GridPanel , {
GO.addressbook.AddresslistsGroupedMultiSelectGrid = Ext.extend(GO.grid.MultiSelectGrid , {
	initComponent : function(){

		var fields = {
			fields:['id', 'name', 'user_name','acl_id','addresslistGroupName','checked'],
			columns:[
				this.checkColumn,{
					header:GO.lang.strName,
					
					dataIndex: 'name',
					id:'name',
					renderer:function(value, p, record){
						if(!GO.util.empty(record.data.tooltip)) {
							p.attr = 'ext:qtip="'+Ext.util.Format.htmlEncode(record.data.tooltip)+'"';
						}
						return value;
					}
				},{
					header: GO.addressbook.lang.addresslistGroup,
					dataIndex: 'addresslistGroupName',
					hidden:true
				}
			]};

		var store = new Ext.data.GroupingStore({
			reader: new Ext.data.JsonReader({
				totalProperty: "total",
				root: "results",
				id: "id",
				fields:fields.fields
			}),
			baseParams: {
				permissionLevel: GO.permissionLevels.read,
				limit: GO.settings.addresslists_store_forced_limit?parseInt(GO.settings.addresslists_store_forced_limit):parseInt(GO.settings.config.nav_page_size)
			},
			proxy: new Ext.data.HttpProxy({
				url:GO.url('addressbook/addresslist/store')
			}),        
			groupField:'addresslistGroupName',
			remoteSort:true,
			remoteGroup:true,
			listeners:{
				load:function(store,records,options){
					if(this.rendered){
						this.getView().toggleRowIndex(0,true);
					} else {
						this.getView().startCollapsed = false;
					}
				},
				scope:this
			}
		});
		
		Ext.apply(this, {
			autoExpandColumn:'name',
			plugins: [this.checkColumn],
			title:GO.addressbook.lang.filterMailings,
			loadMask:true,
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns: fields.columns
			}),
			store: store,
			view: new Ext.grid.GroupingView({
		    hideGroupedColumn:true,
		    groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
		   	emptyText: GO.lang.strNoItems,
		   	showGroupName:false,
				startCollapsed:true
			}),
			allowNoSelection:true,
			bbar: new GO.SmallPagingToolbar({
				items:[
					this.searchField = new GO.form.SearchField({
						store: store,
						width:120,
						emptyText: GO.lang.strSearch
					})
				],
				store:store,
				pageSize:GO.settings.addresslists_store_forced_limit?parseInt(GO.settings.addresslists_store_forced_limit):parseInt(GO.settings.config.nav_page_size)
			})
		});
		
		Ext.applyIf(this,{
			region:'center'
		});
		
		GO.addressbook.AddresslistsGroupedMultiSelectGrid.superclass.initComponent.call(this);
	},
	
	afterRender : function() {
		GO.addressbook.AddresslistsGroupedMultiSelectGrid.superclass.afterRender.call(this);

		var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody, {
			ddGroup : 'AddressBooksDD',
			notifyDrop : this.onNotifyDrop.createDelegate(this)
		});
	},
	
	onNotifyDrop : function(source, e, data){	
		
		var selections = source.dragData.selections;
		var dropRowIndex = this.getView().findRowIndex(e.target);
		var list_id = this.getView().grid.store.data.items[dropRowIndex].id;
		var list_name = this.getView().grid.store.data.items[dropRowIndex].data.name;
		
		var contacts = [];
		var companies = [];
		
		for (var i=0; i<selections.length; i++) {
			
			var selection = selections[i];
			
			if ('name2' in selection.data && 'post_address' in selection.data)
				companies.push(selection.data.id);
			else
				contacts.push(selection.data.id);
			
		}
		
		Ext.Msg.show({
			title: GO.addressbook.lang['addToAddresslist'].replace('%s',list_name),
			msg: GO.addressbook.lang['addToAddresslistPrompt'].replace(/%s/g,list_name),
			buttons: Ext.Msg.YESNOCANCEL,
			scope: this,
			fn: function(btn) {
				if (btn!='cancel') {
					GO.request({
						url: 'addressbook/addresslist/add',
						params: {
							contacts : Ext.encode(contacts),
							companies : Ext.encode(companies),
							addresslistId : list_id,
							move : btn=='yes'
						},
						success: function(options, response, result)
						{
							Ext.Msg.alert(GO.lang['strSuccess'],GO.addressbook.lang['addAddresslistSuccess']);
						},
						scope: this
					});
				}
			}
		});
	}
});