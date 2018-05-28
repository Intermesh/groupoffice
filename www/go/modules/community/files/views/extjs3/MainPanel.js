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
			rootNodes: [
				{
					text: t('My files'),
					iconCls: 'ic-home',
					entityId: 'my-files',
					draggable: false,
					expanded: true,
					children: [], //to prevent router to load this node before params.filter.parentId is set after fetching the storage
					params: {
						filter: {
							parentId: null
						}
					}
				}, {
					text: t('Shared with me'),
					iconCls: 'ic-group',
					entityId: 'shared-with-me',
					draggable: false,
					params: {
						filter: {
							isSharedWithMe: true
						}
					}
				}, {
					text: t('Bookmarks'),
					iconCls: 'ic-bookmark',
					entityId: 'bookmarks',
					draggable: false,
					params: {
						filter: {
							bookmarked: true
						}
					}
				}]
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

		// Load the user's home folder
		this.on('afterrender', function () {
			var callId = go.Jmap.request({
				method: 'Storage/query',
				params: {
					filter: {
						ownedBy: go.User.id
					}
				}
			});

			go.Jmap.request({
				method: 'Storage/get',
				params: {
					"#ids": {
						resultOf: callId,
						name: "Storage/query",
						path: "ids"
					}
				}
			});

			go.Stores.get("Storage").on('changes', this.onStorageChanges, this, {single: true}); // single run, only need to be set once
		}, this);
	},

/**
 * When the storage changes, apply the received rootFolderId.
 * 
 * @param {type} store
 * @param {type} added
 * @param {type} changed
 * @param {type} destroyed
 * @return {undefined}
 */
	onStorageChanges: function (store, added, changed, destroyed) {

		var me = this;
		var storages = store.get(changed.concat(added));
		for (var i = 0; i < storages.length; i++) {
			if (storages[i].ownedBy == go.User.id) {
				var myFilesNodeId = storages[i].rootFolderId;
				
				this.browser.getRootNode('my-files').params.filter.parentId = myFilesNodeId;				
			
					this.folderTree.getTreeNodesByEntityId('my-files').forEach(function(node) {
						node.attributes.params.filter.parentId = myFilesNodeId;				
						//delete node.childNodes;
						node.expanded = true;
						delete node.attributes.children;
	
						node.reload(function() {
							me.folderTree.openPath(me.browser.getPath(true));
						},this);

					});

				if(this.browser.getCurrentDir() == "my-files") {
					this.browser.goto(["my-files", myFilesNodeId]);
				}
			}
		}
	}
});
