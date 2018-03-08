GO.addressbook.CompaniesGrid = function (config) {

	if (!config)
	{
		config = {};
	}
	config.border = false;
	config.paging = true;

	this.fieldDefs = {
		fields: ['id', 'name', 'name2', 'homepage', 'email', 'phone', 'fax', 'address', 'address_no', 'zip', 'city', 'state', 'country', 'post_address', 'post_address_no', 'post_city', 'post_state', 'post_country', 'post_zip', 'bank_no', 'vat_no', 'invoice_email', 'ctime', 'mtime', 'iban', 'crn', 'ab_name', 'color'],
		columns: [
			{
				header: GO.addressbook.lang.id,
				dataIndex: 'id',
				width: 20,
				hidden: true,
				id: 'id'
			}, {
				header: GO.lang['strName'],
				dataIndex: 'name',
				width: 200,
				id: 'name'
			}, {
				header: GO.lang['strName2'],
				dataIndex: 'name2',
				hidden: true,
				width: 200,
				id: 'name2'
			},
			{
				header: GO.lang['strEmail'],
				dataIndex: 'email',
				width: 150,
				id: 'email'
			},
			{
				header: GO.lang['strHomepage'],
				dataIndex: 'homepage',
				width: 100,
				hidden: true,
				id: 'homepage'
			},
			{
				header: GO.lang['strPhone'],
				dataIndex: 'phone',
				width: 100,
				id: 'phone'
			},
			{
				header: GO.lang['strFax'],
				dataIndex: 'fax',
				width: 80,
				hidden: true,
				id: 'fax'
			},
			{
				header: GO.lang['strAddress'],
				dataIndex: 'address',
				hidden: true,
				id: 'address'
			},
			{
				header: GO.lang['strAddressNo'],
				dataIndex: 'address_no',
				hidden: true,
				id: 'address_no'
			},
			{
				header: GO.lang['strZip'],
				dataIndex: 'zip',
				hidden: true,
				id: 'zip'
			},
			{
				header: GO.lang['strCity'],
				dataIndex: 'city',
				width: 150,
				id: 'city'
			},
			{
				header: GO.lang['strState'],
				dataIndex: 'state',
				width: 80,
				hidden: true,
				id: 'state'
			},
			{
				header: GO.lang['strCountry'],
				dataIndex: 'country',
				hidden: true,
				id: 'country',
				renderer: GO.grid.ColumnRenderers.countryCode
			},
			{
				header: GO.lang['strPostAddress'],
				dataIndex: 'post_address',
				hidden: true,
				id: 'post_address'
			},
			{
				header: GO.lang['strPostAddressNo'],
				dataIndex: 'post_address_no',
				hidden: true,
				id: 'post_address_no'
			},
			{
				header: GO.lang['strPostZip'],
				dataIndex: 'post_zip',
				hidden: true,
				id: 'post_zip'
			},
			{
				header: GO.lang['strPostCity'],
				dataIndex: 'post_city',
				hidden: true,
				id: 'post_city'
			},
			{
				header: GO.lang['strPostState'],
				dataIndex: 'post_state',
				width: 80,
				hidden: true,
				id: 'post_state'
			},
			{
				header: GO.lang['strPostCountry'],
				dataIndex: 'post_country',
				hidden: true,
				id: 'post_country',
				renderer: GO.grid.ColumnRenderers.countryCode
			},
			{
				header: GO.addressbook.lang['cmdFormLabelBankNo'],
				dataIndex: 'bank_no',
				hidden: true,
				id: 'bank_no'
			}, {
				header: GO.addressbook.lang['bankBicNo'],
				dataIndex: 'bank_bic',
				hidden: true,
				id: 'bank_bic'
			}, {
				header: GO.addressbook.lang.iban,
				dataIndex: 'iban',
				hidden: true,
				id: 'iban'
			}, {
				header: GO.addressbook.lang.crn,
				dataIndex: 'crn',
				hidden: true,
				id: 'crn'
			},
			{
				header: GO.addressbook.lang['cmdFormLabelVatNo'],
				dataIndex: 'vat_no',
				hidden: true,
				id: 'vat_no'
			},
			{
				header: GO.addressbook.lang['cmdFormLabelInvoiceEmail'],
				dataIndex: 'invoice_email',
				hidden: true,
				id: 'invoice_email'
			}, {
				header: GO.lang.strMtime,
				dataIndex: 'mtime',
				hidden: true,
				width: 110,
				id: 'mtime'
			}, {
				header: GO.lang.strCtime,
				dataIndex: 'ctime',
				hidden: true,
				width: 110,
				id: 'ctime'
			}, {
				header: GO.addressbook.lang.addressbook,
				dataIndex: 'ab_name',
				hidden: true,
				id: 'ab_name'
			}
		]
	};


	if (GO.customfields)
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
			emptyText: GO.lang.strNoItems,
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
