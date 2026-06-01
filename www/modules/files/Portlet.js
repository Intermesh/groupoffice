GO.mainLayout.onReady(function(){
	const fmur= go.Modules.get("legacy", "files").userRights;

	if(go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("legacy", "files") && fmur.mayAccessMainPanel) {
		var recentFilesGrid = new GO.files.RecentFilesGrid();

		GO.summary.portlets['portlet-files']=new GO.summary.Portlet({
			id: 'portlet-files',
			title: t("Files modified in the past 7 days", "files"),
			layout:'fit',
			tools: [{
				id:'close',
				handler: function(e, target, panel){
					panel.removePortlet();
				}
			}],
			items: recentFilesGrid,
			autoHeight:true
		});
	}
});
