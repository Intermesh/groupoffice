go.modules.community.addressbook.LabelsDialog = Ext.extend(go.Window, {
	width: dp(800),
	height: dp(500),
	title: t("Labels"),
	layout: 'fit',
	initComponent: function () {

		if(!this.queryParams) {
			this.queryParams = {};
		}

		this.formPanel = new Ext.form.FormPanel({
			autoScroll: true,
			layout: 'column',
			items: [

				{
					columnWidth: .5,
					items: [{
						defaults: {
							anchor: '100%'
						},
						xtype: "fieldset",
						title: t("Page"),
						items: [
							{
								xtype: 'combo',
								name: 'pageFormat',
								fieldLabel: t('Page format'),
								mode: 'local',
								editable: false,
								triggerAction: 'all',
								store: new Ext.data.ArrayStore({
									fields: [
										'value'
									],
									data: [['A4'], ['Letter']]
								}),
								valueField: 'value',
								displayField: 'value',
								value: 'A4'
							}, {
								xtype: 'numberfield',
								fieldLabel: t('Rows'),
								value: 8,
								name: 'rows'
							}, {
								xtype: 'numberfield',
								fieldLabel: t('Columns'),
								value: 2,
								name: 'columns'
							}]
					},

					{
						columnWidth: .5,
						items: [
							{
								defaults: {
									anchor: '100%'
								},
								xtype: "fieldset",
								title: t("Page margins"),
								items: [
									{
										xtype: 'numberfield',
										fieldLabel: t('Top'),
										value: 10,
										name: 'pageTopMargin'
									}, {
										xtype: 'numberfield',
										fieldLabel: t('Right'),
										value: 10,
										name: 'pageRightMargin'
									}, {
										xtype: 'numberfield',
										fieldLabel: t('Bottom'),
										value: 10,
										name: 'pageBottomMargin'
									}, {
										xtype: 'numberfield',
										fieldLabel: t('Left'),
										value: 10,
										name: 'pageLeftMargin'
									}]
							}]
						},

						{
							defaults: {
								anchor: '100%'
							},
							xtype: "fieldset",
							title: t("Font"),
							items: [{
								xtype: 'combo',
								name: 'font',
								fieldLabel: t('Family'),
								mode: 'local',
								editable: false,
								triggerAction: 'all',
								store: new Ext.data.ArrayStore({
									fields: [
										'value',
										'display'
									],
									data: [['dejavusans', 'Deja vu Sans'], ['helvetica', 'Helvetica'], ['courier', 'Courier']]
								}),
								valueField: 'value',
								displayField: 'display',
								value: 'dejavusans'
							},
								{
									xtype: 'numberfield',
									fieldLabel: t('Size'),
									value: 9,
									name: 'fontSize'
								}]
						}]
				},
				{
					columnWidth: .5,
					items: [
						{
							defaults: {
								anchor: '100%'
							},
							xtype: "fieldset",
							title: t("Label margins"),
							items: [
								{
									xtype: 'numberfield',
									fieldLabel: t('Top'),
									value: 10,
									name: 'labelTopMargin'
								}, {
									xtype: 'numberfield',
									fieldLabel: t('Right'),
									value: 10,
									name: 'labelRightMargin'
								}, {
									xtype: 'numberfield',
									fieldLabel: t('Bottom'),
									value: 10,
									name: 'labelBottomMargin'
								}, {
									xtype: 'numberfield',
									fieldLabel: t('Left'),
									value: 10,
									name: 'labelLeftMargin'
								}]
						}, {
							defaults: {
								anchor: '100%'
							},
							xtype: 'fieldset',
							title: t("Template"),
							items: [
								{
									xtype: 'textarea',
									height: 140,
									value: '{{contact.name}}\n' +
										'[assign address = contact.addresses | filter:type:"postal" | first]\n' +
										'[if !{{address}}]\n' +
										'[assign address = contact.addresses | first]\n' +
										'[/if]\n' +
										'{{address.formatted}}',
									name: 'tpl',
									hideLabel: true
								}
							]
						}]
				}

				]
		});

		this.items = [this.formPanel];

		this.buttons = [
			'->',
			{
				text: t("Download"),
				handler: function() {
					this.print();
				},
				scope: this
			}
		]

		this.supr().initComponent.call(this);

		var v = localStorage.getItem('addressBookLabelValues');

		if(v) {
			this.formPanel.form.setValues(Ext.decode(v));
		}
	},

	print: function () {

		var me = this;
		var formValues = this.formPanel.form.getFieldValues();
		localStorage.setItem('addressBookLabelValues', Ext.encode(formValues));

		me.getEl().mask(t("Exporting..."));
		var promise = go.Jmap.request({
			method: "Contact/query",
			params: me.queryParams
		});

		go.Jmap.request({
			method: "Contact/labels",
			params: Ext.apply(
				formValues,
				{
					"#ids": {
						resultOf: promise.callId,
						path: "/ids"
					}
				})
		}).catch(function (response) {
			Ext.MessageBox.alert(t("Error"), response.message);
		})
			.then(function (response) {
				go.util.viewFile(go.Jmap.downloadUrl(response.blobId, true));
			})
			.finally(function () {
				me.getEl().unmask();
			})
	}
})