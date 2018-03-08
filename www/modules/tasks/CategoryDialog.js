GO.tasks.CategoryDialog = function(config){

	if(!config)
	{
		config = {};
	}

	this.buildForm();

	var focusFirstField = function(){
		this.formPanel.items.items[0].focus();
	};
    
	config.layout='fit';
	config.title=GO.tasks.lang.category;
	config.modal=false;
	config.border=false;
	config.width=400;
	config.autoHeight=true;
	config.resizable=false;
	config.plain=true;
	config.shadow=false,
	config.closeAction='hide';
	config.items=this.formPanel;
	config.focus=focusFirstField.createDelegate(this);
	config.buttons=[{
		text:GO.lang['cmdOk'],
		handler: function()
		{
			this.submitForm(true)
		},
		scope: this
//	},{
//		text:GO.lang['cmdApply'],
//		handler: function()
//		{
//			this.submitForm(false)
//		},
//		scope: this
	},{
		text:GO.lang['cmdClose'],
		handler: function()
		{
			this.hide()
		},
		scope: this
	}];
		
	GO.tasks.CategoryDialog.superclass.constructor.call(this,config);
	
	this.addEvents({'save' : true});
}

Ext.extend(GO.tasks.CategoryDialog, Ext.Window, {
	
	show : function (record)
	{		
		if(!this.rendered)
			this.render(Ext.getBody());
			
		if(record)
		{
			this.category_id=record.data.id;
			
		}else
		{
			this.category_id=0;
		}
		
		this.formPanel.form.baseParams['id'] = this.category_id;
		
		if(this.category_id > 0)
		{
			this.formPanel.form.findField('name').setValue(record.data.name);
			if(GO.settings.has_admin_permission)
			{
				this.formPanel.form.findField('global').setValue(record.data.user_id == 0);
			}
		}else
		{
			this.formPanel.form.reset();
		}
                
		GO.tasks.CategoryDialog.superclass.show.call(this);
	},
	submitForm : function(hide)
	{
		this.formPanel.form.submit(
		{		
			//url:GO.settings.modules.tasks.url+'action.php',
			url:GO.url('tasks/category/submit'),
			params: {
//				task:'save_category',
			//	id:this.category_id
			},
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action)
			{
				if(action.result.id)
				{
					this.category_id=action.result.id;
					this.formPanel.form.baseParams['id'] = this.category_id;
				}
			
				this.fireEvent('save');
				
				if(hide)
				{
					this.hide();
				}
			},
			failure: function(form, action) 
			{
				var error = '';
				if(action.failureType=='client')
				{
					error = GO.lang['strErrorsInForm'];
				}
				else
				{
					error = action.result.feedback;
				}
				Ext.MessageBox.alert(GO.lang['strError'], error);
			},
			scope:this
		});		
	},
	buildForm : function () 
	{
		var items = [];
		items.push({
			fieldLabel: GO.lang['strName'],
			name: 'name',
			allowBlank:false
		});
		if(GO.settings.has_admin_permission)
		{
			items.push(this.globalCategory = new Ext.form.Checkbox({
				name:'global',
				boxLabel:GO.tasks.lang.globalCategory,
				hideLabel:true,
				checked:false
			}));
		}
		
		this.formPanel = new Ext.FormPanel({
			cls:'go-form-panel',
			anchor:'100% 100%',
			bodyStyle:'padding:5px',
			defaults:{anchor: '95%'},
			baseParams:{},
			defaultType:'textfield',
			autoHeight:true,
			waitMsgTarget:true,
			labelWidth:75,
			items: items
		});	
	}	
});