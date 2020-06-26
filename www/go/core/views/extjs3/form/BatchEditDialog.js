go.form.BatchEditDialog = Ext.extend(go.Window, {
	width: dp(800),
	height: dp(800),
	title: 'Batch edit',
	entity: null,
	autoScroll: true,
	initComponent: function() {

		var items = [];

		if(go.Entities.get(this.entityStore).customFields) {
			var fieldsets = go.customfields.CustomFields.getFieldSets(this.entityStore);
			fieldsets.forEach(function(fs) {
				items.push(this.createFieldSet(fs));
			}, this);
		}

		this.formPanel = new Ext.form.FormPanel({
			items: items
		});

		this.items = [this.formPanel];

		this.buttons = [
			'->',
			{
				text: t("Save"),
				scope: this,
				handler: function() {
					this.submit();
				}
			}
		]
		this.supr().initComponent.call(this);

	},

	createFieldSet : function(fs) {

		var fieldSet = new Ext.form.FieldSet({
			title: fs.name
		});

		if(fs.description) {
			fieldSet.add({
				xtype: "box",
				autoEl: "p",
				html: go.util.textToHtml(fs.description)
			});
		}

		var fields = go.customfields.CustomFields.getFormFields(fs.id);

		fields.forEach(function(f) {
			f.disabled = true;
			f.hideLabel = true;

			f = Ext.create(f);

			f.on('change', function() {
				f.ownerCt.ownerCt.doLayout();
			});

			if(!f.isFormField) {
				return;
			}
			fieldSet.add({
				xtype: 'container',
				layout: 'hbox',
				items: [
					{
						xtype: 'checkbox',
						submit: false,
						width: dp(200),
						boxLabel: f.fieldLabel || f.boxLabel,
						listeners: {
							check: function(cb, checked) {
								f.setDisabled(!checked);
							}
						}
					},{
						flex: 1,
						layout: 'form',
						xtype: 'container',
						items: [f]
					}
				]
			})
		}, this);

		return fieldSet;

	},

	/**
	 *
	 * @param {int[]} ids
	 * @returns {go.form.BatchEditDialog}
	 */
	setIds : function (ids) {
		this.ids = ids;

		return this;
	},

	submit: function () {

		if (!this.formPanel.getForm().isValid()) {
			return;
		}
		//get only modified values on existing items, otherwise get all values.
		var id, params = {}, values = this.formPanel.getForm().getFieldValues(), me = this;

		params.update = {};

		this.ids.forEach(function(id) {
			params.update[id] = values;
		})

		me.getEl().mask(t("Saving..."));

		return me.entityStore.set(params).then(function(response) {
			me.close();
			return response;
		}).catch(function(error){
			console.error(error);
		}).finally(function() {
			me.getEl().unmask();
		});
	}



});