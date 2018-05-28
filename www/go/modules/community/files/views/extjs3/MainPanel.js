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
			useRouter: true,
			rootConfig:{
				filters: [{
					text: t('Shared with me'),
					iconCls: 'ic-group',
					entityId: 'shared-with-me',
//					draggable: false,
					filter: {
						isSharedWithMe: true
					}
					
				}, {
					text: t('Bookmarks'),
					iconCls: 'ic-bookmark',
					entityId: 'bookmarks',
//					draggable: false,
					filter: {
						bookmarked: true
					}
					
				}],
				nodeId: null,
				storages: true
			}
		});

		this.folderTree = new go.modules.community.files.FolderTree({
			browser: this.browser,
			anchor: "100% 100%"
		});

		this.usagePanel = new go.modules.community.files.UsagePanel();

		this.sideNav = new go.modules.community.files.SideNav({
			region: 'west',
			cls: 'go-sidenav',
			width: dp(280),
			layout: 'anchor',
			split: true,
			items: [this.folderTree],
			bbar: [this.usagePanel]
		});

		this.nodeDetail = new go.modules.community.files.NodeDetail({
			region: 'east',
			width: 560,
			split: true,
			browser: this.browser,
			tbar: [{
					cls: 'go-narrow',
					iconCls: "ic-arrow-back",
					handler: function () {
						this.westPanel.show();
					},
					scope: this
				}]
		});

		this.centerCardPanel = new go.modules.community.files.CenterPanel({
			region: 'center',
			detailView: this.nodeDetail,
			browser: this.browser
		});

		this.centerPanel = new Ext.Panel({
			region: "center",
			layout: "border",
			split: true,
			items: [
				this.centerCardPanel, //first is default in narrow mode
				this.nodeDetail
			]
		});

		this.items = [
			this.centerPanel, //first is default in narrow mode
			this.sideNav
		];

		go.modules.community.files.MainPanel.superclass.initComponent.call(this);

//		// Load the user's home folder
//		this.on('afterrender', function () {
//			go.Files.onReady(this.onReady, this);
//		}, this);
	},
//	onReady: function (files) {
//
//		
//
//		this.browser.getRootNode('my-files').params.filter.parentId = files.myFilesFolderId;
//		var me = this;
//		this.folderTree.getTreeNodesByEntityId('my-files').forEach(function (node) {
//			node.attributes.params.filter.parentId = files.myFilesFolderId;
//			//delete node.childNodes;
//			node.expanded = true;
//			delete node.attributes.children;
//
//			node.reload(function () {
//				me.folderTree.openPath(me.browser.getPath(true));
//			}, this);
//
//		});
//
//		if (this.browser.getCurrentDir() == "my-files") {
//			this.browser.goto(["my-files", files.myFilesFolderId]);
//		}
//	}


});
