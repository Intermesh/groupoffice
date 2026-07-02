go.modules.community.syncfusion.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {

	title: t("Syncfusion", "syncfusion", "community"),
	iconCls: 'ic-description',
	labelWidth: 180,
	layout: "form",
	autoScroll: true,

	initComponent: function () {

		this.librarySourceCombo = new go.form.SelectField({
			name: 'librarySource',
			fieldLabel: t('Library Source', 'syncfusion', 'community'),
			anchor: '100%',
			options: [
				['cdn', t('CDN (recommended)', 'syncfusion', 'community')],
				['local', t('Local (lib/ directory)', 'syncfusion', 'community')]
			],
			value: 'cdn',
			listeners: {
				change: function (combo, newValue) {
					this.cdnUrlField.setVisible(newValue === 'cdn');
					this.localInfoBox.setVisible(newValue === 'local');
				},
				scope: this
			}
		});

		this.cdnUrlField = new Ext.form.TextField({
			name: 'cdnUrl',
			fieldLabel: t('CDN URL', 'syncfusion', 'community'),
			anchor: '100%',
			emptyText: 'https://cdn.syncfusion.com/ej2/29.1.33/',
			allowBlank: true
		});

		this.localInfoBox = new Ext.BoxComponent({
			hidden: true,
			autoEl: {
				tag: 'div',
				style: 'padding: 4px 0; color: #888; font-size: 11px;',
				html: t('Place ej2.min.js and ej2.min.css in go/modules/community/syncfusion/lib/', 'syncfusion', 'community')
			}
		});

		this.items = [
			{
				xtype: "fieldset",
				title: t("License", "syncfusion", "community"),
				defaults: {
					anchor: '100%'
				},
				items: [
					{
						xtype: 'textfield',
						name: 'licenseKey',
						fieldLabel: t('License Key', 'syncfusion', 'community'),
						allowBlank: true
					}
				]
			},
			{
				xtype: "fieldset",
				title: t("Library", "syncfusion", "community"),
				defaults: {
					anchor: '100%'
				},
				items: [
					this.librarySourceCombo,
					this.cdnUrlField,
					this.localInfoBox
				]
			},
			{
				xtype: "fieldset",
				title: t("Service URLs", "syncfusion", "community"),
				defaults: {
					anchor: '100%'
				},
				items: [
					{
						xtype: 'textfield',
						name: 'documentServiceUrl',
						fieldLabel: t('Document Service URL', 'syncfusion', 'community'),
						allowBlank: true,
						emptyText: 'http://localhost:6002/api/documenteditor'
					},
					{
						xtype: 'textfield',
						name: 'spreadsheetServiceUrl',
						fieldLabel: t('Spreadsheet Service URL', 'syncfusion', 'community'),
						allowBlank: true,
						emptyText: 'http://localhost:6003/api/spreadsheet'
					},
					{
						xtype: 'textfield',
						name: 'serviceSecret',
						fieldLabel: t('Service Secret', 'syncfusion', 'community'),
						allowBlank: true,
						inputType: 'password',
						emptyText: t('Shared JWT secret for Docker services', 'syncfusion', 'community')
					}
				]
			},
			{
				xtype: "fieldset",
				title: t("Setup Instructions", "syncfusion", "community"),
				items: [
					{
						xtype: 'box',
						autoEl: {
							tag: 'div',
							style: 'padding: 8px; color: #555; line-height: 1.6;',
							html: '<p><b>Docker services:</b></p>' +
								'<code>docker run -d -p 6002:80 syncfusion/word-processor-server</code><br/>' +
								'<code>docker run -d -p 6003:80 syncfusion/spreadsheet-server</code>'
						}
					}
				]
			}
		];

		this.supr().initComponent.call(this);

		// Sync visibility on load
		this.on('afterrender', function () {
			var source = this.librarySourceCombo.getValue();
			this.cdnUrlField.setVisible(source === 'cdn');
			this.localInfoBox.setVisible(source === 'local');
		}, this, {single: true});
	}
});
