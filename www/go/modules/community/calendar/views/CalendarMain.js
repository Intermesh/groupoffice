import goui from goui.js
goui.Window = class extends Component {

	id = 'calendar'
	title = t('Calendar')
	cls = 'hbox'

	constructor() {
		super();
		this.html = 'test!!!';
		this.setItems(
			Box.create({html:'bladi'},
				Box({},
					),
				Box(),
			)
		);
	}
}