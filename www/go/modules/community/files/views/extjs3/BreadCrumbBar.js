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
	
	browser:null,
	
	initComponent: function() {
		this.items = [{
			xtype: 'button',
			text: t('My files')
		}];
		go.modules.community.files.BreadCrumbBar.superclass.initComponent.call(this);
		
		
		this.browser.on('pathchanged', function(browser){
			this.redraw();
		},this);
	},
	redraw: function() {
		this.removeAll();
		//Root node (my-files, shared-with-me, bookmarks, etc..)
		this.addButton({
			text: this.browser.getRootNode(this.browser.getCurrentRootNode()).text,
			handler: function(btn) {
				this.browser.goto([this.browser.getCurrentRootNode()]);
			},
			scope:this
		});
		
		var folderPath = this.browser.getPath();
		
		if(!Ext.isEmpty(folderPath)) {
			go.Stores.get('Node').get(folderPath, function(nodes){
				
				var fullButtonPath = [this.browser.getCurrentRootNode()];
				
				// Loop through the nodes to build up the breadcrumb list								
				Ext.each(nodes, function(node, i, all){
					
					fullButtonPath.push(node.id);

					var isLast = (i === all.length - 1);
					this.addButton({
						iconCls:'ic-chevron-right',
						disabled:true
					});
					this.addButton({
						directoryId:node.id,
						path:fullButtonPath.slice(0),
						text: node.name,
						disabled:isLast,
						handler: function(btn) {
							this.browser.goto(btn.path);
						},
						scope:this
					});
				}, this);
				
			},this);
		}
		this.doLayout();
	}
});