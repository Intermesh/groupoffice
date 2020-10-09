 /* global Ext, go */

go.customfields.type.TemplateFieldDialog = Ext.extend(go.customfields.FieldDialog, {
	 height: dp(600),
	 initFormItems : function() {

		 var items =  go.customfields.type.TemplateFieldDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				 xtype: "box",
				 html: '<p><a target="_blank" class="normal-link" href="https://groupoffice.readthedocs.io/en/latest/system-settings/custom-fields.html#template-field">' + t("Visit documentation page") + '</a></p>'
		 },{
				xtype: "textarea",
				name: "options.template",
				fieldLabel: t("Template"),
				grow: true,
				anchor: "100%",
			 	value: '[assign firstContactLink = entity | links:Contact | first]{{firstContactLink.name}}'
			}]);

		 //remove form field props
		 items[0].items.splice(3,3);

		 //remove validation props
		 items[0].columnWidth = 1;
		 items.splice(1, 1);

		 return items;
	 }
 });
