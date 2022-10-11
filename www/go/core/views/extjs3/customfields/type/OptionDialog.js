/* global go, Ext */

go.customfields.type.OptionDialog = Ext.extend(go.Window, {
	title: t('Edit custom select option'),
	modal: true,
	autoScroll: false,
	draggable: false,
	width: dp(500),
	height: dp(500),
	maximizable: false,
	entityStore: null,
	cancelOnEsc: true,
	completeOnEnter: true,
	buttonAlign: "left",
	layout: "fit",
	doSave: false,

	nodeAttributes: {},

	initComponent: function () {
		this.items = [this.form = new Ext.form.FormPanel({
			items: [{
				xtype: "fieldset",
				labelWidth: dp(160),
				items: [
					{
						xtype: "textfield",
						fieldLabel: t("Text"),
						name: "text",
						allowBlank: false,
						maskRe: /[^:]/,
						width: 200
					},
					this.fgColorField = new GO.form.ColorField({
						fieldLabel: t("Text color"),
						name: "foregroundColor"
					}),
					this.bgColorField = new GO.form.ColorField({
						fieldLabel: t("Background color"),
						name: "backgroundColor"
					}),
					this.renderModeCombo = new Ext.form.ComboBox({
						fieldLabel: t("Render mode"),
						name: "renderMode",
						store : new Ext.data.SimpleStore({
							fields : ['value', 'label'],
							data : [
								['cell', t("Cell")],
								['row', t("Row")]
							]
						}),
						mode: "local",
						valueField: "value",
						displayField: "label",
						triggerAction: "all",
						editable : false,
						selectOnFocus : true,
						width : 200
					})
				],
			}
			],
			cls: 'go-form-panel'
		})
		];
		this.buttons = [
			'->',
			this.saveButton = new Ext.Button({
				cls: "primary",
				text: t("Save"),
				handler: ()  => {
					if (this.isBlankSelected(this.fgColorField) && this.isBlankSelected(this.bgColorField)) {
						this.renderModeCombo.setValue(null);
					} else if(go.util.empty(this.renderModeCombo.getValue())) {
						this.renderModeCombo.markInvalid();
						this.renderModeCombo.focus();
						return false;
					}
					const form = this.form.getForm();
					let valid = true, fn = function (i) {
						if (!i.validate()) {
							valid = false;
							i.markInvalid();
							i.focus();
							return false;
						}
					};
					form.items.each(fn, this);

					if(!valid) {
						return false;
					}
					this.doSave = true;
					Ext.apply(this.nodeAttributes, form.getValues(), this.oldAttributes);
					this.close();
				},
				scope: this
			})
		];
		this.supr().initComponent.call(this);
	},

	load: function(node) {
		let form = this.form.getForm();
		this.nodeAttributes = {
			text: node.attributes.text,
			foregroundColor: node.attributes.foregroundColor,
			backgroundColor: node.attributes.backgroundColor,
			renderMode: node.attributes.renderMode
		}
		this.oldAttributes = this.nodeAttributes;
		form.setValues(node.attributes);
		this.renderModeCombo.allowBlank = this.isBlankSelected(this.fgColorField) && this.isBlankSelected(this.bgColorField);
	},

	isBlankSelected: function(fld) {
		const value = fld.getValue();
		return (go.util.empty(value) || value.toLowerCase() === 'ffffff');
	}
});

