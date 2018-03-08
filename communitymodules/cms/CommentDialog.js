/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CommentDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.CommentDialog = function(config){
	
	
	if(!config)
	{
		config={};
	}
	
	
	this.buildForm();
	
	var focusFirstField = function(){
		this.propertiesPanel.items.items[0].focus();
	};
	
	
	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=700;
	config.height=500;
	config.closeAction='hide';
	config.title= GO.cms.lang.comment;					
	config.items= this.formPanel;
	config.focus= focusFirstField.createDelegate(this);
	config.buttons=[{
			text: GO.lang['cmdOk'],
			handler: function(){
				this.submitForm(true);
			},
			scope: this
		},{
			text: GO.lang['cmdApply'],
			handler: function(){
				this.submitForm();
			},
			scope:this
		},{
			text: GO.lang['cmdClose'],
			handler: function(){
				this.hide();
			},
			scope:this
		}					
	];
	
	GO.cms.CommentDialog.superclass.constructor.call(this, config);
	this.addEvents({'save' : true});	
}
Ext.extend(GO.cms.CommentDialog, Ext.Window,{
	
	show : function (comment_id) {
		if(!this.rendered)
		{
			this.render(Ext.getBody());
		}
		
		this.tabPanel.setActiveTab(0);
		
		
		
		if(!comment_id)
		{
			comment_id=0;			
		}
			
		this.setCommentId(comment_id);
		
		if(this.comment_id>0)
		{
			this.formPanel.load({
				url : GO.settings.modules.cms.url+'json.php',
				
				success:function(form, action)
				{
					
					
						
					
					this.selectUser.setRemoteText(action.result.data.user_name);
									
					
					GO.cms.CommentDialog.superclass.show.call(this);
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
			
			
				
			
			
			GO.cms.CommentDialog.superclass.show.call(this);
		}
	},
	
	
		
		
	
	
	setCommentId : function(comment_id)
	{
		this.formPanel.form.baseParams['comment_id']=comment_id;
		this.comment_id=comment_id;
		
	},
	
	submitForm : function(hide){
		this.formPanel.form.submit(
		{
			url:GO.settings.modules.cms.url+'action.php',
			params: {'task' : 'save_comment'},
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action){
				
				this.fireEvent('save', this);
				
				if(hide)
				{
					this.hide();	
				}else
				{
				
					if(action.result.comment_id)
					{
						this.setCommentId(action.result.comment_id);
						
						
											
					}
				}
				
									
			},		
			failure: function(form, action) {
				if(action.failureType == 'client')
				{					
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);			
				} else {
					Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
				}
			},
			scope: this
		});
		
	},
	
	
	buildForm : function () {
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',waitMsgTarget:true,			
			layout:'form',
			autoScroll:true,
			items:[{
				xtype: 'textfield',
			  name: 'file_id',
				anchor: '-20',
			  allowBlank:false,
			  fieldLabel: GO.cms.lang.fileId
			},this.selectUser = new GO.form.SelectUser({
				fieldLabel: GO.lang['strUser'],
				disabled: !GO.settings.modules['cms']['write_permission'],
				value: GO.settings.user_id,
				anchor: '-20'
			}),{
				xtype: 'textfield',
			  name: 'name',
				anchor: '-20',
			  allowBlank:false,
			  fieldLabel: GO.lang.strName
			},{
				xtype: 'textarea',
			  name: 'comments',
				anchor: '-20',
			  allowBlank:true,
			  fieldLabel: GO.cms.lang.comments
			}
]
				
		});
		var items  = [this.propertiesPanel];
		
    
    
    
		
		
		
 
    this.tabPanel = new Ext.TabPanel({
      activeTab: 0,      
      deferredRender: false,
    	border: false,
      items: items,
      anchor: '100% 100%'
    }) ;    
    
    
    this.formPanel = new Ext.form.FormPanel({
    	waitMsgTarget:true,
			url: GO.settings.modules.cms.url+'action.php',
			border: false,
			baseParams: {task: 'comment'},				
			items:this.tabPanel				
		});
    
    
	}
});