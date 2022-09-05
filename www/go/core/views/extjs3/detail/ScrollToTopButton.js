go.detail.ScrollToToButton = Ext.extend(Ext.Button, {

	iconCls: 'ic-arrow-circle-up',
	cls: "accent-icon",
	tooltip: t("Scroll to top"),
	handler: function(btn) {
		btn.getDetailPanel().body.scrollTo("top");
	},
	listeners: {
		afterrender: function(btn) {
			const scrollEl = btn.getDetailPanel().body;
			scrollEl.on("scroll", (e) => {
				btn.setVisible(scrollEl.dom.scrollTop > 30)
			}, this, {buffer: 200});
			btn.setVisible(false);
		}
	},

	getDetailPanel : function() {
		return this.ownerCt.ownerCt;
	}
});