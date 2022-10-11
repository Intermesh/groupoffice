interface Date {
	/** Valid characters: YmdHi */
	to(format: string): string;
	/** Use browser for short date format */
	toShort(): string;
	/** from the Date.days array */
	getDayName(): string;
	/** The week number */
	getWeek(): number;
	/** Respects firstDayOfWeek 0 = firstday */
	getWeekDay(): number
	getDayOfYear() : number
	/** Jump to first day of a specific week */
	setWeek(week: number): void; // Not working with getWeek() to to to next week
	clone(): Date;
	changeTime(h?:number,m?:number,s?:number) : this
	/** return ISO8601 duration from time distance */
	diff(end:Date) : string
	/** Jump to day in current week */
	setDay(day: number): this;
	/** add day to date (can be negative) */
	add(amount:number, unit: 'd'|'m'|'y'|'w'): this;
	addDuration(iso8601: string) : this;
	toJmap(): string
	toUTCJmap(): string
	toSmart(): string
}
interface DateConstructor {
	fromYmd(ymd: string): Date
	/** find short date format and reverse */
	fromShortDate(shortDate: string): Date;
	fromWeek(year: number, week: number): Date
	dateFormat: string
	days: string[]
	months: string[]
	period: string[]
	period1: string[]
	firstWeekday: number
}
function pad(n: string|number): string {
	return (n < 10 ? '0':'') + n;
}
Object.assign(Date, {
	fromWeek(year: number, week: number) {
		let w = new Date(year, 1, 1)
		w.setWeek(week);
		return w;
	},
	fromYmd(ymd: string) {
		let val = ymd.split('-');
		return new Date(+val[0], (+val[1]) - 1, +val[2]);
	},
	fromShortDate(shortDate: string): Date {
		let tester = new Date('1971-02-03'),
			result = tester.toShort(),
			dayPos = result.indexOf('3'),
			monthPos = result.indexOf('2'),
			yearPos = result.indexOf('71'),
			sep = result.match(/[\D]/),
			parts = shortDate.split(sep![0]),
			order:string[] = [], yearFirst = false;
		if (yearPos < monthPos) {
			yearFirst = true;
			order.push(parts[0]);
		} else {
			order.push(parts[2]);
		}
		if (dayPos < monthPos) { // day before month
			order.push(pad(parts[yearFirst ? 2 : 1]));
			order.push(pad(parts[yearFirst ? 1 : 0]));
		} else { // day after month
			order.push(pad(parts[yearFirst ? 1 : 0]));
			order.push(pad(parts[yearFirst ? 2 : 1]));
		}
		return new Date(order.join('-'));
	},
	period: ['seconden', 'minuten', 'uren', 'dagen', 'weken', 'maanden', 'jaar'], // meervoud jaar na telwoord = jaar
	period1: ['seconde', 'minuut', 'uur', 'dag', 'week', 'maand', 'jaar'], // enkelvoud
	days: [],// ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	months: [], //['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'Oktober', 'November', 'December'];
	firstWeekday: 1, //0=sunday, 1=monday
	dateFormat: 'd-m-Y'
});

const durationRegex = /(-)?P(?:([.,\d]+)Y)?(?:([.,\d]+)M)?(?:([.,\d]+)W)?(?:([.,\d]+)D)?(?:T(?:([.,\d]+)H)?(?:([.,\d]+)M)?(?:([.,\d]+)S)?)?/;

Object.assign(Date.prototype, {
	to(format: string) {
		return format
			.replace(/Y/, this.getFullYear() as unknown as string)
			.replace(/y/, (""+this.getFullYear()).substring(2, 4))
			.replace(/e/, this.getDayOfYear() as unknown as string)
			.replace(/m/, pad(this.getMonth() + 1))
			.replace(/d/, pad(this.getDate()))
			.replace(/j/, this.getDate() as unknown as string)
			.replace(/w/, this.getWeek() as unknown as string)
			.replace(/H/, pad(this.getHours()))
			.replace(/i/, pad(this.getMinutes()))
			.replace(/s/, pad(this.getSeconds()))
			.replace(/M/, "\t") // use \t as tmp otherwise November will be Novovember
			.replace(/D/, "\n")
			.replace(/l/, "\r")
			.replace(/N/, Date.months[this.getMonth()].substring(0, 3))
			.split("\t").join(Date.months[this.getMonth()])
			.split("\n").join(this.getDayName().substring(0, 2))
			.split("\r").join(this.getDayName());
	},
	toShort() {
		return (new Intl.DateTimeFormat()).format(this);
	},
	getDayName() {
		return Date.days[this.getDay()];
	},
	getDayOfYear() {
		return (Date.UTC(this.getFullYear(), this.getMonth(), this.getDate()) - Date.UTC(this.getFullYear(), 0, 0)) / 24 / 60 / 60 / 1000;
	},
	getWeekDay() {
		return (this.getDay()==0 ? 6 : (this.getDay() - Date.firstWeekday)); // only for mondays now
	},
	getWeek() {
		var awn = Math.floor(Date.UTC(this.getFullYear(), this.getMonth(), this.getDate() + 3) / 864e5 / 7), // an Absolute Week Number
			wYr = new Date(awn * 6048e5).getUTCFullYear();

		return awn - Math.floor(Date.UTC(wYr, 0, 7) / 6048e5) + 1;
	},
	setWeek(week) {
		this.setMonth(0);
		this.setDate(1);
		this.add((week - 1) * 7,'d');
		this.setDay(Date.firstWeekday);
	},
	setDay(day: number) { // 0 = sunday 6 = saturday // TODO: test
		let days = (Date.firstWeekday === 1 && this.getDay() === 0) ? 8 : this.getDay()+1;
		let diff = this.getDate() - days + Date.firstWeekday;
		this.setDate(diff + day);
		return this;
	},
	changeTime(h=0,m=0,s=0) {
		this.setHours(h,m,s);
		return this;
	},
	clone() {
		return new Date(+this);
	},
	add(amount: number, unit: 'd' | 'm' | 'y' | 'w') {
		switch(unit) {
			case 'w': amount *= 7;
			case 'd': this.setDate(this.getDate() + amount);
				break;
			case 'm': this.setMonth(this.getMonth() + amount);
				break;
			case 'y': this.setFullYear(this.getFullYear() + amount);
		}
		return this;
	},
	diff(end: Date) {
		let endc = end.clone();
		endc.setDate(0);
		let monthDays = endc.getDate(),
			sihdmy = [0,0,0,0,0, end.getFullYear() - this.getFullYear()],
			it = 0,
			map = {getSeconds: 60, getMinutes: 60, getHours: 24, getDate: monthDays, getMonth: 12};
		for(let i in map) {
			let fn = i as 'getSeconds' | 'getMinutes' | 'getHours' | 'getDate' | 'getMonth';
			if(sihdmy[it]+end[fn]() < this[fn]()){
				sihdmy[it+1]--;
				sihdmy[it] += map[fn] - this[fn]() + end[fn]();
			} else if(sihdmy[it]+end[fn]() > this[fn]()) {
				sihdmy[it] += end[fn]() - this[fn]();
			}
			it++;
		}
		// sec, min, hour, day, month, year
		const [s,i,h,d,m,y] = sihdmy;
		return 'P'+(y>0 ? y+'Y':'')+
			(m>0 ? m+'M':'')+
			(d>0 ? d+'D':'')+
			((h || i || s) ? 'T'+
				(h>0 ? h+'H':'')+
				(i>0 ? i+'M':'')+
				(s>0 ? s+'S':''):'');

	},
	addDuration(iso8601) {
		let p:any,
			matches = iso8601.match(durationRegex)!;
		matches.shift(); // full match
		const sign = matches.shift() || '';
		for(let o of ['FullYear', 'Month', 'Week', 'Date', 'Hours', 'Minutes', 'Seconds']) {
			if(p = matches.shift()) { // what is p?
				if(o === 'Week') {
					p *= 7;
					o = 'Date';
				}
				this['set'+o as 'setDate'](this['get'+o as 'getDate']() + parseInt(sign+p))
			}
		}
		return this;
	},
	toUTCJmap() {
		this.setUTCMilliseconds(0);
		return this.toJSON().replace('.000', '');
	},
	toJmap : function() {
		return this.to('Y-m-dTH:i:s');
	},
	toSmart() {
		let now = new Date();
		if (now.to('Ymd') === this.to('Ymd')) {
			return this.to('H:i');
		} else if (now.getFullYear() === this.getFullYear()) {
			return this.to('j N');
		} else {
			return this.to('d-m-Y');
		}
	}
} as Date);

let tmp = new Date('1970-01-01'),
	loc = navigator.language;
for (let i = 0; i < 12; i++) {
	tmp.setMonth(i);
	Date.months.push(tmp.toLocaleString(loc, {month: 'long'})); // slow operation
}
for (let i = 0; i < 7; i++) {
	tmp.setDay(i);
	Date.days.push(tmp.toLocaleString(loc, {weekday: 'long'}));
}