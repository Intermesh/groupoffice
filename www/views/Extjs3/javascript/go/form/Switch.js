go.form.Switch = Ext.extend(Ext.ux.form.XCheckbox, {
	
	//defaultAutoCreate : { tag: 'input', type: 'hidden', autocomplete: 'off'},
	
	onRender:function(ct) {

		// call parent
		go.form.Switch.superclass.onRender.apply(this, arguments);

		this.wrap.addClass('x-switch');

		this.wrap.child('label').removeClass('x-form-cb-label');

		this.thumb = this.el.insertSibling({
			tag:'span',
			cls:'thumb'
		}, 'after');
		this.track = this.el.insertSibling({
			tag:'span',
			cls:'track'
		}, 'after');

	}
});

Ext.reg('switch', go.form.Switch);
