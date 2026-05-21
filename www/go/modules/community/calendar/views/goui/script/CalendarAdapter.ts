import {DataSourceStore, datasourcestore, DateTime, DefaultEntity, Observable, t, Window} from "@intermesh/goui";
import {client, JmapDataSource, jmapds, principalDS} from "@intermesh/groupoffice-core";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";

export interface CalendarProvider {
	[key:string]:any
	enabled: boolean,
	checkbox?: {label:string, color:string, onChange?: (enabled:boolean, key:string)=>void},
	load(start:DateTime,end:DateTime): Promise<void | DefaultEntity[]>
	items(start:DateTime,end:DateTime): Generator<CalendarItem, void>
}

export interface CalendarAdapterEventMap {
	load: {start:DateTime, end: DateTime}
}

export class CalendarAdapter extends Observable<CalendarAdapterEventMap> {

	private start!: DateTime
	private end!: DateTime

	onLoad = () => {}

	goto(start:DateTime,end:DateTime) {
		this.start = start;
		this.end = end;
		const promises =  [];
		for(const type in this.providers) {
			const p = this.providers[type];
			if(p.enabled) {
				p.skipWatch = true; // will skip extra onLoad call for providers that have a load handler
				promises.push(p.load(start, end));
			}
		}
		Promise.all(promises).then(() => {
			this.onLoad()

			this.fire("load", {start, end});

		}).catch(e => Window.error(e))
	}

	private *generator() {
		for(const type in this.providers) {
			const p = this.providers[type];
			if(!p.enabled) continue;
			for (const item of p.items(this.start, this.end)) {
				item.provider = type;
				yield item;
			}
		}
	}

	get items() {
		return this.generator();
	}

	byType(type: string) {
		return this.providers[type];
	}

	public registerProvider(type:string, provider:CalendarProvider) {
		this.providers[type] = provider;
		if(provider.watch && provider.store) {
			provider.store.on('load', () => {
				//debugger;
				if (provider.skipWatch) {
					provider.skipWatch = false; // skip only once
				} else {
					this.onLoad();
				}
			});
		}
	}

	providers: {[type:string] : CalendarProvider} = {}
}