go.form.DurationField = Ext.extend(Ext.form.CompositeField, {

	maxLength: 5,
	//regex: /[0-1]{0,1}\d:[0-5]\d/, // eg: 02:30 (max 19:59)
	//regexText: t('Onjuist format, gebruik "uu:mm"'),
	//maskRe: /[0-9]|:/,
	cls: 'x-form-duration',

	submit: true, //override for compositefield

	maxHours: 23,

	width: 72,

	initComponent() {
		console.log((this.maxHours+"").length);
		const w = (this.maxHours+"").length;
		this.items = [
			this.hFld = new Ext.form.NumberField({
				name:t('Hours'),
				maxValue:this.maxHours,
				autoCreate: {tag: 'input', type: 'text', size: w, autocomplete: 'off', maxlength: w},
				maxLength:w,
				enableKeyEvents: true,
				listeners: {
					// When hour field is bigger then 2 or has string length 2 "01"
					'keyup': (me,e) => {
						const v = e.target.value, key =  e.keyCode;
						if(this.maxHours===23 && key >= 48 && key <= 57 && (v.length > 1 || parseInt(v) > 2)) {
							me.nextSibling().nextSibling().focus();
						}
					}
				},
				style:{textAlign:'right'},
				allowNegative:false,
				minValue: 0,
				decimals: 0,
				selectOnFocus:true,
				emptyText:'--',
				width: w*9,
				fieldClass:'',
				focusClass:''
			}),
			{xtype:'box',html:':'},
			this.mFld = new Ext.form.NumberField({
				name:t('Minutes'),
				maxValue:59,
				autoCreate: {tag: 'input', type: 'text', size: '2', autocomplete: 'off', maxlength: '2'},
				maxLength:2,
				allowNegative:false,
				minValue: 0,
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
			if(!Ext.isEmpty(this.hFld.getValue()) )// && !Ext.isEmpty(this.mFld.getValue()) )
				this.fireEvent('change', me, this.getValue())
		}
		this.hFld.on('change', change);
		this.mFld.on('change', change);

		this.supr().initComponent.call(this);
	},

	setValue: function(v) {
		this.setSeconds(this.inMinutes ? v*60 : v);
	},

	isDirty : function() {
		if(this.disabled || !this.rendered) {
			return false;
		}
		return String(this.getValue()) !== String(this.originalValue);
	},

	setSeconds: function(seconds) {
		var hm = go.util.Format.duration(seconds, false, false).split(':');
		this.hFld.setValue(parseInt(hm[0]));
		this.mFld.setValue(parseInt(hm[1]));
	},

	// override if needed
	getErrors: function(v) {
		//return [];
		const hErrors = this.hFld.getErrors();
		hErrors.push(...this.mFld.getErrors());
		return hErrors;
	},

	validate : function(){
		if(this.disabled || this.validateValue(this.getValue())){
			this.clearInvalid();
			return true;
		}
		return false;
	},

	validateValue: function(value, preventMark) {
		var error = this.getErrors(value)[0];

		if (error == undefined) {
			return true;
		} else {
			this.markInvalid(error);
			return false;
		}
	},

	setMinutes: function(minutes) {
		this.setSeconds(minutes*60);
	},

	getMinutes: function() {
		return (+this.hFld.getValue() * 60) + +this.mFld.getValue();
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