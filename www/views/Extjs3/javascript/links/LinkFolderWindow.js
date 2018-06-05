/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LinkFolderWindow.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

 
GO.LinkFolderWindow = function(config){
	if(!config)
	{
		config={};
	}
	var focusName = function(){
		this.newFolderNameField.focus(true);		
	};
		
	this.newFolderNameField = new Ext.form.TextField({	             	
        fieldLabel: t("Name"),
        name: 'name',
        value: t("New folder"),
        allowBlank:false,
        anchor:'100%'   
    });
	this.formPanel = new Ext.form.FormPanel({
			defaultType: 'textfield',
			labelWidth:75,
			cls:'go-form-panel',
			waitMsgTarget:true,
			items:this.newFolderNameField,
			autoHeight:true	,
			baseParams:{task : 'link_folder', folder_id : 0}
		});
	
	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=400;
	config.autHeight=true;
	config.closeAction='hide';
	config.title= t("New folder");
	config.items= this.formPanel;
	config.focus= focusName.createDelegate(this);
	config.buttons=[{
			text: t("Ok"),
			handler: function(){
				this.submitForm(true);
			},
			scope: this
		},{
			text: t("Apply"),
			handler: function(){
				this.submitForm();
			},
			scope:this
		},{
			text: t("Close"),
			handler: function(){
				this.hide();
			},
			scope:this
		}					
	];
	
	GO.LinkFolderWindow.superclass.constructor.call(this, config);
	
	this.addEvents({'save' : true});	
}

Ext.extend(GO.LinkFolderWindow, GO.Window,{

	
	show : function (config) {
		
		if(!this.rendered)
			this.render(Ext.getBody());
		
		if(!config.folder_id)
		{
			config.folder_id=0
		}
		this.model_id=config.model_id ? config.model_id : 0;
		this.model_name=config.model_name ? config.model_name : 0;
		this.parent_id=config.parent_id ? config.parent_id : 0;
			
		this.setLinkFolderId(config.folder_id);
		
		if(this.folder_id>0)
		{
			this.formPanel.load({
				url : GO.url("linkFolder/load"),
				
				success:function(form, action)
				{		
					this.setLinkFolderId (action.result.data.id);
					this.model_id=action.result.data.model_id;
					this.model_name=action.result.data.model_name;
					this.parent_id=action.result.data.parent_id;
					GO.LinkFolderWindow.superclass.show.call(this);
				},
				failure:function(form, action)
				{
					GO.errorDialog.show(action.result.feedback)
				},
				scope: this
				
			});
		}else 
		{
			
			this.formPanel.form.reset();
			
			GO.LinkFolderWindow.superclass.show.call(this);
		}
	},
	
	setLinkFolderId : function(folder_id)
	{
		if(!folder_id)
		{
			folder_id = 0;
		} 
		this.formPanel.form.baseParams['folder_id']=folder_id;
		this.folder_id=folder_id;		
	},
	
	submitForm : function(hide){
		this.formPanel.form.submit(
		{
			url:GO.url("linkFolder/submit"),
			params: {
				model_id : this.model_id,
				model_name : this.model_name,
				parent_id : this.parent_id
				},
			waitMsg:t("Saving..."),
			success:function(form, action){
				
				this.fireEvent('save', this);
				
				if(hide)
				{
					this.hide();	
				}else
				{				
					if(action.result.folder_id)
					{
						this.setLinkFolderId(action.result.folder_id);
					}
				}					
			},		
			failure: function(form, action) {
				if(action.failureType == 'client')
				{					
					Ext.MessageBox.alert(t("Error"), t("You have errors in your form. The invalid fields are marked."));			
				} else {
					Ext.MessageBox.alert(t("Error"), action.result.feedback);
				}
			},
			scope: this
		});
		
	}
});
