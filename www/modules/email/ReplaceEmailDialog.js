GO.email.ReplaceEmailDialog = function(config) {

    if (!config) {
	config = {};
    }

    config.layout='fit';
    config.modal=true;
    config.resizable=false;
    config.width=400;
    config.height=200;
    config.closeAction='hide';
    config.title=t("Contact", "addressbook");
 
    this.store = new GO.data.JsonStore({
	root: 'addresses',
	fields:['name']
    });

    this.list = new GO.grid.SimpleSelectList({
	store: this.store
    });

    this.list.on('click', function(dataview, index)
    {
	var record = dataview.store.data.items[index];

	this.fireEvent('replace', this, record.data.name);
	this.hide();
    }, this);
    
    config.items= new Ext.Panel({
	autoScroll:true,
	items: [
	new Ext.Panel({
	    border: false,
	    html: t("This contact already has three email addresses in it's profile. You can replace one of the existing ones below by clicking on the email address you want replaced.", "email")
	}),
	this.list
	],
	cls: 'go-form-panel'
    });

    GO.email.ReplaceEmailDialog.superclass.constructor.call(this, config);

    this.addEvents({
	'replace' : true
    });

}
Ext.extend(GO.email.ReplaceEmailDialog, Ext.Window);
