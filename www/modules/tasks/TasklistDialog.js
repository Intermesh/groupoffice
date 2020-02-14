GO.tasks.TasklistDialog = function(config)
{
	if(!config)
	{
		config = {};
	}
	
	this.propertiesTab = new Ext.form.FormPanel({
		waitMsgTarget:true,
		url: GO.settings.modules.tasks.url+'action.php',
		//url:GO.url('tasks/tasklist/submit'),
		title:t("Properties"),
		layout:'form',
		anchor: '100% 100%',
		defaultType: 'textfield',
		autoHeight:true,
		cls:'go-form-panel',waitMsgTarget:true,
		labelWidth: 75,
   
		items: [
		this.selectUser = new GO.form.SelectUser({
			fieldLabel: t("User"),
			disabled : !GO.settings.has_admin_permission,
			value: GO.settings.user_id,
			anchor: '100%'
		}),
		{
			fieldLabel: t("Name"),
			name: 'name',
			allowBlank:false,
			anchor: '100%'
		},this.exportButton = new Ext.Button({			
				text:t("Export"),
				disabled:true,
				handler:function(){
					go.util.downloadFile(GO.url('tasks/task/exportIcs', {"tasklist_id":this.tasklist_id}));
				},
				scope:this
			}),
			this.deleteAllItemsButton = new Ext.Button({
				style:'margin-top:10px',
				xtype:'button',
				text:t("Delete all items"),
				handler:function(){
					Ext.Msg.show({
						title: t("Delete all items"),
						icon: Ext.MessageBox.WARNING,
						msg: t("Are you sure you want to delete all items?"),
						buttons: Ext.Msg.YESNO,
						scope:this,
						fn: function(btn) {
							if (btn=='yes') {
								GO.request({
									timeout:300000,
									maskEl:Ext.getBody(),
									url:'tasks/tasklist/truncate',
									params:{
										tasklist_id:this.tasklist_id
									},
									scope:this
								});
							}
						}
					});
				},
				scope:this
			}),
			this.removeDuplicatesButton =new Ext.Button({
				style:'margin-top:10px',
				xtype:'button',
				text:t("Remove duplicates"),
				handler:function(){
					
					window.open(GO.url('tasks/tasklist/removeDuplicates',{tasklist_id:this.tasklist_id}))
					
				},
				scope:this
			})
		]
	});


	this.readPermissionsTab = new GO.grid.PermissionsPanel({
		
	});


	var uploadFile = new GO.form.UploadFile({
		inputName : 'ical_file',	   
		max:1 			
	});
	
	uploadFile.on('filesChanged', function(input, inputs){
		this.importButton.setDisabled(inputs.getCount()==1);
	}, this);
	

	this.importTab = new Ext.form.FormPanel({
		fileUpload:true,
		waitMsgTarget:true,
		disabled:true,
		title:t("Import"),
		items: [{
			xtype: 'panel',
			html: t("Select an icalendar (*.ics) file", "tasks"),
			border:false	
		},uploadFile,this.importButton = new Ext.Button({
				xtype:'button',
				disabled:true,
				text:t("Import"),
				handler: function(){						
					this.importTab.form.submit({
						//waitMsg:t("Uploading..."),
						// TODO: Fix this import so it works with the new MVC structure
						url: GO.url('tasks/tasklist/importIcs'),//O.settings.modules.tasks.url+'action.php',
						params: {
//							task: 'import',
							tasklist_id:this.tasklist_id
						},
						success: function(form,action)
						{				
							uploadFile.clearQueue();		

							if(action.result.success)
							{
								Ext.MessageBox.alert(t("Success"),action.result.feedback);
							}else
							{
								Ext.MessageBox.alert(t("Error"),action.result.feedback);
							}						
						},
						failure: function(form, action) {
							Ext.MessageBox.alert(t("Error"), action.result.feedback);
						},
						scope: this
					});
				}, 
				scope: this
			})],
		cls: 'go-form-panel'
	});

	this.tabPanel = new Ext.TabPanel({
			hideLabel:true,
			deferredRender:false,
			xtype:'tabpanel',
			activeTab: 0,
			border:false,
			anchor: '100% 100%',
			items:[
			this.propertiesTab,
			this.readPermissionsTab,
			this.importTab 
			]
		});
	
	
	GO.tasks.TasklistDialog.superclass.constructor.call(this,{
		title: t("Tasklist", "tasks"),
		layout:'fit',
		modal:false,
		height:600,
		width:440,
		closeAction:'hide',
		items: this.tabPanel,
		buttons:[{
			text:t("Apply"),
			handler: function(){this.save(false)},
			scope: this
		},{
			text:t("Save"),
			handler: function(){this.save(true)},
			scope: this
		}]
	});
}

Ext.extend(GO.tasks.TasklistDialog, Ext.Window, {
	
	initComponent : function(){
		
		this.addEvents({'save' : true});
		
		GO.tasks.TasklistDialog.superclass.initComponent.call(this);
		
		
	},
				
	show : function (tasklist_id){		
		if(!this.rendered)
			this.render(Ext.getBody());
			
		this.propertiesTab.show();
		
		
		this.removeDuplicatesButton.setDisabled(!tasklist_id);
		this.deleteAllItemsButton.setDisabled(!tasklist_id);
			
		if(tasklist_id > 0)
		{
			if(tasklist_id!=this.tasklist_id)
			{
				this.loadTasklist(tasklist_id);
			}else
			{
				GO.tasks.TasklistDialog.superclass.show.call(this);
			}
		}else
		{
			this.tasklist_id=0;
			this.propertiesTab.form.reset();

			this.readPermissionsTab.setDisabled(true);

			this.exportButton.setDisabled(true);
			this.importTab.setDisabled(true);
		

			GO.tasks.TasklistDialog.superclass.show.call(this);
		}
	},
	loadTasklist : function(tasklist_id)
	{
		this.propertiesTab.form.load({
			url: GO.url('tasks/tasklist/load'),
			params: {
				id:tasklist_id
			},
			
			success: function(form, action) {
				this.tasklist_id=action.result.data.id;
				this.selectUser.setRemoteText(action.result.remoteComboTexts.user_name);
				this.readPermissionsTab.setAcl(action.result.data.acl_id);
				
				this.exportButton.setDisabled(false);
				this.importTab.setDisabled(false);
				GO.tasks.TasklistDialog.superclass.show.call(this);
			},
			failure:function(form, action)
			{
				GO.errorDialog.show(action.result.feedback)
			},
			scope: this
		});
	},
	save : function(hide)
	{
		this.propertiesTab.form.submit({
				
			//url:GO.settings.modules.tasks.url+'action.php',
			url: GO.url('tasks/tasklist/submit'),
			params: {
			//		'task' : 'save_tasklist', 
					'id': this.tasklist_id
			},
			waitMsg:t("Saving..."),
			success:function(form, action){
										
				if(action.result.id)
				{
					this.tasklist_id=action.result.id;
					this.readPermissionsTab.setAcl(action.result.acl_id);
					
					this.exportButton.setDisabled(false);
					this.importTab.setDisabled(false);
				}
				
				this.fireEvent('save');
				
				if(hide)
				{
					this.hide();
				}
					
					
			},

			failure: function(form, action) {
				var error = '';
				if(action.failureType=='client')
				{
					error = t("You have errors in your form. The invalid fields are marked.");
				}else
				{
					error = action.result.feedback;
				}
					
				Ext.MessageBox.alert(t("Error"), error);
			},
			scope:this

		});
			
	}
});
