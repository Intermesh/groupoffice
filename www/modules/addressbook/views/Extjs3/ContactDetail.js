GO.addressbook.ContactDetail = Ext.extend(GO.DetailView, {

	entity: "Contact",
	model_name: "GO\\Addressbook\\Model\\Contact",
	stateId: 'ab-contact-detail',
	id: 'ab-contact-detail',
	loadUrl: ('addressbook/contact/display'),
	initComponent: function () {

		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
					xtype: "box",
					tpl: new Ext.XTemplate('<figure style="background-image: url({[this.photo_link(values)]});" \
					onClick="GO.addressbook.showContactDialog({id}, \\{activeTab:1\\} );"></figure>', {
						photo_link: function (values) {
							if (values.photo_url) {
								return values.photo_url.split('&w=120&h=160&zc=1').join('&w=280');
							}
						}
					}),
					onLoad: function (dv) {
						this.setVisible(!!dv.data.photo_url);
					},
				}, {
					xtype:"box",
					autoEl: "h3",
					cls: "title",
					tpl: '{initials} {title} {name} {suffix}'
				}, 
//				
//				{
//					layout: 'hbox',
//					layoutConfig: {
//						pack: 'center',
//						align: 'middle'
//					},
//					defaults: {
//						iconAlign: 'top',
//						xtype: 'button',
//						cls: 'primary',
//						margins: dp(8) + ' ' + dp(8)
//					},
//					items: [{
//							iconCls: 'ic-phone',
//							text: 'Bellen',
//						}, {
//							iconCls: 'ic-email',
//							text: 'E-mailen'
//						}]
//				}, 
				
				{
					title: t("Contact details", "addressbook"),
					collapsible: true,
					tpl: new Ext.XTemplate('\
					<div class="icons s6">\
					<tpl for="[email,email2,email3]"><tpl if="values">\
						<p>\
							<tpl if="xindex == 1"><i class="icon label">email</i></tpl>\
							{[this.mailTo(values)]}<label>{[this.emailLabels[xindex-1]]}</label>\
						</p>\
					</tpl></tpl>\
					</div><div class="icons s6">\
					<tpl for="[home_phone,cellular,cellular2,fax,work_phone,work_fax]"><tpl if="values">\
						<p>\
							<tpl if="xindex == 1"><i class="icon label">phone</i></tpl>\n\
							<a onclick="GO.mainLayout.fireEvent(\'callto\', \'{.}\');">{.}</a><label>{[this.phoneLabels[xindex-1]]}</label>\
						</p>\
					</tpl></tpl>\
					</div>\
					<div class="icons">\
					<tpl if="formatted_address">\
						<hr class="indent">\
						<p class="s6"><i class="icon label">home</i>\
							<span>{address}<br>\
							<tpl if="address_no">{address_no}<br></tpl>\
							<tpl if="zip">{zip}<br></tpl>\
							<tpl if="city">{city}<br></tpl>\
							<tpl if="state">{state}<br></tpl>\
							<tpl if="country">{[t("countries")[values.country]]}</tpl></span>\
							<label>' + t("Private address", "addressbook") + '</label>\
						</p>\
						<tpl if="birthday"><p class="s6">\
						<i class="icon label">cake</i>\
						<label>' + t('Birthday') + '</label>\
						<span>{birthday}</span>\
						</p></tpl>\
					</tpl>\
					<hr class="indent" />\
					<tpl if="homepage"><p>\
						<i class="icon label">language</i>\
						<label>' + t("Homepage", "addressbook") + '</label>\
						<span>{homepage}</span>\
					</p></tpl>\
					<tpl for="[url_linkedin,url_facebook,url_twitter,skype_name]"><tpl if="values">\
						<tpl if="xindex == 1 && !parent.homepage"><hr class="indent"></tpl>\
						<p>\
							<tpl if="xindex == 1 && !parent.homepage"><i class="icon label">language</i></tpl>\
							<label>{[this.smLabels[xindex-1]]}</label><span>{.}</span>\
						</p>\
					</tpl></tpl>\
					</div>',
									{
										emailLabels: [t('Primary'), t("Home"), t('Work')], //email 1 2 en 3
										phoneLabels: [t("Home"), t("Mobile"), t("Mobile Work"), t("Fax"), t("Work"), t("Work fax")],
										smLabels: ["LinkedIn", "Facebook", "Twitter", "Skype"],
										mailTo: function (email) {
											var click = GO.email && GO.settings.modules.email.read_permission ?
															'onclick="GO.email.showAddressMenu(event, \'' + email + '\');"' :
															'href="mailto:' + email + '"';
											return '<a ' + click + '>' + email + '</a>';
										}
									})
				}, {
					collapsible: true,
					onLoad: function (dv) {
						this.setVisible(!!dv.data.company_name);
					},
					title: t('Company details', 'addressbook'),
					tpl: '<p class="pad"><a href="#company/{company_id}">{company_name}</a></p>\
					<tpl if="company_name2"><h5 class="pad">{company_name2}</h5></tpl>\
					<div class="icons">\
						<tpl if="company_formatted_address">\
							<p class="s6">\
								<i class="icon label">location_on</i>\
								<a href="{company_google_maps_link}" target="_blank">{company_formatted_address}</a>\
								<label>' + t("Visit address", "addressbook") + '</label>\
							</p>\
						</tpl>\
						<tpl if="company_formatted_post_address">\
							<p><i class="icon label">location_on</i>\
								<a href="{company_google_maps_post_link}" target="_blank">{company_formatted_post_address}</a>\
								<label>' + t("Post address", "addressbook") + '</label>\
							</p>\
						</tpl>\
					</div>\
					<tpl if="company_phone || company_email">\
						<hr class="indent">\
						<tpl if="company_email">\
							<div class="s6 icons">\
								<p><i class="icon label">email</i><span>{company_email}</span></p>\
							</div>\
						</tpl>\
						<tpl if="company_phone">\
							<div class="s6 icons">\
								<p><i class="icon label">phone</i><span>{company_phone}</span></p>\
							<div>\
						</tpl>\
					</tpl>'
				}, {
					collapsible: true,
					onLoad: function (dv) {
						this.setVisible(!!dv.data.comment);
					},
					title: t("Remark", "addressbook"),
					tpl: '<p class="pad">{comment}</p>'
				}
//			,{
//				tpl:'<p class="s6 pad"><label>ID</label><span>{id}</span></p> \
//					<p class="s6"><label>'+t("Address book", "addressbook")+'</label><span>{addressbook_name}</span></p>'
//			}
			]});

		GO.addressbook.ContactDetail.superclass.initComponent.call(this, arguments);
		
		go.CustomFields.addDetailPanels(this);

		this.add(new go.links.LinksDetailPanel());

		if (go.Modules.isAvailable("legacy", "comments")) {
			this.add(new go.modules.comments.CommentsDetailPanel());
		}

		if (go.Modules.isAvailable("legacy", "files")) {
			this.add(new go.modules.files.FilesDetailPanel());
		}
	},

	editHandler: function () {
		GO.addressbook.showContactDialog(this.currentId);
	},

	initToolbar: function () {



		var tbarCfg = {
			disabled: true,
			items: [
				'->',
				{
					itemId: "edit",
					iconCls: 'ic-edit',
					tooltip: t("Edit"),
					handler: this.editHandler,
					scope: this
				},
				
				new go.detail.addButton({			
					detailPanel: this
				}),

				{
					iconCls: 'ic-more-vert',
					menu: [
						{
							iconCls: "btn-print",
							text: t("Print"),
							handler: function () {
								this.body.print({title: this.data.name});
							},
							scope: this
						}
						, {
							iconCls: 'ic-merge-type',
							text: t("Merge"),
							disabled: true,
							handler: function () {
								if (!this.selectMergeLinksWindow) {
									this.selectMergeLinksWindow = new GO.dialog.MergeWindow({displayPanel: this});
								}

								this.selectMergeLinksWindow.show();
							},
							scope: this,
						}

					]
				}]
		};


		return new Ext.Toolbar(tbarCfg);
	}
});
