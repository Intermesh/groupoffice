go.filter.types.date = Ext.extend(Ext.Panel, {
	layout: "hbox",
	flex: 1,
	/**
	 * Filter definition
	 * {
					name: 'text', //Filter name
					type: "string", //Sting type of go.filters.type or a full class name
					multiple: false, // nly applies to query field parsing. You can use name: Value1,Value2 nad it will turn into an array for an OR group
					title: "Query",
					customfield: model //When it's a custom field
				},
	 */
	filter: null,
	initComponent: function () {
		
		this.operatorCombo = new go.form.ComboBox({

				hideLabel: true,
				name: "operator",
				value: 'before',
				store: new Ext.data.ArrayStore({
					fields: ['value', 'text'],					
					data: [
						['before', t("is before, today plus")],
						['after', t("is after, today plus")],
						['beforedate', t("is before")],
						['afterdate', t("is after")]
					]
				}),
				valueField: 'value',
				displayField: 'text',
				mode: 'local',
				triggerAction: 'all',
				editable: false,
				selectOnFocus: true,
				forceSelection: true,
				width: Math.ceil(dp(200)),
				listeners: {
					scope: this,
					select: function(combo, record, index) {
						switch(record.data.value) {
							case 'before':
							case 'after':
								this.valueField.setVisible(true);
								this.periodCombo.setVisible(true);
								this.dateField.setVisible(false);
								this.doLayout();
								break;

							case 'beforedate':
							case 'afterdate':
								this.valueField.setVisible(false);
								this.periodCombo.setVisible(false);
								this.dateField.setVisible(true);
								this.doLayout();
						}
					}
				}
			});
			
		this.periodCombo = new go.form.ComboBox({

				hideLabel: true,
				name: "period",
				value: 'days',
				store: new Ext.data.ArrayStore({
					fields: ['value', 'text'],					
					data: [
						['days', t("days")],
						['months', t("months")],
						['years', t("years")]
					]
				}),
				valueField: 'value',
				displayField: 'text',
				mode: 'local',
				triggerAction: 'all',
				editable: false,
				selectOnFocus: true,
				forceSelection: true,
				width: Math.ceil(dp(200))
			});
			
			
		this.valueField = this.createValueField();

		this.dateField = new go.form.DateField({
			hidden: true
		});
		
		this.items = [
			this.operatorCombo,
			this.valueField,
			this.periodCombo,
			this.dateField
		];

		go.filter.types.date.superclass.initComponent.call(this);
	},
	
	createValueField: function() {
		return new GO.form.NumberField({
			serverFormats: false,
			flex: 1,
			decimals: 0,
			name: 'value'
		});
	},
	
	isFormField: true,
	
	name: 'value',
	
	
	getName : function() {
		return this.name;
	},
	
	setValue: function (v) {

		v = v + "";

		var regex = /([><]+) ([0-9]{4}-[0-9]{2}-[0-9]{2})/;
		var matches = v.match(regex);

		if (matches) {
			switch (matches[1]) {
				case '>':
					operator = 'afterdate';
					break;
				case '<':
					operator = 'beforedate';
					break;
			}

			this.dateField.setValue(matches[2]);

			this.operatorCombo.setValue(operator);
			this.valueField.setVisible(false);
			this.periodCombo.setVisible(false);
			this.dateField.setVisible(true);
			this.doLayout();

			return;
		}

		var regex = /([><]+) ([\-0-9]+) (days|months|years)/,
			operator = 'before', period = 'days', number = 0;


		var matches = v.match(regex);

		if (matches) {
			number = parseFloat(matches[2].trim());
			period = matches[3].trim();

			switch (matches[1]) {
				case '>':
					operator = 'after';
					break;
				case '<':
					operator = 'before';
					break;
			}
		}

		this.valueField.setVisible(true);
		this.periodCombo.setVisible(true);
		this.dateField.setVisible(false);

		this.operatorCombo.setValue(operator);
		this.valueField.setValue(number);
		this.periodCombo.setValue(period);
		this.doLayout();


	},
	getValue: function() {

		if(this.dateField.isVisible()) {
			var v =  this.dateField.getValue().format('Y-m-d');
		} else
		{
			var v =  this.valueField.getValue() + ' ' + this.periodCombo.getValue();
		}

		switch(this.operatorCombo.getValue()) {				

			case 'afterdate':
			case 'after':				
				return '> ' + v;

			case 'beforedate':
			case 'before':				
				return '< ' + v;
			
		}
	},
	validate: function() {
		return this.valueField.validate() && this.operatorCombo.validate();
	},
	markInvalid : function() {
		return this.valueField.markInvalid();
	},
	clearInvalid : function() {
		return this.valueField.clearInvalid();
	},
	isDirty : function() {
		return this.valueField.isDirty() || this.operatorCombo.isDirty();
	}
	
});

