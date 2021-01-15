/* global Ext, go, GO */

go.modules.community.addressbook.ContactDetail = Ext.extend(go.detail.Panel, {
	entityStore: "Contact",
	stateId: 'addressbook-contact-detail',
	relations: ["addressbook"],
	initComponent: function () {
		
		this.tbar = this.initToolbar();

		var me = this;
		
		Ext.apply(this, {
			items: [{
				xtype: "panel",
				onLoad: function (detailView) {
					detailView.data.jobTitle = detailView.data.jobTitle || "";

					detailView.applyTemplateToItems(this.items);
					detailView.applyTemplateToItems(this.items.itemAt(0).items);

					var icon = detailView.data.isOrganization ? '<i class="icon">business</i>' : null;

					detailView.avatar.update(go.util.avatar(detailView.data.name, detailView.data.photoBlobId, icon))
				},
				items:[
				{
					xtype: 'container',
					layout: "hbox",
					cls: "go-addressbook-name-panel",
					items: [
						
						this.avatar = new Ext.BoxComponent({
							height: dp(48),
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
							}
							// tpl: new Ext.XTemplate('<div class="avatar {[values.isOrganization && !values.photoBlobId ? \'organization\' : \'\']}" style="{[this.getStyle(values)]}">{[this.getHtml(values)]}</div>',
							// {
							// 	getHtml: function (v) {
							// 		if(v.photoBlobId) {
							// 			return "";
							// 		}
							// 		return v.isOrganization ? '<i class="icon">business</i>' : go.util.initials(v.name);
							// 	},
							// 	getStyle: function (v) {
							// 		return v.photoBlobId ? 'background-image: url(' + go.Jmap.thumbUrl(v.photoBlobId, {w: 40, h: 40, zc: 1})  + ')"' : "background-image:none;background-color: #" + v.color;;
							// 	}
							// })
						}),
					
						this.namePanel = new Ext.BoxComponent({
							style: "display: table;height:100%;",
							tpl: new Ext.XTemplate('<div style="vertical-align: middle;display:table-cell;"><h3 <tpl if="color">style=\"color: #{color};\"</tpl>>{[this.displayName(values)]}</h3><h4>{jobTitle} <tpl if="values.department">- {department}</tpl></h4></div>',
								{
									displayName: function(v) {
										var strRet = "";

										if(v.prefixes) {
											strRet += v.prefixes + "&nbsp;";
										} else if(v.gender) {
											strRet += t('sirMadam')[v.gender] + "&nbsp;";
										}
										strRet += v.name;
										if(v.suffixes) {
											strRet += '&nbsp;' + v.suffixes;
										}
										return strRet;
									}
								}
							)
						}),
						this.urlPanel = new Ext.BoxComponent({
							flex: 1,
							cls: 'go-addressbook-url-panel',
							xtype: "box",
							tpl: '<tpl for="urls">&nbsp;&nbsp;<a target="_blank" href="{url}" class="go-addressbook-url {type}"></a></tpl>'
						})
					]
					
				}, 				

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
								item = e.getTarget("a");

								if(!item) {
									return;
								}

								var	i = Array.prototype.indexOf.call(container.getElementsByTagName("a"), item);
							
								go.util.mailto({
									email: this.data.emailAddresses[i].email,
									name: this.data.name,
									entity: "Contact",
									entityId: this.data.id
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
								item = e.getTarget("a");

								if(!item) {
									return;
								}

								var i = Array.prototype.indexOf.call(container.getElementsByTagName("a"), item);
							
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

								//don't execute when user selects text
								if(window.getSelection().toString().length > 0) {
									return;
								}

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
							<span style="white-space:pre">{formatted}</span>\
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

								//don't execute when user selects text
								if(window.getSelection().toString().length > 0) {
									return;
								}

								var container = box.getEl().dom.firstChild, 
								item = e.getTarget("a", box.getEl());

								if(!item) {
									return;
								}

								var i = Array.prototype.indexOf.call(container.getElementsByTagName("a"), item);
								
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
				},{
					title: t('Company'),
					onLoad: function (dv) {
						this.setVisible(dv.data.IBAN || dv.data.vatNo || dv.data.vatReverseCharge || dv.data.registrationNumber || dv.data.debtorNumber);
					},
					collapsible:true,
					tpl: '<p class="s6 pad">\
						<tpl if="values.IBAN"><label>IBAN</label>\<span>{IBAN}</span><br><br></tpl>\
						<tpl if="values.vatNo"><label>{[t("VAT number")]}</label><span>{vatNo}</span><br><br></tpl>\
						<label>{[t("Reverse charge VAT")]}</label><span>{[values.vatReverseCharge? t("Yes") : t("No") ]}</span>\
					</p>\
					<p class="s6 pad">\
						<tpl if="values.registrationNumber"><label>{[t("Registration number")]}</label><span>{registrationNumber}</span><br><br></tpl>\
						<tpl if="values.debtorNumber"><label>{[t("Debtor number")]}</label><span>{debtorNumber}</span></tpl>\
					</p>'
				}]
			},{
				collapsible: true,
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
		//Sort contact types to top
		this.addLinks(function(a, b) {

			if(a.link.entity == "Contact" && b.link.entity != "Contact") {
				return -1;
			}
			return 0;
		});
		this.addComments();
		this.addFiles();

		this.add(new go.detail.CreateModifyPanel());
	},

	onLoad: function () {

		this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < go.permissionLevels.write);
		this.deleteItem.setDisabled(this.data.permissionLevel < go.permissionLevels.writeAndDelete);
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

			{
				xtype: "linkbrowserbutton"
			},

			this.moreMenu ={
				iconCls: 'ic-more-vert',
				menu: [
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
							go.util.downloadFile(go.Jmap.downloadUrl("community/addressbook/vcard/" + this.data.id));
						},
						scope: this
					},{
						iconCls: "ic-attach-file",
						text: t("Send") + " (vCard)",
						handler: function () {
							Ext.getBody().mask(t("Exporting..."));
							go.Jmap.request({
								method: "Contact/export",
								params: {
									extension: 'vcf',
									ids: [this.data.id]
								},
								scope: this,
								callback: function (options, success, response) {
									Ext.getBody().unmask();
									if(!success) {
										Ext.MessageBox.alert(t("Error"), response.message);
									} else {
										var c = GO.email.showComposer(),b=response.blob;
										c.on('dialog_ready', function() {
											c.emailEditor.attachmentsView.addBlob(b);
										},this,{single:true});
									}
								}
							});
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
			items.splice(items.length - 1, 0,{
				xtype: "detailfilebrowserbutton"
			});
		}

		var tbarCfg = {
			disabled: true,
			items: items
		};

		return new Ext.Toolbar(tbarCfg);

	}
});