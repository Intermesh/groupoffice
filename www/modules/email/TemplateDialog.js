/* global go */

GO.email.TemplateDialog = Ext.extend(go.form.Dialog, {
	title: t('E-mail template'),
	entityStore: "EmailTemplate",
	width: dp(800),
	height: dp(600),	
	initFormItems: function () {

		return [{
				xtype: 'fieldset',
				anchor: "100% 100%",
				defaults: {
					anchor: '100%'
				},
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						allowBlank: false
					},{
						xtype: 'textfield',
						name: 'subject',
						fieldLabel: t("Subject")
					}, {
						anchor: "100% -80",
						xtype: 'xhtmleditor',
						name: 'body',
						hideLabel: true,
						listeners: {
							pasteimage: this.onPasteImage,
							scope: this
						}
					}]
			}];
	},
	
	onPasteImage : function(htmleditor, file, dataURL, imgEl, width, height) {
		
		
	}
});


