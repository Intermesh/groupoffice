go.modules.core.links.LinkBrowserMenuItem = Ext.extend(Ext.menu.Item, {	
	iconCls: "ic-link",
	text: t("Link browser"),
	handler: function(btn) {
		
		var dv = this.findParentByType("detailview"), entityId, entity;
		if(dv) {
			entity = dv.entityStore.entity.name;
			entityId = dv.currentId;
		} else
		{
			//for legacy modules
			var dp = this.findParentByType("displaypanel");
			entity = dp.entity;
			entityId = dp.model_id
		}
		
		
		
		var lb = new go.links.LinkBrowser({
			entity: entity,
			entityId: entityId
		});

		lb.show();
	}	
});


Ext.reg("linkbrowsermenuitem", go.modules.core.links.LinkBrowserMenuItem);