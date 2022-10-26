import {DateTime} from "@goui/util/DateTime.js";

interface RecurrenceConfig {
	rule: RecurrenceRule
	dtstart: Date
	ff: Date
}

type NDay = {day: string, nthOfPeriod?: number};
type Frequency = "yearly" | "monthly" | "weekly" | "daily" | "hourly"
type DayOfWeek = 'mo' | 'tu' | 'we' | 'th' | 'fr' | 'sa' | 'su'
type RecurrenceRule = {
	frequency: Frequency
	interval: number
	skip?: 'omit' | 'backward' | 'forward'
	firstDayOfWeek?: DayOfWeek
	count?: number
	until?: Date
	byDay?: NDay[]
	byMonthDay?: number[]
	byMonth?: string[] //'1'= january
	bySetPosition?: number[]
	byWeekNo?: number[]
	byYearDay?: number[]
	byHour?: number[]
}
/**
 * Class for looping date for Recurrence Rule
 */
export class Recurrence {

	static dayMap = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

	completed?: boolean
	rule: RecurrenceRule
	dtstart: Date
	current: DateTime
	last?: DateTime
	occurrence: number = 0
	indices = {
		"BYSECOND": 0,
		"BYMINUTE": 0,
		"BYHOUR": 0,
		"BYDAY": 0,
		"BYMONTH": 0,
		"BYWEEKNO": 0,
		"BYMONTHDAY": 0
	}

	private dayNb(shortName: string) {
		return {'mo':1,'tu':2, 'wo':3, 'th':4, 'fr':5, 'sa': 6, 'su':0}[shortName];
	}
	private nDayHas(date: DateTime) {
		// todo: change date.getDay() to 'mo' or 'su' and find period type in rrule and nthOfPeriod in date
		for(const d of this.rule.byDay!) {
			if(this.dayNb(d.day) === date.getDay() && d.nthOfPeriod == 1) return true;
		}
		return false;
	}

	constructor(config: RecurrenceConfig) {

		this.rule = config.rule;
		this.dtstart = config.dtstart;
		this.current = new DateTime(+this.dtstart);
		this.rule.interval = this.rule.interval || 1; // default
		this.validate(this.rule);
		if (config.ff) { // fast forward
			while (this.current.date < config.ff) this.next();
		}
		//setup defaults

		//init

	}

	next() {
		let previous = (this.last ? this.last.clone() : null);

		if ((this.rule.count && this.occurrence >= this.rule.count) ||
			(this.rule.until && this.current.date > this.rule.until)) {
			return null;
		}

		// if (this.occurrence == 0 && this.current >= this.dtstart) {
		// 	this.occurrence++;
		// 	return this.current;
		// }

		switch (this.rule.frequency) {
			// case "secondly":
			//   this.nextSecondly();
			//   break;
			// case "minutely":
			//   this.nextMinutely();
			//   break;
			// case "hourly":
			// 	 this.nextHourly();
			// 	 break;
			case "daily":
				this.nextDaily();
				break;
			case "weekly":
				this.nextWeekly();
				break;
			case "monthly":
				this.nextMonthly();
				break;
			case "yearly":
				this.nextYearly();
				break;

			default:
				return null;
		} // while !check_contacting_rules || !valid

		if (this.current == previous) {
			throw new Error('Recursion isn\'t going anywhere');
		}
		this.last = this.current;
		if (this.rule.until && this.current.date > this.rule.until) {
			return null;
		} else {
			this.occurrence++;
			return this.current;
		}
	}

	// private nextHourly() {
	// 	this.current.setHours(this.current.getHours() + this.rule.interval);
	// }

	private nextDaily() {
		if (!this.rule.byHour && !this.rule.byDay) {
			this.current.addDays(this.rule.interval);
			return;
		}
		do {
			if (this.rule.byHour) {
				if (this.current.getHours() == 23) {
					this.current.addDays(this.rule.interval - 1);
				}
				this.current.setHours(this.current.getHours() + 1);
			} else {
				this.current.addDays(this.rule.interval);
			}
		} while (
			!this.nDayHas(this.current) ||
			!this.rule.byMonth!.includes(""+(this.current.getMonth() + 1))
			);
	}

	private nextWeekly() {
		if (!this.rule.byHour && !this.rule.byDay) {
			this.current.addDays(this.rule.interval * 7);
			return;
		}
		do {

			this.current.addDays(1);

			if (Recurrence.dayMap[this.current.getDay()].toLowerCase() === this.rule.firstDayOfWeek &&
				(!this.rule.byHour || this.current.getHours() == 0)
			) { // role over week
				this.current.addDays((this.rule.interval - 1) * 7);
				this.current.setDay(this.dayNb(this.rule.firstDayOfWeek)!); // TODO: test
			}
		} while (!this.nDayHas(this.current));
	}

	private nextMonthly() {
		this.current.setMonth(this.current.getMonth() + this.rule.interval);
	}

	private nextYearly() {
		if (!this.rule.byMonth) {
			this.current.setYear(this.current.getYear() + this.rule.interval);
			return
		} else {
			throw new Error('Not yet supported')
		}
	}


	private validate(p: RecurrenceRule) {
		// if('byDay' in p) {
		// 	this.sortByDay(p.byDay, this.rule.wkst);
		// }
		if('byYearDay' in p && ('byMonth' in p || 'byWeek' in p || 'byMonthDay' in p || 'byDay' in p)) {
			throw new Error('Invalid byYearday rule');
		}
		if ("byWeekNo" in p && "byMonthDay" in p) {
			throw new Error('byWeekNo does not fit to byMonthDay');
		}
		if (['daily', 'weekly', 'monthly', 'yearly'].indexOf(p.frequency) === -1) {
			throw new Error('Invalid frequency rule');
		}
		if(p.frequency == 'monthly' && ('byYearDay' in p || 'byWeekNo' in p)) {
			throw new Error('Invalid monthly rule');
		}
		if (p.frequency == "weekly" && ("byYearDay" in p || "byMonthDay" in p)) {
			throw new Error("Invalid weekly rule");
		}
		if (p.frequency != "yearly" && "byYearDay" in p) {
			throw new Error("byYearDay may only appear in yearly rules");
		}
	}
}