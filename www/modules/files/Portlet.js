GO.mainLayout.onReady(function(){
	if(GO.summary)
	{
		var recentFilesGrid = new GO.files.RecentFilesGrid();

		GO.summary.portlets['portlet-files']=new GO.summary.Portlet({
			id: 'portlet-files',
			title: GO.files.lang.recentFiles,
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