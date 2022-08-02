$regApp('calendar', new class {
	init() {
		this.add(this.ui = new CalendarMain({}));
	}
	stores= {
		Calendar: {filters:{},relations:{}},
		CalendarEvent: {customFields:true}
	}
	routes= {
		"year/(:year)": (year) => {
			this.ui.data.view = 'year';
			this.ui.tabs.change('year');
			this.ui.datePickerBtn.setText(this.util.getText());
		},
		"month/": () => {
			this.ui.data.view = 'month';
			this.ui.tabs.change('month');
			this.ui.datePickerBtn.setText(this.util.getText());
			//$.id('cal-date-btn').innerText = this.getText();
		},
		"week/":() => {
			this.ui.data.view = 'week';
			this.ui.tabs.change('week');
			this.ui.datePickerBtn.setText(this.util.getText());
		}
	}
});