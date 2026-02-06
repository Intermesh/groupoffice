import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {AutocompleteField, btn, Button, DataSourceForm, Form, menu} from "@intermesh/goui";
import {LocationField} from "./LocationField.js";

export type OnlineMeetingService = {
	title:string,
	fn: (calendarEventForm:DataSourceForm<CalendarEvent>) => void
}
class OnlineMeetingServices {

	public readonly services:OnlineMeetingService[]  = [];

	public register(title:string, fn : (calendarEventForm:DataSourceForm<CalendarEvent>) => void) {
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
				await onlineMeetingServices.services[0].fn(form);
			}
		} else {
			this.menu = menu({},
				...onlineMeetingServices.services.map(service => {
					return btn({text: service.title, handler: ()=> {
							service.fn(form);
						}})
				})
				)
		}

	}
}