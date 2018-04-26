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
			
		this.folderTree = new go.modules.community.files.FolderTree();
		this.usagePanel = new go.modules.community.files.UsagePanel();
	
		this.sideNav = new go.modules.community.files.SideNav({
			region: 'west',
			cls: 'go-sidenav',
			width: dp(280),
			split:true,
			items: [this.folderTree],
			bbar:[this.usagePanel]
		});

		this.folderTree.getSelectionModel().on('selectionchange', function (sm) {
//			this.nodeGrid.getStore().baseParams.filter = [{parentId: sm.getSelected().id}];
//			this.nodeGrid.getStore().load();
		}, this);

//		go.Router.add(/files\/node\/([0-9]\/+)/, function(id) {
//			
//		});

		//		rowdblclick: function (grid, rowIndex, e) {

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
			//width: dp(700),
			//narrowWidth: dp(400), //this will only work for panels inside another panel with layout=responsive. Not ideal but at the moment the only way I could make it work
			items: [
				this.cardPanel = new go.modules.community.files.CardPanel({
					region: 'center',
					tbar: {                        // configured using the anchor layout
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
									items: [
										{
											iconCls: 'ic-create-new-folder',
											text: t("Create folder")+'&hellip;',
											handler: this.newFolder,
											scope: this
										},{
											iconCls: 'ic-create-new-folder',
											text: t("Upload files")+'&hellip;',
											handler: function() {
												var input = $(document.createElement("input"));
												input.attr("type", "file");
												input.onchange = function(files) {
													this.cardPanel.fileUpload(files);
												}
												input.trigger("click"); // opening dialog
											},
											scope: this
										},{
											disabled: true,
											text: t('File from template')+'&hellip;',
											icon: 'ic-insert-drive-file'
										}
									]
								}),
								scope: this
							}),{
								tooltip: t("Thumbnails", "files"),
								iconCls: 'ic-view-list',
								handler: function(item){
									var view = this.cardPanel.getLayout().activeItem.stateId==="files-grid" ? 'comfy' : 'list';
									item.setIconClass('ic-view-'+view);
									this.cardPanel.getLayout().setActiveItem(view === 'list' ? 0 : 1);
								},
								scope:this
							},{
								xtype: 'tbsearch'
							}]
						}),
						new Ext.Toolbar({
							layout:'hbox',
							layoutConfig: {
								align: 'middle',
								defaultMargins: {left: dp(4), right: dp(4),bottom:0,top:0}
							},
							items:[
								this.locationTextField = new Ext.form.TextField({
									fieldLabel:t("Location"),
									name:'files-location',
									value: 'Breadcrumbs',
									readOnly:true,
									flex:1
								}),
								this.searchField = new go.toolbar.SearchButton({
									store: this.gridStore
							  })
							]
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

