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

	nodeAttributes: {},

	store: new Ext.data.ArrayStore({
		// autoDestroy: true,
		storeId: "options_renderModes",
		idIndex: 0,
		fields: [
			"value",
			"label"
		]
	}),

	initComponent: function () {
		this.store.loadData([["row", t("Row")], ["column", t("Column")]]);

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
						maskRe: /[^:]/
					},
					this.fgColorField = new GO.form.ColorField({
						fieldLabel: t("Text color"),
						name: "foregroundColor"
					}),
					this.bgColorField = new GO.form.ColorField({
						fieldLabel: t("Background color"),
						name: "backgroundColor"
					}),
					this.renderModeCombo = new go.form.ComboBoxReset({
						fieldLabel: t("Render mode"),
						name: "renderMode",
						store: this.store,
						entityStore: null,
						valueField: "value",
						displayField: "label",
						triggerAction: "all",
						allowBlank: go.util.empty(this.fgColorField.curColor) && go.util.empty(this.bgColorField.curColor)
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
				handler: function() {
					// debugger;
					if(go.util.empty(this.fgColorField.getValue()) && go.util.empty(this.bgColorField.getValue())) {
						this.renderModeCombo.clearValue();
					}
					const form = this.form.getForm();
					if(!form.validate()) {
						return;
					}
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
	},
});

