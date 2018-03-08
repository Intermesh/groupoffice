GO.dialog.ExportDialog = Ext.extend(GO.Window,{
	
	exportController : "",
	
	initComponent : function(){

		Ext.applyIf(this,{
			modal:false,			
			height: 500,
			width: 500,		
			layout:'border',
			closeAction:'hide',
			buttons: [
				{				
					text: GO.lang['cmdOk'],
					handler: function(){this.submitForm()},
					scope:this
				},{				
					text: GO.lang['cmdClose'],
					handler: function(){this.hide()},
					scope:this
				}
			]
    });
		
		if(!this.formConfig)
			this.formConfig = {};
		
		Ext.applyIf(this.formConfig, {
			region:'north',
			standardSubmit:true,
			cls:'go-form-panel',
			autoHeight:true,
			//height:this.formHeight ? this.formHeight : 200,
			url:GO.url(this.exportController+'/export'),
			items:this.formItems
		});
		
		this.formPanel = new Ext.form.FormPanel(this.formConfig);
		
		
		this.attributesField = new Ext.form.Hidden({
			name:'attributes'
		});
		
		this.formPanel.add(this.attributesField);
		
		
		this.attributesPanel= new GO.grid.MultiSelectGrid({			
			region:'center',
			loadMask:true,	
			store:new GO.data.JsonStore({
				url:GO.url(this.exportController+'/attributes'),
				fields:['id','name','checked'],
				autoLoad:true
			})
		});
		
		
		this.items=[
			this.formPanel,
			this.attributesPanel
		]
		 
		GO.dialog.ExportDialog.superclass.initComponent.call(this);
	},
	
//	show : function(){
//		
//		
//		GO.dialog.ExportDialog.superclass.show.call(this);
//	},
	
	submitForm : function(){
		this.formPanel.form.getEl().dom.target='_blank';
		
		this.attributesField.setValue(this.attributesPanel.getSelected().join(','));

		this.formPanel.form.submit(
		{
			failure: function(form, action) {
				if(action.failureType == 'client')			
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);			
			 
			},
			scope: this
		});			
	}
	
});