function buildAdapter(cls, cfg, items) {
    if (cfg.tagName) {
        cfg.autoEl = cfg.tagName;
        delete cfg.tagName;
    }
    if (cfg.icon) {
        cfg.iconCls = 'ic-' + cfg.icon.replace('_', '-');
        delete cfg.icon;
    }
    if (cfg.flex) {
        cfg.cls += ' flex';
    }
    if (cfg.layout) {
        cfg.cls += ' ' + cfg.layout;
        delete cfg.layout;
    }
    if (items)
        cfg.items = items;
    return new cls(cfg);
}
function fieldAdapter(cls, cfg) {
    if (cfg.required) {
        cfg.allowBlank = false;
    }
    if (cfg.label) {
        cfg.fieldLabel = cfg.label;
    }
    return buildAdapter(cls, cfg);
}
function columnAdapter(cfg) {
    for (const col of cfg.columns) {
        col.dataIndex = col.id;
    }
    return cfg;
}
const comp = (cfg, ...items) => buildAdapter(Ext.Container, cfg, items), tbar = (cfg, ...items) => buildAdapter(Ext.Toolbar, cfg, items), btn = cfg => buildAdapter(Ext.Button, cfg), menu = (cfg, ...items) => {
    for (const i in items) {
        if (typeof items[i] !== 'string')
            items[i] = new Ext.menu.Item(items[i].initialConfig);
    }
    return buildAdapter(Ext.menu.Menu, cfg, items);
}, cards = (cfg, ...items) => buildAdapter(Ext.TabPanel, cfg, items), datepicker = cfg => { return new Ext.DatePicker(cfg); }, list = cfg => buildAdapter(Ext.list.ListView, columnAdapter(cfg)), store = cfg => {
    cfg.fields = cfg.properties;
    cfg.entityStore = cfg.entity;
    cfg.enableCustomFields = false;
    return new go.data.Store(cfg);
}, form = (cfg, ...items) => buildAdapter(Ext.form.FormPanel, cfg, items), select = (cfg) => fieldAdapter(go.form.SelectField, cfg), textfield = (cfg) => fieldAdapter(Ext.form.TextField, cfg), datefield = (cfg) => fieldAdapter(Ext.form.DateField, cfg), fieldset = (cfg, ...items) => buildAdapter(Ext.form.FieldSet, cfg, items), checkbox = (cfg) => fieldAdapter(Ext.form.Checkbox, cfg), htmlfield = cfg => fieldAdapter(go.form.HtmlEditor, cfg);
class Dialog extends Ext.Window {
    setItems(...items) {
        for (const cmp of items)
            this.add(cmp);
    }
}
class Component extends Ext.Container {
    constructor() {
        super(...arguments);
        this.e = {};
    }
    get parent() {
        return this.ownerCt;
    }
    get dom() {
        return this.el.dom;
    }
    setItems(...items) {
        for (const cmp of items)
            this.add(cmp);
    }
}
function pad(n) {
    return (n < 10 ? '0' : '') + n;
}
Object.assign(Date, {
    fromWeek(year, week) {
        let w = new Date(year, 1, 1);
        w.setWeek(week);
        return w;
    },
    fromYmd(ymd) {
        let val = ymd.split('-');
        return new Date(+val[0], (+val[1]) - 1, +val[2]);
    },
    fromShortDate(shortDate) {
        let tester = new Date('1971-02-03'), result = tester.toShort(), dayPos = result.indexOf('3'), monthPos = result.indexOf('2'), yearPos = result.indexOf('71'), sep = result.match(/[\D]/), parts = shortDate.split(sep[0]), order = [], yearFirst = false;
        if (yearPos < monthPos) {
            yearFirst = true;
            order.push(parts[0]);
        }
        else {
            order.push(parts[2]);
        }
        if (dayPos < monthPos) {
            order.push(pad(parts[yearFirst ? 2 : 1]));
            order.push(pad(parts[yearFirst ? 1 : 0]));
        }
        else {
            order.push(pad(parts[yearFirst ? 1 : 0]));
            order.push(pad(parts[yearFirst ? 2 : 1]));
        }
        return new Date(order.join('-'));
    },
    period: ['seconden', 'minuten', 'uren', 'dagen', 'weken', 'maanden', 'jaar'],
    period1: ['seconde', 'minuut', 'uur', 'dag', 'week', 'maand', 'jaar'],
    days: [],
    months: [],
    firstWeekday: 1,
    dateFormat: 'd-m-Y'
});
const durationRegex = /(-)?P(?:([.,\d]+)Y)?(?:([.,\d]+)M)?(?:([.,\d]+)W)?(?:([.,\d]+)D)?(?:T(?:([.,\d]+)H)?(?:([.,\d]+)M)?(?:([.,\d]+)S)?)?/;
Object.assign(Date.prototype, {
    to(format) {
        return format
            .replace(/Y/, this.getFullYear())
            .replace(/y/, ("" + this.getFullYear()).substring(2, 4))
            .replace(/e/, this.getDayOfYear())
            .replace(/m/, pad(this.getMonth() + 1))
            .replace(/d/, pad(this.getDate()))
            .replace(/j/, this.getDate())
            .replace(/w/, this.getWeek())
            .replace(/H/, pad(this.getHours()))
            .replace(/i/, pad(this.getMinutes()))
            .replace(/s/, pad(this.getSeconds()))
            .replace(/M/, "\t")
            .replace(/D/, "\n")
            .replace(/l/, "\r")
            .replace(/N/, Date.months[this.getMonth()].substring(0, 3))
            .split("\t").join(Date.months[this.getMonth()])
            .split("\n").join(this.getDayName().substring(0, 2))
            .split("\r").join(this.getDayName());
    },
    toShort() {
        return (new Intl.DateTimeFormat()).format(this);
    },
    getDayName() {
        return Date.days[this.getDay()];
    },
    getDayOfYear() {
        return (Date.UTC(this.getFullYear(), this.getMonth(), this.getDate()) - Date.UTC(this.getFullYear(), 0, 0)) / 24 / 60 / 60 / 1000;
    },
    getWeekDay() {
        return (this.getDay() == 0 ? 6 : (this.getDay() - Date.firstWeekday));
    },
    getWeek() {
        var awn = Math.floor(Date.UTC(this.getFullYear(), this.getMonth(), this.getDate() + 3) / 864e5 / 7), wYr = new Date(awn * 6048e5).getUTCFullYear();
        return awn - Math.floor(Date.UTC(wYr, 0, 7) / 6048e5) + 1;
    },
    setWeek(week) {
        this.setMonth(0);
        this.setDate(1);
        this.add((week - 1) * 7, 'd');
        this.setDay(Date.firstWeekday);
    },
    setDay(day) {
        let days = (Date.firstWeekday === 1 && this.getDay() === 0) ? 8 : this.getDay() + 1;
        let diff = this.getDate() - days + Date.firstWeekday;
        this.setDate(diff + day);
        return this;
    },
    changeTime(h = 0, m = 0, s = 0) {
        this.setHours(h, m, s);
        return this;
    },
    clone() {
        return new Date(+this);
    },
    add(amount, unit) {
        switch (unit) {
            case 'w': amount *= 7;
            case 'd':
                this.setDate(this.getDate() + amount);
                break;
            case 'm':
                this.setMonth(this.getMonth() + amount);
                break;
            case 'y': this.setFullYear(this.getFullYear() + amount);
        }
        return this;
    },
    diff(end) {
        let endc = end.clone();
        endc.setDate(0);
        let monthDays = endc.getDate(), sihdmy = [0, 0, 0, 0, 0, end.getFullYear() - this.getFullYear()], it = 0, map = { getSeconds: 60, getMinutes: 60, getHours: 24, getDate: monthDays, getMonth: 12 };
        for (let i in map) {
            let fn = i;
            if (sihdmy[it] + end[fn]() < this[fn]()) {
                sihdmy[it + 1]--;
                sihdmy[it] += map[fn] - this[fn]() + end[fn]();
            }
            else if (sihdmy[it] + end[fn]() > this[fn]()) {
                sihdmy[it] += end[fn]() - this[fn]();
            }
            it++;
        }
        const [s, i, h, d, m, y] = sihdmy;
        return 'P' + (y > 0 ? y + 'Y' : '') +
            (m > 0 ? m + 'M' : '') +
            (d > 0 ? d + 'D' : '') +
            ((h || i || s) ? 'T' +
                (h > 0 ? h + 'H' : '') +
                (i > 0 ? i + 'M' : '') +
                (s > 0 ? s + 'S' : '') : '');
    },
    addDuration(iso8601) {
        let p, matches = iso8601.match(durationRegex);
        matches.shift();
        const sign = matches.shift() || '';
        for (let o of ['FullYear', 'Month', 'Week', 'Date', 'Hours', 'Minutes', 'Seconds']) {
            if (p = matches.shift()) {
                if (o === 'Week') {
                    p *= 7;
                    o = 'Date';
                }
                this['set' + o](this['get' + o]() + parseInt(sign + p));
            }
        }
        return this;
    },
    toUTCJmap() {
        this.setUTCMilliseconds(0);
        return this.toJSON().replace('.000', '');
    },
    toJmap: function () {
        return this.to('Y-m-dTH:i:s');
    },
    toSmart() {
        let now = new Date();
        if (now.to('Ymd') === this.to('Ymd')) {
            return this.to('H:i');
        }
        else if (now.getFullYear() === this.getFullYear()) {
            return this.to('j N');
        }
        else {
            return this.to('d-m-Y');
        }
    }
});
let tmp = new Date('1970-01-01'), loc = navigator.language;
for (let i = 0; i < 12; i++) {
    tmp.setMonth(i);
    Date.months.push(tmp.toLocaleString(loc, { month: 'long' }));
}
for (let i = 0; i < 7; i++) {
    tmp.setDay(i);
    Date.days.push(tmp.toLocaleString(loc, { weekday: 'long' }));
}
function $regApp(name, cfg) {
    var old = {
        mainPanel: {},
        initModule: function (self) {
            cfg.add = () => { };
            cfg.init();
            cfg.ui.iconCls = 'ic';
            self.panelConfig = cfg.ui;
            for (const path in cfg.routes) {
                go.Router.add(path, cfg.routes[path]);
            }
        }
    };
    const entities = [];
    for (const sname in cfg.stores) {
        cfg.stores[sname].name = sname;
        entities.push(cfg.stores[sname]);
    }
    if (entities.length)
        old.entities = entities;
    go.Modules.register("community", name, old);
}
class CalendarView extends Component {
    constructor(cfg) {
        super(cfg);
        this.day = new Date();
        this.update = (data) => {
            this.recur = this.expandRecurrence();
            this.renderView();
        };
        this.bind();
        this.on('render', () => { this.store.load(); });
    }
    bind() {
        this.store = store({
            entity: 'CalendarEvent',
            properties: ['name', 'start', 'id'],
            listeners: { 'load': (me, records) => this.update() }
        });
    }
    expandRecurrence() {
        let recur = {};
        return recur;
    }
    eventHtml(e, style) {
        return `<div data-id="${e.id}" style="${style}" class="event">
			${e.recurrenceRule ? '<i>refresh</i>' : ''}
			${e.links ? '<i>attachment</i>' : ''}
			${e.alerts ? '<i>notifications</i>' : ''}
			${e.title || '(' + t('Nameless') + ')'}
			${!e.isAllDay ? '<span>' + (e.start && e.start.date().to('H:i')) + ' - ' + e.start.date().addDuration(e.duration).to('H:i') + '</span>' : ''}
		</div>`;
    }
    calculateOverlap(events) {
        let overlap = {};
        for (const event of events) {
            let start = event.start.date(), startM = start.getHours() * 60 + start.getMinutes(), end = start.clone().addDuration(event.duration), endM = end.to('Ymd') > start.to('Ymd') ? 1450 : end.getHours() * 60 + end.getMinutes();
            overlap[event.id] = { start: startM, end: endM, span: 1, max: 1 };
        }
        for (const me in overlap) {
            const o = overlap[me];
            for (const id in overlap) {
                const ov = overlap[id];
                if ((o.start < ov.end && o.start > ov.start) ||
                    (o.end > ov.start && o.end < ov.end)) {
                    o.max++;
                }
                else if (ov.start > o.end)
                    break;
            }
        }
        let position = 0, prevMax = 1, previousCols = {};
        for (const event of events) {
            const o = overlap[event.id];
            let col = position % prevMax;
            if (col + 1 == prevMax)
                position = 0;
            let pcol = col = position % prevMax, ppos = position;
            while (pcol != col || ppos == position && ppos < 6) {
                ppos++;
                if (!previousCols[pcol]) {
                    pcol = ppos % prevMax;
                    continue;
                }
                const previous = overlap[previousCols[pcol].id];
                if (previous.end > o.start && pcol == col) {
                    position++;
                    col = position % prevMax;
                    previous.max = Math.max(o.max, previous.max);
                }
                else if (previous.end > o.start) {
                    o.max = Math.max(o.max, previous.max);
                }
                pcol = ppos % prevMax;
            }
            o.col = col;
            previousCols[position % o.max] = event;
            prevMax = o.max;
        }
        return overlap;
    }
}
class MonthView extends CalendarView {
    constructor() {
        super(...arguments);
        this.stale = true;
        this.ROWHEIGHT = 26;
    }
    setDate(day) {
        if (!day) {
            let now = new Date();
            day = new Date(now.getFullYear(), now.getMonth(), 1);
        }
        this.dom.cls('reverse', (day < this.day));
        this.day = new Date(+day);
        let end = new Date(+day);
        day.setDate(1);
        day.setDay(Date.firstWeekday);
        this.firstDay = new Date(day.toDateString());
        end.setMonth(end.getMonth() + 1);
        end.setDate(0);
        end.setDay(6);
        end.add(1, 'd');
        this.store.filter('date', { after: day.to('Y-m-dT00:00:00'), before: end.to('Y-m-dT00:00:00') }).fetch(0, 500);
    }
    renderView() {
        let it = 0;
        if (!this.stale)
            return;
        let now = new Date(), day = new Date(this.day.toDateString());
        day.setDate(1);
        let start = new Date(+day), e, html = `<ul>`;
        for (var i = 0; i < 7; i++) {
            let d = (i + Date.firstWeekday) % 7;
            html += `<li class="${(day.to('Ym') == now.to('Ym') && now.getDay() == d) ? 'current' : ''}">${Date.days[d]}</li>`;
        }
        day.setDay(Date.firstWeekday);
        html += `</ul>`;
        while (day.to('Ym') <= start.to('Ym')) {
            html += `<ol>
				<li class="weeknb">${day.getWeek()}</li>
				<li class="events">${this.drawWeek(day)}</li>`;
            for (var i = 0; i < 7; i++) {
                var cls = [];
                if (day.to('Ymd') === now.to('Ymd'))
                    cls.push('today');
                if (day.to('Ymd') < now.to('Ymd'))
                    cls.push('past');
                if (day.to('Ym') !== start.to('Ym'))
                    cls.push('other');
                html += `<li class="${cls.join(' ')}" data-date="${day.to('Y-m-d')}"><em>${day.getDate()}</em></li>`;
                day.add(1, 'd');
            }
            html += `</ol>`;
        }
        this.dom.style.height = '100%';
        this.dom.classList.add('cal', 'month', 'active');
        this.dom.innerHTML = html;
    }
    drawWeek(start) {
        let end = new Date(+start), i = 0, e;
        end.add(7, 'd');
        let html = '';
        this.slots = { 0: {}, 1: {}, 2: {}, 3: {}, 4: {}, 5: {}, 6: {} };
        for (var storeIt in this.recur) {
            const r = this.recur[storeIt];
            while (r.current < end) {
                html += this.drawEvent(this.store.get(storeIt), r.current, start);
                r.next();
            }
        }
        for (e of this.store.data.items) {
            if (e.start.date().to('Yw') === start.to('Yw') && !e.recurrenceRule) {
                html += this.drawEvent(e, new Date(e.start), start);
            }
        }
        return html;
    }
    calcRow(start, days) {
        let row = 1, end = Math.min(start + days, 7);
        while (row < 8) {
            for (let i = start; i < end; i++) {
                if (this.slots[i][row]) {
                    break;
                }
                if (i == end - 1) {
                    for (let j = start; j < end; j++) {
                        this.slots[j][row] = true;
                    }
                    return row;
                }
            }
            row++;
        }
        return 10;
    }
    drawEvent(e, eStart, weekstart) {
        let d = e.duration.match(/P.*(\d+)D/);
        const cal = $.db.stores.Calendar.get(e.calendarId);
        let color = cal ? cal.color : '356772', start = eStart.clone(), days = d ? +d[1] : 1;
        let row = this.calcRow(start.getWeekDay(), days);
        let width = Math.min(7, days) * (100 / 7) - .2, left = Math.floor((start - weekstart) / 864e5) * (100 / 7), top = row * this.ROWHEIGHT, style = `background-color:#${color}; width: ${width}%; left:${left}%; top:${top}px;`;
        return super.eventHtml(e, style);
    }
}
//# sourceMappingURL=ui.js.map