go.form.DurationField = Ext.extend(Ext.form.CompositeField, {

	maxLength: 5,
	//regex: /[0-1]{0,1}\d:[0-5]\d/, // eg: 02:30 (max 19:59)
	//regexText: t('Onjuist format, gebruik "uu:mm"'),
	//maskRe: /[0-9]|:/,
	cls: 'x-form-duration',

	width: 72,

	initComponent() {
		this.items = [
			this.hFld = new Ext.form.NumberField({
				name:t('Hours'),
				maxValue:23,
				style:{textAlign:'right'},
				allowNegative:false,
				decimals: 0,
				selectOnFocus:true,
				emptyText:'--',
				width: 18,
				fieldClass:'',
				focusClass:''
			}),
			{xtype:'box',html:':'},
			this.mFld = new Ext.form.NumberField({
				name:t('Minutes'),
				maxValue:59,
				allowNegative:false,
				decimals: 0,
				selectOnFocus:true,
				emptyText:'--',
				width: 18,
				fieldClass:'',
				focusClass:'',
				listeners:{'setvalue':function(me,v) {
						if(!v) v = '0';
						if(v.length < 2) {
							v = '0'+v;
						}
						me.setRawValue(v)
					}
				}})
		];

		const change = (me, val) => {
			if(!Ext.isEmpty(this.hFld.getValue()) && !Ext.isEmpty(this.mFld.getValue()) )
				this.fireEvent('change', me, this.getValue())
		}
		this.hFld.on('change', change);
		this.mFld.on('change', change);

		this.supr().initComponent.call(this);
	},

	setValue(v) {
		this.setSeconds(this.inMinutes ? v*60 : v);
	},

	setSeconds: function(seconds) {
		var hm = go.util.Format.duration(seconds, false, false).split(':');
		this.hFld.setValue(parseInt(hm[0]));
		this.mFld.setValue(parseInt(hm[1]));
	},

	setMinutes: function(minutes) {
		this.setSeconds(minutes*60);
	},

	getMinutes: function() {
		return (this.hFld.getValue()*60) + this.mFld.getValue();
	},

	getValue: function() {
		const m = this.getMinutes();
		return this.inMinutes ? m : m*60;
	},
	afterRender(ct) {
		this.supr().afterRender.call(this,ct);
		this.hFld.el.removeClass('x-form-text');
	}
});

Ext.reg('durationfield', go.form.DurationField);