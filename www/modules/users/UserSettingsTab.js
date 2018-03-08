GO.users.UserSettingsTab = function(config)
	{
		config = config || {};

		this.settingNoReminders = new Ext.ux.form.XCheckbox({
			boxLabel: GO.users.lang['noReminders'],
			name: 'no_reminders',
			checked: false,
			hideLabel:true
		});


		this.formFirstName = new Ext.form.TextField(
		{
			fieldLabel: GO.lang.strFirstName,
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
			fieldLabel: GO.lang.strMiddleName,
			name: 'middle_name'
		});

		this.formLastName = new Ext.form.TextField(
		{
			fieldLabel: GO.lang.strLastName,
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
			fieldLabel: GO.lang.strTitle,
			name: 'title'
		});
		
		this.formAfternameTitle = new Ext.form.TextField(
		{
			fieldLabel: GO.lang.strSuffix,
			name: 'suffix'
		});
	
		this.formInitials = new Ext.form.TextField(
		{
			fieldLabel: GO.lang.strInitials,
			name: 'initials'
		});
		
	
		this.sexCombo = new GO.form.ComboBox({
			fieldLabel: GO.lang.strSex,
			hiddenName:'sex',
			store: new Ext.data.SimpleStore({
				fields: ['value', 'text'],
				data : [
				['M', GO.lang['strMale']],
				['F', GO.lang['strFemale']]
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
			fieldLabel: GO.lang['strBirthday'],
			name: 'birthday',
			format: GO.settings['date_format']
		});
	
		this.formEmail = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strEmail'],
			name: 'email',
			vtype:'emailAddress'

		});
		
		this.formEmail2 = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strEmail'] + ' 2',
			name: 'email2',
			vtype:'emailAddress'
		});
	
		this.formEmail3 = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strEmail'] + ' 3',
			name: 'email3',
			vtype:'emailAddress'
		});
	
		this.formHomePhone = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strPhone'],
			name: 'home_phone'
		});
	
		this.formFax = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strFax'],
			name: 'fax'
		});
	
		this.formCellular = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strCellular'],
			name: 'cellular'
		});


	
		this.formAddress = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strAddress'],
			name: 'address'
		});
	
		this.formAddressNo = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strAddressNo'],
			name: 'address_no'
		});
	
		this.formPostal = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strZip'],
			name: 'zip'
		});

		this.formCity = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strCity'],
			name: 'city'
		});

		this.formState = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strState'],
			name: 'state'
		});
	
		this.formCountry = new GO.form.SelectCountry({
			fieldLabel: GO.lang['strCountry'],
			name: 'country_text',
			hiddenName: 'country'
		});

		this.formCompany = new GO.form.PlainField({
			name:'company_name',
			fieldLabel: GO.lang.strCompany
		});
	
		this.formDepartment = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strDepartment'],
			name: 'department'
		});

		this.formFunction = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strFunction'],
			name: 'function'
		});

		this.formWorkPhone = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strWorkPhone'],
			name: 'work_phone'
		});	

		this.formWorkFax = new Ext.form.TextField(
		{
			fieldLabel: GO.lang['strWorkFax'],
			name: 'work_fax'
		});
	
		
		config.bodyStyle="padding:5px";
		config.border=false;
		config.hideLabel=true;
		config.title = GO.users.lang.account;
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
				title:GO.lang.cmdSettings,
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
				title:GO.lang.personalDetailsFor,
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
				title: GO.lang.fieldsetContact,
				autoHeight: true,
				collapsed: false,
				defaults: { anchor: '100%' },
				items:[this.formEmail,this.formEmail2,this.formEmail3,this.formHomePhone,this.formFax,this.formCellular,this.formWorkPhone,this.formWorkFax]
			},{
					xtype: 'fieldset',
					title: GO.lang.fieldsetAddress,
					autoHeight: true,
					collapsed: false,
					defaults: { anchor: '100%' },
					items:[this.formAddress,this.formAddressNo,this.formPostal,this.formCity,this.formState,this.formCountry]
			}]
		}];
	
		if(GO.addressbook){
			this.companyFieldset = new Ext.form.FieldSet({
				title: GO.lang.fieldsetWork,
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