GO.addressbook.CompanyProfilePanel = function(config)
{
	Ext.apply(config);
	
	
	this.formAddress = new Ext.form.TextArea(
	{
		fieldLabel: t("Address"), 
		name: 'address',
		height: 50,
		maxLength: 255
	});
					
	this.formAddressNo = new Ext.form.TextField(
	{
		fieldLabel: t("Address 2"), 
		name: 'address_no'	
	});
					
	this.formZip = new Ext.form.TextField(
	{
		fieldLabel: t("ZIP/Postal"), 
		name: 'zip'
	});
					
	this.formCity = new Ext.form.TextField(
	{
		fieldLabel: t("City"), 
		name: 'city'
	});
					
	this.formState = new Ext.form.TextField(
	{
		fieldLabel: t("State"), 
		name: 'state'
	});

	this.formCountry = new GO.form.SelectCountry({
		fieldLabel: t("Country"),
		name: 'country_text',
		hiddenName: 'country'
	});
	
	/*
	 * 
	 * 		POST ADDRESS
	 * 
	 */
	 
	this.btnCopyVisitAddress = new Ext.Button({
		text: t("Copy to post address", "addressbook"),
		disabled:false,
		fieldLabel: " ",
		labelSeparator: "",
		handler:function(){
			this.copyVisitToPostAddress();
		},
		scope:this
	});
	 	 
	this.formPostAddress = new Ext.form.TextArea(
	{
		fieldLabel: t("Address"), 
		name: 'post_address',
		height: 50,
		maxLength: 255
	});
					
	this.formPostAddressNo = new Ext.form.TextField(
	{
		fieldLabel: t("Address 2"), 
		name: 'post_address_no'
	});
					
	this.formPostZip = new Ext.form.TextField(
	{
		fieldLabel: t("ZIP/Postal"), 
		name: 'post_zip'
	});
					
	this.formPostCity = new Ext.form.TextField(
	{
		fieldLabel: t("City"), 
		name: 'post_city'
	});
					
	this.formPostState = new Ext.form.TextField(
	{
		fieldLabel: t("State"), 
		name: 'post_state'
	});
	
	this.formPostCountry = new GO.form.SelectCountry({
		fieldLabel: t("Country"),
		name: 'post_country_text',
		hiddenName: 'post_country'
	});
					 
	this.formName = new Ext.form.TextField(
	{
		fieldLabel: t("Name"), 
		name: 'name',
		allowBlank:false
	});

	this.formName2 = new Ext.form.TextField(
	{
		fieldLabel: t("Name 2"),
		name: 'name2'
	});
		
	this.formPhone = new Ext.form.TextField(
	{
		fieldLabel: t("Phone"), 
		name: 'phone', 
		allowBlank: true,
		labelSeparator: this.separator,
		width: this.widthLeftColumn
	});

	this.formFax = new Ext.form.TextField(
	{
		fieldLabel: t("Fax"), 
		name: 'fax'
	});
	
	this.formEmail = new Ext.form.TextField(
	{
		fieldLabel: t("E-mail"), 
		name: 'email',
		vtype:'emailAddress'
	});				
		
	this.formHomepage = new Ext.form.TextField(
	{
		fieldLabel: t("Homepage"),
		name: 'homepage'
	});	
	
	this.formBankNo = new Ext.form.TextField(
	{
		fieldLabel: t("Bank number", "addressbook"),
		name: 'bank_no'
	});	
	
	this.formBankBIC = new Ext.form.TextField(
	{
		fieldLabel: t("Bank BIC number", "addressbook"),
		name: 'bank_bic'
	});	

	this.formVatNo = new Ext.form.TextField(
	{
		fieldLabel: t("VAT number", "addressbook"),
		name: 'vat_no'
	});
	
	this.formInvoiceEmail = new Ext.form.TextField(
	{
		fieldLabel: t("Invoicing email", "addressbook"),
		name: 'invoice_email',
		vtype:'emailAddress'
	});
	
	/*
	 * 
	 * 		ADDRESSBOOK
	 * 
	 */					
	
	this.formAddressBooks = new GO.addressbook.SelectAddressbook({
			fieldLabel: t("Address Book", "addressbook"),
			store: GO.addressbook.writableAddressbooksStore,
			selectOnFocus:true,
			forceSelection: true,
			allowBlank: false,
			anchor:'100%'
		});
	this.formAddressBooks.on('beforeselect', function(combo, record) 	
	{
		if(this.company_id>0)
		{
			return confirm(t("The company and all employees will also be moved to the new address book. Are you sure you want to do this?", "addressbook"));
		}
	}, this);	

	
	this.addressbookFieldset = new Ext.form.FieldSet({
		title: t("Select address book", "addressbook"),
		autoHeight: true,
		border: true,
		collapsed: false,
		items:this.formAddressBooks
	});
	
	this.companyFieldset = new Ext.form.FieldSet({
		title: t("Company details", "addressbook"),
		autoHeight: true,
		collapsed: false,
		border: true,
		defaults: { border: false, anchor: '100%' },
		items: [
			this.formName,
			this.formName2,
			this.formPhone,
			this.formFax,
			this.formEmail,
			this.formHomepage,
			this.formBankNo,
			this.formBankBIC,
			{
				xtype:'textfield',
				fieldLabel:t("IBAN", "addressbook"),
				name:'iban'
			},{
				xtype:'textfield',
				fieldLabel:t("Company Reg. No.", "addressbook"),
				name:'crn'
			},
			this.formVatNo,
			this.formInvoiceEmail
		]
	});
	
	this.visitAddressFieldset = new Ext.form.FieldSet({
		title: t("Visit address", "addressbook"),
		autoHeight: true,
		collapsed: false,
		border: true,
		defaults: { border: false, anchor: '100%' },
		items: [
			this.formAddress,
			this.formAddressNo,
			this.formZip,
			this.formCity,
			this.formState,
			this.formCountry,
			this.btnCopyVisitAddress
		]
	});
	
	this.postAddressFieldset = new Ext.form.FieldSet({
		title: t("Post address", "addressbook"), 
		autoHeight: true,
		collapsed: false,
		border: true,
		defaults: { border: false, anchor: '100%'},
		items: [
			this.formPostAddress,
			this.formPostAddressNo,
			this.formPostZip,
			this.formPostCity,
			this.formPostState,
			this.formPostCountry
		]
	});


this.otherFieldset = new Ext.form.FieldSet({
			collapsed: false,
			title: t("Other"),
			defaults: {
				border: false,
				anchor: '100%'
			},
			items: [
				this.colorField = new GO.form.ColorField({
					fieldLabel : t("Color"),
					value : "FFFFFF",
					width:200,
					name : 'color',
					colors : [
					'EBF1E2',
					'95C5D3',
					'FFFF99',
					'A68340',
					'82BA80',
					'F0AE67',
					'66FF99',
					'CC0099',
					'CC99FF',
					'996600',
					'999900',
					'FF0000',
					'FF6600',
					'FFFF00',
					'FF9966',
					'FF9900',
					'FF6666',
					'CCFFCC',
					/* Line 1 */
					'FB0467',
					'D52A6F',
					'CC3370',
					'C43B72',
					'BB4474',
					'B34D75',
					'AA5577',
					'A25E79',
					/* Line 2 */
					'FF00CC',
					'D52AB3',
					'CC33AD',
					'C43BA8',
					'BB44A3',
					'B34D9E',
					'AA5599',
					'A25E94',
					/* Line 3 */
					'CC00FF',
					'B32AD5',
					'AD33CC',
					'A83BC4',
					'A344BB',
					'9E4DB3',
					'9955AA',
					'945EA2',
					/* Line 4 */
					'6704FB',
					'6E26D9',
					'7033CC',
					'723BC4',
					'7444BB',
					'754DB3',
					'7755AA',
					'795EA2',
					/* Line 5 */
					'0404FB',
					'2626D9',
					'3333CC',
					'3B3BC4',
					'4444BB',
					'4D4DB3',
					'5555AA',
					'5E5EA2',
					/* Line 6 */
					'0066FF',
					'2A6ED5',
					'3370CC',
					'3B72C4',
					'4474BB',
					'4D75B3',
					'5577AA',
					'5E79A2',
					/* Line 7 */
					'00CCFF',
					'2AB2D5',
					'33ADCC',
					'3BA8C4',
					'44A3BB',
					'4D9EB3',
					'5599AA',
					'5E94A2',
					/* Line 8 */
					'00FFCC',
					'2AD5B2',
					'33CCAD',
					'3BC4A8',
					'44BBA3',
					'4DB39E',
					'55AA99',
					'5EA294',
					/* Line 9 */
					'00FF66',
					'2AD56F',
					'33CC70',
					'3BC472',
					'44BB74',
					'4DB375',
					'55AA77',
					'5EA279',
					/* Line 10 */
					'00FF00', '2AD52A',
					'33CC33',
					'3BC43B',
					'44BB44',
					'4DB34D',
					'55AA55',
					'5EA25E',
					/* Line 11 */
					'66FF00', '6ED52A', '70CC33',
					'72C43B',
					'74BB44',
					'75B34D',
					'77AA55',
					'79A25E',
					/* Line 12 */
					'CCFF00', 'B2D52A', 'ADCC33', 'A8C43B',
					'A3BB44',
					'9EB34D',
					'99AA55',
					'94A25E',
					/* Line 13 */
					'FFCC00', 'D5B32A', 'CCAD33', 'C4A83B',
					'BBA344', 'B39E4D',
					'AA9955',
					'A2945E',
					/* Line 14 */
					'FF6600', 'D56F2A', 'CC7033', 'C4723B',
					'BB7444', 'B3754D', 'AA7755',
					'A2795E',
					/* Line 15 */
					'FB0404', 'D52A2A', 'CC3333', 'C43B3B',
					'BB4444', 'B34D4D', 'AA5555', 'A25E5E',
					/* Line 16 */
					'FFFFFF', '949494', '808080', '6B6B6B',
					'545454', '404040', '292929', '000000']
				})
			]
		});

	this.title=t("Company details", "addressbook");
				
	this.labelWidth=120;
	this.bodyStyle='padding: 5px'; 
	this.layout='column';
	this.autoScroll=true;
	this.defaults={border: false};
	this.items=[
		{	 
			columnWidth: .5,
	  //	defaults: { border: false },
			items: [
				this.addressbookFieldset,
				this.companyFieldset,
				this.otherFieldset
			]
		},{
  		columnWidth: .5,
 //   	defaults: { border: false },
    	style: 'margin-left: 5px;',
			items: [this.visitAddressFieldset ,this.postAddressFieldset]
  	}];


	GO.addressbook.CompanyProfilePanel.superclass.constructor.call(this);
}

Ext.extend(GO.addressbook.CompanyProfilePanel, Ext.Panel,{
	setAddressbookID : function(addressbook_id)
	{
		this.formAddressBooks.setValue(addressbook_id);
		
		
	},

	copyVisitToPostAddress : function(){
		this.formPostAddress.setValue(this.formAddress.getValue());
		this.formPostAddressNo.setValue(this.formAddressNo.getValue());
		this.formPostZip.setValue(this.formZip.getValue());
		this.formPostCity.setValue(this.formCity.getValue());
		this.formPostState.setValue(this.formState.getValue());
		this.formPostCountry.setValue(this.formCountry.getValue());
	},


	setCompanyId : function(company_id)
	{
		this.company_id=company_id;
	}
});

