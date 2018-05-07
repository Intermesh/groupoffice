/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */

go.modules.community.files.MainPanel = Ext.extend(Ext.Panel, {

	layout: 'responsive',
	layoutConfig: {triggerWidth: 1000},
	initComponent: function () {

		this.browser = new go.modules.community.files.Browser({
			useRouter:true,
			store: new go.data.Store({
				fields: [
					'id', 
					'name',
					'bookmarked',
					'storageId',
					{name: 'touchedAt', type: 'date'},
					{name: 'size', submit: false},
					{name: 'progress', submit: false},
					{name: 'status', submit: false},
					'isDirectory', 
					{name: 'createdAt', type: 'date'}, 
					{name: 'modifiedAt', type: 'date'}, 
					'aclId'
				],
				baseParams: {
					filter:{isHome:true}
				},
				entityStore: go.Stores.get("Node")
			})
		});
		this.folderTree = new go.modules.community.files.FolderTree({
			browser:this.browser
		});
		this.usagePanel = new go.modules.community.files.UsagePanel();
		
		this.sideNav = new go.modules.community.files.SideNav({
			region: 'west',
			cls: 'go-sidenav',
			width: dp(280),
			split:true,
			items: [this.folderTree],
			bbar:[this.usagePanel]
		});

		this.nodeDetail = new go.modules.community.files.NodeDetail({
			region: 'east',
			width:560,
			split: true,
			tbar: [{
				cls: 'go-narrow',
				iconCls: "ic-arrow-back",
				handler: function () {
					this.westPanel.show();
				},
				scope: this
			}]
		});

		this.centerPanel = new Ext.Panel({
			region: "center",
			layout: "border",
			split: true,
			items: [
				this.centerCardPanel = new go.modules.community.files.CenterPanel({
					region: 'center',
					detailView: this.nodeDetail,
					browser: this.browser,
					tbar: {  // configured using the anchor layout
						xtype : 'container',
						items :[new Ext.Toolbar({
							items:[
							{
								cls: 'go-narrow',
								iconCls: "ic-menu",
								handler: function () {
									this.sideNav.show();
								},
								scope: this
							},
							'->',
							this.addButton = new Ext.Button({
								iconCls: 'ic-add',
								tooltip: t('Add'),
								menu: new Ext.menu.Menu({
									items: [{
										iconCls: 'ic-create-new-folder',
										text: t("Create folder")+'&hellip;',
										handler: function() {
											if(!this.renameDialog) {
												this.renameDialog = new go.modules.community.files.RenameDialog();
											}
											this.renameDialog.show();
										},
										scope: this
									},{
										iconCls: 'ic-file-upload',
										text: t("Upload files")+'&hellip;',
										handler: function() {
											if(!this.uploadDialog) {
												var input = document.createElement("input"),
													me = this;
												input.setAttribute("type", "file");
												input.setAttribute('multiple', true);
												input.onchange = function(e) {
													me.centerCardPanel.fileUpload(this.files);
												};
												this.uploadDialog = input;
											}
											this.uploadDialog.click(); // opening dialog
										},
										scope: this
									},{
										disabled: true,
										text: t('File from template')+'&hellip;',
										icon: 'ic-insert-drive-file'
									}]
								}),
								scope: this
							}),{
								tooltip: t("Thumbnails", "files"),
								iconCls: 'ic-view-list',
								handler: function(item){
									var view = this.centerCardPanel.getLayout().activeItem.stateId==="files-grid" ? 'comfy' : 'list';
									item.setIconClass('ic-view-'+view);
									this.centerCardPanel.getLayout().setActiveItem(view === 'list' ? 0 : 1);
								},
								scope:this
							},{
								xtype: 'tbsearch'
							}]
						}),
						this.breadCrumbs = new go.modules.community.files.BreadCrumbBar({
							browser:this.browser
						})
					]}
				}), //first is default in narrow mode
				this.nodeDetail
			]
		});
		
		this.items = [
			this.centerPanel, //first is default in narrow mode
			this.sideNav
		];

		go.modules.community.files.MainPanel.superclass.initComponent.call(this);
	}
});

