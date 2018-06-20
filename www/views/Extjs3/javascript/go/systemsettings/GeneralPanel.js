go.systemsettings.GeneralPanel = Ext.extend(Ext.form.FormPanel, {
	initComponent: function () {
		Ext.apply(this, {
			title: t('General'),
			autoScroll: true,
			iconCls: 'ic-description',
			items: [{
					xtype: "fieldset",
					defaults: {
						width: dp(240)
					},
					items: [
						{
							xtype: 'textfield',
							name: 'title',
							fieldLabel: t('Title'),
							hint: t("Used as page title and sender name for notifications")
						},
						this.languageCombo = new Ext.form.ComboBox({
							fieldLabel: t('Language'),
							name: 'language',
							store: new Ext.data.SimpleStore({
								fields: ['id', 'language'],
								data: GO.Languages
							}),
							displayField: 'language',
							valueField: 'id',
							hiddenName: 'language',
							mode: 'local',
							triggerAction: 'all',
							editable: false,
							selectOnFocus: true,
							forceSelection: true,
							hint: t("The language is automatically detected from the browser. If the language is not available then this language will be used.")
						}), {
							xtype: 'textfield',
							name: 'URL',
							fieldLabel: t('URL'),
							hint: t("The full URL to Group-Office.")
						}
					]
				}, {
					xtype: "fieldset",
					items: [
						{
							xtype: 'xcheckbox',
							name: 'maintenanceMode',
							hideLabel: true,
							boxLabel: t('Enable maintenance mode'),
							hint: t("When maintenance mode is enabled only administrators can login")
						}, {
							xtype: "xhtmleditor",
							anchor: "100%",
							height: dp(200),
							name: 'loginMessage',
							fieldLabel: t("Login message"),
							hint: t("This message will show on the login screen")
						}
					]
				}, {
					xtype: "fieldset",
					title: t("Appearance"),
					items: [
						this.colorField = new GO.form.ColorField({
							fieldLabel: t("Primary color"),
							showHexValue: true,
							value: null,
							width: 200,
							name: 'primaryColor',
							colors: [
								'EBF1E2',
								'95C5D3',
								'FFFF99',
								'A68340',
								'82BA80',
								'F0AE67',
								'66FF99',
								'CC0099',
								'CC99FF',
								'996600',
								'999900',
								'FF0000',
								'FF6600',
								'FFFF00',
								'FF9966',
								'FF9900',
								'FF6666',
								'CCFFCC',
								/* Line 1 */
								'FB0467',
								'D52A6F',
								'CC3370',
								'C43B72',
								'BB4474',
								'B34D75',
								'AA5577',
								'A25E79',
								/* Line 2 */
								'FF00CC',
								'D52AB3',
								'CC33AD',
								'C43BA8',
								'BB44A3',
								'B34D9E',
								'AA5599',
								'A25E94',
								/* Line 3 */
								'CC00FF',
								'B32AD5',
								'AD33CC',
								'A83BC4',
								'A344BB',
								'9E4DB3',
								'9955AA',
								'945EA2',
								/* Line 4 */
								'6704FB',
								'6E26D9',
								'7033CC',
								'723BC4',
								'7444BB',
								'754DB3',
								'7755AA',
								'795EA2',
								/* Line 5 */
								'0404FB',
								'2626D9',
								'3333CC',
								'3B3BC4',
								'4444BB',
								'4D4DB3',
								'5555AA',
								'5E5EA2',
								/* Line 6 */
								'0066FF',
								'2A6ED5',
								'3370CC',
								'3B72C4',
								'4474BB',
								'4D75B3',
								'5577AA',
								'5E79A2',
								/* Line 7 */
								'00CCFF',
								'2AB2D5',
								'33ADCC',
								'3BA8C4',
								'44A3BB',
								'4D9EB3',
								'5599AA',
								'5E94A2',
								/* Line 8 */
								'00FFCC',
								'2AD5B2',
								'33CCAD',
								'3BC4A8',
								'44BBA3',
								'4DB39E',
								'55AA99',
								'5EA294',
								/* Line 9 */
								'00FF66',
								'2AD56F',
								'33CC70',
								'3BC472',
								'44BB74',
								'4DB375',
								'55AA77',
								'5EA279',
								/* Line 10 */
								'00FF00', '2AD52A',
								'33CC33',
								'3BC43B',
								'44BB44',
								'4DB34D',
								'55AA55',
								'5EA25E',
								/* Line 11 */
								'66FF00', '6ED52A', '70CC33',
								'72C43B',
								'74BB44',
								'75B34D',
								'77AA55',
								'79A25E',
								/* Line 12 */
								'CCFF00', 'B2D52A', 'ADCC33', 'A8C43B',
								'A3BB44',
								'9EB34D',
								'99AA55',
								'94A25E',
								/* Line 13 */
								'FFCC00', 'D5B32A', 'CCAD33', 'C4A83B',
								'BBA344', 'B39E4D',
								'AA9955',
								'A2945E',
								/* Line 14 */
								'FF6600', 'D56F2A', 'CC7033', 'C4723B',
								'BB7444', 'B3754D', 'AA7755',
								'A2795E',
								/* Line 15 */
								'FB0404', 'D52A2A', 'CC3333', 'C43B3B',
								'BB4444', 'B34D4D', 'AA5555', 'A25E5E',
								/* Line 16 */
								'FFFFFF', '949494', '808080', '6B6B6B',
								'545454', '404040', '292929', '000000'
							]


						})
					]
				}]
		});

		go.systemsettings.NotificationsPanel.superclass.initComponent.call(this);

		this.on('render', function () {
			go.Jmap.request({
				method: "core/core/Settings/get",
				callback: function (options, success, response) {
					this.getForm().setValues(response);
				},
				scope: this
			});
		}, this);
	},

	onSubmit: function (cb, scope) {
		go.Jmap.request({
			method: "core/core/Settings/set",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				cb.call(scope, this, success);
			},
			scop: scope
		});
	}


});

