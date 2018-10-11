
/* global go, Ext */

go.modules.core.groups.GroupDefaultsWindow = Ext.extend(go.Window, {
	title: t("Group defaults"),
	autoScroll: true,
	modal: true,
	width: dp(400),
	height: dp(400),
	initComponent: function () {
		
		this.formPanel = new Ext.form.FormPanel({
			items: [{
					xtype: "fieldset",
					items: [
						new go.form.multiselect.Field({
							valueIsId: true,
							hint: t("In addition to members, these groups can also share with all groups and users."),
							name: "defaultGroups",
							idField: "groupId",
							displayField: "name",
							entityStore: go.Stores.get("Group"),
							fieldLabel: t("Groups"),
							storeBaseParams: {
								filter: [{"includeUsers": false}]
							}
					}), {
						xtype: "button",
						text: t("Apply to all"),
						cls: "raised",
						scope: this,
						handler: function() {
							Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to reset all group permissions? This can't be undone."), function(btn) {
								
								if(btn !== "yes") {
									return;
								}
								
								this.getEl().mask();
								
								go.Jmap.request({
									method: "core/groups/Settings/applyDefaultGroups",
									params: this.formPanel.getForm().getFieldValues(),
									callback: function (options, success, response) {
										this.getEl().unmask();										
									},
									scope: this
								});
							}, this);
						}
					}]
				}
			]
		});
		
		this.items = [this.formPanel];
		
		this.bbar = ['->', {
				text: t("Save"),
				handler: function() {
					this.submit();
				},
				scope: this
		}];

		go.modules.core.groups.GroupDefaultsWindow.superclass.initComponent.call(this);
		
		
		this.on('render', function() {
			go.Jmap.request({
				method: "core/groups/Settings/get",
				callback: function (options, success, response) {
					this.formPanel.getForm().setValues(response);
				},
				scope: this
			});
		}, this);
	},

	submit: function (cb, scope) {
		
		this.getEl().mask();
		go.Jmap.request({
			method: "core/groups/Settings/set",
			params: this.formPanel.getForm().getFieldValues(),
			callback: function (options, success, response) {
				this.getEl().unmask();
				this.close();
			},
			scope: this
		});
	}

});



