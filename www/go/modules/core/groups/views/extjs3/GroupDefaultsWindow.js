
/* global go, Ext */

go.modules.core.groups.GroupDefaultsWindow = Ext.extend(go.Window, {
	title: t("Group defaults"),
	autoScroll: true,
	modal: true,
	width: dp(400),
	height: dp(600),
	initComponent: function () {
		
		this.formPanel = new Ext.form.FormPanel({
			items: [{
					xtype: "fieldset",
					items: [{
						xtype: "box",
						autoEl: "p",
						html: t("Members of the groups below can access a new group when it is created. These groups can share items with users and user groups that they are not members of.")
					},
						new go.form.multiselect.Field({
							valueIsId: true,							
							name: "defaultGroups",
							idField: "groupId",
							displayField: "name",
							entityStore: go.Stores.get("Group"),
							fieldLabel: t("Groups"),
							storeBaseParams: {
								filter: [{"includeUsers": false}]
							}
					}), 
					{
						xtype: "box",
						autoEl: "p",
						html: t("Use the button below to apply the above to existing groups. WARNING: This will erase all custom permissions on groups.")
					},
						{
						xtype: "button",
						text: t("Apply to all"),
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



