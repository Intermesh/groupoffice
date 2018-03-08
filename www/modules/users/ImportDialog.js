GO.users.ImportDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'importUsers',
			title:t("Import"),
			formControllerUrl: 'users/user',
			submitAction: 'import',
			loadOnNewModel: false,
			fileUpload : true
		});
		
		GO.users.ImportDialog.superclass.initComponent.call(this);	
	},

	submitForm : function(hide){
		if(this.importPanel.fileSelector.inputs.items.length == 1)
			this.hide();
		else
			GO.users.ImportDialog.superclass.submitForm.call(this,hide);
	},
	
	buildForm : function () {
		
		this.importPanel = new GO.base.model.ImportPanel({
			filetypes:[
				['csv','CSV (Comma Separated Values)']
			],
			controllers:[
				['GO\\Users\\Controller\\UserController',t("User")]
			]
//			,
//			importBaseParams:[
//				{}
//			]
		});
		
		this.updateExisting = new Ext.ux.form.XCheckbox({
			fieldLabel: t("Update existing users by username", "users"),
			name: 'updateExisting'
		});
		
		this.importPanel.add(this.updateExisting);
		
		this.exampleButton = new Ext.Button({
			text:t("Download sample CSV", "users"),
			width: 200,
			fieldLabel: t("Download sample CSV", "users"),
			handler: function(){
				window.open(GO.url('users/user/getImportExample'));
			},
			scope:this			
		});

		this.importPanel.add(this.exampleButton);
		
		this.addPanel(this.importPanel);
	}
});


//
//GO.users.ImportDialog = Ext.extend(Ext.Window, {
//	
//	initComponent : function(){
//		
//		this.title=t("Import");
//		
//		this.width=500;
//		this.autoHeight=true;
//		
//		this.closeAction='hide';
//		
//		this.uploadFile = new GO.form.UploadFile({
//			inputName : 'importfile',
//			max:1  				
//		});				
//		
//		this.upForm = new Ext.form.FormPanel({
//			fileUpload:true,
//			waitMsgTarget:true,
//			items: [new GO.form.HtmlComponent({
//				html: t("You can import users using a CSV file. To know how the CSV file should be formatted, download the sample file.<br />The first line must contain the column names. The following fields are required for each user:<br /><br />username, password, first_name, last_name, email", "users")+'<br /><br />'
//			}),
//			this.uploadFile],
//			cls: 'go-form-panel'
//		});
//		
//		
//		
//		this.items=[
//		
//		this.upForm];
//		
//		this.buttons=[
//		{
//			text:t("Ok"),
//			handler: this.uploadHandler, 
//			scope: this
//		},
//		{
//			text:t("Close"),
//			handler: function(){this.hide()}, 
//			scope: this
//		},{
//			text:t("Download sample CSV", "users"),
//			handler: function(){
//				window.open(GO.url('users/user/getImportExample'));
//			},
//			scope:this			
//		}];
//		
//		this.addEvents({'import': true});
//		
//		GO.users.ImportDialog.superclass.initComponent.call(this);
//	},
//	uploadHandler : function(){
//		this.upForm.form.submit({
//			waitMsg:t("Uploading..."),
//			url:GO.settings.modules.users.url+'action.php',
//			params: {
//			  task: 'import'	
//			},
//			success:function(form, action){
//				this.uploadFile.clearQueue();						
//				this.hide();
//				
//				this.fireEvent('import');
//				
//				var fb = action.result.feedback.replace(/BR/g,'<br />');
//				
//				Ext.MessageBox.alert(t("Success"), fb);
//			},
//			failure: function(form, action) {	
//				if(action.failureType == 'client')
//				{					
//					Ext.MessageBox.alert(t("Error"), t("You have errors in your form. The invalid fields are marked."));			
//				} else {
//					
//					var fb = action.result.feedback.replace(/BR/g,'<br />');
//					
//					Ext.MessageBox.alert(t("Error"), fb);
//				}
//			},
//			scope: this
//		});			
//	}
//});

