var $ = (function () {
    var e = {}, idCounter = 0, isTypOf = function (otype) {
        return function (obj) {
            return Object.prototype.toString.call(obj) === '[object ' + otype + ']';
        };
    };
    return {
        id: document.getElementById.bind(document),
        el: document.createElement.bind(document),
        on: function (name, callback) {
            if (!e[name])
                e[name] = [];
            e[name].push(callback);
            return $;
        },
        fire: function (name) {
            var args = [];
            for (var _i = 1; _i < arguments.length; _i++) {
                args[_i - 1] = arguments[_i];
            }
            if (!e[name])
                return true;
            var result = true;
            for (var _a = 0, _b = e[name]; _a < _b.length; _a++) {
                var fn = _b[_a];
                if (fn.apply($, args) === false)
                    result = false;
            }
            return result;
        },
        isArray: Array.isArray,
        isObject: function (obj) { return (typeof obj === "object") && (obj !== null); },
        isEmpty: function (val) { return val == null || !(Object.keys(val) || val).length; },
        isString: isTypOf('String'),
        isFunction: isTypOf('Function'),
        isNumber: isTypOf('Number'),
        isRegExp: isTypOf('RegExp'),
        isNaN: function (val) { return isNaN(val) && val.toString().trim() !== ''; },
        isDate: function (d) { return d instanceof Date && !isNaN(d.getTime()); },
        toArray: function (obj) { return Object.keys(obj).map(function (i) { return obj[i]; }); },
        luid: function (prefix) {
            if (prefix === void 0) { prefix = '#'; }
            return prefix + (++idCounter);
        },
        // will grap property from object based on jsonPath
        jsonPath: function (obj, path) {
            var s, str = path.split('.');
            while (s = str.shift()) {
                var res = s.match(/\[(.+)\]/);
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
        jsonMerge: function (data, path, value) {
            var s, i = 0, str = path.split('.'), curr = data;
            for (; i < str.length - 1; i++) {
                var s_1 = str[i];
                if (!curr[s_1])
                    curr[s_1] = {};
                curr = curr[s_1];
            }
            curr[str[i]] = value;
        }
    };
})();
