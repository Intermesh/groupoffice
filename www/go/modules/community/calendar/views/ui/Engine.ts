app
let $app: any,
    $ = (function() {
        let e: {[event:string]: Function[]} = {},
            idCounter = 0,
            isTypOf = function<T>(otype: string) : (obj:any) => obj is T {
                return function<T>(obj: any): obj is T {
                    return Object.prototype.toString.call(obj) === '[object ' + otype + ']';
                };
            };
        return {
            id: document.getElementById.bind(document) as (elementId: string) => HTMLElement,
            el: document.createElement.bind(document), //function<K extends keyof HTMLElementTagNameMap>(tagName: K, options?: ElementCreationOptions): HTMLElementTagNameMap[K] { return document.createElement(tagName, options); },
            on: function(name: string, callback: Function) {
                if(!e[name]) e[name] = [];
                e[name].push(callback);
                return $;
            },
            fire: function(name: string, ...args: any): boolean {
                if(!e[name]) return true;
                let result = true;
                for(let fn of e[name]) {
                    if(fn.apply($, args) === false)
                        result = false;
                }
                return result;
            },
            isArray: Array.isArray,
            isObject: function(obj: any): obj is any {return (typeof obj === "object") && (obj !== null);},
            isEmpty: (val: any) => val == null || !(Object.keys(val) || val).length,
            isString: isTypOf<string>('String'),
            isFunction: isTypOf<Function>('Function'),
            isNumber: isTypOf<number>('Number'),
            isRegExp: isTypOf<RegExp>('RegExp'),
            isNaN: function(val: any) { return isNaN(val) && val.toString().trim()!==''; },
            isDate: function(d: any): d is Date { return d instanceof Date && !isNaN(d.getTime());},
            toArray: function(obj: Record<string,string>) { return Object.keys(obj).map((i) => obj[i])},
            luid: function(prefix: string = '#') { return prefix + (++idCounter); },
            // will grap property from object based on jsonPath
            jsonPath: function(obj: any, path: string): any {
                let s:string|undefined
                    ,str: string[] = path.split('.');
                while(s = str.shift()) {
                    const res = s.match(/\[(.+)\]/);
                    if (res) {
                        s = s.slice(0, 0 - res[0].length);
                    }
                    if (!obj || !obj.hasOwnProperty(s)) return undefined;
                    obj = obj[s];
                    if (res && res[1]) {
                        if (obj.indexOf(res[1]) === -1) return undefined;
                        obj = obj[res[1]]
                    }
                }
                return obj;
            },
            jsonMerge(data: any, path: string, value: any) {
                let s:string|undefined, i = 0,
                    str: string[] = path.split('.'),
                    curr = data;
                for(;i < str.length-1; i++) {
                    const s = str[i];
                    if(!curr[s]) curr[s] = {};
                    curr = curr[s];
                }
                curr[str[i]] = value;
            }
        };
    })();