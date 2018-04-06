go.form.FormWindow = Ext.extend(go.Window, {
	autoScroll: true,
	width: 400,
	modal: true,
	showAnimDuration: 0.16,
	hideAnimDuration: 0.16,
//	closeAction: 'hide',
	entityStore: null,
	currentId: null,
	buttonAlign: 'left',
	initComponent: function () {

		this.formPanel = new Ext.FormPanel({
			items: this.initFormItems()
		});

		this.items = [this.formPanel];

		this.buttons = [this.deleteBtn = new Ext.Button({
				text: t("Delete"),
				cls: 'danger',
				handler: this.delete,
				disabled: true,
				scope: this
			}), '->', {
				text: t("Save"),
				handler: this.submitForm,
				scope: this
			}];

		go.form.FormWindow.superclass.initComponent.call(this);

		if (this.formValues) {
			this.formPanel.form.setValues(this.formValues);
			delete this.formValues;
		}
	},

	load: function (pk) {
		this.currentId = pk;

		this.actionStart();
		this.entityStore.get([pk], function (entities) {
			if (entities[0]) {
				this.deleteBtn.setDisabled(false);
				this.formPanel.getForm().setValues(entities[0]);
				this.actionComplete();
			}
		}, this);
		return this;
	},

	delete: function () {
		this.entityStore.set({destroy: [this.currentId]}, function (options, success, response) {
			if (response.destroyed) {
				this.hide();
			}
		}, this);
	},

	actionStart: function () {
		if (this.getBottomToolbar()) {
			this.getBottomToolbar().setDisabled(true);
		}
		if (this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(true);
		}

		if (this.getFooterToolbar()) {
			this.getFooterToolbar().setDisabled(true);
		}
	},

	actionComplete: function () {
		if (this.getBottomToolbar()) {
			this.getBottomToolbar().setDisabled(false);
		}

		if (this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(false);
		}
		if (this.getFooterToolbar()) {
			this.getFooterToolbar().setDisabled(false);
		}
	},
	
	getSubmitValues : function() {
		return this.formPanel.getForm().getFieldValues();
	},

	submitForm: function () {

		if (!this.formPanel.getForm().isValid()) {
			return;
		}

		var id, params = {}, values = this.getSubmitValues();
		//		//this.id is null when new
		if (this.currentId) {

			id = this.currentId;

			params.update = {};
			params.update[this.currentId] = values;
		} else {

			id = Ext.id();
			params.create = {};
			params.create[id] = values;
		}

		this.actionStart();
		this.entityStore.set(params, function (options, success, response) {

			this.actionComplete();

			var saved = (params.create ? response.created : response.updated) || {};
			if (saved[id]) {				
				this.fireEvent("save", this, values);

				var serverId = params.create ? response.created[id].id : response.updated[id].id;

				this.entityStore.entity.goto(serverId);

				this.close();
			} else
			{
				//something went wrong
				var notSaved = (params.create ? response.notCreated : response.notUpdated) || {};
				if (!notSaved[id]) {
					notSaved[id] = {type: "unknown"};
				}

				switch (notSaved[id].type) {
					case "forbidden":
						Ext.MessageBox.alert(t("Access denied"), t("Sorry, you don't have permissions to update this item"));
						break;

					default:
						
						//mark validation errors
						for(name in notSaved[id].validationErrors) {
							var field = this.formPanel.getForm().findField(name);
							if(field) {
								field.markInvalid(notSaved[id].validationErrors[name].description);
							}
						}
						
						Ext.MessageBox.alert(t("Error"), t("Sorry, something went wrong. Please try again."));
						break;
				}
			}
		}, this);

	},

	focus: function () {
		var firstField = this.formPanel.form.items.find(function (item) {
			if (!item.disabled && item.isVisible() && item.getValue() == "")
				return true;
		});

		if (firstField) {
			firstField.focus();
		}
	},

	initFormItems: function () {
		return [
//			{
//				xtype: 'textfield',
//				name: 'name',
//				fieldLabel: "Name",
//				anchor: '100%',
//				required: true
//			}
		];
	}
});

