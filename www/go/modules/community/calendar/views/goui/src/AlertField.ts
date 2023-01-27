import {Component} from "@goui/component/Component.js";
import {t} from "@goui/Translate.js";
import {MapField, mapfield} from "@goui/component/form/MapField.js";
import {containerfield} from "@goui/component/form/ContainerField.js";
import {select} from "@goui/component/form/SelectField.js";
import {numberfield} from "@goui/component/form/NumberField.js";
import {btn} from "@goui/component/Button.js";

interface Alert {
	trigger:any // {offset, relativeTo} | {when}
	acknowledged?:string
	action?:'display'|'email'
}

export class AlertField extends Component {

	list: MapField

	constructor() {
		super();
		this.items.add(
			this.list = mapfield({name: 'alerts',
				buildField: (v) => containerfield({flex:'1 0 100%',cls: 'flow',itemId:'defaultAlertsWithTime'},
					select({width: 120, name:'action', options:[
							{name: t('Email'), value:'email'},
							{name:t('Notification'), value: 'display'}
						]}),
					numberfield({width: 70, name:'offset', decimals:0, value:1}),
					select({flex:'1 0', options: [
							{value: '0', name: t('at start time')},
							{value: 'minutes', name: t('minute(s) before')},
							{value: 'hours', name: t('hour(s) before')},
							{value: 'days', name: t('day(s) before')},
						]})
				)
			}),
			btn({
				text: 'add alert',
				handler: () => {
					this.addAlert({trigger:{when:'now'}});
				}
			}),
		)
	}

	addAlert(data: Alert) {
		this.list.add(data);
	}
}