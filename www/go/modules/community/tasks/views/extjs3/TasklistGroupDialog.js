go.modules.community.tasks.TasklistGroupDialog = Ext.extend(go.form.Dialog, {
	title: t("Group", "projects2"),
	titleField: "group_name",
	width: dp(800),
	height: dp(600),
	store: new go.data.Store({
		fields: ['id', {name: 'name'}, {name: 'color'}],
		entityStore: ""
	}),
	initComponent: function () {

		Ext.apply(this, {
			items: [this.formPanel = new Ext.form.FormPanel({
				items: [
					this.groupNameField = new Ext.form.TextField({
						name: 'group_name',
						fieldLabel: t("Group name", "projects2"),
						value: ''
					})
				],
				autoScroll: true,
				cls: 'go-form-panel'
			})],
			buttons: [{
				text: t("Ok"),
				handler: function () {
					this.fireEvent('group_name', this.groupNameField.getValue());
					this.hide();
				},
				scope: this
			}, {
				text: t("Close"),
				handler: function () {
					this.hide();
				},
				scope: this
			}]
		});

		go.modules.community.tasks.TasklistGroupDialog.superclass.initComponent.call(this);

		this.addEvents({
			'group_name': true
		}, this);
	},

	show: function (config) {

		go.modules.community.tasks.TasklistGroupDialog.superclass.show.call(this, config);
		this.groupNameField.setValue('');
	}

});