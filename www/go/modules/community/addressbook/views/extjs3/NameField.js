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
				this.nameMenu.items.first().items.each(function(i) {
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
			this.nameMenu.items.get(0).items.each(function (i) {
				formPanel.form.add(i);
			}, this);
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
				},
				afterrender: function (menu) {

					this.nameMenu.keyNav.destroy();
					this.nameMenu.keyNav = new Ext.KeyNav(menu.getEl(), {
						enter: function (e) {
							e.preventDefault();
							this.nameMenu.hide();
							this.focusNextEl();
						},
						scope: this
					});

					this.salutationField.on('specialkey', function (field, e) {
						// e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
						// e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
						if (e.getKey() == e.TAB) {
							this.nameMenu.hide();		
							e.preventDefault();
							this.focusNextEl();
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
							items: [
								{
									xtype: 'textfield',
									name: 'prefixes',
									fieldLabel: t("Prefix")
								}, {
									xtype: 'textfield',
									name: 'initials',
									fieldLabel: t("Initials")
								}, this.firstName = new Ext.form.TextField({
									xtype: 'textfield',
									name: 'firstName',
									fieldLabel: t("First")
								}), this.middleName = new Ext.form.TextField({
									xtype: 'textfield',
									name: 'middleName',
									fieldLabel: t("Middle")
								}), this.lastName = new Ext.form.TextField({
									xtype: 'textfield',
									name: 'lastName',
									fieldLabel: t("Last")
								}), this.suffixField = new Ext.form.TextField({
									xtype: 'textfield',
									name: 'suffixes',
									fieldLabel: t("Suffix")
								}), this.salutationField = new Ext.form.TextField({
									xtype: 'textfield',
									name: 'salutation',
									fieldLabel: t("Salutation")
								})
							]
						});
	}
});
