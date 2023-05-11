/* global go, Ext */

go.customfields.type.AttachmentsDialog = Ext.extend(go.customfields.FieldDialog, {
	height: dp(800),

	initFormItems () {
		var items = this.supr().initFormItems.call(this);

		items.push({
			columnWidth: 1,
			xtype: "fieldset",
			title: t("Options"),
			items: [
				{
					xtype: 'radiogroup',
					fieldLabel: t("File types"),
					name: "options.accept",
					value: '*/*',
					items: [
						{boxLabel: t("All"), inputValue: '*/*'},
						{boxLabel: t("Images"), inputValue: 'image/*'},
						{boxLabel: t("Video"), inputValue: 'video/*'},
						{boxLabel: t("Documents"), inputValue: '.xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf'},
						{boxLabel: t("PDFs"), inputValue: 'application/pdf'}
					]

				},{
					xtype: 'radiogroup',
					fieldLabel: t("File selection"),
					name: "options.multiFileSelect",
					value: false,
					items: [
						{boxLabel: t("Single"), inputValue: false},
						{boxLabel: t("Multiple"), inputValue: true}
					]
				// }, {
				// 	xtype: 'checkbox',
				// 	fieldLabel: t("Show file description field"),
				// 	name: "options.allowDescription",
				// 	value: true
				}
			]
		});

		return items;
	}
});
