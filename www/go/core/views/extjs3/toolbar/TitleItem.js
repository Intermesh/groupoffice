
go.toolbar.TitleItem = Ext.extend(Ext.Toolbar.Item, {
	/**
	 * @cfg {String} text The text to be used as innerHTML (html tags are accepted)
	 */

	constructor: function (config) {
		go.toolbar.TitleItem.superclass.constructor.call(this, Ext.isString(config) ? {text: config} : config);
	},

	// private
	onRender: function (ct, position) {
		this.autoEl = {cls: 'xtb-title', html: this.text || ''};
		go.toolbar.TitleItem.superclass.onRender.call(this, ct, position);
	},

	/**
	 * Updates this item's text, setting the text to be used as innerHTML.
	 * @param {String} t The text to display (html accepted).
	 */
	setText: function (t) {
		if (this.rendered) {
			this.el.update(t);
		} else {
			this.text = t;
		}
	}
});
Ext.reg('tbtitle', go.toolbar.TitleItem);

