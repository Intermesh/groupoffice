import {btn, colorfield, comp, t, textfield} from "@intermesh/goui";
import {client, FormWindow} from "@intermesh/groupoffice-core";

export class SubscribeWebCalWindow extends FormWindow {
	constructor() {
		super('Calendar');
		this.title = 'Enter webcal address';
		this.width = 700;
		this.height = 260;
		this.maximizable = false;
		this.resizable = false;

		this.generalTab.items.add(comp({cls:'flow pad'},
			textfield({name: 'name', label: t('Name'), flex:1}),
			colorfield({name: 'color', label: t('Color'), width: 100}),
			textfield({label:'URI', name:'webcalUri'}),
		));

	}
}