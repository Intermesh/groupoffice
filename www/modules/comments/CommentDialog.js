/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CommentDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.comments.CommentDialog = function(config){
	if(!config)
	{
		config={};
	}
	this.buildForm();
	var focusFirstField = function(){
		this.formPanel.items.items[0].focus();
	};
	config.collapsible=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=630;
	config.autoHeight=true;
	
	config.closeAction='hide';
	config.title= t("Comment", "comments");					
	config.items= this.formPanel;
	config.focus= focusFirstField.createDelegate(this);
	config.buttons=[{
			text: t("Save"),
			handler: function(){
				this.submitForm(true);
			},
			scope: this
		}				
	];
	GO.comments.CommentDialog.superclass.constructor.call(this, config);
	this.addEvents({'save' : true});	
}
Ext.extend(GO.comments.CommentDialog, Ext.Window,{
	show : function (comment_id, config) {
		if(!this.rendered)
		{
			this.render(Ext.getBody());
		}
		if(!comment_id)
		{
			comment_id=0;			
		}
		this.setCommentId(comment_id);
		
		if (!GO.util.empty(config) && !GO.util.empty(config.link_config))
			this.toggleActionDate(config.link_config['model_name']);
		else if (!GO.util.empty(config) && !GO.util.empty(config['model_name']))
			this.toggleActionDate(config['model_name']);
		
		delete this.link_config;
		
		if(this.comment_id>0)
		{
			this.formPanel.load({
				url : GO.url('comments/comment/load'),
				waitMsg:t("Loading..."),
				success:function(form, action)
				{
					GO.comments.CommentDialog.superclass.show.call(this);
					var response = Ext.decode(action.response['responseText']);
					if (response.data['category_id']==0)
						this.categoriesCB.setValue('');
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
			GO.comments.CommentDialog.superclass.show.call(this);
			if (config && config.link_config && !this.actionDateField.disabled) {
				this.actionDateField.setValue(config.link_config['action_date']);
			}
		}
		
		if(config)
		{
			if (config.link_config) {
				this.link_config=config.link_config;

				this.formPanel.baseParams.model_id=config.link_config.model_id;
				this.formPanel.baseParams.model_name=config.link_config.model_name;
			} else {
				if(config.model_name)
					this.formPanel.baseParams.model_name=config.model_name;
				if(config.model_id)
					this.formPanel.baseParams.model_id=config.model_id;
				if(!this.actionDateField.disabled && config.action_date)
					this.actionDateField.setValue(config.action_date);
			}
		}
	},
	toggleActionDate : function(modelName) {
		var withActionDate = modelName == 'GO\\Addressbook\\Model\\Contact';
		this.actionDateField.setDisabled(!withActionDate);
		this.actionDateField.setVisible(withActionDate);
	},
	setCommentId : function(comment_id)
	{
		this.formPanel.form.baseParams['id']=comment_id;
//		this.formPanel.form.baseParams['comment_id']=comment_id;
		this.comment_id=comment_id;
	},
	submitForm : function(hide){
		this.formPanel.form.submit(
		{
			url:GO.url('comments/comment/submit'),
//			params: {'task' : 'save_comment'},
			waitMsg:t("Saving..."),
			success:function(form, action){
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
				
				if(this.link_config && this.link_config.callback)
				{					
					this.link_config.callback.call(this);					
				}
								
				if (!GO.util.empty(this.formPanel.baseParams['model_name']) && this.formPanel.baseParams['model_name']=='GO\\Addressbook\\Model\\Contact' && !GO.util.empty(GO.addressbook.contactsGrid)) {
					GO.addressbook.contactsGrid.store.reload();
				}
				
				this.fireEvent('save', this);				
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
	},
	buildForm : function () {
		    
    this.formPanel = new Ext.form.FormPanel({
	    waitMsgTarget:true,
			url: GO.settings.modules.comments.url+'action.php',
			border: false,
			autoHeight: true,
			cls:'go-form-panel',
			baseParams: {id:0, model_name:''},				
			items:[{
					xtype: 'fieldset',
					items:[{
						xtype: 'textarea',
						name: 'comments',
						anchor: '100%',
						height: 200,
						hideLabel:true
					},
					this.categoriesCB = new GO.comments.CategoriesComboBox(),
					this.actionDateField = new Ext.form.DateField({
						name: 'action_date',
						fieldLabel: t("Action date", "comments"),
						format : GO.settings['date_format'],
						disabled: true
					})
				]
			}]
							
		});
	}
});


GO.comments.showCommentDialog = function(comment_id, config){

	if(!GO.comments.commentDialog)
		GO.comments.commentDialog = new GO.comments.CommentDialog();

	if(GO.comments.commentDialogListeners){
		GO.comments.commentDialog.on(GO.comments.commentDialogListeners);
		delete GO.comments.commentDialogListeners;
	}

	GO.comments.commentDialog.show(comment_id, config);
	
	return GO.comments.commentDialog;
}

GO.comments.browseComments= function (model_id, model_name, action_date)
{
	if(!GO.comments.commentsBrowser)
	{
		GO.comments.commentsBrowser = new GO.comments.CommentsBrowser();
	}
	if(GO.comments.commentDialogListeners){
		GO.comments.commentsBrowser.on(GO.comments.commentDialogListeners);
	}
	
	if (!GO.util.empty(action_date))
		GO.comments.commentsBrowser.show({model_id: model_id, model_name:model_name, action_date: action_date});
	else
		GO.comments.commentsBrowser.show({model_id: model_id, model_name:model_name});
	
	return GO.comments.commentsBrowser;
};

//
//GO.newMenuItems.push({
//	text: t("Comment", "comments"),
//	iconCls: 'go-menu-icon-comments',
//	handler:function(item, e){				
//		GO.comments.showCommentDialog(0, {
//			link_config: item.parentMenu.link_config			
//		});
//	}
//});
