class Component extends Ext.Container {
    constructor() {
        super(...arguments);
        this.e = {};
        this.active = false;
        this.painting = false;
    }
    isRendered() {
        return this.dom !== undefined;
    }
    get parent() {
        return this.ownerCt.body;
    }
    get dom() {
        this.el.dom;
    }
    setItems(items) {
        this.add(items);
    }
    fire(name, ...args) {
        let result = true;
        if (!this.e[name])
            return result;
        for (let fn of this.e[name]) {
            args.unshift(this);
            result = result && fn.apply(this, args);
        }
        return result;
    }
    fireEvent(name, ...args) {
    }
}
const Cmp = (cfg) => new Component(cfg);
const AUTH_GUEST = { username: 'Guest', displayName: 'Anonymouse', accessToken: '', accountId: '', accounts: {}, capabilities: { maxSizeUpload: 1000 } };
let $app, $ = (function () {
    let e = {}, idCounter = 0, isTypOf = function (otype) {
        return function (obj) {
            return Object.prototype.toString.call(obj) === '[object ' + otype + ']';
        };
    };
    return {
        id: document.getElementById.bind(document),
        el: function (tagName, options) { return document.createElement(tagName, options); },
        extend: Object.assign,
        apply: function (source, destination) {
            return $.extend(destination || {}, source);
        },
        on: function (name, callback) {
            if (!e[name])
                e[name] = [];
            e[name].push(callback);
            return $;
        },
        fire: function (name, ...args) {
            if (!e[name])
                return true;
            let result = true;
            for (let fn of e[name]) {
                if (fn.apply($, args) === false)
                    result = false;
            }
            return result;
        },
        isArray: Array.isArray,
        isObject: function (obj) { return (typeof obj === "object") && (obj !== null); },
        isEmpty: (val) => val == null || !(Object.keys(val) || val).length,
        isString: isTypOf('String'),
        isFunction: isTypOf('Function'),
        isNumber: isTypOf('Number'),
        isRegExp: isTypOf('RegExp'),
        isNaN: function (val) { return isNaN(val) && val.toString().trim() !== ''; },
        isDate: function (d) { return d instanceof Date && !isNaN(d.getTime()); },
        auth: AUTH_GUEST,
        server: window.location.origin + window.location.pathname,
        throttle: function (callback) {
            var active = false, evt, handler = function () {
                active = false;
                callback(evt);
            };
            return function handleEvent(e) {
                evt = e;
                if (!active) {
                    active = true;
                    requestAnimationFrame(handler);
                }
                ;
            };
        },
        notify: function (msg) {
            let notify = $.el('li').cls(msg.category), tid, text = '';
            if (msg.category === 'system') {
                notify.cls('+error');
            }
            if (msg.title) {
                text = '<b>' + msg.title + '</b> ';
            }
            notify.html(text + msg.content);
            let notifier = document.getElementById('notifier');
            notifier.appendChild(notify);
            if (msg.category === 'status') {
                tid = setTimeout(function () { notify.remove(); }, 5000);
            }
            notify.on('click', function (e) { this.remove(); if (tid)
                clearTimeout(tid); });
        },
        print: function (el) {
            let printer = document.getElementById('printer');
            printer.innerHTML = el.innerHTML;
            window.print();
        },
        uri: { api: '', download: '', upload: '', sse: '' },
        fileView: undefined,
        app: {},
        appId: '',
        escapeHTML: function (unsafe) {
            return unsafe.replace(/[\u0000-\u002F\u003A-\u0040\u005B-\u0060\u007B-\u00FF]/g, (c) => '&#' + ('000' + c.charCodeAt(0)).substr(-4, 4) + ';');
        },
        download: function (blobId, fileName = '') {
            if (blobId) {
                return this.uri.download.replace('{blobId}', blobId).replace('{name}', fileName.replace(/[^a-zA-Z0-9._-]*/g, ''));
            }
        },
        http: function () {
            return this.jmap;
        },
        browser: function () {
            var N = navigator.appName, ua = navigator.userAgent, tem;
            var M = ua.match(/(opera|chrome|safari|firefox|msie)\/?\s*(\.?\d+(\.\d+)*)/i);
            if (M && (tem = ua.match(/version\/([\.\d]+)/i)) != null)
                M[2] = tem[1];
            M = M ? [M[1], M[2]] : [N, navigator.appVersion, '-?'];
            return M.join(';');
        },
        uuid: function () {
            return "10000000-1000-4000-8000-100000000000".replace(/[018]/g, (c) => {
                return (+c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> +c / 4).toString(16);
            });
        },
        toArray: function (obj) { return Object.keys(obj).map((i) => obj[i]); },
        luid: function (prefix = '#') { return prefix + (++idCounter); },
        jsonPath: function (obj, path) {
            let s, str = path.split('.');
            while (s = str.shift()) {
                const res = s.match(/\[(.+)\]/);
                if (res) {
                    s = s.slice(0, 0 - res[0].length);
                }
                if (!obj || !obj.hasOwnProperty(s))
                    return undefined;
                obj = obj[s];
                if (res && res[1]) {
                    if (obj.indexOf(res[1]) === -1)
                        return undefined;
                    obj = obj[res[1]];
                }
            }
            return obj;
        },
        jsonMerge(data, path, value) {
            let s, i = 0, str = path.split('.'), curr = data;
            for (; i < str.length - 1; i++) {
                const s = str[i];
                if (!curr[s])
                    curr[s] = {};
                curr = curr[s];
            }
            curr[str[i]] = value;
        }
    };
})();
Object.assign(Element.prototype, {
    on: function (event, listener, useCapture) {
        this.addEventListener(event, listener, useCapture);
        return this;
    },
    off: function (event, listener, useCapture) {
        this.removeEventListener(event, listener, useCapture);
        return this;
    },
    cls: function (name, enable) {
        if (!name)
            return this;
        if ($.isArray(name)) {
            name.map((n) => { this.cls(n, enable); });
            return this;
        }
        name = name;
        if (enable !== undefined) {
            name = (enable ? '+' : '-') + name;
        }
        switch (name.substring(0, 1)) {
            case '+':
                this.classList.add(name.substring(1));
                break;
            case '-':
                this.classList.remove(name.substring(1));
                break;
            case '!':
                this.classList.toggle(name.substring(1));
                break;
            default: this.className = name;
        }
        return this;
    },
    attr: function (name, value) {
        if (value === undefined) {
            return this.getAttribute(name);
        }
        this.setAttribute(name, value);
        return this;
    },
    has: function (clsOrAttr) {
        if (clsOrAttr.substring(0, 1) === '.') {
            return this.classList.contains(clsOrAttr.substring(1));
        }
        return this.hasAttribute(clsOrAttr);
    },
    isA: function (tagName) {
        return this.tagName.toLowerCase() === tagName.toLowerCase();
    },
    prev: function () { return this.previousElementSibling; },
    next: function () { return this.nextElementSibling; },
    up: function (expression, until) {
        if (!expression)
            return this.parentElement;
        let dom = this;
        do {
            if (dom === until)
                return null;
            if (!dom.matches(expression))
                continue;
            return dom;
        } while (dom = dom.parentElement);
    },
    child: function (index) {
        switch (index) {
            case 0: return this.firstElementChild;
            case -1: return this.lastElementChild;
            default: return this.children[index];
        }
    },
    putf: function (...elements) {
        this.prepend(...elements);
        return this;
    },
    put: function (...elements) {
        this.append(...elements);
        return this;
    },
    html: function (html, pos) {
        switch (pos) {
            case 0:
                this.insertAdjacentHTML('afterbegin', html);
                return this.child(0);
            case -1:
                this.insertAdjacentHTML('beforeend', html);
                return this.child(-1);
            default:
                this.innerHTML = html;
                return this;
        }
    },
    owns: function (el) {
        do {
            if (el === this)
                return true;
        } while (el = el.parentElement);
        return false;
    }
});
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
//# sourceMappingURL=ui.js.map