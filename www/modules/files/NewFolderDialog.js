/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: NewFolderDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.files.NewFolderDialog = function(config){	
	if(!config)
	{
		config={};
	}
	
	this.newFolderNameField = new Ext.form.TextField({	              	
		fieldLabel: t("Name"),
		name: 'name',
		value: t("New folder", "files"),
		allowBlank:false,
		anchor:'100%',
		validator:function(v){
			return !v.match(/[&\/:\*\?"<>|\\]/);
		}   
	});
	this.newFolderFormPanel = new Ext.form.FormPanel({
			
		baseParams:{
			parent_id:0
		},
		defaultType: 'textfield',
		labelWidth:75,
		autoHeight:true,
		cls:'go-form-panel',
		waitMsgTarget:true,
		items:this.newFolderNameField,
		keys:[{
			key: Ext.EventObject.ENTER,
			fn: function(key, e){
				this.submitForm();
			},
			scope:this
		}]
	});
	
	var focusName = function(){
		this.newFolderNameField.focus(true);		
	};
	config.collapsible=false;
	config.maximizable=false;
	config.modal=true;
	config.resizable=false;
	config.width=dp(500);
	config.items=this.newFolderFormPanel;
	config.height=dp(160);
	config.closeAction='hide';
	config.focus=focusName.createDelegate(this);
	config.title= t("Add folder", "files");		
	config.buttons= [{
		text: t("Ok"),
		handler: function(){	
			this.submitForm();						
		},
		scope:this
	},
	{
		text: t("Close"),
		handler: function(){
			this.hide();
		},
		scope: this
	}];
				
	GO.files.NewFolderDialog.superclass.constructor.call(this, config);
	
	this.addEvents({
		save:true
	});
}
Ext.extend(GO.files.NewFolderDialog, go.Window,{
	
	submitForm : function(){
		this.newFolderFormPanel.form.submit({
										
			url: GO.url('files/folder/submit'),
			waitMsg:t("Saving..."),
			success:function(form, action){								
				this.fireEvent('save', action.result);															
				this.hide();
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
						
	},
	
	show : function (parent_id) {
		if(!this.rendered)
		{
			this.render(Ext.getBody());
		}
		this.newFolderFormPanel.baseParams.parent_id=parent_id;
		this.newFolderFormPanel.form.reset();
		
		GO.files.NewFolderDialog.superclass.show.call(this);
	}
});
