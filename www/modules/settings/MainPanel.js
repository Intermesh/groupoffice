GO.settings.MainPanel = function(config){

	if(!config)
	{
		config = {};
	}
	config.labelWidth=150;
	config.border=false;
	config.padding= 10;
	config.url= GO.url('settings/setting/load');

	config.items={
		xtype:'fieldset',
		labelAlign:'top',
		title:t("Text for login screen", "settings"),
		items:[{
			boxLabel:t("Enable login screen text", "settings"),
			xtype:'checkbox',
			hideLabel:true,			
			name:'login_screen_text_enabled',
			anchor:"100%"
		},{
			fieldLabel:t("Title", "settings"),
			xtype:'textfield',
			hideLabel:true,
			name:'login_screen_text_title',
			anchor:"100%"
		},{
			fieldLabel:t("Text", "settings"),
			xtype:'htmleditor',
			hideLabel:true,
			name:'login_screen_text',
			anchor:"100%",
			height:100
		}]
	}

	config.tbar=new Ext.Toolbar({
		cls:'go-head-tb',
		items: [{
	      	 	xtype:'htmlcomponent',
			html:t("Settings", "settings"),
			cls:'go-module-title-tbar'
		},{
		iconCls: 'btn-save',
		text: t("Save"),
		cls: 'x-btn-text-icon',
		handler: function()
		{
			this.el.mask(t("Loading..."));				
//			this.form.baseParams.save = true;
			this.form.submit({
				url: GO.url('settings/setting/submit'),
				success: function(form,action){
					this.el.unmask();
					if (!GO.util.empty(action.result.feedback))
						Ext.MessageBox.alert('',action.result.feedback);
				},
				failure: function(form,action){
					this.el.unmask();
					Ext.MessageBox.alert(t("Error"),action.result.feedback);
				},
				scope: this
			});
		},
		scope: this
	},{
		iconCls: 'btn-delete',
		text: t("Cancel"),
		cls: 'x-btn-text-icon',
		handler: function()
		{
			this.form.baseParams.save = false;
			this.form.load();
		},
		scope: this
	}]
	});

	GO.settings.MainPanel.superclass.constructor.call(this, config);
};

Ext.extend(GO.settings.MainPanel, Ext.FormPanel, {
	afterRender : function()
	{
		GO.settings.MainPanel.superclass.afterRender.call(this);
		this.form.load();
		this.form.timeout=360;
	}
});

GO.moduleManager.addModule('settings', GO.settings.MainPanel, {
	title : t("Settings", "settings"),
	iconCls : 'go-tab-icon-settings',
	admin :true
});
