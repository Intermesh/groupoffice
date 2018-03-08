/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CategoryDialog.js
 * @copyright Copyright Intermesh
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */
 
GO.cms.CategoryDialog = function(config){
	
	if(!config)
	{
		config={};
	}
	
	
	this.buildForm();
	
	
	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=500;
	config.height=150;
	config.closeAction='hide';
	config.title= GO.cms.lang.category;					
	config.items= this.formPanel;
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
	
	GO.cms.CategoryDialog.superclass.constructor.call(this, config);
	
	this.addEvents({
		'save' : true
	});
}
Ext.extend(GO.cms.CategoryDialog, Ext.Window,{
	
	show : function (attributes) {
		if(!this.rendered)
		{
			this.render(Ext.getBody());
		}

		this.setCategoryId(attributes.id);

		this.nameField.setValue(attributes.text);
		this.parentNameField.setValue(attributes.parentName);

		GO.cms.CategoryDialog.superclass.show.call(this);

	},
	
	submitForm : function(hide){
		this.formPanel.form.submit(
		{
			url:GO.settings.modules.cms.url+'action.php',
			params: {
				'task' : 'update_category_name',
				'id' : this.categoryId
			},
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action){
				this.fireEvent('save', this);				
				if(hide)
				{
					this.hide();	
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
	
	setCategoryId : function(id) {
		this.categoryId = id;
	},
	
	buildForm : function () {
		this.formPanel = new Ext.form.FormPanel({
			cls:'go-form-panel',
			waitMsgTarget:true,
			layout:'form',
			autoScroll:false,
			url: GO.settings.modules.cms.url+'action.php',
			border: false,
			baseParams: {
				task: 'update_category'
			},
			items:[
				this.nameField = new Ext.form.TextField({
					name: 'name',
					anchor: '-20',
					allowBlank:false,
					fieldLabel: GO.lang.strName
				}),this.parentNameField = new GO.form.PlainField({
					name: 'parent_name',
					anchor: '-20',
					allowBlank:false,
					fieldLabel: GO.cms.lang.parentCategory
				})
			]
		});
	}
});