GO.customfields.CategoryFormPanel = function(config)
{
	if(!config)
	{
		config = {};
	}

	GO.customfields.CategoryFormPanel.superclass.constructor.call(this, config);
}

Ext.extend(GO.customfields.CategoryFormPanel, Ext.Panel,{

	setSelectedFileIds : function(selected_file_ids) {
		if (typeof(selected_file_ids)=='undefined')
			this.selected_file_ids = new Array();
		else
			this.selected_file_ids = selected_file_ids;
	},

	submitForm : function() {
		this.form.submit({
			url:GO.settings.modules.customfields.url+'action.php',
			params: {
				'task' : 'bulk_edit',
				'selected_file_ids' : Ext.encode(this.selected_file_ids)
			},
			waitMsg:t("Saving..."),
			success:function(form, action){
				Ext.Msg.alert(t("Success", "customfields"), t("The changes have been applied to the customfields of the selected files.", "customfields"))
			},
			failure: function(form, action) {
				var error = '';
				if(action.failureType=='client')
				{
					error = t("You have errors in your form. The invalid fields are marked.");
				}else
				{
					error = action.result.feedback;
				}

				Ext.MessageBox.alert(t("Error"), error);
			},
			scope:this

		});
	}
});
