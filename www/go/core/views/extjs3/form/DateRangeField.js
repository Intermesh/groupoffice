go.form.DateRangeField = Ext.extend(Ext.Button, {

	value: null,
	lastValue: null,

	text: t("No range selected"),

	iconCls: 'ic-schedule',

	isFormField: true,

	autoWidth: false,

	setToday :  function() {
		var todayStart = (new Date()).clearTime();
		this.startDatePicker.setValue(todayStart);
		this.endDatePicker.setValue(todayStart);
		this.onEndDateSelect();
	},

	setYesterday :  function() {
		var todayStart = (new Date()).clearTime(),
			yesterdayStart = new Date(todayStart).add(Date.DAY, -1);
		this.startDatePicker.setValue(yesterdayStart);
		this.endDatePicker.setValue(yesterdayStart);
		this.onEndDateSelect();
	},

	setThisWeek :  function() {
		var todayStart = (new Date()).clearTime(),
			thisWeekStart = (new Date(todayStart)).add(Date.DAY, (todayStart.format('N') - 1) * -1),
			thisWeekEnd = (new Date(thisWeekStart)).add(Date.DAY, 6);
		this.startDatePicker.setValue(thisWeekStart);
		this.endDatePicker.setValue(thisWeekEnd);
		this.onEndDateSelect();
	},

	setLastWeek :  function() {
		var todayStart = (new Date()).clearTime(),
			lastWeekStart = (new Date(todayStart)).add(Date.DAY, (todayStart.format('N') + 6) * -1),
			lastWeekEnd = (new Date(lastWeekStart)).add(Date.DAY, 6);
		this.startDatePicker.setValue(lastWeekStart);
		this.endDatePicker.setValue(lastWeekEnd);
		this.onEndDateSelect();
	},

	setThisMonth :  function() {
		var todayStart = (new Date()).clearTime(),
			thisMonthStart = new Date(todayStart.getFullYear(), todayStart.getMonth(), 1),
			thisMonthEnd = new Date(todayStart.getFullYear(), todayStart.getMonth() + 1, 1).add(Date.DAY, -1);
		this.startDatePicker.setValue(thisMonthStart);
		this.endDatePicker.setValue(thisMonthEnd);
		this.onEndDateSelect();
	},

	setLastMonth :  function() {
		var todayStart = (new Date()).clearTime(),
			lastMonthStart = new Date(todayStart.getFullYear(), todayStart.getMonth()-1, 1),
			lastMonthEnd = new Date(todayStart.getFullYear(), todayStart.getMonth(), 1).add(Date.DAY, -1);
		this.startDatePicker.setValue(lastMonthStart);
		this.endDatePicker.setValue(lastMonthEnd);
		this.onEndDateSelect();
	},

	setThisYear :  function() {
		var todayStart = (new Date()).clearTime(),
			thisYearStart = new Date(todayStart.getFullYear() , 0, 1),
			thisYearEnd = new Date(todayStart.getFullYear() +1 , 0, 1).add(Date.DAY, -1);
		this.startDatePicker.setValue(thisYearStart);
		this.endDatePicker.setValue(thisYearEnd);
		this.onEndDateSelect();
	},

	setLastYear :  function() {
		var todayStart = (new Date()).clearTime(),
			lastYearStart = new Date(todayStart.getFullYear() , 0, 1),
			lastYearEnd = new Date(todayStart.getFullYear() +1 , 0, 1).add(Date.DAY, -1);
		this.startDatePicker.setValue(lastYearStart);
		this.endDatePicker.setValue(lastYearEnd);
		this.onEndDateSelect();
	},


	initComponent: function() {

		var me = this;

		this.startDatePicker = new Ext.DatePicker({
			showToday: false
		});

		this.endDatePicker = new Ext.DatePicker({
			showToday: false
		});
		this.endDatePicker.on("select", this.onEndDateSelect, this);



		this.menu = new Ext.menu.Menu({
			cls: "x-menu-no-icons",
			items: [{
				text: t("Today"),
				handler: this.setToday,
				scope: this
			},{
				text: t("Yesterday"),
				handler: this.setYesterday,
				scope: this
			},{
				text: t("This week"),
				handler: this.setThisWeek,
				scope: this
			},{
				text: t("Last week"),
				handler: this.setLastWeek,
				scope: this
			},{
				text: t("This month"),
				handler: this.setThisMonth,
				scope: this
			},{
				text: t("Last month"),
				handler: this.setLastMonth,
				scope: this
			},{
				text: t("This year"),
				handler: this.setThisYear,
				scope: this
			},{
				text: t("Last year"),
				handler: this.setLastYear,
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

	// setDateRange: function(btn) {
	//
	// },

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
