Ext.ns("go.modules.deprecated.deprecatedcustomfields.type");

go.customfields.type.EncryptedText = Ext.extend(go.customfields.type.Text, {
	
	name : "EncryptedText",
	
	label: t("Encrypted text"),
	
	iconCls: "ic-lock",	
	
	getDetailField: function(customfield, config) {
		return new go.detail.Property({
			itemId: customfield.databaseName,
			label: customfield.name,
			icon: this.iconCls,
			setValue : function(v) {
				this.value = this.format(v);
				if(this.rendered) {
					this.valueCmp.update(t("Point to view value"));
						Ext.QuickTips.register({
							target: this.valueCmp,
							text: this.value,
							width: 100
					});
				} 
				
				
			}
		});
	},
	
	getFilter : function() {
		return false;
	}
	
});

go.customfields.CustomFields.registerType(new go.customfields.type.EncryptedText());

