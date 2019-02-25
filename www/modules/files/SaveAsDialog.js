GO.files.SaveAsDialog = Ext.extend(GO.Window, {
	
	handler : function(){
		
	},
	initComponent : function(){
		
		this.layout='border';
		this.title=t("Save file", "files");
		this.height=dp(700);
		this.width=dp(900);
		this.border=false;
		this.collapsible=true;
		this.maximizable=true;
		this.closeAction='hide';
			
		this.buttons=[
				{
					text: t("Ok"),				        						
					handler: function(){

						if(this.nameField.isValid()){
							var files = this.fb.gridStore.getRange();

							for(var i=0;i<files.length;i++)
							{
								if(files[i].data.extension!='folder' && files[i].data.name==this.nameField.getValue())
								{
									var tpl = new Ext.Template(t("{filename} already exists, do you want to overwrite the file?", "files"));

									if(!confirm(tpl.apply({filename: files[i].data.name})))
									{
										return false;
									}
									break;
								}
							}

							this.handler.call(this.scope, this, this.fb.folder_id, this.nameField.getValue());
						}else
						{
							this.nameField.focus();
						}
					}, 
					scope: this 
				},{
					text: t("Close"),				        						
					handler: function(){
						this.hide();
					},
					scope:this
				}				
			];
		
		this.fb = new GO.files.FileBrowser({
			id: 'saveas-filebrowser',
			region:'center',
			border:false,
			loadDelayed:true,
			hideActionButtons:true,
			treeCollapsed:false,
			filePanelCollapsed:true,
			fileClickHandler: function(r){										
			},
			scope: this
		});
				
		this.fb.on('fileselected',function(fb, r){
			if(r.data.extension!='folder')
				this.formPanel.form.findField('filename').setValue(r.data.name);
		}, this);
		
		this.nameField = new Ext.form.TextField({				
				fieldLabel:t("Name"),
				name:'filename',
				anchor:'100%',
				validator:function(v){
					return !v.match(/[&\/:\*\?"<>|\\]/);
				}
			});
			
		var focusField = function(){
			this.nameField.focus(true);
		};
		
		this.focus=focusField.createDelegate(this);
		
		this.formPanel = new Ext.form.FormPanel({
			region:'north',
			cls:'go-form-panel',
			autoHeight: true,
			items:this.nameField
		});
		
		this.items=[this.fb,this.formPanel];
		
		GO.files.SaveAsDialog.superclass.initComponent.call(this);
	},
	
	show : function(config){
		this.nameField.setValue(config.filename.replace(/[&\/:\*\?"<>|\\]/g, ""));
		
		var extension = GO.util.getFileExtension(config.filename);
		
		if(config.folder_id || config.folder_id == 0){
			this.fb.setRootID(config.folder_id, config.folder_id);
		} else
		{
			this.fb.setRootID(0, 0);
		}
		
		this.fb.setFilesFilter(extension);		
		
		if(!config.scope)
			config.scope=this;
		
		if(config.handler)
		{
			this.handler = config.handler.createDelegate(config.scope);
		}
		
		GO.files.SaveAsDialog.superclass.show.call(this);
	}
	
});
