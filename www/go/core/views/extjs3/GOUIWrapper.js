go.GOUIWrapper = Ext.extend(Ext.BoxComponent, {

	cls: "goui-wrapper",
	initComponent: function () {
		go.GOUIWrapper.superclass.initComponent.call(this);

		this.on("afterrender", () => {
			this.comp.render(this.el);
		}, this);
	},
});