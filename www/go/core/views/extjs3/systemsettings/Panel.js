
go.systemsettings.Panel = Ext.extend(Ext.form.FormPanel, {

	hasPermission: function() {
		const module = go.Modules.get(this.package, this.module);
		return module.userRights.mayManage;
	},
	
	autoScroll: true,
	afterRender: function() {
		go.systemsettings.Panel.superclass.afterRender.call(this);
		
		var module = go.Modules.get(this.package, this.module);
		
		if(!module.settings){
			console.error("Could not load the settings for module: "+this.package+"/"+this.module+". Fields will be empty.");
			return;
		}

		var v = module.settings, f = this.getForm(), hasReadOnlyFields = false;

		v.readOnlyKeys.forEach(function(key) {
			var field = f.findField(key);
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
				html: "<i class='icon'>info</i> " + t("Some fields on this page can't be edited becuase they have been locked in the server configuration file.")
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
		
	}

	
});

Ext.reg("systemsettingspanel", go.systemsettings.Panel );