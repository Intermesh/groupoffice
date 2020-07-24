go.pdftemplate.BlocksField = Ext.extend(go.form.FormGroup, {
	xtype: "formgroup",
	sortable: true,
	name: "blocks",
	addButtonText: t("Add block"),
	itemCfg: {
		items: [{
			xtype: "container",
			layout: "form",
			defaults:  {
				anchor: "100%"
			},
			hideLabel: true,
			items: [{
				xtype: "hidden",
				name: "id" //for keeping records
			},

				{
					xtype: "container",
					layout: 'hbox',

					defaults: {
						flex: 1,
						layout: "form",
						defaults: {
							anchor: "100%"
						},
						labelAlign: 'top',
						cls: "go-form-panel"
					},
					items:[{
						items: [{
							fieldLabel: "x",
							name: "x",
							xtype: "gonumberfield",
							decimals: 0,
							value: null
						}]
					}, {
						items: [{
							fieldLabel: "y",
							name: "y",
							xtype: "gonumberfield",
							decimals: 0,
							value: null
						}]
					}, {
						items: [{
							fieldLabel: t("Width"),
							name: "width",
							xtype: "gonumberfield",
							decimals: 0,
							value: null
						}]
					}, {
						items: [{
							fieldLabel: t("Height"),
							name: "height",
							xtype: "gonumberfield",
							decimals: 0,
							value: null
						}]
					}, {
						items: [{
							fieldLabel: t("Align"),
							name: "align",
							xtype: "textfield",
							decimals: 0,
							value: "L"
						}]
					}, {
						items: [{
							fieldLabel: t("Type"),
							name: "type",
							xtype: "textfield",
							decimals: 0,
							setFocus: true,
							value: "html"
						}]
					}]
				},

				{

				xtype: "textarea",
					hideLabel: true,
				//height: dp(48),
				allowBlank: false,
				// grow: true,
				name: "content",
				// growMin : dp(24),
				height: dp(400),
				// setSize: function(w, h) {
				// 	//somehow needed in hbox layout :(
				// 	Ext.form.TextArea.prototype.setSize.call(this, w, h);
				// 	this.autoSize();
				// },
				// listeners: {
				// 	autosize: function (field, h) {
				// 		//needed for making hbox layout grow along
				// 		field.ownerCt.ownerCt.setHeight(h);
				// 	}
				// }
			}

				]
		}]
	}
});

Ext.reg("goblocksfield", go.pdftemplate.BlocksField );