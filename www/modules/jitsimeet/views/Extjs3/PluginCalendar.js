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
					iconCls: 'ic-video-call',
					text: t("Add online meeting link", "jitsimeet"),
					handler: function(btn) {

						const form = this.findParentByType("form").form,
							descriptionField = form.findField('description'),
							jitsiLink = meetUri+(Math.random() + 1).toString(36).substring(2);

						const desc = descriptionField.getValue();
						descriptionField.setValue((desc ? desc + "\n" : "") + t("Online meeting link", "jitsimeet") + ":\n\n" + jitsiLink + "\n\n");

						const loc = form.findField("location");
						if(!loc.getValue()){
							loc.setValue(t("Online meeting", "jitsimeet"));
						}
					}
				})
				this.propertiesPanel.insert(-1, new Ext.Container({fieldLabel: '&nbsp;', labelSeparator: "",items:[this.jitsiButton]}));
			}
			
		}),
		setData : GO.calendar.EventDialog.prototype.setData.createSequence(function(action){
			//console.log(action.result.data.jitsiMeet);
			this.jitsiButton.setDisabled(action.result.data.jitsiMeet);
		})
		
	});
});
