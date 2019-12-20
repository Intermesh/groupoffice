GO.email.FindContactDialog = function(config) {

	if (!config) {
		config = {};
	}
    
	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=600;
	config.height=400;
	config.closeAction='hide';
	config.title=t("Contacts", "addressbook");

	this.contactsGrid = new GO.email.ContactsGrid({
		singleSelect:true,
		store: new GO.data.JsonStore({
		  url : GO.url("addressbook/contact/store"),
		  id : 'wemail',
		  fields : ['id', 'name',  'email', 'ab_name', 'company_name', "function","department"],
		  remoteSort : true
		})
	});
	config.items=[this.contactsGrid];


	GO.email.FindContactDialog.superclass.constructor.call(this, config);

	this.contactsGrid.on('rowdblclick', function(grid, rowIndex)
	{
		var record = grid.getStore().getAt(rowIndex);
		this.mergeEmail(record.data.id);
	}, this);

	this.addEvents({
		'email_merged' : true
	});

}
Ext.extend(GO.email.FindContactDialog, go.Window, {

	email : '',
	replace_email : '',
	contact_id : 0,
    
	show : function(record) {
	
		if (!this.rendered) {
			this.render(Ext.getBody());
		}

		this.email = record.email;

		if(record.first_name != record.email)
		{
			var query = record.first_name + ' ' + record.middle_name + ' ' + record.last_name;
			this.contactsGrid.contactsSearchField.setValue(query);
			this.contactsGrid.store.baseParams.query=query;
		}
	
		this.contactsGrid.store.reload();
			
		GO.email.FindContactDialog.superclass.show.call(this);
	},

	mergeEmail : function(contact_id)
	{
		if(contact_id)
		{
			this.contact_id = contact_id;
		}
	
		Ext.Ajax.request({
	    url: GO.url('addressbook/contact/mergeEmailWithContact'),
			params: {
				contact_id: this.contact_id,
				email: this.email,
				replace_email: this.replace_email
			},
			callback: function(options, success, response)
			{
				var data = Ext.decode(response.responseText);

				this.replace_email = '';

				if(data.success)
				{
					if(data.addresses && data.contact_name)
					{
						this.showReplaceDialog(data.addresses, data.contact_name);
					}else
					{
						alert(t("Email address succesfully added to contact", "addressbook"));
						this.fireEvent('email_merged',contact_id);
						this.hide();
					}
				} else if(data.feedback) {
					alert(data.feedback);
				}
			},
			scope: this
		});
	},

	showReplaceDialog : function(addresses, contact_name)
	{
		if(!GO.email.replaceEmailDialog)
		{
			GO.email.replaceEmailDialog = new GO.email.ReplaceEmailDialog();

			GO.email.replaceEmailDialog.on('replace', function(e, email)
			{	
				this.replace_email = email;
		
				this.mergeEmail();
			},this)
		}
	
		GO.email.replaceEmailDialog.store.loadData(
		{
			addresses : addresses
		});

		GO.email.replaceEmailDialog.setTitle(t("Contact", "addressbook") + ': '+ contact_name);

		GO.email.replaceEmailDialog.show();	
	}

});
