GO.mainLayout.onReady(function(){
	if(GO.summary)
	{
		this.emailPortlet = new GO.email.PortletPanel();
		
		GO.summary.portlets['portlet-email']=new GO.summary.Portlet({
			id: 'portlet-email',
			iconCls: 'go-module-icon-email',
			title: GO.email.lang.email,
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
