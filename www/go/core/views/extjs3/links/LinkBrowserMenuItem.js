go.links.LinkBrowserMenuItem = Ext.extend(Ext.menu.Item, {	
	iconCls: "ic-link",
	text: t("Links"),
	handler: function(btn) {
		
		var dv = this.findParentByType("detailview"), entityId, entity;
		if(dv) {
			entity = dv.entityStore.entity.name;
			entityId = dv.currentId;
		} else
		{
			//for legacy modules
			var dp = this.findParentByType("displaypanel") || this.findParentByType("tmpdetailview");
			entity = dp.entity;
			entityId = dp.model_id || dp.currentId
			
		}
		
		
		
		var lb = new go.links.LinkBrowser({
			entity: entity,
			entityId: entityId
		});

		lb.show();
	}	
});


go.links.LinkBrowserButton = Ext.extend(Ext.Button, {
	iconCls: "ic-link",
	tooltip: t("Links"),
	handler: function(btn) {

		var dv = this.findParentByType("detailview"), entityId, entity;
		if(dv) {
			entity = dv.entityStore.entity.name;
			entityId = dv.currentId;
		} else
		{
			//for legacy modules
			var dp = this.findParentByType("displaypanel") || this.findParentByType("tmpdetailview");
			entity = dp.entity;
			entityId = dp.model_id || dp.currentId

		}



		var lb = new go.links.LinkBrowser({
			entity: entity,
			entityId: entityId
		});

		lb.show();
	}
});

Ext.reg("linkbrowsermenuitem", go.links.LinkBrowserMenuItem);
Ext.reg("linkbrowserbutton", go.links.LinkBrowserButton);