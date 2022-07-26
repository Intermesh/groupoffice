interface Element {
    html (html:string): this
    on<K extends keyof HTMLElementEventMap>(type: K, listener: (this: HTMLElement, ev: HTMLElementEventMap[K]) => any, options?: boolean | AddEventListenerOptions): this;
    off<K extends keyof HTMLElementEventMap>(type: K, listener: (this: HTMLElement, ev: HTMLElementEventMap[K]) => any, options?: boolean | AddEventListenerOptions): this;
    cls (name: string | string[], enabled?: boolean) : this
    put(...elements: Element[]) : this
	ins(...elements: Element[]) : this
    attr (name: string): string
    attr (name: string, value:any): this
    has (clsOrAttr: string): boolean
    isA(tagName: string): boolean
    owns(element: Element) : boolean
}

Object.assign(Element.prototype,{
    on: function(event: string, listener: (e: Event) => void, useCapture?: boolean) {
        this.addEventListener(event, listener, useCapture);
        return this;
    },
    off: function(event: string, listener: (e: Event) => void, useCapture?: boolean) {
        this.removeEventListener(event, listener, useCapture);
        return this;
    },
    cls: function(name: string|string[], enable?: boolean) {
        if(!name) return this;
        if($.isArray(name)) {
            (name as string[]).map((n) => { this.cls(n, enable)});
            return this;
        }
        name = name as string;
        if(enable !== undefined) {
            name = (enable ? '+' : '-') + name;
        }
        switch (name.substring(0, 1)) {
            case '+': this.classList.add(name.substring(1));
                break;
            case '-': this.classList.remove(name.substring(1));
                break;
            case '!': this.classList.toggle(name.substring(1));
                break;
            default: this.className = name;
        }
        return this;
    },
    attr: function(name: string, value?: string) {
        if(value === undefined) {
            return this.getAttribute(name);
        }
        this.setAttribute(name, value);
        return this;
    },
    has: function(clsOrAttr: string) {
        if (clsOrAttr.substring(0, 1) === '.') {
            return this.classList.contains(clsOrAttr.substring(1));
        }
        return this.hasAttribute(clsOrAttr);
    },
    isA: function(tagName: string) { /* Check element by tagname */
        return this.tagName.toLowerCase() === tagName.toLowerCase();
    },
    ins: function(...elements) {
        this.prepend(...elements);
        return this;
    },
    put: function(...elements) {
        this.append(...elements);
        return this;
    },
    html: function(html: string) {
 		this.innerHTML = html;
		return this;
    },
    owns: function(el) {
        do {
            if (el === this)
                return true;
        } while(el = el.parentElement!)
        return false;
    }
} as Element);