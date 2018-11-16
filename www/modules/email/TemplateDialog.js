/* global go */

GO.email.TemplateDialog = Ext.extend(go.form.Dialog, {
	title: t('E-mail template'),
	entityStore: "EmailTemplate",
	width: dp(600),
	height: dp(400),
	autoScroll: true,
	initFormItems: function () {

		return [{
				xtype: 'fieldset',
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


