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
			thisMonthEnd = new Date(todayStart.getFullYear(), todayStart.getMonth() + 1, 0);
		this.startDatePicker.setValue(thisMonthStart);
		this.endDatePicker.setValue(thisMonthEnd);
		this.onEndDateSelect();
	},

	setLastMonth :  function() {
		var todayStart = (new Date()).clearTime(),
			lastMonthStart = new Date(todayStart.getFullYear(), todayStart.getMonth()-1, 1),
			lastMonthEnd = new Date(todayStart.getFullYear(), todayStart.getMonth(), 0);
		this.startDatePicker.setValue(lastMonthStart);
		this.endDatePicker.setValue(lastMonthEnd);
		this.onEndDateSelect();
	},

	setThisYear :  function() {
		this.setYear((new Date()).getFullYear());
	},


	setYear :  function(year) {
		const	yearStart = new Date(year , 0, 1),
			yearEnd = new Date(year + 1 , 0, 0);
		this.startDatePicker.setValue(yearStart);
		this.endDatePicker.setValue(yearEnd);
		this.onEndDateSelect();
	},

	setMonth : function(year, month) {
		const	yearStart = new Date(year , month, 1),
			yearEnd = new Date(year , month + 1, 0);
		this.startDatePicker.setValue(yearStart);
		this.endDatePicker.setValue(yearEnd);
		this.onEndDateSelect();
	},

	setQuarter : function(year, q) {
		q--;
		const	start = new Date(year , q * 3, 1);
		const end = new Date(year, q * 3 + 3, 0);

		this.startDatePicker.setValue(start);
		this.endDatePicker.setValue(end);
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


		const years = [];

		for(let year = (new Date()).getFullYear() - 1, minYear = year - 8; year > minYear; year--) {
			years.push({
				text: year,
				handler: function(item) {
					this.setYear(item.text);
				},
				menu: new go.form.DateRangeFieldYearMenu({
					year: year,
					field: this
				}),
				scope: this
			})
		}

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
				scope: this,
				menu: new go.form.DateRangeFieldYearMenu({
					year: (new Date()).getFullYear(),
					field: this
				})
			},{
				text: t("Year"),
				menu: {
					cls: "x-menu-no-icons",
					items: years
				},
				scope: this
			},{
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


go.form.DateRangeFieldYearMenu = Ext.extend(Ext.menu.Menu, {
	year: null,
	field: null,
	cls: "x-menu-no-icons",
	initComponent : function() {

		this.items = [
				{
					text: "Q1",
					handler: function () {
						this.field.setQuarter(this.year, 1);
					},
					scope: this,
					menu: {
						cls: "x-menu-no-icons",
						scope: this,
						items: [{
							text: t("full_months")[1],
							handler: function() {
								this.field.setMonth(this.year, 0);
							},
							scope: this
						},{
							text: t("full_months")[2],
							handler: function() {
								this.field.setMonth(this.year, 1);
							},
							scope: this
						},{
							text: t("full_months")[3],
							handler: function() {
								this.field.setMonth(this.year, 3);
							},
							scope: this
						}]
					}
				},
				{
					text: "Q2",
					handler: function () {
						this.field.setQuarter(this.year, 2);
					},
					scope: this,
					menu: {
						cls: "x-menu-no-icons",
						scope: this,
						items: [{
							text: t("full_months")[4],
							handler: function() {
								this.field.setMonth(this.year, 3);
							},
							scope: this
						},{
							text: t("full_months")[5],
							handler: function() {
								this.field.setMonth(this.year, 4);
							},
							scope: this
						},{
							text: t("full_months")[6],
							handler: function() {
								this.field.setMonth(this.year, 5);
							},
							scope: this
						}]
					}
				},
				{
					text: "Q3",
					handler: function () {
						this.field.setQuarter(this.year, 3);
					},
					scope: this,
					menu: {
						cls: "x-menu-no-icons",
						scope: this,
						items: [{
							text: t("full_months")[7],
							handler: function() {
								this.field.setMonth(this.year, 6);
							},
							scope: this
						},{
							text: t("full_months")[8],
							handler: function() {
								this.field.setMonth(this.year, 7);
							},
							scope: this
						},{
							text: t("full_months")[9],
							handler: function() {
								this.field.setMonth(this.year, 8);
							},
							scope: this
						}]
					}
				},
				{
					text: "Q4",
					handler: function () {
						this.field.setQuarter(this.year, 4);
					},
					scope: this,
					menu: {
						cls: "x-menu-no-icons",
						scope: this,
						items: [{
							text: t("full_months")[10],
							handler: function() {
								this.field.setMonth(this.year, 9);
							},
							scope: this
						},{
							text: t("full_months")[11],
							handler: function() {
								this.field.setMonth(this.year, 10);
							},
							scope: this
						},{
							text: t("full_months")[12],
							handler: function() {
								this.field.setMonth(this.year, 11);
							},
							scope: this
						}]
					}
				}
			];

			go.form.DateRangeFieldYearMenu.superclass.initComponent.call(this);
		}
})