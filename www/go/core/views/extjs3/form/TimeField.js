go.form.TimeField = Ext.extend(Ext.form.TextField, {
	width: dp(72),
	defaultAutoCreate : {tag: 'input', type: 'time', size: '20', autocomplete: 'off'},

	setTime: function(minutes) {
		var duration = go.util.Format.duration(minutes);
		if(duration.length < 5) {
			duration = '0'+duration;
		}
		this.setRawValue(duration);
	},

	getValue: function() {
		var v = this.getRawValue();
		if(!v) {
			return;
		}
		return v+':00'; // add some second to match mysql time field 
	}
});

Ext.reg('timefield', go.form.TimeField);