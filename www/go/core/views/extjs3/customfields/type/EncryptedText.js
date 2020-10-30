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
			initComponent: function() {
				go.detail.Property.prototype.initComponent.call(this);

				this.valueCmp.on("render", function() {
					this.valueCmp.getEl().on("click", function() {
						go.util.copyTextToClipboard(this.value);
						go.Notifier.flyout({html: t("Value copied to clipboard"), time: 2000});
					}, this);
				}, this);
			},
			setValue : function(v) {

				this.value = this.format(v);
				if(this.valueCmp.rendered) {
					this.valueCmp.update(t("Point to view value"));
					this.setQuickTip();
				}else{
					this.valueCmp.on("render", function(cmp){
						cmp.update(t("Point to view value"));
						this.setQuickTip();
					}, this, {single: true});
				}
			},
			setQuickTip : function() {
				this.valueCmp.getEl().setStyle("cursor", "pointer");
				Ext.QuickTips.register({
					target: this.valueCmp,
					text: this.value,
					width: 100
				});
			}
		});
	},
	
	getFilter : function() {
		return false;
	}
	
});

// go.customfields.CustomFields.registerType(new go.customfields.type.EncryptedText());

