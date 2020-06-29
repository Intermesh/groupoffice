go.form.TimeField = Ext.extend(Ext.form.TextField, {
	width: dp(90),
	defaultAutoCreate : {tag: 'input', type: 'time', size: '20', autocomplete: 'off'},
	inMinutes: false,

	onBlur: function() {

		if(Ext.isSafari) {
			var v = this.getRawValue();
			if(!Ext.isEmpty(v) && v.indexOf(':') === -1) {
				this.setRawValue(v + ':00');
			}
		}

		go.form.TimeField.superclass.onBlur.call(this);
	},

	setMinutes: function(minutes) {
		var duration = go.util.Format.duration(minutes);
		if(duration.length < 5) {
			duration = '0'+duration;
		}
		this.setRawValue(duration);
	},

	getMinutes: function() {
		return go.util.Format.minutes(this.getRawValue());
	},

	setValue : function(v) {
		if(!go.util.empty(v)) {
			if(this.inMinutes) {
				this.setMinutes(v);
				return;
			}
			var parts = v.split(":");
			if(parts.length == 3) {
				parts.pop(); //pop seconds
			}
			v = parts.join(":");
		} 
		go.form.TimeField.superclass.setValue.call(this, v);
	},

	getValue: function() {
		var v = this.getRawValue();
		if(this.inMinutes) {
			return this.getMinutes();
		}
		if(!v) {
			return;
		}
		if(v.length > 5) {
			return v;
		}
		return v + ':00'; // add some second to match mysql time field 
	}
});

Ext.reg('nativetimefield', go.form.TimeField);