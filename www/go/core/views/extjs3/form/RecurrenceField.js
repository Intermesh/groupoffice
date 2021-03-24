go.form.RecurrenceField = Ext.extend(Ext.form.TriggerField, {
    startDate: null,
    editable: false,
    value: t('Not recurring'),
    fieldLabel: t('Recurrence'),

    rule: null, // raw json rrule

    weekOfMonth: 1,

    initComponent: function() {
      this.supr().initComponent.call(this);
      this.setStartDate(new Date());
      this.frequencyStore = new Ext.data.ArrayStore({
          fields : ['id', 'text', 'plural', 'repeatDefault', 'untilDefault', 'freq'],
          idIndex: 0,
          data : [
              ['daily', t("day"), t('days'), 30, '30-d', t('Daily')],
              ['weekly', t("week"), t('weeks'), 13, '91-d', t('Weekly')],
              ['monthly', t("month"), t('months'), 12, '1-y', t('Monthly')],
              ['yearly', t("year"), t('years'), 5, '5-y', t('Annually')]
          ]
      });
    },

    getValue : function(){
        return this.rule;
    },
    setValue : function(rule){
        if(Ext.isObject(rule))
            this.rule = rule;
        else
            this.rule = null;
        return this.supr().setValue.call(this, this.parseRule(rule));
    },
    isDirty : function() {
        if(this.disabled || !this.rendered) {
            return false;
        }
        return this.parseRule(this.getValue()) !== this.parseRule(this.originalValue);
    },

    /**
     * Change an RRule object into human readable text
     * @param {object} recurrenceRule as in jmap spec
     * @returns {string} flat text
     */
    parseRule: function(obj) {
        if(!obj || !obj.frequency) {
            return t('Not recurring');
        }
        var record = this.frequencyStore.getById(obj.frequency);
        var str = record.data.freq;
        if(obj.interval) {
            str = t('Every') + ' '+ obj.interval + ' '+ (obj.interval > 1 ? record.data.plural: record.data.text);
        }
        if(obj.byDay) {
            var dayNames = {mo: t('Monday'), tu: t('Tuesday'), we:t('Wednesday'),th:t('Thursday'),fr:t('Friday'),sa:t('Saturday'),su:t('Sunday')},
                days = [],
                workdays = (obj.byDay.length === 5);
            for(var i = 0; i < obj.byDay.length; i++) {
                if(obj.byDay[i].day == 'sa' || obj.byDay[i].day == 'su'){
                    workdays = false;
                }
                var nthDay = '';
                if(obj.byDay[i].nthOfPeriod) {
                    nthDay = t('the')+' '+this.getSuffix(obj.byDay[i].nthOfPeriod)+ ' ';
                }
                days.push(nthDay+dayNames[obj.byDay[i].day]);
            }
            if(workdays) {
                days = [t('Workdays')];
            }
            str += (' '+t('at ')+days.join(', '));
        }
        if(obj.byMonthDay) {
            str += (' '+ t('at day')+' '+obj.byMonthDay.join(', '))
        }

        if(obj.count) {
            str += ', '+obj.count+ ' '+t('times');
        }
        if(obj.until) {
            str += ', '+t('until')+ ' '+(new Date(obj.until)).format(go.User.dateFormat);
        }
        return str;
    },

    setStartDate: function(date) {
        this.startDate = date.clone();
        for(var i = 0,m = date.getMonth(); m == date.getMonth(); date = date.add('d', -7)) {
            i++;
        }
        this.weekOfMonth = i;
        this.weekday = [{
            day: this._day()
        }];

        Ext.destroy(this.menu);
        this.menu = null;
    },

    _day:function (){
        return [null,'mo','tu','we','th','fr','sa','so'][this.startDate.format('N')];
    },

    onTriggerClick : function(){
        if(this.disabled){
            return;
        }
        if(this.menu == null){
            this.menu = new Ext.menu.Menu({
                cls: 'x-menu-no-icons',
                defaults: {
                    handler: function(item, ev) {
                        this.setValue(item.rrule);
                    },scope:this
                },
                items: [
                    {text: t('Not recurring'), rrule: null},
                    '-',
                    {text: t('Daily'), rrule: {frequency: 'daily'} },
                    {text: t('Weekly') + ' ' +t('at ')+this.startDate.format('l'), rrule: {frequency: 'weekly'} },
                    {text: t('Monthly')+ ' ' +t('at day')+' '+this.startDate.format('j'), rrule: {frequency:'monthly', byMonthDay:[this.startDate.format('j')]} },
                    {text: t('Monthly')+ ' ' +t('at the')+' '+this.getSuffix()+ ' '+this.startDate.format('l'), rrule: {frequency:'monthly', byDay:[{day:this._day(),nthOfPeriod:this.weekOfMonth}]} },
                    {text: t('Annually')+ ' ' +t('at ')+this.startDate.format('j F'), rrule: {frequency:'yearly'} },
                    {text: t('Each working day'), rrule: {frequency:'weekly', byDay: [{day:'mo'},{day:'tu'},{day:'we'},{day:'th'},{day:'fr'}]} },
                    '-',
                    {text: t('Customize')+'...', handler: function() {
                        var dlg = this.customRuleDialog();
                        dlg.show();
                        dlg.load(this.rule || {frequency:'weekly'});
                    },scope:this},
                ]
            });
        }
        this.onFocus();
        // Ext.apply(this.menu.picker,  {
        //     startDate: this.startDate
        // });
        //this.menu.picker.setValue(this.getValue() || {});
        this.menu.show(this.el, "tl-bl?");
        this.menuEvents('on');
    },

    getSuffix : function(week) {
        week = week || this.weekOfMonth;
        switch (week) {
            case 1:return t("first");
            case 2:return t("second");
            case 3:return t("third");
            case 4:return t("fourth");
            default:return t("last");
        }
    },

    onMenuHide: function(){
        this.focus(false, 60);
        this.menuEvents('un');
    },
    menuEvents: function(method){
       // this.menu[method]('select', this.onSelect, this);
        this.menu[method]('hide', this.onMenuHide, this);
        this.menu[method]('show', this.onFocus, this);
    },
    onDestroy : function(){
        Ext.destroy(this.menu);
        go.form.RecurrenceField.superclass.onDestroy.call(this);
    },

    customRuleDialog: function() {

        var weeklyOptions = new Ext.ButtonGroup({
            itemId: 'weeklyOptions',
            xtype: 'buttongroup',
            name: 'byDay',
            hideMode:'offsets',
            fieldLabel: t('At days'),
            layoutConfig: {pack:'middle'},
            layout:'hbox',
            value: this.weekday,
            isFormField: true,
            defaults: {
                enableToggle: true,
                listeners:{
                    toggle:function(btn,pressed) {
                        if(!pressed && btn.ownerCt.getValue().length === 0){
                            btn.ownerCt.setValue([{day:this._day()}]);
                        }
                    },scope:this
                }
            },
            items: [
                {text:t('Monday').substring(0,2),day:'mo'},
                {text:t('Tuesday').substring(0,2),day:'tu'},
                {text:t('Wednesday').substring(0,2),day:'we'},
                {text:t('Thursday').substring(0,2),day:'th'},
                {text:t('Friday').substring(0,2),day:'fr'},
                {text:t('Saturday').substring(0,2),day:'sa'},
                {text:t('Sunday').substring(0,2),day:'su'}],
            getName: function() {
                return this.name;
            },
            getValue: function() {
                if(!this.rendered) {
                    return this.value;
                }
                var value = [];
                for(var i = 0; i < 7; i++) {
                    if(this.items.items[i].pressed) {
                        value.push({day: this.items.items[i].day});
                    }
                }
                return value;
            },
            setValue: function(days) {
                this.value = days;
                for(var i = 0; i < days.length; i++) {
                    for(var j = 0; j < 7; j++) {
                        this.items.items[j].toggle(false);
                        if(this.items.items[j].day == days[i].day){
                            this.items.items[j].toggle(true);
                            break;
                        }
                    }
                }
                return this;
            },
            markInvalid:Ext.emptyFn,
            clearInvalid:Ext.emptyFn,
            validate: function(){return true;},
            isDirty: function() {
                return true;
                // if(this.disabled || !this.rendered) {
                //     return false;
                // }
                // return JSON.stringify(this.getValue()) !== JSON.stringify(this.originalValue);
            },
            reset : function(){
                this.setValue(this.originalValue);
                this.clearInvalid();
            },
            initValue : function(){
                if(this.value !== undefined){
                    this.setValue(this.value);
                }else if(!Ext.isEmpty(this.el.dom.value)){
                    this.setValue(this.el.dom.value);
                }
                this.originalValue = this.getValue();
            }
        });
        weeklyOptions.initValue();
        var me = this;

        var customWindow = new go.Window({
            title: t('Customize recurrence'),
            modal: true,
            width: dp(376),
            items: [{
                xtype:'form',
                labelWidth: dp(72),
                cls: 'go-form-panel',
                items: [
                    {
                        xtype:'container',
                        fieldLabel: t('Every'),
                        layout:'hbox',
                        items:[{
                            xtype:'numberfield',
                            decimals:0,
                            name : 'interval',
                            minValue:1,
                            width : 50,
                            value : 1,
                            serverFormats: true,
                            listeners: {valid:function(me) {
                                var freq = me.nextSibling();
                                    freq.displayField = me.value==1 ? 'text' : 'plural';
                                    freq.list = freq.tpl = null;
                                    if(freq.rendered) {
                                        freq.initList();
                                        freq.setValue(freq.getValue());
                                    }
                            }}
                        },{
                            xtype:'combo',
                            hiddenName : 'frequency',
                            triggerAction : 'all',
                            editable : false,
                            selectOnFocus : true,
                            width : dp(120),
                            forceSelection : true,
                            mode : 'local',
                            valueField : 'id',
                            displayField : 'text',
                            store : this.frequencyStore,
                            hideLabel : true,
                            listeners: {
                                select: function(me, record) {
                                    customWindow.changeFrequency(record.id);
                                }
                            }
                        }
                    ]},
                    {
                        xtype:'combo',
                        hidden:true,
                        disabled:true,
                        itemId:'monthlyOptions',
                        hiddenName : 'monthlyType',
                        fieldLabel: t('at the'),
                        triggerAction : 'all',
                        selectOnFocus : true,
                        width : dp(160),
                        forceSelection : true,
                        mode : 'local',
                        editable:false,
                        value : 'byMonthDay',
                        valueField : 'value',
                        displayField : 'text',
                        store : new Ext.data.SimpleStore({
                            fields : ['value', 'text'],
                            data : [
                                ['byMonthDay', this.startDate.format('jS')],
                                ['byDay', this.getSuffix()+ ' '+this.startDate.format('l')]
                            ]
                        })
                    },
                    weeklyOptions,
                    {
                        xtype:'container',
                        layout:'column',
                        items:[{
                            xtype:'container',
                            columnWidth: .5,
                            layout:'form',
                            items: [{
                                xtype: 'radiogroup',
                                itemId: 'endsRatio',
                                fieldLabel: t("Ends"),
                                value: 'forever',
                                submit: false,
                                width:160,
                                columns: 1,
                                items: [
                                    {boxLabel: t("Never"),  inputValue: 'forever'},
                                    {boxLabel: t("After"),     inputValue: 'count'},
                                    {boxLabel: t("At"),  inputValue: 'until'}
                                ],
                                listeners: {
                                    scope: this,
                                    change: function(group, checked) {
                                        this.endAtCtr.getComponent('repeatCount').setDisabled(checked.inputValue != 'count');
                                        this.endAtCtr.getComponent('endDate').setDisabled(checked.inputValue != 'until');
                                    }
                                }
                            }]
                        },this.endAtCtr = new Ext.Container({
                            xtype:'container',
                            columnWidth: .5,
                            layout:'form',
                            defaults:{hideLabel:true},
                            items:[
                                {
                                    xtype:'displayfield',
                                    html:'&nbsp;'
                                }, {
                                    xtype: 'numberfield',
                                    itemId: 'repeatCount',
                                    disabled: true,
                                    name: 'count',
                                    maxLength: 1000,
                                    width : 100,
                                    value: 13,
                                    decimals:0,
                                    suffix: t('times')
                                }, {
                                    xtype: 'datefield',
                                    itemId: 'endDate',
                                    name : 'until',
                                    width : 120,
                                    disabled : true,
                                    value: (new Date()).add(Date.MONTH,3),
                                    format : GO.settings['date_format'],
                                    allowBlank : true,
                                    minValue: this.startDate.add('d', 1)
                                }
                            ]
                        })]
                    }
                ]
            }],
            load: function(rrule) {
                //customWindow.on('afterRender',function() {
                    var form = customWindow.findByType('form')[0].getForm();

                    if(rrule.frequency) {
                        form.setValues(rrule);
                        customWindow.changeFrequency(rrule.frequency);
                        if (rrule.until) {
                            form.findField('endsRatio').setValue('until');
                            form.setValues({until: rrule.until});
                        }
                        if (rrule.count) {
                            form.findField('endsRatio').setValue('count');
                            form.setValues({count: rrule.count});
                        }
                        if(rrule.byDay) {
                            form.findField('monthlyOptions').setValue('byDay')
                        }
                        if(rrule.byMonthDay) {
                            form.findField('monthlyOptions').setValue('byMonthDay')
                        }
                    }
                //},this);

            },
            changeFrequency: function(f){
                var record = me.frequencyStore.getById(f);
                // set defaults for endAt fields
                var repeat = me.endAtCtr.getComponent('repeatCount');
                if(repeat.disabled) {
                    repeat.setValue(record.data.repeatDefault);
                }
                var until = me.endAtCtr.getComponent('endDate');
                if(until.disabled) {
                    var add = record.data.untilDefault.split('-'),
                        d = me.startDate.add(add[1],parseInt(add[0]));
                    until.setValue(d);
                }

                // show-n-hide option panels for week and month frequency
                var f = customWindow.findByType('form')[0];
                f.getComponent('weeklyOptions').setVisible(record.id == 'weekly');
                f.getComponent('weeklyOptions').setDisabled(record.id != 'weekly');

                f.getComponent('monthlyOptions').setVisible(record.id == 'monthly');
                f.getComponent('monthlyOptions').setDisabled(record.id != 'monthly');

                //customWindow.doLayout();
                f.items.get(0).doLayout();
            },
            buttons:[{
                text:t('Ok'),
                handler: function(btn) {
                    var form =  btn.ownerCt.ownerCt.findByType('form')[0].getForm(),
                    rule = form.getFieldValues();
                    if(rule.interval == 1)  delete rule.interval; // remove default

                    if(rule.monthlyType) {
                        switch(rule.monthlyType) {
                            case 'byMonthDay':
                                rule.byMonthDay = [parseInt(this.startDate.format('j'))];
                                break;
                            case 'byDay':
                                rule.byDay = [{day:this._day(),nthOfPeriod:this.weekOfMonth}];
                                break;
                        }
                        delete rule.monthlyType;
                    }

                    this.setValue(rule);
                    customWindow.close();
                },
                scope:this
            }]
        });
        return customWindow;
    }
})