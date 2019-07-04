GO.addressbook.ContactDetail = Ext.extend(GO.DetailView, {

	entity: "Contact",
	model_name: "GO\\Addressbook\\Model\\Contact",
	stateId: 'ab-contact-detail',
	id: 'ab-contact-detail',
	loadUrl: ('addressbook/contact/display'),
	collapsibleSections: {},
	hiddenSections : [],
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
					tpl: new Ext.XTemplate('\
							{initials} {title} {name} {suffix}\
							{[this.subheader(values.company_id,values.company_name,values.department,values.function)]}\
					',{
						subheader: function (company_id,company_name,department,jobtitle) {
	
							// If empty, return nothing
							if(!company_name && !department && !jobtitle){
								return '';
							}
							
							var str = '<br><small>';
							
							if(jobtitle){
								str+=jobtitle;
							}
							
							if(company_name){
								if(!jobtitle){
									str+='<a href="#company/'+company_id+'">'+company_name+'</a>';
								} else {
									str+=' @ <a href="#company/'+company_id+'">'+company_name+'</a>';
								}
							}
							
							if(department){
								if(!jobtitle && !company_name){
									str+='('+department+')';
								} else {
									str+=' ('+department+')';
								}
							}
							
							str+='</small>';
							
							return str;
						}
					})
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
							<a onclick="GO.util.callToHandler(\'{.}\');">{.}</a><label>{[this.phoneLabels[xindex-1]]}</label>\
						</p>\
					</tpl></tpl>\
					</div>\
					<div class="icons">\
					<hr class="indent">\
					<tpl if="formatted_address">\
						<p class="s6"><i class="icon label">home</i>\
							<a href="{google_maps_link}" target="_blank">{address}<br>\
							<tpl if="address_no">{address_no}<br></tpl>\
							<tpl if="zip">{zip}<br></tpl>\
							<tpl if="city">{city}<br></tpl>\
							<tpl if="state">{state}<br></tpl>\
							<tpl if="country">{[t("countries")[values.country]]}</tpl></a>\
							<label>' + t("Private address", "addressbook") + '</label>\
						</p>\
					</tpl>\
					<tpl if="birthday"><p class="s6">\
						<i class="icon label">cake</i>\
						<label>' + t('Birthday') + '</label>\
						<span>{birthday}</span>\
					</p></tpl>\
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
						<div class="icons"><p><i class="icon label">import_contacts</i>\
						{addressbook_name}\
						<label>' + t("Address book", "addressbook") + '</label>\
					</p></div>\
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
								<p><i class="icon label">phone</i><a onclick="GO.util.callToHandler(\'{.}\');">{company_phone}</a></p>\
							<div>\
						</tpl>\
					</tpl>'
				},  {
					collapsible: true,
					onLoad: function (dv) {
						this.setVisible(!!dv.data.comment);
					},
					title: t("Remark", "addressbook"),
					tpl: '<p class="pad">{comment}</p>'
				}, new go.panels.CreateModifyTpl()
//			,{
//				tpl:'<p class="s6 pad"><label>ID</label><span>{id}</span></p> \
//					<p class="s6"><label>'+t("Address book", "addressbook")+'</label><span>{addressbook_name}</span></p>'
//			}
			]});

		GO.addressbook.ContactDetail.superclass.initComponent.call(this, arguments);
		
		if(GO.customfields){
			this.add({
				onLoad: function (dv) {
					dv.data.panelId = dv.id;
				},
				tpl: new Ext.XTemplate(GO.customfields.displayPanelTemplate+GO.customfields.displayPanelBlocksTemplate,
				{
					collapsibleSectionHeader: function(title, id, dataKey,extraClassName){
						this.panel.collapsibleSections[id]=dataKey;

						var extraclassname = '';

						if(typeof(extraClassName)!='undefined')
							extraclassname = extraClassName;

						return '<div class="collapsible-display-panel-header '+extraclassname+'"><div style="float:left">'+title+'</div><div class="x-tool x-tool-toggle" style="float:right;margin:0px;padding:0px;cursor:pointer" id="toggle-'+id+'">&nbsp;</div></div>';
					},
					panel: this
				})
			});
		}

		this.add(go.links.getDetailPanels());

		if (go.Modules.isAvailable("legacy", "comments")) {
			this.add(new go.modules.comments.CommentsDetailPanel());
		}

		if (go.Modules.isAvailable("legacy", "files")) {
			this.add(new go.modules.files.FilesDetailPanel());
		}
	},
	
	onBodyClick :  function(e, target){

		if(target.id.substring(0,6)=='toggle'){
			var toggleId = target.id.substring(7,target.id.length);

			this.toggleSection(toggleId);
		}
	},
	
	toggleSection : function(toggleId, collapse){

		var el = Ext.get(toggleId);
		var toggleBtn = Ext.get('toggle-'+toggleId);

		if(!toggleBtn)
			return false;
		
		var saveState=false;
		if(typeof(collapse)=='undefined'){
			collapse = !toggleBtn.hasClass('go-tool-toggle-collapsed');// toggleBtn.dom.innerHTML=='-';
			saveState=true;
		}

		
		if(collapse){
			//data not loaded yet			

			if(this.hiddenSections.indexOf(this.collapsibleSections[toggleId])==-1)
				this.hiddenSections.push(this.collapsibleSections[toggleId]);
		}else
		{
			var index = this.hiddenSections.indexOf(this.collapsibleSections[toggleId]);
			if(index>-1)
				this.hiddenSections.splice(index,1);
		}

		if(!el && !collapse){
			this.reload();
		}else
		{
			if(el)
				el.setDisplayed(!collapse);

			if(collapse){
				toggleBtn.addClass('go-tool-toggle-collapsed');
			}else
			{
				toggleBtn.removeClass('go-tool-toggle-collapsed');
			}
			//dom.innerHTML = collapse ? '+' : '-';
		}
		if(saveState)
			this.saveState();
	},

	editHandler: function () {
		GO.addressbook.showContactDialog(this.currentId);
	},

	initToolbar: function () {

		var moreMenuItems = [
			{
				xtype: "linkbrowsermenuitem"
			},
			'-',{
				iconCls: "ic-print",
				text: t("Print"),
				handler: function () {
					this.body.print({title: this.data.name});
				},
				scope: this
			}, this.mergeButton = new Ext.menu.Item({
				iconCls: 'ic-merge-type',
				text: t("Merge"),
				disabled: true,
				handler: function () {
					if (!this.selectMergeLinksWindow) {
						this.selectMergeLinksWindow = new GO.dialog.MergeWindow({displayPanel: this,entity: "Contact"});
					}

					this.selectMergeLinksWindow.show();
				},
				scope: this
			})
		];
		
		if(go.Modules.isAvailable("legacy", "files")) {
			moreMenuItems.splice(1,0,{
				xtype: "filebrowsermenuitem"
			});
		}
		
		if(go.Modules.isAvailable("core", "users")){

			this.createUserButton = new Ext.menu.Item({
				iconCls:'btn-add',
				text: t("Create user"),
				disabled:true,
				handler:function(){

					if(GO.util.empty(this.data.go_user_id)){

						var username =this.data.last_name;
						var arr = this.data.email.split('@');
						if(arr[0]){
							username = arr[0];	
						}
						
						var data = {
							displayName:this.data.name,
							email:this.data.email,
							recoveryEmail:this.data.email,
							username:username
						};

						var dlg = new go.modules.core.users.CreateUserWizard();
						
						var me = this;
	
						dlg.onSaveSuccess = function(response){

							if(response && response.id){
								GO.request({
									url: 'addressbook/contact/submit',
									params: {
										id: me.data.id,
										go_user_id:response.id
									},
									scope: me,
									success: function(response, options, result) {
										me.reload();
									}
								});			
							}
						},
						
						dlg.applyData(data);
						dlg.show();
						
					}else	{
						var dlg = new go.usersettings.UserSettingsDialog();
						dlg.show(this.data.go_user_id);
					}
				},
				scope:this
			});
			
			moreMenuItems.splice(3,0,this.createUserButton);
		}

		var tbarCfg = {
			disabled: true,
			items: [
				'->',
				this.editBtn = new Ext.Button({
					itemId: "edit",
					iconCls: 'ic-edit',
					tooltip: t("Edit"),
					handler: this.editHandler,
					scope: this
				}),
				
				new go.detail.addButton({			
					detailView: this
				}),

				{
					iconCls: 'ic-more-vert',
					menu:moreMenuItems
				}]
		};
		
		return new Ext.Toolbar(tbarCfg);
	},
	
	afterRender: function() {
		GO.addressbook.ContactDetail.superclass.afterRender.call(this);
		this.body.on('click', this.onBodyClick, this);
	},
	
	onLoad : function() {
		
		if(this.createUserButton){
			
			this.createUserButton.setDisabled(false);
			if(GO.util.empty(this.data.go_user_id)){
				this.createUserButton.setText(t("Create user"));
			} else {
				this.createUserButton.setText(t("Edit user"));
			}
		}
		
		this.editBtn.setDisabled(this.data.permission_level < GO.permissionLevels.write);
		this.mergeButton.setDisabled(this.data.permission_level < GO.permissionLevels.write);
		
		GO.addressbook.ContactDetail.superclass.onLoad.call(this);
	}
});
