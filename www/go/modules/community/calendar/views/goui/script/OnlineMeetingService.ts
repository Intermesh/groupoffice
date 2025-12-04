import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {AutocompleteField, btn, Button, DataSourceForm, menu} from "@intermesh/goui";
import {LocationField} from "./LocationField.js";

export type OnlineMeetingService = {
	title:string,
	fn: (calendarEvent:CalendarEvent) => Promise<string>
}
class OnlineMeetingServices {

	public readonly services:OnlineMeetingService[]  = [];

	public register(title:string, fn : (calendarEvent:CalendarEvent) => Promise<string>) {
		this.services.push({
			title,
			fn
		})
	}
}

export const onlineMeetingServices = new OnlineMeetingServices();


export class OnlineMeetingButton extends Button {
	constructor(locationField: LocationField, form: DataSourceForm<CalendarEvent>) {
		super();

		this.icon = "video_call";
		this.hidden = onlineMeetingServices.services.length == 0;
		this.cls = "filled";
		this.width = 48;

		if(onlineMeetingServices.services.length == 1) {
			this.handler = async () => {
				locationField.value = await onlineMeetingServices.services[0].fn(form.value);
			}
		} else {
			this.menu = menu({},
				...onlineMeetingServices.services.map(s => {
					return btn({text: s.title, handler: async ()=> {
							locationField.value = await s.fn(form.value);
						}})
				})
				)
		}

	}
}