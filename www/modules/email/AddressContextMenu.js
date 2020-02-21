/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: AddressContextMenu.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.AddressContextMenu = function(config)
{
	if(!config)
	{
		config = {};
	}
	config['shadow']='frame';
	config['minWidth']=180;
	
	
				
	this.composeButton = new Ext.menu.Item({
		iconCls: 'btn-compose',
		text: t("Compose", "email"),
		cls: 'x-btn-text-icon',
		handler: function(){

			var values = {
				to: this.address
				};
			this.queryString = decodeURI(this.queryString);
			var pairs = this.queryString.split('&');
			var pair;
			for(var i=0;i<pairs.length;i++){
				pair = pairs[i].split('=');
							
				if(pair.length==2){
					values[pair[0]]=pair[1];
				}
			}
			
			var composerConfig = {
				values : values
			};
			
			//if we're on the e-mail panel use the currently active account.			
			var ep = GO.mainLayout.getModulePanel("email");			
			if(ep && ep.isVisible())
				composerConfig.account_id=ep.account_id;			

			GO.email.showComposer(composerConfig);
		},
		scope: this
	});
	this.searchButton = new Ext.menu.Item({
		iconCls: 'btn-search',
		text: t("Search through Group-Office", "email").replace('{product_name}', GO.settings.config.product_name),
		cls: 'x-btn-text-icon',
		handler: function() {
			go.util.search('"' + this.address + '"'); 			
		},
		scope: this
	});
				
	this.searchMessagesButton = new Ext.menu.Item({
		iconCls: 'btn-search',
		text: t("Show messages in current folder", "email"),
		cls: 'x-btn-text-icon',
		handler: function(){
			GO.email.searchSender(this.address);
		},
		scope: this
	});
				
	config.items=[this.composeButton,
	this.searchButton,
	this.searchMessagesButton
	
	];
	
	if(go.Modules.isAvailable("community", "addressbook")) {
		
		this.store = new go.data.Store({
			entityStore: "Contact",
			fields: ["id", "name", 'isOrganization']
		});
		
		this.addEvents({change: true, beforechange: true});
		
		this.store.on("load", this.updateMenu, this);
		
		
		
		this.createItem = new Ext.menu.Item({
			iconCls: 'ic-add',
			text: t("Create contact"),
			handler: function() {
				
				var nameParts = this.personal.split(" "), v = {
					name: this.personal,
					firstName: nameParts.shift(),
					emailAddresses: [{
							type: "work",
							email: this.address
					}]
				};
				
				v.lastName = nameParts.join(" ");				
				
				var dlg = new go.modules.community.addressbook.ContactDialog();
				dlg.show();
				dlg.setValues(v);
			},
			scope: this
		});

		this.updateItem = new Ext.menu.Item({
			iconCls: 'ic-add',
			text: t("Add to contact"),
			handler: function() {

				var me = this;
				
				var select = new go.util.SelectDialog({
					entities: ['Contact'],
					query: me.personal,
					mode: 'id',				
					singleSelect: true,
					selectMultiple: function (ids) {
						var dlg = new go.modules.community.addressbook.ContactDialog();
						dlg.on("load", function() {
							var a = dlg.formPanel.entity.emailAddresses;
							a.push({
								type: "work",
								email: me.address
							});
							dlg.setValues({
								emailAddresses: a
							});
						});
						dlg.load(ids[0]).show();

						
					}					
				});
				select.show();
				
			},
			scope: this
		});
		
		config.items.push("-", this.createItem, this.updateItem);

	}

	GO.email.AddressContextMenu.superclass.constructor.call(this, config);	
}

Ext.extend(GO.email.AddressContextMenu, Ext.menu.Menu,{
	personal : '',
	address : '',
	showAt : function(xy, address, personal, queryString)
	{
		this.queryString=queryString || '';
		this.address = address || '';
		this.personal= personal || '';
		
		this.store.setFilter("email", {email: this.address});
		this.store.load();
		
		GO.email.AddressContextMenu.superclass.showAt.call(this, xy);
	},
	
	updateMenu: function () {

		if(!this.el) {
			return;
		}
		
		this.initItems();
		var item, rem = [], items = [];
		this.items.each(function(i){
				rem.push(i);
		});
		for (var i = 3, len = rem.length - 2; i < len; ++i){
				item = rem[i];
				this.remove(item, true);
		}
		
		
		this.el.sync();
		
		
		var records = this.store.getRange(), len = records.length;
		
		if(len) {
			this.insert(3, "-");	
		}
		
		for (var i = 0; i < len; i++) {
			this.insert(4 + i, {
				iconCls: records[i].data.isOrganization ? "entity ic-business purple" : 'entity ic-person blue',
				text: t("Open") + ": " + records[i].data.name,
				contactId: records[i].data.id,
				handler: function() {
					// var dlg = new go.modules.community.addressbook.ContactDialog();
					// dlg.load(this.contactId).show();

					var win = new go.links.LinkDetailWindow({
						entity: "Contact"
					});

					win.load(this.contactId);

				}
			});
		}

		if(len) {
			this.insert(4+i, "-");	
		}
		
	}
});
