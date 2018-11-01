go.form.RadioGroup = Ext.extend(Ext.form.RadioGroup, {
	/**
	 * Gets the selected {@link Ext.form.Radio} in the group, if it exists.
	 * @return text
	 */
	getValue: function () {

		var out = go.form.RadioGroup.superclass.getValue.call(this);
		return out ? out.inputValue : null;
	}
});