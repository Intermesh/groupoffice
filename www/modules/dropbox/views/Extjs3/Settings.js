GO.dropbox.SettingsPanel = function(config) {

	config = config?config:{};

	config.title = GO.dropbox.lang.name;
	config.bodyStyle = 'padding:5px';
	
	config.items=[
		new Ext.form.FieldSet({
			bodyStyle:'padding:5px',
			items:[
				new GO.form.HtmlComponent({
					style:'margin-bottom:5px',
					html:GO.dropbox.lang.connectDisconnectDescription
				}),
				new Ext.Button({
					style:'margin-top:20px',
					text:GO.dropbox.lang.connect,
					handler:function(){
						window.open(GO.url('dropbox/auth/start'));
					},
					scope:this
				}),new Ext.Button({
					style:'margin-top:20px',
					text:GO.dropbox.lang.disconnect,
					handler:function(){
						GO.request({
							url:'dropbox/auth/disconnect',
							success:function(){
								Ext.Msg.alert(GO.lang.strSuccess, GO.dropbox.lang.disconnectedSuccessfully);
							}
						});
					},
					scope:this
				})
			]
		})
	];
	
	// Add the settings button for system administrators. (The users who have at least manage permissions on the module)
	if(GO.settings.modules.dropbox.permission_level >= GO.permissionLevels.manage){
		
		config.items.push(
			new Ext.form.FieldSet({
				bodyStyle:'padding:5px',
				items:[
					new GO.form.HtmlComponent({
						style:'margin-bottom:5px',
						html:GO.dropbox.lang.adminsettingsDescription
					}),
					new Ext.Button({
						text:GO.dropbox.lang.settings,
						handler:function(){
							if(!this.dropBoxSettingsDialog){
								this.dropBoxSettingsDialog = new GO.dropbox.SettingsDialog();
							}
							this.dropBoxSettingsDialog.show();
						},
						scope:this
					})
				]
			})
		);
		
	}
	
	GO.dropbox.SettingsPanel.superclass.constructor.call(this, config);
};

Ext.extend(GO.dropbox.SettingsPanel, Ext.Panel, {
	onLoadSettings : function(action) {		
		// If (dis)connected disable the connec/disconnect button
	}
});

GO.mainLayout.onReady(function() {
	GO.moduleManager.addSettingsPanel('dropbox',GO.dropbox.SettingsPanel);
});