GO.addressbook.ContactProfilePanel = function(config)
	{
		config = config || {};
		Ext.apply(config);

		if(!config.forUser){		
			this.formFirstName = new Ext.form.TextField(
			{
				fieldLabel: t("First name"),
				name: 'first_name',
				panel: this,
				validateValue: function(val) {
					var bool = (val!='' || this.panel.formLastName.getValue()!='');
					if(!bool)
					{
						this.markInvalid(this.blankText);
					}else
					{
						this.panel.formLastName.clearInvalid();
					}
					return bool;
				}
			});

			this.formMiddleName = new Ext.form.TextField(
			{
				fieldLabel: t("Middle name"),
				name: 'middle_name'
			});

			this.formLastName = new Ext.form.TextField(
			{
				fieldLabel: t("Last name"),
				name: 'last_name',
				panel: this,
				validateValue: function(val) {
					var bool = (val!='' || this.panel.formFirstName.getValue()!='');
					if(!bool)
					{
						this.markInvalid(this.blankText);
					}else
					{
						this.panel.formFirstName.clearInvalid();
					}
					return bool;
				}
			});
		}
	
		this.formTitle = new Ext.form.TextField(
		{
			fieldLabel: t("Title"),
			name: 'title'
		});
		
		this.formAfternameTitle = new Ext.form.TextField(
		{
			fieldLabel: t("Suffix"),
			name: 'suffix'
		});
	
		this.formInitials = new Ext.form.TextField(
		{
			fieldLabel: t("Initials"),
			name: 'initials'
		});
	
		this.sexCombo = new GO.form.ComboBox({
			fieldLabel: t("Sex"),
			hiddenName:'sex',
			store: new Ext.data.SimpleStore({
				fields: ['value', 'text'],
				data : [
				['M', t("Male")],
				['F', t("Female")]
				]
        
			}),
			value:'M',
			valueField:'value',
			displayField:'text',
			mode: 'local',
			triggerAction: 'all',
			editable: false,
			selectOnFocus:true,
			forceSelection: true
		});

		this.formSalutation = new Ext.form.TextField(
		{
			fieldLabel: t("Salutation", "addressbook"),
			name: 'salutation'
		});
	
		this.formBirthday = new Ext.form.DateField({
			fieldLabel: t("Birthday"),
			name: 'birthday',
			format: GO.settings['date_format']
		});
	
		if(!config.forUser){
			this.formEmail = new Ext.form.TextField(
			{
				fieldLabel: t("E-mail"),
				name: 'email',
				vtype:'emailAddress'

			});
		}
	
		this.formEmail2 = new Ext.form.TextField(
		{
			fieldLabel: t("E-mail") + ' 2',
			name: 'email2',
			vtype:'emailAddress'
		});
	
		this.formEmail3 = new Ext.form.TextField(
		{
			fieldLabel: t("E-mail") + ' 3',
			name: 'email3',
			vtype:'emailAddress'
		});
	
		this.formHomePhone = new Ext.form.TextField(
		{
			fieldLabel: t("Phone (Home)", "addressbook"),
			name: 'home_phone'
		});
	
		this.formFax = new Ext.form.TextField(
		{
			fieldLabel: t("Fax (Home)", "addressbook"),
			name: 'fax'
		});
	
		this.formCellular = new Ext.form.TextField(
		{
			fieldLabel: t("Mobile"),
			name: 'cellular'
		});
		
		this.formCellular2 = new Ext.form.TextField(
		{
			fieldLabel: t("2nd mobile"),
			name: 'cellular2'
		});
		
		this.formHomepage = new Ext.form.TextField(
		{
			fieldLabel: t("Homepage"),
			name: 'homepage'
		});
	
														
	
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
	
		this.formPostal = new Ext.form.TextField(
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



		this.formCompany = new GO.addressbook.SelectCompany({
			fieldLabel: t("Company"),
			name: 'company',
			hiddenName: 'company_id',
			emptyText: t("Please select a company", "addressbook"),
			addressbook_id: this.addressbook_id
		});
	
		this.formDepartment = new Ext.form.TextField(
		{
			fieldLabel: t("Department"),
			name: 'department'
		});

		this.formFunction = new Ext.form.TextField(
		{
			fieldLabel: t("Function"),
			name: 'function'
		});

		this.formWorkPhone = new Ext.form.TextField(
		{
			fieldLabel: t("Phone (Work)", "addressbook"),
			name: 'work_phone'
		});	

		this.formWorkFax = new Ext.form.TextField(
		{
			fieldLabel: t("Fax (Work)", "addressbook"),
			name: 'work_fax'
		});


		this.formAddressBooks = new GO.addressbook.SelectAddressbook({
			fieldLabel: t("Address Book", "addressbook"),
			store: GO.addressbook.writableAddressbooksStore,			
			selectOnFocus:true,
			forceSelection: true,
			allowBlank: false,
			anchor:'100%'
		});
		
		if(!config.forUser){
			this.formAddressBooks.on('beforeselect', function(combo, record)
			{
				if(this.formCompany.getValue()==0 || confirm(t("The company and all employees will also be moved to the new address book. Are you sure you want to do this?", "addressbook")))
				{
					this.setAddressbookID(record.data.id);
					this.setSalutation();
					return true;
				}else
				{
					return false;
				}
			}, this);
		

			this.formAddressBooks.on('select', function(){
				this.setSalutation(true)
			}, this);

			this.formFirstName.on('blur', function(){
				this.setSalutation(false)
			}, this);
			this.formMiddleName.on('blur', function(){
				this.setSalutation(false)
			}, this);
			this.formLastName.on('blur', function(){
				this.setSalutation(false)
			}, this);
				
			this.formFirstName.on('change', function(){
				this.setSalutation(true)
			}, this);
			this.formMiddleName.on('change', function(){
				this.setSalutation(true)
			}, this);
			this.formLastName.on('change', function(){
				this.setSalutation(true)
			}, this);
		}
		this.formInitials.on('blur', function(){
			this.setSalutation(false)
		}, this);
		this.formTitle.on('blur', function(){
			this.setSalutation(false)
		}, this);
		this.sexCombo.on('change', function(){
			this.setSalutation(true)
		}, this);

		
		this.formInitials.on('change', function(){
			this.setSalutation(true)
		}, this);
		this.formTitle.on('change', function(){
			this.setSalutation(true)
		}, this);

		this.addressbookFieldset = new Ext.form.FieldSet(
		{
			xtype: 'fieldset',
			title: t("Select address book", "addressbook"),
			collapsed: false,
			items: this.formAddressBooks
		});
		
	
		this.personalFieldset = new Ext.form.FieldSet(
		{
			xtype: 'fieldset',
			title: t("Personal details", "addressbook"),
			collapsed: false,
			defaults: {
				border: false,
				anchor:'100%'
			}
		});
		
		if(!config.forUser){
			this.personalFieldset.add([
				this.formFirstName,this.formMiddleName,this.formLastName
				]);	
		}
		
		this.personalFieldset.add([
			this.formTitle,this.formInitials,this.formAfternameTitle,this.sexCombo,
			this.formSalutation,
			this.formBirthday							
			]);
	
		this.addressFieldset = new Ext.form.FieldSet(
		{
			xtype: 'fieldset',
			title: t("Address", "addressbook"),
			collapsed: false,
			defaults: {
				border: false,
				anchor:'100%'
			},
			items: [this.formAddress,this.formAddressNo,this.formPostal,this.formCity,this.formState,this.formCountry]
		});
	
		this.contactFieldset =new Ext.form.FieldSet(
		{
			xtype: 'fieldset',
			title: t("Contact details", "addressbook"),
			collapsed: false,
			defaults: {
				border: false,
				anchor:'100%'
			}
		});
		
		
		if(!config.forUser){
			this.contactFieldset.add(this.formEmail);	
		}
		
		this.contactFieldset.add([this.formEmail2,this.formEmail3,this.formHomePhone,this.formFax,this.formCellular,this.formCellular2,this.formWorkPhone,this.formWorkFax,this.formHomepage]);
		
		
		this.workFieldset = new Ext.form.FieldSet(
		{
			xtype: 'fieldset',
			title: t("Work", "addressbook"),
			collapsed: false,
			defaults: {
				border: false,
				anchor:'100%'
			},
			items: [this.formCompany,this.formDepartment,this.formFunction]
		});
  
		this.actionDateFieldset = new Ext.form.FieldSet({
			collapsed: false,
			title: t("Other"),
			defaults: {
				border: false,
				anchor: '100%'
			},
			items: [
				this.formActionDateField = new Ext.form.DateField({
					name : 'action_date',
					format : GO.settings['date_format'],
					allowBlank : true,
					fieldLabel: t("Action date", "addressbook")
				}),
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
 
			var leftColItems = [];
		
		//if(!config.forUser)
			leftColItems.push(this.addressbookFieldset);
		
		leftColItems.push(this.personalFieldset,this.workFieldset,this.actionDateFieldset);
		
	
		this.title= t("Contact details", "addressbook");
		this.autoScroll=true;
		this.layout= 'column';
		this.labelWidth=125;
		
		this.defaults={
			border: false			
			
		};
		
		this.items= [
		{
			defaults:{
				style:'margin-right:10px'
			},
			itemId:'leftCol',
			columnWidth: .5,
			items: leftColItems			
		},{
			itemId:'rightCol',
			columnWidth: .5,
			items: [
			this.contactFieldset,
			this.addressFieldset
			]
		}
		];
	
		GO.addressbook.ContactProfilePanel.superclass.constructor.call(this);
	}

Ext.extend(GO.addressbook.ContactProfilePanel, Ext.Panel,{
	setSalutation : function(overwrite)
	{
		if(overwrite || this.formSalutation.getValue()==''){
			var firstName = this.formFirstName.getValue();
			var middleName = this.formMiddleName.getValue();
				middleName = !GO.util.empty(middleName) ? middleName[0].toUpperCase()+middleName.substring(1) : '';
			var lastName = this.formLastName.getValue();
				lastName = !GO.util.empty(lastName) ? lastName[0].toUpperCase()+lastName.substring(1) : '';
			var initials = this.formInitials.getValue();
			var title = this.formTitle.getValue();
			var record = this.formAddressBooks.store.getById(this.formAddressBooks.getValue());
			var sal = record.get('default_salutation');

			var sex = sal.slice(sal.indexOf('[')+1, sal.indexOf(']'));
			var sex_split = sex.split('/');
			var gender = (this.sexCombo.getValue() == 'M')? sex_split[0] : sex_split[1];

			sal = sal.replace('['+sex+']', gender);
			sal = sal.replace('{first_name}', firstName);
			sal = sal.replace('{middle_name}', middleName);
			sal = sal.replace('{last_name}', lastName);
			sal = sal.replace('{initials}', initials);
			sal = sal.replace('{title}', title);
			sal = sal.replace(/\s+/g, ' ');

			this.formSalutation.setValue(sal);
		}
	},
	setAddressbookID : function(addressbook_id)
	{
		this.formAddressBooks.setValue(addressbook_id);		
		this.formCompany.store.baseParams['addressbook_id'] = addressbook_id;
		this.formCompany.clearLastSearch();

//		if(go.Modules.isAvailable("core", "customfields")) {
//			var allowed_cf_categories = this.formAddressBooks.store.getById(addressbook_id).data.allowed_cf_categories.split(',');
//			GO.addressbook.contactDialog.updateCfTabs(allowed_cf_categories);
//		}
	},
	setValues : function(record)
	{
		this.formFirstName.setValue(record.name);
		this.formEmail.setValue(record.email);
		this.formHomePhone.setValue(record.phone);
		this.formCompany.setValue(record.company);
	}

});
