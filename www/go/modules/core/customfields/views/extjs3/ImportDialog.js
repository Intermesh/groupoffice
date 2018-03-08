GO.customfields.ImportDialog = Ext.extend(Ext.Window, {

	initComponent : function(){

		this.title=t("Import");

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
			text:t("Ok"),
			handler: this.uploadHandler,
			scope: this
		},
		{
			text:t("Close"),
			handler: function(){this.hide()},
			scope: this
		}];

		this.addEvents({'importSelectOptions': true});

		GO.customfields.ImportDialog.superclass.initComponent.call(this);
	},
	uploadHandler : function(){
		this.upForm.form.submit({
			waitMsg:t("Uploading..."),
			url:this.task  == 'treeselect_import' ? GO.url('customfields/field/importTreeSelectOptions') : GO.url('customfields/field/importSelectOptions'),
			success:function(form, action){
				this.uploadFile.clearQueue();
				this.hide();

				this.fireEvent('importSelectOptions');
			},
			failure: function(form, action) {
				if(action.failureType == 'client')
				{
					Ext.MessageBox.alert(t("Error"), t("You have errors in your form. The invalid fields are marked."));
				} else {

					var fb = action.result.feedback.replace(/BR/g,'<br />');

					Ext.MessageBox.alert(t("Error"), fb);
				}
			},
			scope: this
		});
	}
});

