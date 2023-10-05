import {Window, table, column, datasourcestore} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
export class CalendarSubscribe extends Window {
	constructor() {
		super();
		this.items.add(table({
			store: datasourcestore({
				dataSource: jmapds('Calendar'),
				//properties: ['id', 'name', 'color', 'isSubscribed']
			}),
			columns: [
				column({id:'id'}),
				column({id:'name'})
			]
		}))
	}

}