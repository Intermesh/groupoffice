class EventDialog extends Dialog {

	title = t('New Event')
	width = 800
	height = 650

	constructor() {
		super();

		this.setItems(this.form = form({cls: 'scroll fit'},
			textfield({placeholder: t('Enter a title, name or place'), name: 'title' }),
			select({name:'calendarId', required:true, options: [
				['key', 'value']
			]}),
			fieldset({cls: 'c6'},
				checkbox({name: 'isAllDay', boxLabel: t('All day')}),
				datefield({label: t('Start'), name:'start'}),
				textfield({name: 'startTime'}),
				datefield({label:t('End'), name: 'end'}),
			),
			fieldset({layout: 'hbox'},
				select({name: 'freeBusyStatus', value: 'busy', required: true, options: [
					['busy', t('Busy')],
					['free', t('Free')]
				]}),
				select({name: 'privacy', required:true, value: 'standard', label: t('Visibility'), options: [
					['standard', t('Standard')],
					['public', t('Public')],
					['private', t('Private')]
				]})
			),
			htmlfield({name:'description', label: t('Desciption')})
			),
			tbar({},
				btn({text:'Close', handler: _ => this.close()}),
				'->',
				btn({text:t('Save'), handler: _ => this.form.submit()})
			)
		);
	}

}