GO.users.UserSettingsTab = function(config)
	{
		config = config || {};

		this.settingNoReminders = new Ext.ux.form.XCheckbox({
			boxLabel: t("Do not create reminders for me", "users"),
			name: 'no_reminders',
			checked: false,
			hideLabel:true
		});


		this.formFirstName = new Ext.form.TextField(
		{
			fieldLabel: t("First name"),
			name: 'first_name',
			panel: this,
			validateValue: function(val) {
				var bool = (val!='' || this.panel.formLastName.getValue()!='');
				if(!bool)
					this.markInvalid(this.blankText);
				else
					this.panel.formLastName.clearInvalid();
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
				if(val!='' || this.panel.formFirstName.getValue()!='')
				{
					this.panel.formFirstName.clearInvalid();
					return true;
				}
				else
				{
					this.markInvalid(this.blankText);
					return false
				}
			}
		});
	
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


	
		this.formBirthday = new Ext.form.DateField({
			fieldLabel: t("Birthday"),
			name: 'birthday',
			format: GO.settings['date_format']
		});
	
		this.formEmail = new Ext.form.TextField(
		{
			fieldLabel: t("E-mail"),
			name: 'email',
			vtype:'emailAddress'

		});
		
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
			fieldLabel: t("Phone"),
			name: 'home_phone'
		});
	
		this.formFax = new Ext.form.TextField(
		{
			fieldLabel: t("Fax"),
			name: 'fax'
		});
	
		this.formCellular = new Ext.form.TextField(
		{
			fieldLabel: t("Mobile"),
			name: 'cellular'
		});


	
		this.formAddress = new Ext.form.TextField(
		{
			fieldLabel: t("Address"),
			name: 'address'
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

		this.formCompany = new GO.form.PlainField({
			name:'company_name',
			fieldLabel: t("Company")
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
			fieldLabel: t("Phone (work)"),
			name: 'work_phone'
		});	

		this.formWorkFax = new Ext.form.TextField(
		{
			fieldLabel: t("Fax (work)"),
			name: 'work_fax'
		});
	
		
		config.bodyStyle="padding:5px";
		config.border=false;
		config.hideLabel=true;
		config.title = t("Account", "users");
		config.layout='column';
		config.defaults={
			columnWidth:.5,
			border:false
		};
		config.labelWidth=190;
		config.items=[{
			columnWidth:.5, 
			items: [{
				xtype:'fieldset',		
				defaults: {
					border: true,
					anchor:'100%'
				},
				autoHeight:true,
				title:t("Settings"),
				items:[
					this.settingNoReminders
				]
			},{
				xtype:'fieldset',		
				defaults: {
					border: true,
					anchor:'100%'
				},
				autoHeight:true,
				title:t("Personal details for"),
				items:[
					this.formFirstName,
					this.formMiddleName,
					this.formLastName,
					this.formTitle,
					this.formAfternameTitle,
					this.formInitials,
					this.sexCombo, 
					this.formBirthday
				]
			}]
		},{
  		columnWidth: .5,
    	defaults: { border: true },
    	style: 'margin-left: 5px;',
			items: [{
				xtype: 'fieldset',
				title: t("Contact details"),
				autoHeight: true,
				collapsed: false,
				defaults: { anchor: '100%' },
				items:[this.formEmail,this.formEmail2,this.formEmail3,this.formHomePhone,this.formFax,this.formCellular,this.formWorkPhone,this.formWorkFax]
			},{
					xtype: 'fieldset',
					title: t("Address"),
					autoHeight: true,
					collapsed: false,
					defaults: { anchor: '100%' },
					items:[this.formAddress,this.formAddressNo,this.formPostal,this.formCity,this.formState,this.formCountry]
			}]
		}];
	
		if(go.Modules.isAvailable("community", "addressbook")){
			this.companyFieldset = new Ext.form.FieldSet({
				title: t("Work"),
				autoHeight: true,
				collapsed: false,
				defaults: { anchor:'100%'},
				items: [this.formCompany,this.formDepartment,this.formFunction]
			});
			config.items[0].items.push(this.companyFieldset);
		}
	
	
		GO.users.UserSettingsTab.superclass.constructor.call(this, config);	
	
	}

Ext.extend(GO.users.UserSettingsTab, Ext.Panel,{



});