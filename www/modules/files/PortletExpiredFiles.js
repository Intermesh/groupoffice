GO.mainLayout.onReady(function(){
	const fmur= go.Modules.get("legacy", "files").userRights;

	if(go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("legacy", "files") && fmur.mayAccessMainPanel) {
		GO.summary.portlets['portlet-expired-files']={
			multiple:true,
			portletType: 'portlet-expired-files',
			title: t("Expired files", "files"),
			layout:'fit',
			tools: [{
				id:'close',
				handler: function(e, target, panel){
					panel.removePortlet();
				}
			}],
			items: [],
			autoHeight:true			,
			listeners:{
				render:function(){
					
					if(!this.portletExpiredFilesGrid){
						this.portletExpiredFilesGrid = new GO.files.PortletExpiredFilesGrid();
						this.add(this.portletExpiredFilesGrid);
					}

				}
			}
		};
	}
});
