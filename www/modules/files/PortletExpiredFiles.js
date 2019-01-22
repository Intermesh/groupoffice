GO.mainLayout.onReady(function(){
	if(go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("legacy", "files")) {
		GO.summary.portlets['portlet-expired-files']={
			multiple:true,
			portletType: 'portlet-expired-files',
			title: t("Expired files", "files"),
			layout:'fit',
			tools: [{
//				id: 'gear',
//				handler: function(e, target, panel){
////					console.log(panel.mainPanel);
//					console.log(panel.settings);
////					
////					if(!panel.settings){
////						panel.settings = {};
////					} 
////					panel.settings.test1 = 'TESTJE';
////					panel.mainPanel.saveActivePortlets();
//				}
//			},{
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
