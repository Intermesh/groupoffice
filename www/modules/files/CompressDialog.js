GO.files.CompressDialog = Ext.extend(GO.Window,{
	width: 300,
	height:120,
	layout:'fit',
	title:GO.lang.compress,
	focus:function(){
		this.formPanel.form.reset();
		this.formPanel.form.findField('name').focus(true);
	},
	initComponent: function(){
		this.formPanel = new Ext.FormPanel({
			cls:'go-form-panel',
			items:[{
				fieldLabel: GO.files.lang.enterName,
				anchor:'100%',
				xtype:'textfield',
				name: 'name',
				allowBlank:false
			}
//			{
//				xtype:'checkbox',
//				name:'utf8',
//				boxLabel:'Encode filenames for Linux'
//			}
		]
		});
		
		
		this.items=[this.formPanel];
		
		this.buttons=[{
			text:GO.lang.cmdOk,
			handler:function(){
				var f = this.formPanel.form;
				if(f.isValid()){
					this.handler.call(this.scope, this, f.findField('name').getValue());
					this.close();
				}

			},
			scope:this
		}];
	
		GO.files.CompressDialog.superclass.initComponent.call(this);
	}
});