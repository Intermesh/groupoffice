go.Modules.register("legacy", 'jitsimeet', {
	title: t("Jitsi"),
	systemSettingsPanels: ["GO.jitsimeet.SystemSettingsPanel"]
});


GO.mainLayout.on('authenticated', function() {
	if(go.Modules.isAvailable('legacy','jitsimeet') ) {


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
								jitsiLink = meetUri + btn._jitsiRoom;

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
				this.jitsiButton.setDisabled(action.result.data.jitsiMeet);
				//I am sure there is a better place to store this - but I dont know the framework well enough:
				this.jitsiButton._jitsiRoom = action.result.data.jitsiRoom;
			})

		});
	}
});

