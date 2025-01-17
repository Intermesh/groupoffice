import {SelectField, Config, createComponent, FieldEventMap, DateInterval} from "@intermesh/goui";
import {t} from "./Index.js";

interface Alert {
	trigger:any // {offset, relativeTo} | {when}
	acknowledged?:string
	action?:'display'|'email',

}

export class AlertField extends SelectField {
	// is this a/for full day event
	fullDay = false
	// is this field use for the calendar its default alerts
	isForDefault = false
	// should the event use the default instead
	useDefault = false
	constructor() {
		super();
		this.name = 'alerts';
		this.label = t('Reminder');
		this.drawOptions();
		this.on('change',(me,v) => {
			this.useDefault = (me.control as HTMLSelectElement).value === 'default';
		})
	}

	drawOptions() {
		this.options = this.fullDay ? [
			{value: 'P0D', name: t('At the start day') + " (9:00)"},
			{value: '-P1D', name: '1 '+ t('day')+' '+t('before') + " (9:00)"},
			{value: '-P2D', name: '2 '+ t('days')+' '+t('before')+ " (9:00)"},
			{value: '-P7D', name: '1 '+ t('week')+' '+t('before')+ " (9:00)"},
		] : [
			{value: '-PT5M', name: '5 '+ t('minutes')+' '+t('before')},
			{value: '-PT10M', name: '10 '+ t('minutes')+' '+t('before')},
			{value: '-PT15M', name: '15 '+ t('minutes')+' '+t('before')},
			{value: '-PT1H', name: '1 '+ t('hour')+' '+t('before')},
			{value: '-PT2H', name: '2 '+ t('hours')+' '+t('before')},
			{value: 'P0D', name: t('At the start')},
		];
		if(!this.isForDefault) {
			this.value = 'default';
			this.options.unshift({value: 'default', name: t('Default')});
		}
		this.options.unshift({value: null, name: t('None')})
		super.drawOptions();
	}

	get value() {

		const v = super.value;
		if(v && v !== 'default')
			this.addOptionIfNotExist(v as string);
		return (v && v !== 'default') ? {1:{trigger:{offset:v}}} : (v === 'default' ? {} : null);
	}

	set value(v: {[id:string]:Alert}|'default'|null) {

		if(!v) {
			v = null;
		} else if(!(typeof v === 'string') ) {
			const firstKey = Object.keys(v)[0];
			if(v[firstKey]) {
				v = v[firstKey].trigger ? v[firstKey].trigger.offset : v[firstKey].trigger.when;
			} else {
				v = null;
			}
		}

		this.useDefault = v ==='default';

		if(v && v !== 'default')
			this.addOptionIfNotExist(v as never);

		super.value = v as never;
	}


	private addOptionIfNotExist(offset: string) {
		for (const [key, value] of Object.entries(this.options)) {
			if(value.value == offset) {
				return; // found, exit
			}
		}
		// not found, add
		const opts = this.options
		opts.push({value: offset, name: this.nameFromOffset(offset)});

		this.options = opts;
	}

	private nameFromOffset(offset: string) {
		const d = new DateInterval(offset);
		let time = [];
		if(d.days) time.push(d.days + ' ' + t(d.days > 1 ? 'days' : 'day'));
		if(d.hours) time.push(d.hours + ' ' + t(d.hours > 1 ? 'hours' : 'hour'));
		if(d.minutes) time.push(d.minutes + ' ' + t(d.minutes > 1 ? 'minutes' : 'minute'));
		time.push(t(d.invert ? 'before' : 'after'));
		return time.join(' ');
	}

	setDefaultLabel(alerts?: {[id:string]:Alert}) {
		let txt = t('None');
		if (alerts) {
			const firstKey = Object.keys(alerts)[0];
			if(alerts[firstKey]) {
				const duration = this.parseDuration(alerts[firstKey].trigger.offset);
				txt = this.durationToText(duration);
			}
		}
		this.input!.options[1].innerText = t('Default') + ' ('+txt+')';
	}

	private durationToText(duration:any) {
		let str = [];
		// if(duration.year) {
		// 	str += (duration.year) + ' ' + (duration.year === 1 ? t('year') : t('years')));
		// }
		if(duration.day) {
			str.push(duration.day + ' ' + (duration.day === 1 ? t('day') : t('days')));
		}
		if(duration.hour) {
			str.push(duration.hour + ' ' + (duration.hour === 1 ? t('hour') : t('hours')));
		}
		if(duration.minute) {
			str.push(duration.minute + ' ' + (duration.minute === 1 ? t('minute') : t('minutes')));
		}

		return str.join(', ') + ' ' + (duration.negative ? t('before') : t('after'));
	}

	private parseDuration(val: string) {
		const value: any = {negative:false},
			p = val.split('');
		if(p[0] == '-') {
			value.negative = true;
			p.shift();
		}
		if(p[0] == 'P') p.shift();
		let time = false;
		let num = '';
		for(const char of p) {
			if(/[A-Z]/.test(char!)) {
				const n = parseFloat(num);
				switch(char) {
					case 'T': time = true; break;
					case 'Y': value.year = n; break;
					case 'M': time ? (value.minute = n) : (value.month = n); break;
					case 'D': value.day = n; break;
					case 'W': value.week = n; break;
					case 'H': value.hour = n; break;
					case 'S': value.second = n; break;
				}
				num = '';
			} else {
				num += char;
			}
		}
		return value;
	}

}

export const alertfield = (config?: Config<AlertField, FieldEventMap<AlertField>>) => createComponent(new AlertField(), config);