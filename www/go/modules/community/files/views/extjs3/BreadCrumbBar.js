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
				browser.at = 'mine';
				browser.path = [];
				browser.open();
			},
			scope:this
		});
		if(!Ext.isEmpty(browser.path)) {
			go.Stores.get('Node').get(browser.path, function(nodes){
				Ext.each(nodes, function(node, i, all){
					var isLast = (i === all.length - 1);
					this.addButton({
						iconCls:'ic-chevron-right',
						disabled:true
					});
					this.addButton({
						directoryId:node.id,
						text: node.name,
						disabled:isLast,
						handler: function(btn) {
							browser.open(btn.directoryId);
						},
						scope:this
					});
				}, this);
			},this);
		}
		this.doLayout();
	}
});