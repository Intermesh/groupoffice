Ext.ns("go.modules.deprecated.deprecatedcustomfields.type");

go.customfields.type.EncryptedText = Ext.extend(go.customfields.type.Text, {
	
	name : "EncryptedText",
	
	label: t("Encrypted text"),
	
	iconCls: "ic-lock",

	renderDetailView: function(value) {
		return value ? `<div style="cursor:pointer" ext:qtip="${Ext.util.Format.htmlEncode(Ext.util.Format.htmlEncode(value))}" onclick="go.util.copyTextToClipboard('${Ext.util.Format.htmlEncode(value)}');go.Notifier.flyout({description: '${Ext.util.Format.htmlEncode(t('Value copied to clipboard'))}', time: 2000})">${t("Point to view value")}</div>` : "";
	},
	
	// getDetailField: function(customfield, config) {
	// 	return new go.detail.Property({
	// 		itemId: customfield.databaseName,
	// 		label: customfield.name,
	// 		icon: this.iconCls,
	// 		initComponent: function() {
	// 			go.detail.Property.prototype.initComponent.call(this);
	//
	// 			this.valueCmp.on("render", function() {
	// 				this.valueCmp.getEl().on("click", function() {
	// 					go.util.copyTextToClipboard(this.value);
	// 					go.Notifier.flyout({description: t("Value copied to clipboard"), time: 2000});
	// 				}, this);
	// 			}, this);
	// 		},
	// 		setValue : function(v) {
	//
	// 			this.value = this.format(v);
	// 			if(this.valueCmp.rendered) {
	// 				this.valueCmp.update(t("Point to view value"));
	// 				this.setQuickTip();
	// 			}else{
	// 				this.valueCmp.on("render", function(cmp){
	// 					cmp.update(t("Point to view value"));
	// 					this.setQuickTip();
	// 				}, this, {single: true});
	// 			}
	// 		},
	// 		setQuickTip : function() {
	// 			this.valueCmp.getEl().setStyle("cursor", "pointer");
	// 			Ext.QuickTips.register({
	// 				target: this.valueCmp,
	// 				text: this.value,
	// 				width: 100
	// 			});
	// 		}
	// 	});
	// },
	//
	getFilter : function() {
		return false;
	},

	getColumn: function(field) {

		const def = go.customfields.type.EncryptedText.superclass.getColumn.call(this, field);
		def.renderer = (v) =>{
			return v ? `<div style="cursor:pointer" ext:qtip="${Ext.util.Format.htmlEncode(v)}" onclick="go.util.copyTextToClipboard('${Ext.util.Format.htmlEncode(v)}');go.Notifier.flyout({description: '${Ext.util.Format.htmlEncode(t('Value copied to clipboard'))}', time: 2000})">${t("Point to view value")}</div>` : "";
		};

		return def;
	}
	
});

// go.customfields.CustomFields.registerType(new go.customfields.type.EncryptedText());

