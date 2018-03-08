GO.modules.InstallLicenseDialog = Ext.extend(GO.dialog.TabbedFormDialog, {
	fileUpload: true,
	title: "Install license file",
	height: 200,
	enableApplyButton: false,
	formControllerUrl: 'modules/license',
	submitAction: 'upload',
	loadOnNewModel: false,
	buildForm: function() {



		var uploadPanel = new Ext.Panel({
			layout: 'form',
			items: [{
					xtype: 'htmlcomponent',
					html: 'When you\'ve bought licenses from the App center, you can download your license file from the shop at <a target="_blank" href="https://www.group-office.com/downloads">https://www.group-office.com/downloads</a>. Click on \'Select license file\' to upload the file from your computer. Please wait one minute for the license file to be installed.',
					style:'margin-bottom:15px'
				}, this.uploadFile = new GO.form.UploadFile({
					inputName: 'license_file',
					addText: "Select license file",
					max: '1'
				})]
		});

		this.addPanel(uploadPanel);


	},
	afterSubmit: function() {
		this.uploadFile.clearQueue();
		
		GO.request({
			maskEl:this.getEl(),
			url:'modules/license/install',
			success:function(){
				Ext.MessageBox.alert("Group-Office license installed successfully", "Your license was installed and the new users were automatically added to the App permissions if necessary.\n\nThank you for using Group-Office!");
			}
		});		
		
	}
});