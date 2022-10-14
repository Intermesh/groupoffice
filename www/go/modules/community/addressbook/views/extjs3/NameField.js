go.modules.community.addressbook.NameField = Ext.extend(Ext.form.TextField, {
	name: 'name',
	fieldLabel: t("Name"),
	anchor: '100%',
	allowBlank: false,
	nameMenuEnabled: true,
	initComponent: function () {
		go.modules.community.addressbook.NameField.superclass.initComponent.call(this);
		
		this.createNameMenu();

		this.on("focus", function () {
			if (this.nameMenuEnabled) {
				this.nameMenu.show(this.getEl());
			}
		}, this);
		
		this.on("added", function() {
			//manually register form fields for settings panel
			this.formcontainer = this.findParentByType('formcontainer');
			
			if(this.formcontainer) {
				this.nameMenu.findBy((i) => i.isFormField).forEach(function (i) {
					this.formcontainer.addAdditionalField(i);
				}, this)
				
				return;
			}
		}, this);
		
		this.on("afterrender", function() {		
			if(this.formcontainer ) {
				return;
			}
			
			var formPanel = this.findParentByType('form');
			this.nameMenu.findBy((i) => i.isFormField).forEach(function (i) {
				formPanel.form.add(i);
			});
		})
	},

	createNameMenu: function () {
		var me = this;

		this.nameMenu = new Ext.menu.Menu({
			isComposite: true, //for formcontainer
			items: this.createContactNameFieldSet(),
			focus: function () {
				me.firstName.focus();
			},
			listeners: {
				hide: function () {
					this.buildFullName();
//					this.jobTitle.focus();
					this.focusNextEl();
				},
				afterrender: function (menu) {

					this.nameMenu.keyNav.destroy();
					this.nameMenu.keyNav = new Ext.KeyNav(menu.getEl(), {
						enter: function (e) {
							e.preventDefault();
							this.nameMenu.hide();

						},
						scope: this
					});

					this.salutationField.on('specialkey', function (field, e) {
						// e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
						// e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
						if (e.getKey() == e.TAB) {
							e.preventDefault();
							this.nameMenu.hide();		

						}

					}, this);
				},
				scope: this
			}
		});

		this.on('destroy', function () {
			this.nameMenu.destroy();
		}, this);

		return this.nameMenu;
	},
	focusNextEl : function() {

		if(this.formcontainer) {
			// in user settings
			const fs = this.formcontainer.items.itemAt(0);
			const jobTitle = fs.items.itemAt(1);
			jobTitle.focus();
			return;
		}

		var found = false;
		var entityForm = this.findParentByType("entityform");
		entityForm.form.items.each(function(item) {
			if(found) {
				item.focus();
				return false;
			}
			
			if(item == this) {
				found = true;
			}
		}, this);
	},
	buildFullName: function () {
		var  name =this.firstName.getValue(),
						m = this.middleName.getValue(),
						l = this.lastName.getValue();

		if (m) {
			name += " " + m;
		}

		if (l) {
			name += " " + l;
		}

		this.setValue(name);

	},

	createContactNameFieldSet: function () {
		return new Ext.form.FieldSet(
						{
							width: dp(800),
							items: [

								{
									xtype: "container",
									layout: "form",
									cls: "go-hbox",
									items: [

										this.firstName = new Ext.form.TextField({
											xtype: 'textfield',
											name: 'firstName',
											fieldLabel: t("First"),

											flex: 1
										}), this.middleName = new Ext.form.TextField({
											xtype: 'textfield',
											name: 'middleName',
											fieldLabel: t("Middle"),
											width: dp(128)
										}), this.lastName = new Ext.form.TextField({
											xtype: 'textfield',
											name: 'lastName',
											fieldLabel: t("Last"),
											flex: 1
										}),
										{
											xtype: 'textfield',
											name: 'initials',
											fieldLabel: t("Initials"),
											width: dp(100)
										}
									]
								},
								{
									xtype: "container",
									layout: "form",
									cls: "go-hbox",
									items: [
										{
											xtype: 'textfield',
											name: 'prefixes',
											fieldLabel: t("Prefix"),
											flex: 1
										}, this.suffixField = new Ext.form.TextField({
											xtype: 'textfield',
											name: 'suffixes',
											fieldLabel: t("Suffix"),
											flex: 1
										})
									]
								},
								this.salutationField = new Ext.form.TextField({
									xtype: 'textfield',
									name: 'salutation',
									fieldLabel: t("Salutation"),
									anchor: "100%"
								})
								]
						});
	}
});
