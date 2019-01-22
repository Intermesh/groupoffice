GO.mainLayout.onReady(function(){
	if(go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("legacy", "email"))
	{
		this.emailPortlet = new GO.email.PortletPanel();
		
		GO.summary.portlets['portlet-email']=new GO.summary.Portlet({
			id: 'portlet-email',
			title: t("Email", "email"),
			layout:'fit',
			tools: [{
				id: 'gear',
				handler: function(){
					if(!this.emailPortletSettings){
						this.emailPortletSettings = new GO.email.PortletSettingsDialog();
						this.emailPortletSettings.on("hide", function(){
							this.emailPortlet.folderStore.reload();
						},this);
					}
				
					this.emailPortletSettings.show();
				},
				scope:this
			},{
				id:'close',
				handler: function(e, target, panel){
					panel.removePortlet();
				}
			}],
			items: this.emailPortlet,
			autoHeight:true
		});
	}
});
