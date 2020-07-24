/* global go */

go.pdftemplate.TemplateDialog = Ext.extend(go.form.Dialog, {
	title: t('E-mail template'),
	entityStore: "PdfTemplate",
	width: dp(1000),
	height: dp(800),
	formPanelLayout: "form",
	resizable: true,
	maximizable: true,
	collapsible: true,
	modal: false,

	initFormItems: function () {


		return [{
			xtype: 'fieldset',
			defaults: {
				anchor: '100%'
			},
			items: [{
				xtype: 'textfield',
				name: 'name',
				fieldLabel: t("Name")
			}, {
				xtype: 'golanguagecombo'
			}]
		},{
			title: t("Page"),
			xtype: 'fieldset',
			defaults: {
				anchor: '100%'
			},
			items: [{
				xtype: "checkbox",
				fieldLabel: t("Landscape"),
				name: "landscape"
			},{
				xtype: 'combo',
				name: 'pageSize',
				fieldLabel: t('Page size'),
				mode: 'local',
				editable: false,
				triggerAction: 'all',
				value: "A4",
				store: new Ext.data.ArrayStore({
					fields: [
						'value'
					],
					data: [['A4'], ['Letter']]
				}),
				valueField: 'value',
				displayField: 'value'
			},{
				xtype: 'combo',
				name: 'measureUnit',
				fieldLabel: t('Measure unit'),
				mode: 'local',
				editable: false,
				triggerAction: 'all',
				value: "mm",
				store: new Ext.data.ArrayStore({
					fields: [
						'value',
						'display'
					],
					data: [['mm', 'Milimeters'], ['in', 'Inches'], ['pt', 'Points'], ['cm', 'Centimeters']]
				}),
				valueField: 'value',
				displayField: 'display'
			},

			this.fileUploadField = new go.form.FileButtonField({
				fieldLabel: t("Stationary PDF"),
				name: 'stationary',
				anchor: '100%',
				accept: '.pdf'
			})]
		}, {
			xtype: 'fieldset',
			title: t("Margins"),
			layout: 'hbox',
			defaults: {
				layout: 'form',
				labelAlign: 'top',
				cls: 'go-form-panel'
			},
			items: [{
				items: [{
					xtype: 'gonumberfield',
					fieldLabel: t('Top'),
					name: 'marginTop',
					value: 10
				}]
			}, {
				items: [{
					xtype: 'gonumberfield',
					fieldLabel: t('Right'),
					name: 'marginRight',
					value: 10
				}]
			},{
				items: [{
					xtype: 'gonumberfield',
					fieldLabel: t('Bottom'),
					name: 'marginBottom',
					value: 10
				}]
			}, {
				items: [{
					xtype: 'gonumberfield',
					fieldLabel: t('Left'),
					name: 'marginLeft',
					value: 10
				}]
			}]
		}, {
			xtype: 'fieldset',
			title: t("Blocks"),
			items: [{
				xtype: "goblocksfield"
			}]
		}];
	}
});


