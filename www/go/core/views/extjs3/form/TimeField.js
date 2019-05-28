go.form.TimeField = Ext.extend(Ext.form.TextField, {
	width: dp(72),
	defaultAutoCreate : {tag: 'input', type: 'time', size: '20', autocomplete: 'off'},

	setMinutes: function(minutes) {
		var duration = go.util.Format.duration(minutes);
		if(duration.length < 5) {
			duration = '0'+duration;
		}
		this.setRawValue(duration);
	},
	getMinutes() {
		return go.util.Format.minutes(this.getRawValue());
	},

	getValue: function() {
		var v = this.getRawValue();
		if(!v) {
			return;
		}
		if(v.length > 5) {
			return v;
		}
		return v+':00'; // add some second to match mysql time field 
	}
});

Ext.reg('timefield', go.form.TimeField);