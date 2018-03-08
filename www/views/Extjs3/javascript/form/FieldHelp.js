Ext.ux.FieldHelp = Ext.extend(Object, (function(){
	function syncInputSize(w, h) {
		h = this.height || h;
		this.el.setSize(w, h);
	}

	function afterFieldRender() {
		if (!this.wrap) {
			this.wrap = this.el.wrap({
				cls: 'x-form-field-wrap'
			});
			this.positionEl = this.resizeEl = this.wrap;
			this.actionMode = 'wrap';
			this.onResize = this.onResize.createSequence(syncInputSize);
		}
		this.helpTextEl = this.wrap[this.helpAlign == 'top' ? 'insertFirst' : 'createChild']({
			cls: 'x-form-helptext',
			html: this.helpText
		});
	}

	return {
		constructor: function(t, align) {
			this.helpText = t;
			this.align = align;
		},

		init: function(f) {
			f.helpAlign = this.align;
			f.helpText = this.helpText;
			f.afterRender = f.afterRender.createSequence(afterFieldRender);
		}
	};
})());