go.form.DateRangeField = Ext.extend(Ext.Button, {

	value: null,
	lastValue: null,

	text: t("No range selected"),

	iconCls: 'ic-schedule',

	isFormField: true,

	initComponent: function() {

		var me = this;

		this.startDatePicker = new Ext.DatePicker({
			showToday: false
		});

		this.endDatePicker = new Ext.DatePicker({
			showToday: false
		});
		this.endDatePicker.on("select", this.onEndDateSelect, this);

		var todayStart = (new Date()).clearTime(),
			todayEnd = new Date(todayStart).add(Date.DAY, 1),
			yesterdayStart = new Date(todayStart).add(Date.DAY, -1),

			thisWeekStart = (new Date(todayStart)).add(Date.DAY, (todayStart.format('N') - 1) * -1),
			thisWeekEnd = (new Date(thisWeekStart)).add(Date.DAY, 6),

		 	thisMonthStart = new Date(todayStart.getFullYear(), todayStart.getMonth(), 1),
		 	thisMonthEnd = new Date(todayStart.getFullYear(), todayStart.getMonth() + 1, 1).add(Date.DAY, -1),

		 	lastMonthStart = new Date(todayStart.getFullYear(), todayStart.getMonth()-1, 1),
		 	lastMonthEnd = new Date(todayStart.getFullYear(), todayStart.getMonth(), 1).add(Date.DAY, -1)

			lastYearStart = new Date(todayStart.getFullYear() - 1, 0, 1),
			lastYearEnd = new Date(todayStart.getFullYear(), 0, 1).add(Date.DAY, -1);

		this.menu = new Ext.menu.Menu({
			items: [{
				text: t("Today"),
				start: todayStart,
				end: todayStart,
				handler: this.setDateRange,
				scope: this
			},{
				text: t("Yesterday"),
				start: yesterdayStart,
				end: yesterdayStart,
				handler: this.setDateRange,
				scope: this
			},{
				text: t("This week"),
				start: thisWeekStart,
				end: thisWeekEnd,
				handler: this.setDateRange,
				scope: this
			},{
				text: t("This month"),
				start: thisMonthStart,
				end: thisMonthEnd,
				handler: this.setDateRange,
				scope: this
			},{
				text: t("Last month"),
				start: lastMonthStart,
				end: lastMonthEnd,
				handler: this.setDateRange,
				scope: this
			},{
				text: t("Last year"),
				start: lastYearStart,
				end: lastYearEnd,
				handler: this.setDateRange,
				scope: this
			}, {
				text: t("Custom"),
				menu: [{
					xtype: "container",
					layout: "column",
					width: dp(600),
					defaults: {
						columnWidth: .5,
						xtype: "container",
						layout: "anchor",
						anchor: "100%"
					},
					items: [{
						items: [
							{
								style: 'padding-left: ' + dp(16) + 'px',
								xtype: "box",
								html: t("Start") + ":"
							},
							this.startDatePicker
						]
					},{
						items: [
							{
								style: 'padding-left: ' + dp(16) + 'px',
								xtype: "box",
								html: t("End") + ":"
							},
							this.endDatePicker
						]
					}
					]
				}],
				doFocus: function () {
					me.startDatePicker.focus();
				}

			}]
		});

		this.supr().initComponent.call(this);
	},

	setDateRange: function(btn) {
		this.startDatePicker.setValue(btn.start);
		this.endDatePicker.setValue(btn.end);
		this.onEndDateSelect();
	},

	getName: function () {
		return this.name;
	},

	isDirty: function () {

	},

	reset: function () {
		this.value = null;
		this.updateBtnText();
	},

	setValue: function (v) {
		this.lastValue = this.getValue();
		this.value = v;
		this.updateBtnText();
	},

	getValue: function () {
		return this.value;
	},

	getRawValue : function() {
		return this.getText();
	},

	markInvalid: function (msg) {

	},

	clearInvalid: function () {

	},

	isValid : function(preventMark){

		return true;
	},

	validate: function () {
		return true;
	},

	focus: function () {

	},

	updateBtnText: function() {
		if(this.value == null) {
			this.setText(t("No range selected"));
			return;
		}
		var txt = go.util.Format.date(this.startDatePicker.getValue()) + ' - ' + go.util.Format.date(this.endDatePicker.getValue());
		this.setText(txt);
	},
	onEndDateSelect : function() {

		this.value = this.startDatePicker.getValue().format("Y-m-d") +
			".." +
			this.endDatePicker.getValue().format('Y-m-d')

		this.updateBtnText();

		this.fireEvent("change", this, this.getValue(), this.lastValue);
	}
});

Ext.reg("godaterangefield", go.form.DateRangeField);
