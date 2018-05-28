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
		this.items = [];
		go.modules.community.files.BreadCrumbBar.superclass.initComponent.call(this);
		
		go.Stores.get('Node').on('changes', function(){
			this.redraw();
		},this);
		this.browser.on('pathchanged', function(browser){
			this.redraw();
		},this);
	},
	
	afterRender: function() {
		go.modules.community.files.BreadCrumbBar.superclass.afterRender.call(this);
		this.ownerCt.on('resize',function(me,adjWidth,adjHeight,rawWidth) {
			this.redraw();
		},this);
	},
	
	redraw: function(w) {
		var isLast = true,
			node,
			pxPerChar = dp(9), 
			pxUsed = this.browser.getRootNode().text.length * pxPerChar + dp(56), // for first btn and end padding
			pxMax = this.el.getWidth(),
			folderPath = this.browser.getPath().slice(0);
		pxUsed = pxUsed || 0;
		this.removeAll();
		folderPath.shift();
		if(!Ext.isEmpty(folderPath)) {
			var nodes = go.Stores.get('Node').get(folderPath);
			
			if(nodes){
				nodes = nodes.reverse();
				// Loop through the nodes to build up the breadcrumb list	
				for(var i = 0; i < nodes.length; i++) {
					node = nodes[i];
					pxUsed += (node.name.length * pxPerChar + dp(56));
					this.insertButton(0,{
						directoryId:node.id,
						text: node.name,
						disabled:isLast,
						handler: function(btn) {
							this.browser.goto(btn.directoryId);
						},
						scope:this
					});
					this.insert(0,{
						xtype:'tbtext',
						html:'<i class="icon">chevron_right</i>',
						width: dp(24)
					});
					if(pxUsed > pxMax) {
						this.insert(0,{
							xtype:'tbtext',
							text:' ...',
							width: dp(24)
						});
						
						break;
					}
					isLast = false;
				}
				
			}
		}

		if(pxUsed <= pxMax) {
		
			//Root node (my-files, shared-with-me, bookmarks, etc..)
			this.insertButton(0, {
				text: this.browser.getRootNode().text,
				handler: function(btn) {
					this.browser.goto([this.browser.path[0]]);
				},
				scope:this
			});
		}
		this.doLayout();
	}
});