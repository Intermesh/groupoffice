GO.customcss.MainPanel = function(config){
	config = config || {};

	config.items=[{
		xtype:'textarea',
		fieldLabel:'CSS',
		name:'css',
		anchor:'100% 50%',
		plugins: new GO.plugins.InsertAtCursorTextareaPlugin()
	},{
		xtype:'textarea',
		fieldLabel:'Javascript',
		name:'javascript',
		anchor:'100% 50%'
	}];

	config.labelAlign='top';
	config.waitMsgTarget=true;
	
	config.border= false;

	config.tbar=new Ext.Toolbar({
		cls:'go-head-tb',
		items:[{
		        xtype:'htmlcomponent',
		        html:GO.customcss.lang.name,
		        cls:'go-module-title-tbar'
		},{
				iconCls:'btn-save',
				text:GO.lang.cmdSave,
				handler:function(){
					this.form.submit({
						url:GO.url('customcss/customcss/data'),
						waitMsg:GO.lang['waitMsgSave'],
						callback:function(){

						}
					});
				},
				scope:this
		},{
			iconCls: 'btn-files',
			text:GO.customcss.lang.selectFile,
			handler:function(){

				GO.files.createSelectFileBrowser();

				GO.selectFileBrowser.setFileClickHandler(function(r){
					this.form.findField('css').insertAtCursor(GO.settings.config.full_url+'index.php?r=files/file/download&id='+r.data.id);
//					this.form.findField('css').insertAtCursor(GO.settings.modules.files.url+'download.php?id='+r.data.id);
					GO.selectFileBrowserWindow.hide();
				}, this);

				GO.selectFileBrowser.setFilesFilter(this.filesFilter);
				GO.selectFileBrowser.setRootID(GO.customcss.filesFolderId, GO.customcss.filesFolderId);
				GO.selectFileBrowserWindow.show();

			},
			scope:this
		}]
	});

	GO.customcss.MainPanel.superclass.constructor.call(this,config);
}

Ext.extend(GO.customcss.MainPanel, Ext.form.FormPanel, {
	afterRender : function(){
		GO.customcss.MainPanel.superclass.afterRender.call(this);
		this.form.load({
			url:GO.url('customcss/customcss/data'),
			waitMsg:GO.lang['waitMsgLoad']
		});
	}
	
});


GO.moduleManager.addModule('customcss', GO.customcss.MainPanel, {
	title : GO.customcss.lang.name,
	iconCls : 'go-tab-icon-customcss',
	admin:true
});
