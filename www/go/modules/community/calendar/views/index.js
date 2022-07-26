$regApp('calendar', new class {
	init() {
		this.add(this.ui = new CalendarMain({}));
	}
	stores= {
		Calendarz: {fil},
		CalendarEventz: {query:true}
	}
	routes= {
		"year/(:year)": (year) => {
			$app.data.view = 'year';
			this.ui.tabs.change('year');
			this.ui.datePickerBtn.setText(this.util.getText());
		},
		"month/": () => {
			$app.data.view = 'month';
			this.ui.tabs.change('month');
			this.ui.datePickerBtn.setText(this.util.getText());
			//$.id('cal-date-btn').innerText = this.getText();
		},
		"week/":() => {
			$app.data.view = 'week';
			this.ui.tabs.change('week');
			this.ui.datePickerBtn.setText(this.util.getText());
		}
	}
});