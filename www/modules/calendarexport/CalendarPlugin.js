go.Modules.register("legacy", "calendarexport");

GO.moduleManager.onModuleReady('calendar',function(){

	Ext.override(GO.calendar.MainPanel,{
		
		initComponent : GO.calendar.MainPanel.prototype.initComponent.createSequence(function() {
			if(go.Modules.isAvailable("legacy", 'calendarexport')) {
				this.exportMenu = new GO.base.ExportMenu({className: 'GO\\Calendarexport\\Export\\CurrentView'});
				this.centerPanel.topToolbar.items.add(
					this.exportMenu
				);
			}
		})
		
	});
});
