go.Modules.register("legacy", 'jitsimeet', {
	mainPanel: GO.email.EmailClient,
	title: t("Jitsi"),
	systemSettingsPanels: ["GO.jitsimeet.SystemSettingsPanel"]
});


GO.moduleManager.onModuleReady('calendar',function(){

	Ext.override(GO.calendar.EventDialog,{

		initWindow : GO.calendar.EventDialog.prototype.initWindow.createSequence(function(){
			let meetUri = go.Modules.get('legacy','jitsimeet').settings.jitsiUri;
			if(meetUri) {
				if (meetUri.substr(-1) != '/') meetUri += '/';
				this.jitsiButton = new Ext.Button({
					//name: 'jitsiMeet'
					text: 'Add link for video meeting',
					handler: function(btn) {
						let descriptionField = btn.ownerCt.previousSibling(),
							jitsiLink = meetUri+(Math.random() + 1).toString(36).substring(2);
						descriptionField.setValue(descriptionField.getValue()+"\n"+jitsiLink)
					}
				})
				this.propertiesPanel.insert(-1, new Ext.Container({fieldLabel: '&nbsp;',items:[this.jitsiButton]}));
			}
			
		}),
		setData : GO.calendar.EventDialog.prototype.setData.createSequence(function(action){
			//console.log(action.result.data.jitsiMeet);
			this.jitsiButton.setDisabled(action.result.data.jitsiMeet);
		})
		
	});
});
