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
		
		go.Stores.get('Node').on('changes', this.redraw,this);
		
		this.on('resize',function(me,adjWidth,adjHeight,rawWidth) {
			this.redraw();
		});
		this.browser.on('pathchanged', function(browser){
			this.redraw();
		},this);
	},
	
	
	
	redraw: function(w) {
		w = w || this.el.getWidth();
		this.removeAll();
		var folderPath = this.browser.getPath();
		
		if(!Ext.isEmpty(folderPath)) {
			console.log('width',this.el.getWidth());
			var nodes = go.Stores.get('Node').get(folderPath);
			
			if(nodes){
				nodes = nodes.reverse();
				// Loop through the nodes to build up the breadcrumb list								
				Ext.each(nodes, function(node, i, all){

					var isLast = (i === 0);
					
					var b = this.insertButton(0,{
						directoryId:node.id,
						text: node.name,
						disabled:isLast,
						handler: function(btn) {
							this.browser.goto(btn.directoryId);
						},
						scope:this
					});
					console.log(b);
					this.insertButton(0,{
						iconCls:'ic-chevron-right',
						disabled:true
					});
				}, this);
				
			}
		}
		//Root node (my-files, shared-with-me, bookmarks, etc..)
		this.insertButton(0, {
			text: this.browser.getRootNode(this.browser.getCurrentRootNode()).text,
			handler: function(btn) {
				this.browser.goto([this.browser.getCurrentRootNode()]);
			},
			scope:this
		});
		this.doLayout();
	}
});