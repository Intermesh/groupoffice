/* global go */

go.pdftemplate.TemplateDialog = Ext.extend(go.form.Dialog, {
	title: t('PDF template'),
	entityStore: "PdfTemplate",
	width: dp(1000),
	height: dp(800),
	formPanelLayout: "column",
	resizable: true,
	maximizable: true,
	collapsible: true,
	modal: false,

	initFormItems: function () {

		this.addPanel({
			title: t("Content blocks"),
			items: [
				{
					xtype: 'fieldset',
					items: [{
						xtype: "goblocksfield"
					}]
				}
			]
		});

		return [{
			columnWidth: 1,
			labelAlign: "top",
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
			columnWidth: .5,
			labelAlign: "top",
			title: t("Page"),
			xtype: 'fieldset',
			defaults: {
				anchor: '100%'
			},
			items: [{
				xtype: "checkbox",
				boxLabel: t("Landscape"),
				hideLabel: true,
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
			columnWidth: .5,
			xtype: 'fieldset',
			title: t("Margins"),
			labelAlign: 'top',

			items: [{
				xtype: 'gonumberfield',
				fieldLabel: t('Top'),
				name: 'marginTop',
				value: 10
			},{
				xtype: 'gonumberfield',
				fieldLabel: t('Right'),
				name: 'marginRight',
				value: 10
			},{
				xtype: 'gonumberfield',
				fieldLabel: t('Bottom'),
				name: 'marginBottom',
				value: 10
			},{
				xtype: 'gonumberfield',
				fieldLabel: t('Left'),
				name: 'marginLeft',
				value: 10
			}]

		}];
	}
});


