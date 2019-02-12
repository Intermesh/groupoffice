
go.systemsettings.Panel = Ext.extend(Ext.form.FormPanel, {
	
	afterRender: function() {
		go.systemsettings.Panel.superclass.afterRender.call(this);
		
		this.getForm().setValues(go.Modules.get(this.package, this.module).settings);
	},
	
	onSubmit: function (cb, scope) {
		
		var module = go.Modules.get(this.package, this.module), p = {"update": {}};
		
		p.update[module.id] = {settings: this.getForm().getFieldValues()};
		
		go.Stores.get("Module").set(p, function (options, success, response) {
			cb.call(scope, this, success);
		},
		scope);
		
	}

	
});