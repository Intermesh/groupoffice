GO.customfields.ImportDialog = Ext.extend(Ext.Window, {

	initComponent : function(){

		this.title=GO.lang.cmdImport;

		this.width=500;
		this.autoHeight=true;

		this.closeAction='hide';

		this.uploadFile = new GO.form.UploadFile({
			inputName : 'importfile',
			max:1
		});

		this.upForm = new Ext.form.FormPanel({
			fileUpload:true,
			waitMsgTarget:true,
			baseParams:{field_id:0},
			items: [new GO.form.HtmlComponent({
				html: this.importText+'<br /><br />'
			}),
			this.uploadFile],
			cls: 'go-form-panel'
		});

		this.items=[

		this.upForm];

		this.buttons=[
		{
			text:GO.lang.cmdOk,
			handler: this.uploadHandler,
			scope: this
		},
		{
			text:GO.lang['cmdClose'],
			handler: function(){this.hide()},
			scope: this
		}];

		this.addEvents({'importSelectOptions': true});

		GO.customfields.ImportDialog.superclass.initComponent.call(this);
	},
	uploadHandler : function(){
		this.upForm.form.submit({
			waitMsg:GO.lang.waitMsgUpload,
			url:this.task  == 'treeselect_import' ? GO.url('customfields/field/importTreeSelectOptions') : GO.url('customfields/field/importSelectOptions'),
			success:function(form, action){
				this.uploadFile.clearQueue();
				this.hide();

				this.fireEvent('importSelectOptions');
			},
			failure: function(form, action) {
				if(action.failureType == 'client')
				{
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);
				} else {

					var fb = action.result.feedback.replace(/BR/g,'<br />');

					Ext.MessageBox.alert(GO.lang['strError'], fb);
				}
			},
			scope: this
		});
	}
});

