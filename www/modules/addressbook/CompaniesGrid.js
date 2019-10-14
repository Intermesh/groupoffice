GO.addressbook.CompaniesGrid = function (config) {

	if (!config)
	{
		config = {};
	}
	config.border = false;
	config.paging = true;
	
	config.autoExpandColumn = 'name';

	this.fieldDefs = {
		fields: ['id', 'name', 'name2', 'homepage', 'email', 'phone', 'fax', 'address', 'address_no', 'zip', 'city', 'state', 'country', 'post_address', 'post_address_no', 'post_city', 'post_state', 'post_country', 'post_zip', 'bank_no', 'vat_no', 'invoice_email','username','musername' , 'ctime', 'mtime', 'iban', 'crn', 'ab_name', 'color'],
		columns: [
			{
				header: t("ID", "addressbook"),
				dataIndex: 'id',
				width: 20,
				hidden: true,
				id: 'id'
			}, {
				header: t("Name"),
				dataIndex: 'name',
				width: 200,
				id: 'name'
			}, {
				header: t("Name 2"),
				dataIndex: 'name2',
				hidden: true,
				width: 200,
				id: 'name2'
			},
			{
				header: t("E-mail"),
				dataIndex: 'email',
				width: 150,
				hidden: true,
				id: 'email'
			},
			{
				header: t("Homepage"),
				dataIndex: 'homepage',
				width: 100,
				hidden: true,
				id: 'homepage'
			},
			{
				header: t("Phone"),
				dataIndex: 'phone',
				width: 100,
				hidden: true,
				id: 'phone'
			},
			{
				header: t("Fax"),
				dataIndex: 'fax',
				width: 80,
				hidden: true,
				id: 'fax'
			},
			{
				header: t("Address"),
				dataIndex: 'address',
				hidden: true,
				id: 'address'
			},
			{
				header: t("Address 2"),
				dataIndex: 'address_no',
				hidden: true,
				id: 'address_no'
			},
			{
				header: t("ZIP/Postal"),
				dataIndex: 'zip',
				hidden: true,
				id: 'zip'
			},
			{
				header: t("City"),
				dataIndex: 'city',
				width: 150,
				id: 'city'
			},
			{
				header: t("State"),
				dataIndex: 'state',
				width: 80,
				hidden: true,
				id: 'state'
			},
			{
				header: t("Country"),
				dataIndex: 'country',
				hidden: true,
				id: 'country',
				renderer: GO.grid.ColumnRenderers.countryCode
			},
			{
				header: t("Address (post)"),
				dataIndex: 'post_address',
				hidden: true,
				id: 'post_address'
			},
			{
				header: t("Number of house (post)"),
				dataIndex: 'post_address_no',
				hidden: true,
				id: 'post_address_no'
			},
			{
				header: t("ZIP/Postal (post)"),
				dataIndex: 'post_zip',
				hidden: true,
				id: 'post_zip'
			},
			{
				header: t("City (post)"),
				dataIndex: 'post_city',
				hidden: true,
				id: 'post_city'
			},
			{
				header: t("State (post)"),
				dataIndex: 'post_state',
				width: 80,
				hidden: true,
				id: 'post_state'
			},
			{
				header: t("Country (post)"),
				dataIndex: 'post_country',
				hidden: true,
				id: 'post_country',
				renderer: GO.grid.ColumnRenderers.countryCode
			},
			{
				header: t("Bank number", "addressbook"),
				dataIndex: 'bank_no',
				hidden: true,
				id: 'bank_no'
			}, {
				header: t("Bank BIC number", "addressbook"),
				dataIndex: 'bank_bic',
				hidden: true,
				id: 'bank_bic'
			}, {
				header: t("IBAN", "addressbook"),
				dataIndex: 'iban',
				hidden: true,
				id: 'iban'
			}, {
				header: t("Company Reg. No.", "addressbook"),
				dataIndex: 'crn',
				hidden: true,
				id: 'crn'
			},
			{
				header: t("VAT number", "addressbook"),
				dataIndex: 'vat_no',
				hidden: true,
				id: 'vat_no'
			},
			{
				header: t("Invoicing email", "addressbook"),
				dataIndex: 'invoice_email',
				hidden: true,
				id: 'invoice_email'
			},{
				header: t("Created by"),
				dataIndex: 'username',
				hidden:true
			},
			{
				header: t("Modified by"),
				dataIndex: 'musername',
				hidden:true
			}, {
				xtype: "datecolumn",
				header: t("Modified at"),
				dataIndex: 'mtime',
				hidden: true,
				id: 'mtime'
			}, {
				xtype: "datecolumn",
				header: t("Created at"),
				dataIndex: 'ctime',
				hidden: true,				
				id: 'ctime'
			}, {
				header: t("Address book", "addressbook"),
				dataIndex: 'ab_name',
				hidden: true,
				id: 'ab_name'
			}
		]
	};


	if(go.Modules.isAvailable("core", "customfields"))
	{
		GO.customfields.addColumns("GO\\Addressbook\\Model\\Company", this.fieldDefs);
	}



	GO.addressbook.CompaniesGrid.superclass.constructor.call(this, config);
};


Ext.extend(GO.addressbook.CompaniesGrid, GO.grid.GridPanel, {
	applyAddresslistFilters: function ()
	{
		this.store.setBaseParam('addresslist_filters', 1);
	},

	initComponent: function () {

		this.store = new GO.data.JsonStore({
			url: GO.url('addressbook/company/store'),
			baseParams: {
				filters: 1,
				addresslist_filters: 1
			},
			root: 'results',
			id: 'id',
			totalProperty: 'total',
			fields: this.fieldDefs.fields,
			remoteSort: true
		});

		this.store.on('load', function ()
		{
			if (this.store.reader.jsonData.feedback)
			{
				alert(this.store.reader.jsonData.feedback);
			}
		}, this);

		var companiesColumnModel = new Ext.grid.ColumnModel({
			defaults: {
				sortable: true
			},
			columns: this.fieldDefs.columns
		});

		this.colModel = companiesColumnModel;

		this.view = new Ext.grid.GridView({
			emptyText: t("No items to display"),
			getRowClass: function (record, rowIndex, rp, ds) {

				if (!rp.tstyle)
					rp.tstyle = '';

				if (!rp.initialstyle)
					rp.initialstyle = rp.tstyle;

				if (record.data.color) {
					rp.tstyle += "color:#" + record.data.color + ";";
				} else {
					rp.tstyle = rp.initialstyle;
				}

				return;
			}
		}),
						this.sm = new Ext.grid.RowSelectionModel();
		this.loadMask = true;

		this.enableDragDrop = true;
		this.ddGroup = 'AddressBooksDD';
		
		GO.addressbook.CompaniesGrid.superclass.initComponent.call(this);
	}
});
