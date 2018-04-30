/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
go.modules.community.files.BreadCrumbBar = Ext.extend(Ext.Toolbar, {
	
	initComponent: function() {
		this.items = [{
			xtype: 'button',
			text: t('My files')
		}];
		go.modules.community.files.BreadCrumbBar.superclass.initComponent.call(this)
	},
	redraw: function(browser) {
		this.removeAll();
		//Root node (mine,shared,etc..)
		this.addButton({
			text: browser.rootNames[browser.at],
			handler: function(btn) {
				browser.store.setBaseParam('filter',{isHome:true});
				browser.store.load();
				browser.nav([]);
			},
			scope:this
		});
		go.Stores.get('Node').get(browser.path, function(nodes){
			Ext.each(nodes, function(node, i, all){
				var isLast = (i === all.length - 1);
				this.addButton({
					directoryId:node.id,
					text: node.name,
					handler: function(btn) {
						this.nav(btn.directoryId);
					},
					scope:this
				});
				if(isLast) {
					return;
				}
				this.addButton({
					iconCls:'ic-chevron-right',
					disabled:true
				});
			}, this);
		},this);
		this.doLayout();
	}
});