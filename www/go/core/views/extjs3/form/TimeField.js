go.form.TimeField = Ext.extend(Ext.form.TextField, {
	width: dp(96),
	defaultAutoCreate : {tag: 'input', type: 'time', size: '20', autocomplete: 'off'},
	// set true to get/set integer seconds/minutes value instead of time string
	asInteger: true,
	// set true to get/set value in minutes instead of seconds
	inMinutes: false,

	initComponent: function() {
		if(!this.allowBlank) {
			// https://bugzilla.mozilla.org/show_bug.cgi?id=1479708
			// Disable clear button if a field is required
			this.defaultAutoCreate.required = true;
		}
	},

	onBlur: function() {

		if(Ext.isSafari) {
			var v = this.getRawValue();
			if(!Ext.isEmpty(v) && v.indexOf(':') === -1) {
				this.setRawValue(v + ':00');
			}
		}

		go.form.TimeField.superclass.onBlur.call(this);
	},

	setSeconds: function(seconds) {
		var duration = go.util.Format.duration(seconds, true, false);
		if(duration.length < 5) {
			duration = '0'+duration;
		}
		this.setRawValue(duration);
	},

	setMinutes: function(minutes) {
		this.setSeconds(minutes*60);
	},

	getSeconds: function() {
		return this.getMinutes() * 60; // this field cant display/set seconds
	},

	getMinutes: function(minutes) {
		return go.util.Format.minutes(this.getRawValue());
	},

	setValue : function(v) {
		if(!go.util.empty(v)) {
			if(this.asInteger) {
				if(this.inMinutes) {
					this.setMinutes(v);
				} else {
					this.setSeconds(v);
				}
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
		if(this.asInteger) {
			if(this.inMinutes) {
				return this.getMinutes();
			} else {
				return this.getSeconds();
			}
		}
		var v = this.getRawValue();
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