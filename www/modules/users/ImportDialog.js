GO.users.ImportDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'importUsers',
			title:GO.lang.cmdImport,
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
				['GO\\Users\\Controller\\UserController',GO.lang.user]
			]
//			,
//			importBaseParams:[
//				{}
//			]
		});
		
		this.updateExisting = new Ext.ux.form.XCheckbox({
			fieldLabel: GO.users.lang.updateExistingOnImport,
			name: 'updateExisting'
		});
		
		this.importPanel.add(this.updateExisting);
		
		this.exampleButton = new Ext.Button({
			text:GO.users.lang.downloadSampleCSV,
			width: 200,
			fieldLabel: GO.users.lang.downloadSampleCSV,
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
//		this.title=GO.lang.cmdImport;
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
//				html: GO.users.lang.importText+'<br /><br />'
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
//			text:GO.lang.cmdOk,
//			handler: this.uploadHandler, 
//			scope: this
//		},
//		{
//			text:GO.lang['cmdClose'],
//			handler: function(){this.hide()}, 
//			scope: this
//		},{
//			text:GO.users.lang.downloadSampleCSV,
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
//			waitMsg:GO.lang.waitMsgUpload,
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
//				Ext.MessageBox.alert(GO.lang.strSuccess, fb);
//			},
//			failure: function(form, action) {	
//				if(action.failureType == 'client')
//				{					
//					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);			
//				} else {
//					
//					var fb = action.result.feedback.replace(/BR/g,'<br />');
//					
//					Ext.MessageBox.alert(GO.lang['strError'], fb);
//				}
//			},
//			scope: this
//		});			
//	}
//});

