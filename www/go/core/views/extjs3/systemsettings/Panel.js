
go.systemsettings.Panel = Ext.extend(Ext.form.FormPanel, {

	hasPermission: function() {
		if(go.User.isAdmin) {
			return true;
		}
		const module = go.Modules.get(this.package, this.module);
		return module.userRights.mayManage;
	},
	
	autoScroll: true,
	afterRender: function() {
		go.systemsettings.Panel.superclass.afterRender.call(this);
		
		this.loadSettings();
	},

	loadSettings: function() {
		const module = go.Modules.get(this.package, this.module);

		if(!module.settings){
			console.debug("Module: "+this.package+"/"+this.module+" has no settings model.");
			return;
		}

		console.log(module.settings);

		const v = module.settings, f = this.getForm();
		let hasReadOnlyFields = false;

		v.readOnlyKeys.forEach(function(key) {
			const field = f.findField(key);
			if(field) {
				field.setDisabled(true);
				hasReadOnlyFields = true;
			}
		});

		if(hasReadOnlyFields) {
			this.insert(0, {
				xtype: 'box',
				autoEl: 'p',
				cls: 'info',
				html: "<i class='icon'>info</i> " + t("Some fields on this page can't be edited because they have been locked in the server configuration file.")
			});
		}

		this.getForm().setValues(v);
	},
	
	onSubmit: function (cb, scope) {
		
		var module = go.Modules.get(this.package, this.module), p = {"update": {}}, s = this.getForm().getFieldValues(true);

		if(Object.keys(s).length === 0) {
			var me = this;
			// we need to use settimeout because we want to fire the callback on the next loop. Otherwise system settings will submit too early.
			setTimeout(function() {
				cb.call(scope, me, true);
			}, 0);
			return;
		}
		
		p.update[module.id] = {settings: s};
		
		go.Db.store("Module").set(p, function (options, success, response) {
			cb.call(scope, this, success);
		},
		scope);
		
	},

	isValid() {
		return this.form.isValid();
	}

	
});

Ext.reg("systemsettingspanel", go.systemsettings.Panel );