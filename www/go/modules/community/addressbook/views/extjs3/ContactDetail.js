/* global Ext, go, GO */

go.modules.community.addressbook.ContactDetail = Ext.extend(go.detail.Panel, {
	entityStore: "Contact",
	stateId: 'addressbook-contact-detail',
	relations: ["addressbook"],
	initComponent: function () {
		
		this.tbar = this.initToolbar();

		var me = this;
		
		Ext.apply(this, {
			items: [	
			
				
				{
					xtype: 'container',
					layout: "hbox",
					cls: "go-addressbook-name-panel",
					items: [
						
						this.avatar = new Ext.BoxComponent({
							xtype: "box",
							cls: "go-detail-view-avatar",
							style: "cursor: pointer",
							listeners: {
								render: function() {
									this.getEl().on("click", function() {
										if(me.data.photoBlobId) {
											window.open(go.Jmap.downloadUrl(me.data.photoBlobId, true));
										}
									});
								}
							},
							tpl: new Ext.XTemplate('<div class="avatar {[values.isOrganization && !values.photoBlobId ? \'organization\' : \'\']}" style="{[this.getStyle(values.photoBlobId)]}">{[this.getHtml(values.isOrganization, values.photoBlobId)]}</div>', 
							{
								getHtml: function (isOrganization, photoBlobId) {
									return isOrganization && !photoBlobId ? '<i class="icon">business</i>' : "";
								},
								getStyle: function (photoBlobId) {
									return photoBlobId ? 'background-image: url(' + go.Jmap.downloadUrl(photoBlobId) + ')"' : "";
								}
							})
						}),
					
						this.namePanel = new Ext.BoxComponent({
							tpl: "<h3>{name}</h3><h4>{jobTitle}</h4>" 							
						}),						
						this.urlPanel = new Ext.BoxComponent({
							flex: 1,
							cls: 'go-addressbook-url-panel',
							xtype: "box",
							tpl: '<tpl for=".">&nbsp;&nbsp;<a target="_blank" href="{url}" class="go-addressbook-url {type}"></a></tpl>'
						})
					],
					onLoad: function (detailView) {
						detailView.data.jobTitle = detailView.data.jobTitle || "";						
						detailView.namePanel.update(detailView.data);
						detailView.urlPanel.update(detailView.data.urls);
						detailView.avatar.update(detailView.data);
					}
					
				}, 				
				
				
				
				// {
				// 	onLoad: function(dv) {
				// 		dv.emailButton.menu.removeAll();						
				// 		dv.data.emailAddresses.forEach(function(a) {

							
				// 			var	mailto = '"' + dv.data.name.replace(/"/g, '\\"') + '" <' + a.email + '>';
							

				// 			dv.emailButton.menu.addMenuItem({
				// 				text: "<div>" + a.email + "</div><small>" + (t("emailTypes")[a.type] || a.type) + "</small>",
				// 				href: "mailto:" + mailto,
				// 				handler: function(btn, e) {
				// 					go.util.mailto({
				// 						email: a.email,
				// 						name: dv.data.name
				// 					}, e);
				// 				}
				// 			});
				// 		});
				// 		dv.emailButton.setDisabled(dv.data.emailAddresses.length === 0);
						
						
				// 		dv.callButton.menu.removeAll();						
				// 		dv.data.phoneNumbers.forEach(function(a) {
				// 			var sanitized = a.number.replace(/[^0-9+]/g, "");

				// 			dv.callButton.menu.addMenuItem({
				// 				text: "<div>" + a.number + "</div><small>" + (t("phoneTypes")[a.type] || a.type)  + "</small>",
				// 				href: "tel://" + sanitized,
				// 				handler: function(btn, e) {									
				// 					go.util.callto({
				// 						number: sanitized,
				// 						name: dv.name
				// 					}, e);
				// 				}
				// 			});
				// 		});
				// 		dv.callButton.setDisabled(dv.data.phoneNumbers.length === 0);
						
				// 	},
				// 	xtype: "toolbar",
				// 	cls: "actions",
				// 	buttonAlign: "center",
				// 	items: [
				// 		this.emailButton = new Ext.Button({
				// 			menu: {cls: "x-menu-no-icons", items: []},
				// 			text: t("E-mail"),
				// 			iconCls: 'ic-email',
				// 			disabled: true
				// 		}),
						
				// 		this.callButton = new Ext.Button({
				// 			menu: {cls: "x-menu-no-icons", items: []},
				// 			text: t("Call"),
				// 			iconCls: 'ic-phone',
				// 			disabled: true
				// 		})
				// 	]
				// },


				// {
				// 	xtype: 'panel',
				// 	title: t("Communication"),
				// 	layout: "hbox",				
					
				// 	onLoad: function (detailView) {

				// 		this.items.each(function(i) {
				// 			i.update(detailView.data);
				// 		});					
						
				// 		detailView.phoneNumbers.setVisible(detailView.data.phoneNumbers.length > 0);
				// 		detailView.emailAddresses.setVisible(detailView.data.emailAddresses.length > 0);
				// 	},

				// 	items: [
				
				// 	]
				// },


				this.emailAddresses = new Ext.BoxComponent({
					xtype: "box",
					listeners: {
						scope: this,
						afterrender: function(box) {
							box.getEl().on('click', function(e) {		
								
								//don't execute when user selects text
								if(window.getSelection().toString().length > 0) {
									return;
								}
								
								var container = box.getEl().dom.firstChild, 
								item = e.getTarget("a"),								
									i = Array.prototype.indexOf.call(container.getElementsByTagName("a"), item);									
							
								go.util.mailto({
									email: this.data.emailAddresses[i].email,
									name: this.data.name
								}, e);

							}, this);
						}
					},
					tpl: '<div class="icons">\
						<tpl for="emailAddresses">\
							<a><tpl if="xindex == 1"><i class="icon label">email</i></tpl>\
							<span>{email}</span>\
							<label>{[t("emailTypes")[values.type] || values.type]}</label>\
							</a>\
						</tpl>\
					</div>'
				}), 


				this.phoneNumbers = new Ext.BoxComponent({
					xtype: "box",
					listeners: {
						scope: this,
						afterrender: function(box) {
							
							box.getEl().on('click', function(e){				
								
								//don't execute when user selects text
								if(window.getSelection().toString().length > 0) {
									return;
								}

								var container = box.getEl().dom.firstChild, 
								item = e.getTarget("a"),
								i = Array.prototype.indexOf.call(container.getElementsByTagName("a"), item);						
							
								go.util.callto({
									number: this.data.phoneNumbers[i].number.replace(/[^0-9+]/g, ""),
									name: this.data.name,
									entity: "Contact",
									entityId: this.data.id
								}, e);

							}, this);
						}
					},
					tpl: '<div class="icons">\
						<tpl for="phoneNumbers">\
							<a><tpl if="xindex == 1"><i class="icon label">phone</i></tpl>\
							<span>{number}</span>\
							<label>{[t("phoneTypes")[values.type] || values.type]}</label>\
							</a>\
						</tpl>\
					</div>'
				}),
				
				{
					xtype: "box",
					listeners: {
						scope: this,
						afterrender: function(box) {
							
							box.getEl().on('click', function(e){								
								var container = box.getEl().dom.firstChild, 
								item = e.getTarget("a", box.getEl()),
								i = Array.prototype.indexOf.call(container.getElementsByTagName("a"), item);
								
								go.util.streetAddress(this.data.addresses[i]);
							}, this);
						}
					},
					tpl: '<div class="icons">\
					<tpl for="addresses">\
						<hr class="indent">\
						<a class="s6"><i class="icon label">location_on</i>\
							<span>{street} {street2}<br>\
							<tpl if="zipCode">{zipCode}<br></tpl>\
							<tpl if="city">{city}<br></tpl>\
							<tpl if="state">{state}<br></tpl>\
							<tpl if="country">{country}</tpl></span>\
							<label>{[t("addressTypes")[values.type] || values.type]}</label>\
						</a>\
					</tpl>\
					</div>'
				}, {
					xtype: "box",
					listeners: {
						scope: this,
						afterrender: function(box) {
							
							box.getEl().on('click', function(e){								
								var container = box.getEl().dom.firstChild, 
								item = e.getTarget("a", box.getEl()),
								i = Array.prototype.indexOf.call(container.getElementsByTagName("a"), item);
								
								go.util.showDate(new Date(this.data.dates[i].date));
							}, this);
						}
					},
					tpl: '<tpl if="dates.length"><div class="icons">\
						<hr class="indent">\
						<tpl for="dates"><a class="s6"><tpl if="xindex == 1"><i class="icon label">cake</i></tpl>\
							<span>{[go.util.Format.date(values.date)]}</span>\
							<label>{[t("dateTypes")[values.type] || values.type]}</label>\
						</a></tpl>\
					</div>	</tpl>'
				},	{
					xtype: "box",
					tpl: '<div class="icons"><hr class="indent"><tpl for="addressbook"><p><i class="icon label">import_contacts</i><span>{name}</span>	<label>{[t("Address book")]}</label>\</p></tpl></div>'
				},
				{
					xtype: 'panel',
					title: t("Notes"),
					autoHeight: true,
					items: [{
						xtype: 'readmore'
					}],
					onLoad: function (detailView) {						
						this.setVisible(!!detailView.data.notes);
						this.items.first().setText('<div style="white-space: pre-wrap">' + Ext.util.Format.htmlEncode(detailView.data.notes) + "</div>");
					}
				}
			]
		});


		go.modules.community.addressbook.ContactDetail.superclass.initComponent.call(this);

		this.addCustomFields();
		this.addLinks();
		this.addComments();
		this.addFiles();
	},

	onLoad: function () {

		this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < go.permissionLevels.write);

		this.starItem.setIconClass(this.data.starred ? "ic-star" : "ic-star-border");
		go.modules.community.addressbook.ContactDetail.superclass.onLoad.call(this);
	},

	initToolbar: function () {

		var items = this.tbar || [];

		items = items.concat([
			'->',
			{
				itemId: "edit",
				iconCls: 'ic-edit',
				tooltip: t("Edit"),
				handler: function (btn, e) {
					var dlg = new go.modules.community.addressbook.ContactDialog();
					dlg.load(this.data.id).show();
				},
				scope: this
			},

			new go.detail.addButton({
				detailView: this
			}),

			this.moreMenu ={
				iconCls: 'ic-more-vert',
				menu: [
					{
						xtype: "linkbrowsermenuitem"
					},
					'-',
					this.starItem = new Ext.menu.Item({
						iconCls: "ic-star",
						text: t("Star"),
						handler: function () {
							var update = {};
							update[this.currentId] = {starred: this.data.starred ? null : true};
							
							go.Db.store("Contact").set({
								update: update
							});
						},
						scope: this
					}),
					'-',
					{
						iconCls: "ic-print",
						text: t("Print"),
						handler: function () {
							this.body.print({title: this.data.name});
						},
						scope: this
					},{
						iconCls: "ic-cloud-download",
						text: t("Export") + " (vCard)",
						handler: function () {
							document.location = go.Jmap.downloadUrl("community/addressbook/vcard/" + this.data.id);
						},
						scope: this
					},
					'-',
					
					this.deleteItem = new Ext.menu.Item({
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
								if (btn !== "yes") {
									return;
								}
								this.entityStore.set({destroy: [this.currentId]});
							}, this);
						},
						scope: this
					})

				]
			}]);
		
		if(go.Modules.isAvailable("legacy", "files")) {
			this.moreMenu.menu.splice(1,0,{
				xtype: "filebrowsermenuitem"
			});
		}


		var tbarCfg = {
			disabled: true,
			items: items
		};


		return new Ext.Toolbar(tbarCfg);


	}
});


