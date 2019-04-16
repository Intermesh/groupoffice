// Type definitions for ExtJS 3.4.0

/*
The ext documentation:
https://docs.sencha.com/extjs/3.4.0/
*/

declare namespace Ext.util {
	export class Format {
		static capitalize(): any;
		static date(value: string | Date, format?: string): string;
		static dateRenderer(): any;
		static defaultValue(): any;
		static ellipsis(): any;
		static fileSize(): any;
		static htmlDecode(): any;
		static htmlEncode(): any;
		static lowercase(): any;
		static math(): any;
		static nl2br(): any;
		static number(): any;
		static numberRenderer(): any;
		static plural(): any;
		static round(): any;
		static stripScripts(): any;
		static stripTags(): any;
		static substr(): any;
		static trim(): any;
		static undef(): any;
		static uppercase(): any;
		static usMoney(): any;
	}
	export class JSON {
		static decode(json: string): object;
		static decode<T>(json: string): T;
		static encode(o: object): string;
		static encodeDate(d: Date): string;
	}
	interface IListenerOptions {
		scope?: object;
		delay?: number;
		single?: boolean;
		buffer?: number;
		target?: Observable;
	}
	export interface IObservable {
		listeners?: object;
	}
	/**
	 * Base class that provides a common interface for publishing events.
	 * Subclasses are expected to to have a property "events" with all the events defined, and, optionally, a property "listeners" with configured listeners defined.
	 */
	export class Observable {
		addEvents(o: object | string, ...optional: string[]): void;
		addListener(eventName: string, fn: Function, scope?: object, options?: IListenerOptions): void;
		static capture(o: Observable, fn: Function, scope: object): void;
		enableBubble(events: string[]): void;
		/**
		 * Fires the specified event with the passed parameters (minus the event name).
		 * @param eventName The name of the event to fire.
		 * @param args Variable number of parameters are passed to handlers.
		 * @returns false if any of the handlers return false otherwise it returns true.
		 */
		fireEvent(eventName: string, ...args: any[]): boolean;
		hasListener(eventName: string): boolean;
		static observeClass(c: Function, listeners: object): void;
		on(eventName: string, fn: Function, scope?: object, options?: IListenerOptions): void;
		on(events: object): void;
		purgeListeners(): void;
		relayEvents(o: object, events: string[]): void;
		static releaseCapture(o: Observable): void;
		removeListener(eventName: string, handler: Function, scope?: object): void;
		resumeEvents(): void;
		suspendEvents(queueSuspended: boolean): void;
		un(eventName: string, handler: Function, scope?: object): void;
	}
	export class ClickRepeater extends Observable { }
	export class MixedCollection<T = Ext.Panel> extends Observable {
		[n: number]: T;
		length: number;
		allowFunctions: boolean;
		constructor(allowFunctions?: boolean, keyFn?: Function);
		add(key: string, o: T): T;
		addAll(objs: Array<T>): void;
		clear(): void;
		clone(): this;
		contains(o: T): boolean;
		containsKey(key: string): boolean;
		each(fn: (item: T, index: number, length: number) => boolean | void): boolean | void;
		eachKey(fn: (key: string, o: T) => void, scope?: object): void;
		filter(property: string, value: string | RegExp, anyMatch?: boolean, caseSensitive?: boolean): this;
		filterBy(fn: (o: T, key: string) => void, scope?: object): this;
		/**
		 * Returns the first item in the collection which elicits a true return value from the
		 * passed selection function.
		 * @param {Function} fn The selection function to execute for each item.
		 * @param {Object} scope (optional) The scope (this reference) in which the function is executed. Defaults to the browser window.
		 * @return {Object} The first item in the collection which returned true from the selection function.
		 */
		find(fn: Function, scope?: object): T;
		findIndex(property: string, value: string | RegExp, start?: number, anyMatch?: boolean, caseSensitive?: boolean): number;
		findIndexBy(fn: (o: T, key: string) => boolean | void): number;
		first(): T;
		/**
		 * This method calls item(). Returns the item associated with the passed key OR index. Key has priority over index. This is the equivalent of calling key first, then if nothing matched calling itemAt.
		 * @param key The key or index of the item.
		 * @returns If the item is found, returns the item. If the item was not found, returns undefined. If an item was found, but is a Class, returns null.
		 */
		get(key: string | number): T;
		/**
		 * This method calls item(). Returns the item associated with the passed key OR index. Key has priority over index. This is the equivalent of calling key first, then if nothing matched calling itemAt.
		 * @param key The key or index of the item.
		 * @returns If the item is found, returns the item. If the item was not found, returns undefined. If an item was found, but is a Class, returns null.
		 */
		get<U>(key: string | number): U;
		getCount(): number;
		getKey(item: T): string;
		getRange(startIndex?: number, endIndex?: number): Array<T>;
		getRange<U>(startIndex?: number, endIndex?: number): Array<U>;
		indexOf(o: T): number;
		indexOfKey(key: string): number;
		insert(index: number, key: string, o?: T): T;
		item(key: string | number): T;
		itemAt(index: number): T;
		key(key: string | number): T;
		keySort(direction?: Direction, fn?: Function): void;
		last(): T;
		remove(o: T): T;
		removeAt(index: number): T | false;
		removeKey(key: string): T | false;
		reorder(mapping: object): void;
		replace(key: string, o: T): T;
		sort(direction?: Direction, fn?: (a: T, b: T) => number): void;
		sort<U>(direction?: Direction, fn?: (a: U, b: U) => number): void;
	}
	/**
	 * Provides the ability to execute one or more arbitrary tasks in a multithreaded manner
	 */
	export class TaskRunner {
		constructor(interval?: number);
		start(task: Task): Task;
		stop(task: Task): Task;
		stopAll(): void;
	}
	export interface Task {
		run: Function;
		interval: number;
		args?: any[];
		scope?: object;
		duration?: number;
		repeat?: number;
	}
}

declare namespace Ext {

	export class QuickTips {
		static disable(): void;
		static enable(): void;
		static getQuickTip(): QuickTips;
		static init(autoRender?: boolean): void;
		static isEnabled(): boolean;
		static register(cfg: object): void;
		static tips(cfg: object): void;
		static unregister(el: string | HTMLElement | Element): void;
	}
	export class DomQuery {
		/**
		 * Collection of matching regular expressions and code snippets. Each capture group within () will be replace the {} in the select statement as specified by their index.
		 */
		matchers: any[];
		/**
		 * Collection of operator comparison functions. The default operators are =, !=, ^=, $=, *=, %=, |= and ~=. New operators can be added as long as the match the format c= where c is any character other than space, > <.
		 */
		operators: object;
		/**
		 * Object hash of "pseudo class" filter functions which are used when filtering selections.
		 */
		pseudos: object;

		static compile(selector: string, type?: string): Function;
		static filter(el: string[], selector: string, nonMatches?: boolean): Node[];
		static is(el: string | Node | string[] | Node[], selector: string): boolean;
		static jsSelect(selector: string, root?: Node | string): Node[];
		static selectNode(selector: string, root?: Node): Node;
		static selectNumber(selector: string, root?: Node, defaultValue?: number): number;
		static selectValue(selector: string, root?: Node, defaultValue?: string): string;
		private static select(selector: string, root?: Node): Node[];
	}
	export interface IDataView extends IBoxComponent {
		blockRefresh?: boolean;
		deferEmptyText?: boolean;
		emptyText?: string;
		itemSelector?: string;
		loadingText?: string;
		multiSelect?: boolean;
		overClass?: string;
		selectedClass?: string;
		simpleSelect?: boolean;
		singleSelect?: boolean;
		store?: Ext.data.Store | Ext.data.IStore;
		tpl?: string | string[] | Ext.XTemplate | Ext.IXTemplate;
		trackOver?: boolean;
		prepareData?: (data: any, recordIndex: number, record: Ext.data.Record<any>) => any;
	}
	export class DataView extends BoxComponent {
		constructor(cfg?: IDataView);
		/**
		 * Gets a record from a node
		 * @param node The node to evaluate
		 */
		getRecord<T>(node: HTMLElement): data.Record<T>;
		/**
		 * Returns the store associated with this DataView.
		 */
		getStore(): data.Store;
		/**
		 * Gets an array of the selected records
		 * @return {Array} An array of {@link Ext.data.Record} objects
		 */
		getSelectedRecords(): data.Record<any>[];
		getSelectedRecords<T>(): data.Record<T>[];
		clearSelections(suppressEvent?: boolean): void;
		clearSelections(suppressEvent?: boolean, skipUpdate?: boolean): void;
		/**
		 * Returns true if the passed node is selected, else false.
		 * @param {HTMLElement/Number/Ext.data.Record} node The node, node index or record to check
		 * @return {Boolean} True if selected, else false
		 */
		isSelected(node: HTMLElement | number | Ext.data.Record<any>): boolean;
		/**
		 * Deselects a node.
		 * @param {HTMLElement/Number/Record} node The node, node index or record to deselect
		 */
		deselect(node: HTMLElement | number | Ext.data.Record<any>): void;
		/**
		* Selects a set of nodes.
		* @param {Array/HTMLElement/String/Number/Ext.data.Record} nodeInfo An HTMLElement template node, index of a template node,
		* id of a template node, record associated with a node or an array of any of those to select
		* @param {Boolean} keepExisting (optional) true to keep existing selections
		* @param {Boolean} suppressEvent (optional) true to skip firing of the selectionchange vent
		*/
		select(nodeInfo: HTMLElement | number | Ext.data.Record<any> | string, keepExisting?: boolean, suppressEvent?: boolean): void;
		select(nodeInfo: (HTMLElement | number | Ext.data.Record<any> | string)[], keepExisting?: boolean, suppressEvent?: boolean): void;
		/**
		 * Gets the number of selected nodes.
		 * @returns The node count
		 */
		getSelectionCount(): number;
		/**
		 * Selects a range of nodes. All nodes between start and end are selected.
		 * @param start The index of the first node in the range
		 * @param end The index of the last node in the range
		 * @param keepExisting True to retain existing selections
		 */
		selectRange(start: number, end: number, keepExisting: boolean): void;

		protected loadingText: string;
		protected getTemplateTarget(): Ext.Element;
		protected all: Ext.CompositeElementLite;
		emptyText: string;
	}
	export interface IElement { }
	export class DomHelper {
		/**
		 * Applies a style specification to an element.
		 * @param {String/HTMLElement} el The element to apply styles to
		 * @param {String/Object/Function} styles A style specification string e.g. 'width:100px', or object in the form {width:'100px'}, or
		 * a function which returns such a specification.
		 */
		static applyStyles(el: string | HTMLElement | Element, styles: string | object | Function): void;
		/**
		 * Creates new DOM element(s) and appends them to el.
		 * @param {Mixed} el The context element
		 * @param {Object/String} o The DOM object spec (and children) or raw HTML blob
		 * @param {Boolean} returnElement (optional) true to return a Ext.Element
		 * @return {HTMLElement/Ext.Element} The new node
		 */
		static append(el: string | HTMLElement | Element, o: any, returnElement?: false): HTMLElement;
		static append(el: string | HTMLElement | Element, o: any, returnElement?: true): Element;
		/**
		 * Creates new DOM element(s) without inserting them to the document.
		 * @param {Object/String} o The DOM object spec (and children) or raw HTML blob
		 * @return {HTMLElement} The new uninserted node
		 */
		static createDom(cfg: any): HTMLElement;
		/**
		 * Creates a new Ext.Template from the DOM object spec.
		 * @param {Object} o The DOM object spec (and children)
		 * @return {Ext.Template} The new template
		 */
		static createTemplate(o: any): Template;
		/**
		 * Returns the markup for the passed Element(s) config.
		 * @param {Object} o The DOM object spec (and children)
		 * @return {String}
		 */
		static createHtml(o: any): string;
		/**
		 * Creates new DOM element(s) and inserts them after el.
		 * @param {Mixed} el The context element
		 * @param {Object} o The DOM object spec (and children)
		 * @param {Boolean} returnElement (optional) true to return a Ext.Element
		 * @return {HTMLElement/Ext.Element} The new node
		 */
		static insertAfter(el: string | HTMLElement | Element, o: any, returnElement?: false): HTMLElement;
		static insertAfter(el: string | HTMLElement | Element, o: any, returnElement?: true): Element;
		/**
		 * Creates new DOM element(s) and inserts them before el.
		 * @param {Mixed} el The context element
		 * @param {Object/String} o The DOM object spec (and children) or raw HTML blob
		 * @param {Boolean} returnElement (optional) true to return a Ext.Element
		 * @return {HTMLElement/Ext.Element} The new node
		 */
		static insertBefore(el: string | HTMLElement | Element, o: any, returnElement?: false): HTMLElement;
		static insertBefore(el: string | HTMLElement | Element, o: any, returnElement?: true): Element;
		/**
		 * Creates new DOM element(s) and inserts them as the first child of el.
		 * @param {Mixed} el The context element
		 * @param {Object/String} o The DOM object spec (and children) or raw HTML blob
		 * @param {Boolean} returnElement (optional) true to return a Ext.Element
		 * @return {HTMLElement/Ext.Element} The new node
		 */
		static insertFirst(el: string | HTMLElement | Element, o: any, returnElement?: false): HTMLElement;
		static insertFirst(el: string | HTMLElement | Element, o: any, returnElement?: true): Element;
		/**
		 * Inserts an HTML fragment into the DOM.
		 * @param {String} where Where to insert the html in relation to el - beforeBegin, afterBegin, beforeEnd, afterEnd.
		 * @param {HTMLElement} el The context element
		 * @param {String} html The HTML fragment
		 * @return {HTMLElement} The new node
		 */
		static insertHtml(where: string, el: HTMLElement, html: string): HTMLElement;
		/**
		 * Returns the markup for the passed Element(s) config.
		 * @param {Object} o The DOM object spec (and children)
		 * @return {String}
		 */
		static markup(o: any): string;
		/**
		 * Creates new DOM element(s) and overwrites the contents of el with them.
		 * @param {Mixed} el The context element
		 * @param {Object/String} o The DOM object spec (and children) or raw HTML blob
		 * @param {Boolean} returnElement (optional) true to return a Ext.Element
		 * @return {HTMLElement/Ext.Element} The new node
		 */
		static overwrite(el: string | HTMLElement | Element, o: any, returnElement?: false): HTMLElement;
		static overwrite(el: string | HTMLElement | Element, o: any, returnElement?: true): Element;
	}
	/**
	* Encapsulates a DOM element, adding simple DOM manipulation facilities, normalizing for browser differences.
	*/
	export class Element {
		/**
		 * true to automatically adjust width and height settings for box-model issues (default to true)
		 */
		autoBoxAdjust: boolean;
		defaultUnit: string;
		dom: HTMLElement;
		id: string;
		originalDisplay: string;
		readonly DISPLAY: number;
		readonly OFFSETS: number;
		readonly VISIBILITY: number;
		readonly visibilityCls: string;
		/**
		 * Create a new Element directly.
		 * @param element
		 * @param forceNew By default the constructor checks to see if there is already an instance of this element in the cache and if there is it returns the same instance. This will skip that check (useful for extending this class).
		 */
		constructor(element: string | HTMLElement, forceNew?: boolean);

		/**
		* Adds one or more CSS classes to the element. Duplicate classes are automatically filtered out.
		* @param {String/Array} className The CSS class to add, or an array of classes
		* @return {Ext.Element} this
		*/
		addClass(className: string | string[]): this;
		addClassOnClick(className: string): this;
		addClassOnFocus(className: string): this;
		addClassOnOver(className: string): this;
		addKeyListener(key: number | object | string | number[] | string[], fn: Function, scope?: object): KeyMap;
		addKeyMap(config: IKeyMap): KeyMap;
		addListener(eventName: string, fn: Function, scope?: object, options?: object): this;
		alignTo(element: Element | HTMLElement, position: string, offsets?: [number, number], animate?: boolean | object): this;
		anchorTo(element: Element | HTMLElement, position: string, offsets?: [number, number], animate?: boolean | object, monitorScroll?: boolean | number, callback?: Function): this;
		animate(args: object, duration?: number, onComplete?: Function, easing?: string, animType?: string): this;
		appendChild(el: string | HTMLElement | Element | CompositeElement | Array<string | HTMLElement | Element | CompositeElement>): this;
		appendTo(el: Element | HTMLElement): this;
		applyStyles(styles: string | object | Function): this;
		blur(): this;
		boxWrap(cls?: string): Element;
		center(centerIn?: Element): void;
		child(selector: string, returnDom?: boolean): Element | HTMLElement;
		clean(forceReclean?: boolean): void;
		clearOpacity(): this;
		clearPositioning(value?: string): this;
		clip(): this;
		contains(el: HTMLElement | string): boolean;
		createChild(config: object, insertBefore?: HTMLElement, returnDom?: boolean): Element;
		/**
		* Creates a proxy element of this element
		* @param {String/Object} config The class name of the proxy element or a DomHelper config object
		* @param {String/HTMLElement} renderTo (optional) The element or element id to render the proxy to (defaults to document.body)
		* @param {Boolean} matchBox (optional) True to align and size the proxy to this element now (defaults to false)
		* @return {Ext.Element} The new proxy element
		*/
		createProxy(config: string | object, renderTo?: string | HTMLElement, matchBox?: boolean): Element;
		createShim(): Element;
		down(selector: string, returnDom?: boolean): HTMLElement | Element;
		enableDisplayMode(display?: string): this;
		/**
		 * Looks at this node and then at parent nodes for a match of the passed simple selector (e.g. div.some-class or span:first-child)
		 * @param selector The simple selector to test
		 * @param maxDepth The max depth to search as a number or element (defaults to 50 || document.body)
		 * @returns The matching DOM node (or null if no match was found)
		 */
		findParent(selector: string, maxDepth?: number): HTMLElement;
		/**
		 * Looks at this node and then at parent nodes for a match of the passed simple selector (e.g. div.some-class or span:first-child)
		 * @param selector The simple selector to test
		 * @param maxDepth The max depth to search as a number or element (defaults to 50 || document.body)
		 * @param returnEl True to return a Ext.Element object instead of DOM node
		 * @returns The matching Ext.Element (or null if no match was found)
		 */
		findParent(selector: string, maxDepth?: number, returnEl?: true): Element;
		findParentNode(selector: string, maxDepth?: number, returnEl?: boolean): void;
		first(selector?: string, returnDom?: boolean): Element | HTMLElement;
		fly(el: string | HTMLElement, named?: string): void;
		focus(defer?: number): Element;
		static get(el: Element): void;
		getAlignToXY(element: Element, position: string, offsets?: [number, number]): [number, number];
		getAnchorXY(anchor?: string, local?: boolean, size?: Size): [number, number];
		getAttribute(name: string, namespace?: string): string;
		getBorderWidth(side: string): number;
		getBottom(local: boolean): number;
		getBox(contentBox?: boolean, local?: boolean): Box;
		getCenterXY(): [number, number];
		getColor(attr: string, defaultValue: string, prefix?: string): void;
		getComputedHeight(): number;
		getComputedWidth(): number;
		getFrameWidth(sides: string): number;
		getHeight(contentHeight?: boolean): number;
		getLeft(local: boolean): number;
		getMargins(sides?: string): Margins | number;
		getOffsetsTo(element: Element): [number, number];
		getPadding(side: string): number;
		getPositioning(): object;
		getRegion(): void;
		getRight(local: boolean): number;
		getScroll(): Position;
		getSize(contentSize?: boolean): Size;
		getStyle(property: string): string;
		/**
		 * Returns the dimensions of the element available to lay content out in.
		 * getStyleSize utilizes prefers style sizing if present, otherwise it chooses the larger of offsetHeight/clientHeight and offsetWidth/clientWidth. To obtain the size excluding scrollbars, use getViewSize Sizing of the document body is handled at the adapter level which handles special cases for IE and strict modes, etc.
		 */
		getStyleSize(): Size;
		getStyles(style1: string, style2: string, ...etc: string[]): object;
		getTextWidth(text: string, min?: number, max?: number): number;
		getTop(local: boolean): number;
		getUpdater(): Updater;
		getValue(asNumber: boolean): string | number;
		getViewSize(): Size;
		getWidth(contentWidth?: boolean): number;
		getX(): number;
		getXY(): [number, number];
		getY(): number;
		hasClass(className: string): boolean;
		hide(animate?: boolean | object): this;
		hover(overFn: Function, outFn: Function, scope?: object, options?: object): this;
		initDD(group: string, config: object, overrides: object): void;
		initDDProxy(group: string, config: object, overrides: object): void;
		initDDTarget(group: string, config: object, overrides: object): void;
		insertAfter(el: Element | HTMLElement): this;
		insertBefore(el: Element | HTMLElement): this;
		insertFirst(el: Element | HTMLElement): Element;
		insertHtml(where: string, html: string, returnEl?: boolean): HTMLElement | Element;
		insertSibling(el: Element | HTMLElement, where?: string, returnDom?: boolean): Element;
		is(selector: string): boolean;
		isBorderBox(): boolean;
		isDisplayed(): boolean;
		isMasked(): boolean;
		isScrollable(): boolean;
		isVisible(): boolean;
		last(selector?: string, returnDom?: boolean): Element | HTMLElement;
		load(options: string): this;
		mask(msg?: string, msgCls?: string): void;
		move(direction: string, distance: number, animate?: boolean | object): this;
		moveTo(x: number, y: number, animate?: boolean): this;
		next(selector?: string, returnDom?: boolean): Element | HTMLElement;
		on(eventName: string, fn: Function, scope?: object, options?: object): void;
		/**
		 * Gets the parent node for this element, optionally chaining up trying to match a selector
		 * @param selector Find a parent node that matches the passed simple selector
		 * @returns The parent Element or null
		 */
		parent(selector?: string): Element;
		/**
		 * Gets the parent node for this element, optionally chaining up trying to match a selector
		 * @param selector Find a parent node that matches the passed simple selector
		 * @param returnDom True to return a raw dom node instead of an Ext.Element
		 * @returns The parent node or null
		 */
		parent(selector: string, returnDom: true): HTMLElement;
		position(pos?: string, zIndex?: number, x?: number, y?: number): void;
		prev(selector?: string, returnDom?: boolean): void;
		purgeAllListeners(): this;
		query(selector: string): HTMLElement[];
		radioClass(className: string[]): this;
		relayEvent(eventName: string, object: object): void;
		remove(): void;
		removeAllListeners(): this;
		removeAnchor(): this;
		removeClass(className: string): this;
		removeListener(eventName: string, fn: Function, scope: object): this;
		repaint(): this;
		replace(el: Element): this;
		replaceClass(oldClassName: string, newClassName: string): this;
		replaceWith(el: object): this;
		scroll(direction: string, distance: number, animate?: boolean): boolean;
		scrollIntoView(container?: string | HTMLElement | Element, hscroll?: boolean): this;
		scrollTo(side: string, value: number): this;
		select(selector: string): CompositeElement | CompositeElementLite;
		static select(selector: string, root?: boolean): CompositeElement | CompositeElementLite;
		set(o: object, useSet?: boolean): this;
		setBottom(bottom: string): this;
		setBounds(x: number, y: number, width: number | string, height: number | string, animate?: boolean): this;
		setBox(box: Box, adjust?: boolean, animate?: boolean | object): this;
		setDisplayed(value: boolean | string): this;
		setHeight(height: number | string, animate?: boolean): this;
		setLeft(left: string): this;
		setLeftTop(left: string, top: string): this;
		setLocation(x: number, y: number, animate?: boolean): this;
		setOpacity(opacity: number, animate?: boolean): this;
		setPositioning(posCfg: object): this;
		setRegion(region: object, animate?: boolean): this;
		setRight(right: string): this;
		setSize(width: number | string | Size, height: number | string, animate?: boolean): this;
		/**
		* Wrapper for setting style properties, also takes single object parameter of multiple styles.
		* @param {String/Object} property The style property to be set, or an object of multiple styles.
		* @param {String} value (optional) The value to apply to the given property, or null if an object was passed.
		* @return {Ext.Element} this
		*/
		setStyle(property: string, value: string): this;
		setStyle(property: any): this;
		setTop(top: string): this;
		setVisibilityMode(visMode: number): this;
		setVisible(visible: boolean, animate?: boolean): this;
		setWidth(width: number | string, animate?: boolean): this;
		setX(The: number, animate?: boolean): this;
		setXY(pos: [number, number], animate?: boolean): this;
		setY(The: number, animate?: boolean): this;
		show(animate?: boolean): this;
		swallowEvent(eventName: string, preventDefault?: boolean): this;
		toggle(animate?: boolean): this;
		toggleClass(className: string): this;
		translatePoints(xy: [number, number]): Position;
		translatePoints(x: number, y: number): Position;
		un(eventName: string, fn: Function, scope: object): this;
		unclip(): this;
		unmask(): void;
		/**
		* Disables text selection for this element (normalized across browsers)
		* @return {Ext.Element} this
		*/
		unselectable(): this;
		up(selector: string, maxDepth?: number): this;
		update(html: string): this;
		/**
		 * Creates and wraps this element with another element
		 * @param config DomHelper element config object for the wrapper element or null for an empty div
		 * @param returnDom True to return the raw DOM element instead of Ext.Element
		 * @return The newly created wrapper element
		 */
		wrap(config?: object): Element;
		wrap(config: object, returnDom: true): HTMLElement;
	}
	export interface IComponent extends util.IObservable {
		renderTo?: string | Element | HTMLElement;
		xtype?: string;
		autoEl?: string | IBodyCfg;
		allowDomMove?: boolean;
		applyTo?: string | HTMLElement | Element;
		html?: string;
		stateful?: boolean;
		stateId?: string;
		ctCls?: string;
		itemId?: string;
		cls?: string;
		id?: string;
		style?: string | object;
		bubbleEvents?: string[];
		fieldLabel?: string;
		hidden?: boolean;
		plugins?: any;
		data?: any;
		tpl?: Template | XTemplate | string[];
		labelStyle?: string;
		hideMode?: HideMode;
		disabled?: boolean;
	}
	/**
	 * Base class for all Ext components.
	 * All subclasses of Component may participate in the automated Ext component lifecycle of creation, rendering and destruction which is provided by the Container class.
	 */
	export class Component extends util.Observable {
		readonly itemId: string;
		readonly cls: string;
		readonly id: string;
		protected html: string | object;
		readonly data: any;
		readonly disabled: boolean;
		readonly hidden: boolean;
		readonly ownerCt?: Container;
		readonly rendered: boolean;
		protected container: Ext.Element;

		constructor(cfg?: IComponent);
		protected initComponent(): void;

		/**
		* Adds a CSS class to the component's underlying element.
		* @param {string} cls The CSS class name to add
		* @return {Ext.Component} this
		*/
		addClass(cls: string): this;
		/**
		* Apply this component to existing markup that is valid. With this function, no call to render() is required.
		* @param {String/HTMLElement} el
		*/
		applyToMarkup(el: string | HTMLElement): void;
		/**
		 * Bubbles up the component/container heirarchy, calling the specified function with each component. The scope (this) of function call will be the scope provided or the current component. The arguments to the function will be the args provided or the current component. If the function returns false at any point, the bubble is stopped.
		 * @param fn The function to call
		 * @param scope The scope of the function (defaults to current node)
		 * @param args The args to call the function with (default to passing the current component)
		 */
		bubble(fn: Function, scope?: object, args?: object): this;
		/**
		 * Clone the current component using the original config values passed into this instance by default.
		 * @param overrides A new config containing any properties to override in the cloned version. An id property can be passed on this object, otherwise one will be generated to avoid duplicates.
		 */
		cloneConfig(overrides: object): Component;
		/**
		 * Destroys this component by purging any event listeners, removing the component's element from the DOM, removing the component from its Ext.Container (if applicable) and unregistering it from Ext.ComponentMgr. Destruction is generally handled automatically by the framework and this method should usually not need to be called directly.
		 */
		destroy(): void;
		/**
		 * Disable this component and fire the 'disable' event.
		 * @param silent
		 */
		disable(silent?: boolean): this;
		/**
		 * Enable this component and fire the 'enable' event.
		 */
		enable(): this;
		/**
		 * Find a container above this component at any level by a custom function. If the passed function returns true, the container will be returned.
		 * @param fn The custom function to call with the arguments (container, this component).
		 */
		findParentBy<T = Container>(fn: (container: T, cmp: this) => boolean): T;
		/**
		 * Find a container above this component at any level by xtype or class
		 * @param xtype The xtype to check for this Component. Note that the the component can either be an instance or a component class:
		 * @param shallow False to check whether this Component is descended from the xtype (this is the default), or true to check whether this Component is directly of the specified xtype.
		 * @returns The first Container which matches the given xtype or class
		 */
		findParentByType(xtype: string | Component, shallow?: boolean): Container;
		/**
		 * Try to focus this component.
		 * @param selectText If applicable, true to also select the text in this component
		 * @param delay Delay the focus this number of milliseconds (true for 10 milliseconds)
		 */
		focus(selectText?: boolean, delay?: boolean | number): this;
		/**
		 * Provides the link for Observable's fireEvent method to bubble up the ownership hierarchy.
		 * @returns the Container which owns this Component.
		 */
		getBubbleTarget(): Container;
		/**
		 * Returns the {@link Ext.Element} which encapsulates this Component.
		 * This will usually be a DIV element created by the class's onRender method, but that may be overridden using the {@link #autoEl} config.
		 * Note: this element will not be available until this Component has been rendered.
		 * @return {Ext.Element} The Element which encapsulates this Component.
		 */
		getEl(): Element;
		getId(): string;
		getItemId(): string;
		getXType(): string;
		getXTypes(): string;
		hide(): this;
		isVisible(): boolean;
		isXType(xtype: string, shallow?: boolean): boolean;
		mon(item: Ext.util.Observable | Ext.Element, ename: object | string, fn?: Function, scope?: object, opt?: object): void;
		mun(item: Ext.util.Observable | Ext.Element, ename: object | string, fn?: Function, scope?: object): void;
		nextSibling(): object;
		previousSibling(): object;
		/**
		* Removes a CSS class from the component's underlying element.
		* @param {string} cls The CSS class name to remove
		* @return {Ext.Component} this
		*/
		removeClass(cls: string): this;
		/**
		* Render this Component into the passed HTML element.
		* If you are using a {@link Ext.Container Container} object to house this Component, then
		* do not use the render method.
		* A Container's child Components are rendered by that Container's
		* {@link Ext.Container#layout layout} manager when the Container is first rendered.
		* Certain layout managers allow dynamic addition of child components. Those that do
		* include {@link Ext.layout.CardLayout}, {@link Ext.layout.AnchorLayout},
		* {@link Ext.layout.FormLayout}, {@link Ext.layout.TableLayout}.
		* If the Container is already rendered when a new child Component is added, you may need to call
		* the Container's {@link Ext.Container#doLayout doLayout} to refresh the view which causes any
		* unrendered child Components to be rendered. This is required so that you can add multiple
		* child components if needed while only refreshing the layout once.
		* When creating complex UIs, it is important to remember that sizing and positioning
		* of child items is the responsibility of the Container's {@link Ext.Container#layout layout} manager.
		* If you expect child items to be sized in response to user interactions, you must
		* configure the Container with a layout manager which creates and manages the type of layout you
		* have in mind.
		* Omitting the Container's {@link Ext.Container#layout layout} config means that a basic
		* layout manager is used which does nothing but render child components sequentially into the
		* Container. No sizing or positioning will be performed in this situation.
		* @param {Element/HTMLElement/String} container (optional) The element this Component should be
		* rendered into. If it is being created from existing markup, this should be omitted.
		* @param {String/Number} position (optional) The element ID or DOM node index within the container before
		* which this component will be inserted (defaults to appending to the end of the container)
		*/
		render(container?: Element | HTMLElement | string, position?: number): this;
		setDisabled(disabled: boolean): this;
		/**
		 * Convenience function to hide or show this component by boolean.
		 * @param visible True to show, false to hide
		 */
		setVisible(visible: boolean): this;
		show(...args: any[]): this;
		update(htmlOrData: string | object, loadScripts?: boolean, callback?: Function): void;
		saveState(): void;

		protected onRender(ct: Ext.Element, position?: string | number): void;

		/** A custom style specification to be applied to this component's Element. Should be a valid argument to Ext.Element.applyStyles. */
		private readonly style?: object | string;
	}
	export interface Margins {
		top: number;
		right: number;
		bottom: number;
		left: number;
	}
	export interface IBoxComponent extends IComponent {
		anchor?: string;
		autoHeight?: boolean;
		autoScroll?: boolean;
		autoWidth?: boolean;
		boxMaxHeight?: number;
		boxMaxWidth?: number;
		boxMinHeight?: number;
		boxMinWidth?: number;
		flex?: number;
		height?: number | string;
		margins?: Margins | string;
		pageX?: number;
		pageY?: number;
		region?: string;
		tabTip?: string;
		width?: number | string;
		x?: number;
		y?: number;
	}
	/**
	 * Base class for any Component that is to be sized as a box, using width and height.
	 */
	export class BoxComponent extends Component {
		readonly width: number;
		readonly height: number;
		readonly boxMinHeight: number;
		readonly flex: number;
		margins: Margins | string;
		getPosition(local?: boolean): [number, number];
		getWidth(): number;
		getHeight(): number;
		setWidth(width: number): BoxComponent;
		setWidth(height: string): BoxComponent;
		setHeight(width: number): BoxComponent;
		setHeight(height: string): BoxComponent;
		setPosition(left: number, top: number): BoxComponent;
		setSize(width: number, height: number): this;
		setSize(size: Size): this;
		getBox(local?: boolean): Box;

		constructor(cfg?: IBoxComponent);

		protected afterRender(): void;
		protected onResize(adjWidth: number, adjHeight: number, rawWidth?: number, rawHeight?: number): void;
		readonly scrollOffset: number;
	}
	type Direction = 'ASC' | 'DESC';
	type AlignToDeprecated = 'tl' | 't' | 'tr' | 'l' | 'c' | 'r' | 'bl' | 'b' | 'br';
	type AlignTo =
		'tl-tl' | 'tl-t' | 'tl-tr' | 'tl-l' | 'tl-c' | 'tl-r' | 'tl-bl' | 'tl-b' | 'tl-br' |
		't-tl' | 't-t' | 't-tr' | 't-l' | 't-c' | 't-r' | 't-bl' | 't-b' | 't-br' |
		'tr-tl' | 'tr-t' | 'tr-tr' | 'tr-l' | 'tr-c' | 'tr-r' | 'tr-bl' | 'tr-b' | 'tr-br' |
		'l-tl' | 'l-t' | 'l-tr' | 'l-l' | 'l-c' | 'l-r' | 'l-bl' | 'l-b' | 'l-br' |
		'c-tl' | 'c-t' | 'c-tr' | 'c-l' | 'c-c' | 'c-r' | 'c-bl' | 'c-b' | 'c-br' |
		'r-tl' | 'r-t' | 'r-tr' | 'r-l' | 'r-c' | 'r-r' | 'r-bl' | 'r-b' | 'r-br' |
		'bl-tl' | 'bl-t' | 'bl-tr' | 'bl-l' | 'bl-c' | 'bl-r' | 'bl-bl' | 'bl-b' | 'bl-br' |
		'b-tl' | 'b-t' | 'b-tr' | 'b-l' | 'b-c' | 'b-r' | 'b-bl' | 'b-b' | 'b-br' |
		'br-tl' | 'br-t' | 'br-tr' | 'br-l' | 'br-c' | 'br-r' | 'br-bl' | 'br-b' | 'br-br' |
		'tl-tl?' | 'tl-t?' | 'tl-tr?' | 'tl-l?' | 'tl-c?' | 'tl-r?' | 'tl-bl?' | 'tl-b?' | 'tl-br?' |
		't-tl?' | 't-t?' | 't-tr?' | 't-l?' | 't-c?' | 't-r?' | 't-bl?' | 't-b?' | 't-br?' |
		'tr-tl?' | 'tr-t?' | 'tr-tr?' | 'tr-l?' | 'tr-c?' | 'tr-r?' | 'tr-bl?' | 'tr-b?' | 'tr-br?' |
		'l-tl?' | 'l-t?' | 'l-tr?' | 'l-l?' | 'l-c?' | 'l-r?' | 'l-bl?' | 'l-b?' | 'l-br?' |
		'c-tl?' | 'c-t?' | 'c-tr?' | 'c-l?' | 'c-c?' | 'c-r?' | 'c-bl?' | 'c-b?' | 'c-br?' |
		'r-tl?' | 'r-t?' | 'r-tr?' | 'r-l?' | 'r-c?' | 'r-r?' | 'r-bl?' | 'r-b?' | 'r-br?' |
		'bl-tl?' | 'bl-t?' | 'bl-tr?' | 'bl-l?' | 'bl-c?' | 'bl-r?' | 'bl-bl?' | 'bl-b?' | 'bl-br?' |
		'b-tl?' | 'b-t?' | 'b-tr?' | 'b-l?' | 'b-c?' | 'b-r?' | 'b-bl?' | 'b-b?' | 'b-br?' |
		'br-tl?' | 'br-t?' | 'br-tr?' | 'br-l?' | 'br-c?' | 'br-r?' | 'br-bl?' | 'br-b?' | 'br-br?';
	export interface IButton extends IBoxComponent {
		allowDepress?: boolean;
		arrowAlign?: 'right' | 'bottom';
		autoWidth?: boolean;
		buttonSelector?: string;
		clickEvent?: string;
		cls?: string;
		disabled?: boolean;
		enableToggle?: boolean;
		handleMouseEvents?: boolean;
		handler?: (b: Button, e: EventObject) => void;
		hidden?: boolean;
		icon?: string;
		iconAlign?: Align;
		iconCls?: string;
		menu?: string | object;
		menuAlign?: AlignTo | AlignToDeprecated;
		minWidth?: number;
		overflowText?: string;
		pressed?: boolean;
		repeat?: boolean | util.ClickRepeater;
		scale?: string;
		scope?: object;
		tabIndex?: number;
		text?: string;
		toggleGroup?: string;
		toggleHandler?: (b: Button, state: boolean) => void;
		tooltip?: string | object;
		tooltipType?: 'qtip' | 'title';
		type?: 'button' | 'submit' | 'reset';
	}
	export type Align = 'top' | 'right' | 'bottom' | 'left';
	export class Button extends BoxComponent implements Action {
		readonly btnEl: Element;
		readonly disabled: boolean;
		readonly hidden: boolean;
		menu: menu.Menu;
		readonly pressed: boolean;
		template?: Template;

		protected iconAlign: Align;
		protected scale: 'small' | 'medium' | 'large';
		protected type: 'button' | 'submit' | 'reset';

		constructor(cfg?: IButton | any);

		each(fn: Function, scope: object): void;
		execute(...args: any[]): void;
		getIconClass(): string;
		isDisabled(): boolean;
		isHidden(): boolean;
		setDisabled(disabled: boolean);
		setHidden(hidden: boolean);
		getPressed(group: string): Button;
		getText(): string;
		hasVisibleMenu(): boolean;
		hideMenu(): this;
		setHandler(handler: Function, scope?: object): this;
		setIcon(icon: string): this;
		setIconClass(cls: string): this;
		/**
		 * Sets this Button's text
		 * @param {String} text The button text
		 * @return {Ext.Button} this
		 */
		setText(text: string): this;
		setTooltip(tooltip: string | object): this;
		showMenu(): this;
		/**
		 * If a state it passed, it becomes the pressed state otherwise the current state is toggled.
		 * @param state Force a particular state
		 * @param supressEvent True to stop events being fired when calling this method
		 */
		toggle(state?: boolean, supressEvent?: boolean): this;
		showMenu(): this;

		protected getMenuClass(): string;
	}
	export interface ISplitButton extends IButton {
		arrowHandler?: Function;
		arrowTooltip?: Function;
	}
	export class SplitButton extends Button {
		constructor(cfg?: ISplitButton);
		setArrowHandler(handler: Function, scope?: object): void;
	}
	export interface Layout {
		type?: string;
		padding?: number | string;
		align?: string;
		labelSeparator?: string;
		titleCollapse?: boolean;
		animate?: boolean;
		activeOnTop?: boolean;
		fill?: boolean;
		hideLabels?: boolean;
		labelAlign?: Align;
		labelPad?: number;
		labelWidth?: number;
	}
	export interface Box {
		x: number;
		y: number;
		width: number;
		height: number;
		bottom?: number;
		right?: number;
	}
	export interface Margins {
		top: number;
		left: number;
		right: number;
		bottom: number;
	}
	export interface Position {
		left: number;
		top: number;
	}
	export interface Size {
		width: number;
		height: number;
	}
	export interface IContainer extends IBoxComponent {
		activeItem?: string | number;
		autoDestroy?: boolean;
		bubbleEvents?: string[];
		bufferResize?: boolean | number;
		defaultType?: string;
		defaults?: any;
		forceLayout?: boolean;
		hideBorders?: boolean;
		items?: Component[] | IComponent[] | any[];
		layout?: string | Layout | layout.ContainerLayout;
		layoutConfig?: Layout;
		monitorResize?: boolean;
		resizeEvent?: string;
	}
	export class Container extends BoxComponent {
		static LAYOUTS: { [index: string]: Function };
		/** The collection of components in this container as a Ext.util.MixedCollection */
		readonly items: util.MixedCollection<any>;
		protected defaults: any;
		protected layout: string | Layout | layout.ContainerLayout;

		constructor(cfg?: IContainer);

		/**
		 * Adds Component(s) to this Container.
		 * @param component Either one or more Components to add or an Array of Components to add. See items for additional information.
		 * @returns {} The Components that were added.
		 */
		add(...component: BoxComponent[]): BoxComponent[];
		add<T = BoxComponent>(...component: T[]): T[];
		/**
		 * Cascades down the component/container heirarchy from this component (called first), calling the specified function with each component. The scope (this) of function call will be the scope provided or the current component. The arguments to the function will be the args provided or the current component. If the function returns false at any point, the cascade is stopped on that branch.
		 * @param fn The function to call
		 * @param scope The scope of the function (defaults to current component)
		 * @param args The args to call the function with (defaults to passing the current component)
		 */
		cascade(fn: Function, scope?: object, args?: any[]): this;
		/**
		 * Force this container's layout to be recalculated. A call to this function is required after adding a new component to an already rendered container, or possibly after changing sizing/position properties of child components.
		 * @param shallow True to only calc the layout of this component, and let child components auto calc layouts as required (defaults to false, which calls doLayout recursively for each subcontainer)
		 * @param force True to force a layout to occur, even if the item is hidden.
		 * @returns {this}
		 */
		doLayout(shallow?: boolean, force?: boolean): this;
		/**
		 * Find a component under this container at any level by property
		 * @param prop
		 * @param value
		 * @returns {} Array of Ext.Components
		 */
		find(prop: string, value: any): Component[];
		find<T>(prop: string, value: any): T[];
		/**
		 * Find a component under this container at any level by a custom function. If the passed function returns true, the component will be included in the results. The passed function is called with the arguments (component, this container).
		 * @param fn The function to call
		 * @param scope
		 */
		findBy(fn: (item: Component, container: Container) => boolean, scope?: object): Component[];
		findBy<T>(fn: (item: T, container: Container) => boolean, scope?: object): T[];
		/**
		 * Find a component under this container at any level by xtype or class
		 * @param xtype The xtype string for a component, or the class of the component directly
		 * @param shallow False to check whether this Component is descended from the xtype (this is the default), or true to check whether this Component is directly of the specified xtype.
		 */
		findByType(xtype: string, shallow?: boolean): Component[];
		/**
		 * Examines this container's items property and gets a direct child component of this container.
		 * @param comp This parameter may be any of the following: a String representing the itemId or id of the child component, a Number representing the position of the child component within the items property. For additional information see Ext.util.MixedCollection.get.
		 * @returns The component (if found).
		 */
		getComponent(comp: string | number): Component;
		getComponent<T>(comp: string | number): T;
		/**
		 * Returns the layout currently in use by the container. If the container does not currently have a layout set, a default Ext.layout.ContainerLayout will be created and set as the container's layout.
		 * @returns The container's layout
		 */
		getLayout(): layout.ContainerLayout;
		/**
		 * Returns the layout currently in use by the container. If the container does not currently have a layout set, a default Ext.layout.ContainerLayout will be created and set as the container's layout.
		 * @returns The container's layout
		 */
		getLayout<T>(): T;
		/**
		 * Returns the Element to be used to contain the child Components of this Container.
		 * An implementation is provided which returns the Container's Element, but if there is a more complex structure to a Container, this may be overridden to return the element into which the layout renders child Components.
		 * @returns {Ext.Element} The Element to render child Components into.
		 */
		getLayoutTarget(): Element;
		/**
		 * Inserts a Component into this Container at a specified index. Fires the beforeadd event before inserting, then fires the add event after the Component has been inserted.
		 * @param index The index at which the Component will be inserted into the Container's items collection
		 * @param component The child Component to insert.
		 * @returns The Component (or config object) that was inserted with the Container's default config values applied.
		 */
		insert(index: number, component: Component | IComponent): Component;
		/**
		 * Removes a component from this container. Fires the beforeremove event before removing, then fires the remove event after the component has been removed.
		 * @param component The component reference or id to remove.
		 * @param autoDestroy True to automatically invoke the removed Component's Ext.Component.destroy function. Defaults to the value of this Container's autoDestroy config.
		 */
		remove(component: Component | string, autoDestroy?: boolean): Component;
		/**
		 * Removes all components from this container.
		 * @param autoDestroy True to automatically invoke the removed Component's Ext.Component.destroy function. Defaults to the value of this Container's autoDestroy config.
		 * @returns Array of the destroyed components
		 */
		removeAll(autoDestroy?: boolean): Component[];
		/**
		* Find a component under this container at any level by id
		* @param {String} id
		* @deprecated Fairly useless method, since you can just use Ext.getCmp. Should be removed for 4.0
		* If you need to test if an id belongs to a container, you can use getCmp and findParent*.
		* @return Ext.Component
		*/
		findById(id: string): Component;
		findById<T extends Component>(id: string): T;
	}
	export interface IKeyMap {
		key: number[] | number | string;
		fn: Function;
		scope?: object;
		ctrl?: boolean;
		shift?: boolean;
		alt?: boolean;
		stopEvent?: boolean;
	}
	export class KeyMap {
		constructor(el: string | Element, config: IKeyMap, eventName?: string);
		addBinding(config: IKeyMap): void;
		disable(): void;
		enable(): void;
		isEnabled(): boolean;
		on(key: number[] | number | string, fn: Function, scope?: Object): void;
		setDisabled(disabled: boolean): void;
	}
	export interface IToolbar extends IContainer {
		buttonAlign?: Align;
		enableOverflow?: boolean;
		layout?: string | Layout;
	}
	export class Toolbar extends Container {
		constructor(cfg?: IToolbar);
	}
	export class Template {
		constructor(...cfg: any[]);
	}
	export interface ITemplate { }
	export class XTemplate extends Template {
		constructor(...cfg: any[]);
	}
	export interface IXTemplate extends ITemplate { }
	export interface IBodyCfg {
		tag?: string;
		cls?: string;
		html?: string;
		type?: string;
		size?: string | number;
		autocomplete?: string;
		src?: string;
	}
	export interface IToolButton {
		id: string;
		handler: (event: EventObject, toolEl: Element, panel: Panel, tc: Object) => void;
		stopEvent?: boolean;
		scope?: object;
		qtip?: string | object;
		hidden?: boolean;
		on?: object;
	}
	export interface IPanel extends IContainer {
		animCollapse?: boolean;
		applyTo?: string | HTMLElement | Element;
		autoHeight?: boolean;
		autoLoad?: object | string | Function;
		baseCls?: string;
		bbar?: BoxComponent[] | IBoxComponent[] | Toolbar;
		bbarCfg?: IBodyCfg;
		bodyBorder?: boolean;
		bodyCfg?: IBodyCfg;
		bodyCssClass?: string;
		bodyStyle?: string;
		border?: boolean;
		buttonAlign?: string;
		buttons?: IButton[] | Button[];
		bwrapCfg?: IBodyCfg;
		closable?: boolean;
		collapseFirst?: boolean;
		collapsed?: boolean;
		collapsedCls?: string;
		collapsible?: boolean;
		disabled?: boolean;
		disabledClass?: string;
		draggable?: boolean;
		elements?: string;
		fbar?: BoxComponent[] | IBoxComponent[] | Toolbar;
		floating?: boolean | object;
		footer?: boolean;
		footerCfg?: IBodyCfg;
		frame?: boolean;
		header?: boolean;
		headerAsText?: boolean;
		headerCfg?: IBodyCfg;
		hideCollapseTool?: boolean;
		iconCls?: string;
		keys?: object | Array<IKeyMap>;
		maskDisabled?: boolean;
		minButtonWidth?: number;
		padding?: number | string;
		preventBodyReset?: boolean;
		resizeEvent?: string;
		shadow?: boolean | string;
		shadowOffset?: number;
		shim?: boolean;
		tbar?: BoxComponent[] | IBoxComponent[] | Toolbar;
		tbarCfg?: IBodyCfg;
		title?: string;
		titleCollapse?: boolean;
		toolTemplate?: Template | XTemplate;
		tools?: IToolButton[];
		unstyled?: boolean;
		floatable?: boolean;
		split?: boolean;
		mainItem?: number;
	}
	export class Panel extends Container {
		readonly body: Element;
		readonly buttons: Button[];
		readonly bwrap: Element;
		readonly collapsed: boolean;
		readonly dd?: dd.DragSource;
		readonly footer: Element;
		readonly header: Element;

		constructor(config?: IPanel);
		constructor(config: Ext.IPanel);
		constructor(config?: any);
		addButton(config: Button): Button;
		addButton(config: Button[]): Button[];
		addButton(config: Button | IButton | string, handler?: Function, scope?: object): Button;
		collapse(animate?: boolean): this;
		expand(animate?: boolean): this;
		getBottomToolbar(): Toolbar;
		getFooterToolbar(): Toolbar;
		getFrameHeight(): number;
		getFrameWidth(): number;
		getInnerHeight(): number;
		getInnerWidth(): number;
		getLayoutTarget(): Element;
		getTool(id: string): object;
		getTopToolbar(): Toolbar;
		getUpdater(): Updater;
		load(config?: ILoadCfg): this;
		setIconClass(cls: string): void;
		setTitle(title: string, iconCls?: string): this;
		toggleCollapse(animate?: boolean): this;
		getContentTarget(): Element;

		protected onRender(ct: Ext.Element, position?: string | number): void;
	}
	interface ILoadCfg {
		url: string;
		params?: object | string;
		callback?: Function;
		scope?: object;
		discardUrl?: boolean;
		nocache?: boolean;
		text?: string;
		timeout?: number;
		scripts?: boolean;
	}
	export class Updater extends util.Observable {
		defaultUrl: string;
		disableCaching: boolean;
		el: Element;
		formUpdateDelegate: Function;
		indicatorText: string;
		loadScripts: boolean;
		refreshDelegate: Function;
		renderer: object;
		showLoadIndicator: boolean | string;
		sslBlankUrl: string;
		timeout: number;
		transaction: object;
		updateDelegate: Function;

		constructor(el: HTMLElement | Element, forceNew?: boolean);

		abort(): void;
		formUpdate(form: string | HTMLElement, url?: string, reset?: boolean, callback?: Function): void;
		getDefaultRenderer(): object;
		getEl(): Element;
		getRenderer(): object;
		isAutoRefreshing(): boolean;
		isUpdating(): boolean;
		refresh(callback?: Function): void;
		setDefaultUrl(defaultUrl: string): void;
		setRenderer(renderer: object): void;
		showLoading(): void;
		startAutoRefresh(interval: number, url?: string | object | Function, params?: string | object, callback?: (oElement: Element, bSuccess: boolean) => void, refreshNow?: boolean): void;
		stopAutoRefresh(): void;
		update(options: {
			url?: string;
			method?: string;
			params?: string | object | Function;
			scripts?: boolean;
			callback?: Function;
			scope?: object;
			discardUrl?: object;
			timeout?: number;
			text?: string;
			nocache?: boolean;
		}): void;
	}
	export class WindowGroup {
		protected zseed: number;
		constructor();
		bringToFront(win: string | Window): boolean;
		each(fn: Function, scope?: Window): void;
		get(id: string | Window): Window;
		getActive(): Window;
		getBy(fn: Function, scope?: object): Window[];
		hideAll(): void;
		register(win: Window): void;
		sendToBack(win: string | Window): Window;
		unregister(win: Window): void;
	}
	export interface IWindow extends IPanel {
		animateTarget?: string | Element;
		baseCls?: string;
		closable?: boolean;
		closeAction?: string;
		collapsed?: boolean;
		collapsible?: boolean;
		constrain?: boolean;
		constrainHeader?: boolean;
		defaultButton?: string | number | Component;
		draggable?: boolean;
		expandOnShow?: boolean;
		hidden?: boolean;
		hideAnimDuration?: number;
		manager?: WindowGroup;
		maximizable?: boolean;
		maximized?: boolean;
		minHeight?: number;
		minWidth?: number;
		minimizable?: boolean;
		modal?: boolean;
		onEsc?: Function;
		plain?: boolean;
		resizable?: boolean;
		resizeHandles?: string;
		showAnimDuration?: number;
		x?: number;
		y?: number;
	}
	export class Window extends Panel {

		constructor(config: IWindow);

		alignTo(element: Element | HTMLElement, position: string, offsets?: [number, number]): this;
		anchorTo(element: Element | HTMLElement, position: string, offsets?: [number, number], monitorScroll?: boolean | number): this;
		center(): this;
		clearAnchor(): this;
		close(): void;
		hide(animateTarget?: boolean, callback?: Function, scope?: object): this;
		maximize(): this;
		minimize(): this;
		onHide(): void;
		onShow(): void;
		restore(): this;
		setActive(active: boolean): void;
		setAnimateTarget(el: string | Element): void;
		/**
		 * Shows the window, rendering it first if necessary, or activates it and brings it to front if hidden.
		 * @param animateTarget The target element or id from which the window should animate while opening (defaults to null with no animation)
		 * @param callback A callback function to call after the window is displayed
		 * @param scope The scope (this reference) in which the callback is executed. Defaults to this Window.
		 */
		show(animateTarget?: string, callback?: () => any, scope?: object): this;
		toBack(): this;
		toFront(e?: boolean): this;
		toggleMaximize(): this;
		protected getState(): object;

		protected afterShow(isAnim?: boolean): void;
		/**
		 * The minimum width in pixels allowed for this window (defaults to 200).  Only applies when resizable = true.
		 */
		protected readonly minWidth: number;
		/**
		 * The minimum height in pixels allowed for this window (defaults to 100).  Only applies when resizable = true.
		 */
		protected readonly minHeight: number;
	}
	export interface ITabPanel extends IPanel {
		activeTab?: string | number;
		animScroll?: boolean;
		autoTabSelector?: string;
		autoTabs?: boolean;
		baseCls?: string;
		deferredRender?: boolean;
		elements?: string;
		enableTabScroll?: boolean;
		frame?: boolean;
		hideBorder?: boolean;
		itemCls?: string;
		layoutOnTabChange?: boolean;
		minTabWidth?: number;
		plain?: boolean;
		resizeTabs?: boolean;
		scrollDuration?: number;
		scrollIncrement?: number;
		scrollRepeatInterval?: number;
		tabCls?: string;
		tabMargin?: number;
		tabPosition?: string;
		tabWidth?: number;
		wheelIncrement?: number;
	}
	export class TabPanel extends Panel {
		constructor(cfg?: ITabPanel);

		/**
		* Sets the specified tab as the active tab. This method fires the beforetabchange event which can return false to cancel the tab change.
		* @param item The id or tab Panel to activate. This parameter may be any of the following: A String representing the itemId or id of the child component, or a Number representing the position of the child component within the items property.
		*/
		activate(tab: string | Panel): void;
		/**
		* Suspends any internal calculations or scrolling while doing a bulk operation. See {@link #endUpdate}
		*/
		beginUpdate(): void;
		/**
		* Resumes calculations and scrolling at the end of a bulk operation. See {@link #beginUpdate}
		*/
		endUpdate(): void;
		/**
		* Returns the Component which is the currently active tab. Note that before the TabPanel
		* first activates a child Component, this method will return whatever was configured in the
		* {@link #activeTab} config option.
		* @return {BoxComponent} The currently active child Component if one is active, or the {@link #activeTab} config value.
		*/
		getActiveTab(): BoxComponent | string | number;
		getActiveTab<T extends BoxComponent>(): T;
		/**
		* Gets the specified tab by id.
		* @param {String} id The tab id
		* @return {Panel} The tab
		*/
		getItem(id: string): Panel;
		/**
		* Gets the DOM element for the tab strip item which activates the child panel with the specified
		* ID. Access this to change the visual treatment of the item, for example by changing the CSS class name.
		* @param {Panel/Number/String} tab The tab component, or the tab's index, or the tabs id or itemId.
		* @return {HTMLElement} The DOM node
		*/
		getTabEl(tab: Panel | number | string): HTMLElement;
		/**
		* Provides template arguments for rendering a tab selector item in the tab strip.
		* This method returns an object hash containing properties used by the TabPanel's {@link #itemTpl}
		* to create a formatted, clickable tab selector element. The properties which must be returned
		* are:
		* id : StringA unique identifier which links to the item
		* text : StringThe text to display
		* cls : StringThe CSS class name
		* iconCls : StringA CSS class to provide appearance for an icon.
		*
		* @param {Ext.BoxComponent} item The {@link Ext.BoxComponent BoxComponent} for which to create a selector element in the tab strip.
		* @return {Object} An object hash containing the properties required to render the selector element.
		*/
		getTemplateArgs(item: BoxComponent): object;
		/**
		* Hides the tab strip item for the passed tab
		* @param {Number/String/Panel} item The tab index, id or item
		*/
		hideTabStripItem(item: number | string | Panel): void;
		/**
		* True to scan the markup in this tab panel for {@link #autoTabs} using the
		* {@link #autoTabSelector}
		* @param {Boolean} removeExisting True to remove existing tabs
		*/
		readTabs(removeExisting: boolean): void;
		/**
		* Scrolls to a particular tab if tab scrolling is enabled
		* @param {Panel} item The item to scroll to
		* @param {Boolean} animate True to enable animations
		*/
		scrollToTab(item: Panel, animate: boolean): void;
		/**
		* Sets the specified tab as the active tab. This method fires the {@link #beforetabchange} event which
		* can return false to cancel the tab change.
		* @param {String/Number} item
		* The id or tab Panel to activate. This parameter may be any of the following:
		* <ul class="mdetail-params">
		* a String : representing the {@link Ext.Component#itemId itemId}
		* or {@link Ext.Component#id id} of the child component
		* a Number : representing the position of the child component
		* within the {@link Ext.Container#items items} property
		*
		* For additional information see {@link Ext.util.MixedCollection#get}.
		*/
		setActiveTab(item: string | number | Ext.Panel): void;
		/**
		* Unhides the tab strip item for the passed tab
		* @param {Number/String/Panel} item The tab index, id or item
		*/
		unhideTabStripItem(item: number | string | Panel): void;
	}
	type MessageBoxButtonReturns = 'ok' | 'yes' | 'no' | 'cancel';
	export interface IMessageBox {
		animEl?: string | Element,
		buttons?: object | boolean,
		closable?: boolean,
		cls?: string,
		defaultTextHeight?: number,
		fn?: (buttonId: MessageBoxButtonReturns, text: string, opt: object) => any,
		buttonId?: string,
		text?: string,
		opt?: object,
		scope?: object,
		icon?: string,
		iconCls?: string,
		maxWidth?: number,
		minWidth?: number,
		modal?: boolean,
		msg?: string,
		multiline?: boolean,
		progress?: boolean,
		progressText?: string,
		prompt?: boolean,
		proxyDrag?: boolean,
		title?: string,
		value?: string,
		wait?: boolean,
		waitConfig?: object,
		width?: number;
	}
	export class MessageBox {
		static readonly CANCEL: object;
		static readonly ERROR: string;
		static readonly INFO: string;
		static readonly OK: object;
		static readonly OKCANCEL: object;
		static readonly QUESTION: string;
		static readonly WARNING: string;
		static readonly YESNO: object;
		static readonly YESNOCANCEL: object;
		static buttonText: object;
		static defaultTextHeight: number;
		static maxWidth: number;
		static minProgressWidth: number;
		static minPromptWidth: number;
		static minWidth: number;

		static alert(title: string, msg: string, fn?: Function, scope?: object): MessageBox;
		static confirm(title: string, msg: string, fn?: Function, scope?: object): MessageBox;
		static getDialog(titleText: object): Window;
		static hide(): MessageBox;
		static isVisible(): boolean;
		static progress(title: string, msg: string, progressText?: string): MessageBox;
		static prompt(title: string, msg: string, fn?: Function, scope?: object, multiline?: boolean | number, value?: string): MessageBox;
		static setIcon(icon: string): MessageBox;
		static show(config: IMessageBox): MessageBox;
		static updateProgress(value?: number, progressText?: string, msg?: string): MessageBox;
		static updateText(text?: string): MessageBox;
		static wait(msg: string, title?: string, config?: any): MessageBox;
	}
	export class Fx {

	}
	export class CompositeElementLite {
		readonly elements: Array<HTMLElement>;
		add(els: Array<HTMLElement> | CompositeElementLite): this;
		clear(): void;
		contains(el: string | Element | HTMLElement): boolean;
		each(fn: (el: Element, c: CompositeElement, idx: number) => void): this;
		fill(els: Array<HTMLElement> | CompositeElement): this;
		filter(selector: string | Function): this;
		first(): Element;
		getCount(): number;
		indexOf(el: string | Element | HTMLElement): object;
		item(index: number): Element;
		last(): Element;
		removeElement(el: string | Element | HTMLElement, removeDom?: boolean): this;
		replaceElement(el: string | Element | number, replacement: string | Element, domReplace?: boolean): this;
	}
	export class CompositeElement extends CompositeElementLite {
		constructor(els: object, root: object);
	}
	export interface EventManagerOptions {
		/**
		 * The scope (this reference) in which the handler function is executed. Defaults to the Element.
		 */
		scope?: object;
		/**
		 * A simple selector to filter the target or look for a descendant of the target
		 */
		delegate?: string;
		/**
		 * True to stop the event. That is stop propagation, and prevent the default action.
		 */
		stopEvent?: boolean;
		/**
		 * True to prevent the default action
		 */
		preventDefault?: boolean;
		/**
		 * True to prevent event propagation
		 */
		stopPropagation?: boolean;
		/**
		 * False to pass a browser event to the handler function instead of an Ext.EventObject
		 */
		normalized?: boolean;
		/**
		 * The number of milliseconds to delay the invocation of the handler after te event fires.
		 */
		delay?: number;
		/**
		 * True to add a handler to handle just the next firing of the event, and then remove itself.
		 */
		single?: boolean;
		/**
		 * Causes the handler to be scheduled to run in an Ext.util.DelayedTask delayed by the specified number of milliseconds. If the event fires again within that time, the original handler is not invoked, but the new handler is scheduled in its place.
		 */
		buffer?: number;
		/**
		 * Only call the handler if the event was fired on the target Element, not if the event was bubbled up from a child node.
		 */
		target?: Element;
		mousedown?: Function;
		dblclick?: Function;
		click?: Function;
		keyup?: Function;
	}
	export class EventManager {
		/**
		* Appends an event handler to an element.  The shorthand version {@link #on} is equivalent.  Typically you will
		* use {@link Ext.Element#addListener} directly on an Element in favor of calling this version.
		* @param {String/HTMLElement} el The html element or id to assign the event handler to.
		* @param {String} eventName The name of the event to listen for.
		* @param {Function} handler The handler function the event invokes. This function is passed
		* the following parameters:
		* evt : EventObjectThe {@link Ext.EventObject EventObject} describing the event.
		* t : ElementThe {@link Ext.Element Element} which was the target of the event.
		* Note that this may be filtered by using the delegate option.
		* o : ObjectThe options object from the addListener call.
		*
		* @param {Object} scope (optional) The scope (this reference) in which the handler function is executed. Defaults to the Element.
		* @param {Object} options (optional) An object containing handler configuration properties.
		* This may contain any of the following properties:
		* scope : Object reference) in which the handler function is executed. Defaults to the Element.
		* delegate : StringA simple selector to filter the target or look for a descendant of the target
		* stopEvent : BooleanTrue to stop the event. That is stop propagation, and prevent the default action.
		* preventDefault : BooleanTrue to prevent the default action
		* stopPropagation : BooleanTrue to prevent event propagation
		* normalized : BooleanFalse to pass a browser event to the handler function instead of an Ext.EventObject
		* delay : NumberThe number of milliseconds to delay the invocation of the handler after te event fires.
		* single : BooleanTrue to add a handler to handle just the next firing of the event, and then remove itself.
		* buffer : NumberCauses the handler to be scheduled to run in an {@link Ext.util.DelayedTask} delayed
		* by the specified number of milliseconds. If the event fires again within that time, the original
		* handler is not invoked, but the new handler is scheduled in its place.
		* target : Element if the event was bubbled up from a child node.
		*
		* See {@link Ext.Element#addListener} for examples of how to use these options.
		*/
		static addListener(el: string | EventTarget, eventName: string, handler: (evt: EventObject, t: Element, o: object) => void, scope?: object, options?: EventManagerOptions): void;
		/**
		* @return {Boolean} True if the document is in a 'complete' state (or was determined to
		* be true by other means). If false, the state is evaluated again until canceled.
		*/
		static checkReadyState(e: object): boolean;
		/**
		* Returns true if the control, meta, shift or alt key was pressed during this event.
		* @return {Boolean}
		*/
		static hasModifier(): boolean;
		/**
		* Appends an event handler to an element.  Shorthand for {@link #addListener}.
		* @param {String/HTMLElement} el The html element or id to assign the event handler to
		* @param {String} eventName The name of the event to listen for.
		* @param {Function} handler The handler function the event invokes.
		* @param {Object} scope (optional) (this reference) in which the handler function executes. Defaults to the Element.
		* @param {Object} options (optional) An object containing standard {@link #addListener} options
		* @member Ext.EventManager
		* @method on
		*/
		static on(el: string | HTMLElement | Document, options?: any): void;
		static on(el: string | HTMLElement, eventName: string, handler: (evt: EventObject, t: Element, o: object) => void, scope?: object, options?: EventManagerOptions): void;
		/**
		* Adds a listener to be notified when the document is ready (before onload and before images are loaded). Can be
		* accessed shorthanded as Ext.onReady().
		* @param {Function} fn The method the event invokes.
		* @param {Object} scope (optional) The scope (this reference) in which the handler function executes. Defaults to the browser window.
		* @param {boolean} options (optional) Options object as passed to {@link Ext.Element#addListener}. It is recommended that the options
		* {single: true} be used so that the handler is removed on first invocation.
		*/
		static onDocumentReady(fn: Function, scope?: object, options?: boolean): void;
		/**
		* Adds a listener to be notified when the user changes the active text size. Handler gets called with 2 params, the old size and the new size.
		* @param {Function} fn	  The function the event invokes.
		* @param {Object}   scope   The scope (this reference) in which the handler function executes. Defaults to the browser window.
		* @param {boolean}  options Options object as passed to {@link Ext.Element#addListener}
		*/
		static onTextResize(fn: Function, scope: object, options: boolean): void;
		/**
		* Adds a listener to be notified when the browser window is resized and provides resize event buffering (100 milliseconds),
		* passes new viewport width and height to handlers.
		* @param {Function} fn	  The handler function the window resize event invokes.
		* @param {Object}   scope   The scope (this reference) in which the handler function executes. Defaults to the browser window.
		* @param {boolean}  options Options object as passed to {@link Ext.Element#addListener}
		*/
		static onWindowResize(fn: Function, scope: object, options: boolean): void;
		/**
		* Removes all event handers from an element.  Typically you will use {@link Ext.Element#removeAllListeners}
		* directly on an Element in favor of calling this version.
		* @param {String/HTMLElement} el The id or html element from which to remove all event handlers.
		*/
		static removeAll(el: string | HTMLElement | Document): void;
		/**
		* Removes an event handler from an element.  The shorthand version {@link #un} is equivalent.  Typically
		* you will use {@link Ext.Element#removeListener} directly on an Element in favor of calling this version.
		* @param {String/HTMLElement} el The id or html element from which to remove the listener.
		* @param {String} eventName The name of the event.
		* @param {Function} fn The handler function to remove. This must be a reference to the function passed into the {@link #addListener} call.
		* @param {Object} scope If a scope (this reference) was specified when the listener was added,
		* then this must refer to the same object.
		*/
		static removeListener(el: string | HTMLElement, eventName: string, fn: Function, scope: object): void;
		/**
		* Removes the passed window resize listener.
		* @param {Function} fn		The method the event invokes
		* @param {Object}   scope	The scope of handler
		*/
		static removeResizeListener(fn: Function, scope: object): void;
		/**
		* Removes an event handler from an element.  Shorthand for {@link #removeListener}.
		* @param {String/HTMLElement} el The id or html element from which to remove the listener.
		* @param {String} eventName The name of the event.
		* @param {Function} fn The handler function to remove. This must be a reference to the function passed into the {@link #on} call.
		* @param {Object} scope If a scope (this reference) was specified when the listener was added,
		* then this must refer to the same object.
		* @member Ext.EventManager
		* @method un
		*/
		static un(el: string | HTMLElement, eventName: string, fn: Function, scope: object): void;
		static removeFromSpecialCache(arg: any): void;

		static readonly A: number;
		static readonly ALT: number;
		static readonly B: number;
		static readonly BACKSPACE: number;
		static readonly C: number;
		static readonly CAPS_LOCK: number;
		static readonly CONTEXT_MENU: number;
		static readonly CTRL: number;
		static readonly D: number;
		static readonly DELETE: number;
		static readonly DOWN: number;
		static readonly E: number;
		static readonly EIGHT: number;
		static readonly END: number;
		static readonly ENTER: number;
		static readonly ESC: number;
		static readonly F: number;
		static readonly F1: number;
		static readonly F10: number;
		static readonly F11: number;
		static readonly F12: number;
		static readonly F2: number;
		static readonly F3: number;
		static readonly F4: number;
		static readonly F5: number;
		static readonly F6: number;
		static readonly F7: number;
		static readonly F8: number;
		static readonly F9: number;
		static readonly FIVE: number;
		static readonly FOUR: number;
		static readonly G: number;
		static readonly H: number;
		static readonly HOME: number;
		static readonly I: number;
		static readonly INSERT: number;
		static readonly J: number;
		static readonly K: number;
		static readonly L: number;
		static readonly LEFT: number;
		static readonly M: number;
		static readonly N: number;
		static readonly NINE: number;
		static readonly NUM_CENTER: number;
		static readonly NUM_DIVISION: number;
		static readonly NUM_EIGHT: number;
		static readonly NUM_FIVE: number;
		static readonly NUM_FOUR: number;
		static readonly NUM_MINUS: number;
		static readonly NUM_MULTIPLY: number;
		static readonly NUM_NINE: number;
		static readonly NUM_ONE: number;
		static readonly NUM_PERIOD: number;
		static readonly NUM_PLUS: number;
		static readonly NUM_SEVEN: number;
		static readonly NUM_SIX: number;
		static readonly NUM_THREE: number;
		static readonly NUM_TWO: number;
		static readonly NUM_ZERO: number;
		static readonly O: number;
		static readonly ONE: number;
		static readonly P: number;
		static readonly PAGE_DOWN: number;
		static readonly PAGE_UP: number;
		static readonly PAUSE: number;
		static readonly PRINT_SCREEN: number;
		static readonly Q: number;
		static readonly R: number;
		static readonly RETURN: number;
		static readonly RIGHT: number;
		static readonly S: number;
		static readonly SEVEN: number;
		static readonly SHIFT: number;
		static readonly SIX: number;
		static readonly SPACE: number;
		static readonly T: number;
		static readonly TAB: number;
		static readonly THREE: number;
		static readonly TWO: number;
		static readonly U: number;
		static readonly UP: number;
		static readonly V: number;
		static readonly W: number;
		static readonly X: number;
		static readonly Y: number;
		static readonly Z: number;
		static readonly ZERO: number;
		/**
		* Forces a document ready state transition for the framework.  Used when Ext is loaded
		* into a DOM structure AFTER initial page load (Google API or other dynamic load scenario.
		* Any pending 'onDocumentReady' handlers will be fired (if not already handled).
		*/
		fireDocReady: object;
		/**
		* Url used for onDocumentReady with using SSL (defaults to Ext.SSL_SECURE_URL)
		*/
		ieDeferSrc: boolean;
		/**
		* The frequency, in milliseconds, to check for text resize events (defaults to 50)
		*/
		textResizeInterval: number;
	}
	/**
	 * Just as Ext.Element wraps around a native DOM node, Ext.EventObject wraps the browser's native event-object normalizing cross-browser differences, such as which mouse button is clicked, keys pressed, mechanisms to stop event-propagation along with a method to prevent default actions from taking place.
	 */
	export class EventObject {
		browserEvent: Event;
		type: string;
		/**
		* Gets the character code for the event.
		* @return {Number}
		*/
		getCharCode(): number;
		/**
		 * Returns a normalized keyCode for the event.
		 * @return {Number} The key code
		 */
		getKey(): number;
		/**
		 * Gets the x coordinate of the event.
		 * @return {Number}
		 */
		getPageX(): number;
		/**
		 * Gets the y coordinate of the event.
		 * @return {Number}
		 */
		getPageY(): number;
		/**
		 * Gets the related target.
		 * @return {HTMLElement}
		 */
		getRelatedTarget(): HTMLElement;
		/**
		 * Gets the target for the event.
		 * @param selector A simple selector to filter the target or look for an ancestor of the target
		 * @param maxDepth The max depth to search as a number or element
		 * @param returnEl True to return a Ext.Element object instead of DOM node
		 * @returns
		 */
		getTarget(selector: string, maxDepth: number | Element, returnEl: true): Element;
		getTarget(selector?: string, maxDepth?: number | Element): HTMLElement;
		/**
		* Normalizes mouse wheel delta across browsers
		* @return {Number} The delta
		*/
		getWheelDelta(): number;
		/**
		 * Gets the page coordinates of the event.
		 * @return {Array} The xy values like [x, y]
		 */
		getXY(): [number, number];
		/**
		 * Prevents the browsers default handling of the event.
		 */
		preventDefault(): void;
		/**
		 * Stop the event (preventDefault and stopPropagation)
		 */
		stopEvent(): void;
		/**
		 * Cancels bubbling of the event.
		 */
		stopPropagation(): void;
		/**
		* Returns true if the target of this event is a child of el.  Unless the allowEl parameter is set, it will return false if if the target is el.
		* @param {Mixed} el The id, DOM element or Ext.Element to check
		* @param {Boolean} related (optional) true to test if the related target is within el instead of the target
		* @param {Boolean} allowEl (optional) true to also check if the passed element is the target or related target
		* @return {Boolean}
		*/
		within(): boolean;

		shiftKey: boolean;
		ctrlKey: boolean;
		BACKSPACE: number;
		TAB: number;
		NUM_CENTER: number;
		ENTER: number;
		RETURN: number;
		SHIFT: number;
		CTRL: number;
		CONTROL: number;
		ALT: number;
		PAUSE: number;
		CAPS_LOCK: number;
		ESC: number;
		SPACE: number;
		PAGE_UP: number;
		PAGEUP: number;
		PAGE_DOWN: number;
		PAGEDOWN: number;
		END: number;
		HOME: number;
		LEFT: number;
		UP: number;
		RIGHT: number;
		DOWN: number;
		PRINT_SCREEN: number;
		INSERT: number;
		DELETE: number;
		ZERO: number;
		ONE: number;
		TWO: number;
		THREE: number;
		FOUR: number;
		FIVE: number;
		SIX: number;
		SEVEN: number;
		EIGHT: number;
		NINE: number;
		A: number;
		B: number;
		C: number;
		D: number;
		E: number;
		F: number;
		G: number;
		H: number;
		I: number;
		J: number;
		K: number;
		L: number;
		M: number;
		N: number;
		O: number;
		P: number;
		Q: number;
		R: number;
		S: number;
		T: number;
		U: number;
		V: number;
		W: number;
		X: number;
		Y: number;
		Z: number;
		CONTEXT_MENU: number;
		NUM_ZERO: number;
		NUM_ONE: number;
		NUM_TWO: number;
		NUM_THREE: number;
		NUM_FOUR: number;
		NUM_FIVE: number;
		NUM_SIX: number;
		NUM_SEVEN: number;
		NUM_EIGHT: number;
		NUM_NINE: number;
		NUM_MULTIPLY: number;
		NUM_PLUS: number;
		NUM_MINUS: number;
		NUM_PERIOD: number;
		NUM_DIVISION: number;
		F1: number;
		F2: number;
		F3: number;
		F4: number;
		F5: number;
		F6: number;
		F7: number;
		F8: number;
		F9: number;
		F10: number;
		F11: number;
		F12: number;

		static BACKSPACE: number;
		static TAB: number;
		static NUM_CENTER: number;
		static ENTER: number;
		static RETURN: number;
		static SHIFT: number;
		static CTRL: number;
		static CONTROL: number;
		static ALT: number;
		static PAUSE: number;
		static CAPS_LOCK: number;
		static ESC: number;
		static SPACE: number;
		static PAGE_UP: number;
		static PAGEUP: number;
		static PAGE_DOWN: number;
		static PAGEDOWN: number;
		static END: number;
		static HOME: number;
		static LEFT: number;
		static UP: number;
		static RIGHT: number;
		static DOWN: number;
		static PRINT_SCREEN: number;
		static INSERT: number;
		static DELETE: number;
		static ZERO: number;
		static ONE: number;
		static TWO: number;
		static THREE: number;
		static FOUR: number;
		static FIVE: number;
		static SIX: number;
		static SEVEN: number;
		static EIGHT: number;
		static NINE: number;
		static A: number;
		static B: number;
		static C: number;
		static D: number;
		static E: number;
		static F: number;
		static G: number;
		static H: number;
		static I: number;
		static J: number;
		static K: number;
		static L: number;
		static M: number;
		static N: number;
		static O: number;
		static P: number;
		static Q: number;
		static R: number;
		static S: number;
		static T: number;
		static U: number;
		static V: number;
		static W: number;
		static X: number;
		static Y: number;
		static Z: number;
		static CONTEXT_MENU: number;
		static NUM_ZERO: number;
		static NUM_ONE: number;
		static NUM_TWO: number;
		static NUM_THREE: number;
		static NUM_FOUR: number;
		static NUM_FIVE: number;
		static NUM_SIX: number;
		static NUM_SEVEN: number;
		static NUM_EIGHT: number;
		static NUM_NINE: number;
		static NUM_MULTIPLY: number;
		static NUM_PLUS: number;
		static NUM_MINUS: number;
		static NUM_PERIOD: number;
		static NUM_DIVISION: number;
		static F1: number;
		static F2: number;
		static F3: number;
		static F4: number;
		static F5: number;
		static F6: number;
		static F7: number;
		static F8: number;
		static F9: number;
		static F10: number;
		static F11: number;
		static F12: number;
	}
	export class Ajax extends data.Connection {
		static serializeForm(form: string | HTMLElement): string;
		static defaultHeaders: object;
		static method: string;
	}

	// Ext properties

	/** URL to a 1x1 transparent gif image used by Ext to create inline icons with CSS background images. In older versions of IE, this defaults to "http://extjs.com/s.gif" and you should change this to a URL on your server. For other browsers it uses an inline data URL. */
	export var BLANK_IMAGE_URL: string;
	/** URL to a blank file used by Ext when in secure mode for iframe src and onReady src to prevent the IE insecure content warning ('about:blank', except for IE in secure mode, which is 'javascript:""'). */
	export var SSL_SECURE_URL: string;
	/** Indicates whether to use native browser parsing for JSON methods. This option is ignored if the browser does not support native JSON methods. Note: Native JSON methods will not work with objects that have functions. Also, property names must be quoted, otherwise the data will not parse. (Defaults to false) */
	export var USE_NATIVE_JSON: boolean;
	/** A reusable empty function */
	export var emptyFn: (...args: any[]) => any;
	/** HIGHLY EXPERIMENTAL True to force css based border-box model override and turning off javascript based adjustments. This is a runtime configuration and must be set before onReady.

	Defaults to: false */
	export var enableForcedBoxModel: boolean;
	/** True if the Ext.Fx Class is available */
	export var enableFx: boolean;
	/**True to automatically uncache orphaned Ext.Elements periodically (defaults to true)

	Defaults to: true */
	export var enableGarbageCollector: boolean;
	/** True to automatically purge event listeners during garbageCollection (defaults to false).

	Defaults to: false */
	export var enableListenerCollection: boolean;
	/**EXPERIMENTAL - True to cascade listener removal to child elements when an element is removed. Currently not optimized for performance.

	Defaults to: false */
	export var enableNestedListenerRemoval: boolean;
	/**True if the detected platform is Adobe Air. */
	export var isAir: boolean;
	/**True if the detected browser is Internet Explorer running in non-strict mode. */
	export var isBorderBox: boolean;
	/**True if the detected browser is Chrome. */
	export var isChrome: boolean;
	/**True if the detected browser uses the Gecko layout engine (e.g. Mozilla, Firefox). */
	export var isGecko: boolean;
	/**True if the detected browser uses a pre-Gecko 1.9 layout engine (e.g. Firefox 2.x). */
	export var isGecko2: boolean;
	/**True if the detected browser uses a Gecko 1.9+ layout engine (e.g. Firefox 3.x). */
	export var isGecko3: boolean;
	/**True if the detected browser is Internet Explorer. */
	export var isIE: boolean;
	/**True if the detected browser is Internet Explorer 10.x */
	export var isIE10: boolean;
	/**True if the detected browser is Internet Explorer 10.x */
	export var isIE11: boolean;
	/**True if the detected browser is Internet Explorer 10.x or higher */
	export var isIE10p: boolean;
	/**True if the detected browser is Internet Explorer 6.x. */
	export var isIE6: boolean;
	/**True if the detected browser is Internet Explorer 7.x. */
	export var isIE7: boolean;
	/**True if the detected browser is Internet Explorer 8.x. */
	export var isIE8: boolean;
	/**True if the detected browser is Internet Explorer 9.x. */
	export var isIE9: boolean;
	/**True if the detected browser is Internet Explorer 9.x or lower */
	export var isIE9m: boolean;
	/**True if the detected platform is Linux. */
	export var isLinux: boolean;
	/**True if the detected platform is Mac OS. */
	export var isMac: boolean;
	/**True if the detected browser is Opera. */
	export var isOpera: boolean;
	/**True when the document is fully initialized and ready for action

	Defaults to: false */
	export var isReady: boolean;
	/**True if the detected browser is Safari. */
	export var isSafari: boolean;
	/**True if the detected browser is Safari 2.x. */
	export var isSafari2: boolean;
	/**True if the detected browser is Safari 3.x. */
	export var isSafari3: boolean;
	/**True if the detected browser is Safari 4.x. */
	export var isSafari4: boolean;
	/**True if the page is running over SSL */
	export var isSecure: boolean;
	/**True if the browser is in strict (standards-compliant) mode, as opposed to quirks mode */
	export var isStrict: boolean;
	/**True if the detected browser uses WebKit. */
	export var isWebKit: boolean;
	/**True if the detected platform is Windows. */
	export var isWindows: boolean;
	/**By default, Ext intelligently decides whether floating elements should be shimmed. If you are using flash, you may want to set this to true. */
	export var useShims: boolean;
	/**The version of the framework */
	export var version: string;

	// Ext methods
	export function addBehaviors(obj: object): void;
	/**
	 * Copies all the properties of config to obj.
	 * @param {Object} obj The receiver of the properties
	 * @param {Object} config The source of the properties
	 * @param {Object} defaults A different object that will also be applied for default values
	 * @return {Object} returns obj
	 * @member Ext apply
	 */
	export function apply<T = any>(obj: T, config: Partial<T>, defaults?: T): T;
	/**
	 * Copies all the properties of config to obj if they don't already exist.
	 * @param {Object} obj The receiver of the properties
	 * @param {Object} config The source of the properties
	 * @return {Object} returns obj
	 */
	export function applyIf<T = any>(obj: T, config: object): T;
	/**
	 * Creates a copy of the passed Array with falsy values removed.
	 * @param {Array/NodeList} arr The Array from which to remove falsy values.
	 * @return {Array} The new, compressed Array.
	 */
	export function clean(arr: Array<any>): Array<any>;
	/**
	 * Copies a set of named properties fom the source object to the destination object.
	 * @param {Object} dest The destination object.
	 * @param {Object} source The source object.
	 * @param {Array/String} names Either an Array of property names, or a comma-delimited list of property names to copy.
	 * @return {Object} The modified object.
	*/
	export function copyTo(dest: object, source: object, names: Array<string> | string): object;
	/**
	 * Creates a new Component from the specified config object using the
	 * config object's {@link Ext.component#xtype xtype} to determine the class to instantiate.
	 * @param {Object} config A configuration object for the Component you wish to create.
	 * @param {Constructor} defaultType The constructor to provide the default Component type if
	 * the config object does not contain a xtype. (Optional if the config contains a xtype).
	 * @return {Ext.Component} The newly instantiated Component.
	 */
	export function create(config: object, defautType?: Function): Component;
	/**
	 * Creates a delegate (callback) that sets the scope to obj.
	 * Call directly on any function. Example: Ext.createDelegate(this.myFunction, this, [arg1, arg2])
	 * Will create a function that is automatically scoped to obj so that the <tt>this</tt> variable inside the
	 * callback points to obj.
	 * @param {Function} fn The function to delegate.
	 * @param {Object} scope (optional) The scope (<b>this reference) in which the function is executed.
	 * <b>If omitted, defaults to the browser window.
	 * @param {Array} args (optional) Overrides arguments for the call. (Defaults to the arguments passed by the caller)
	 * @param {Boolean/Number} appendArgs (optional) if True args are appended to call args instead of overriding,
	 * if a number the args are inserted at the specified position
	 * @return {Function} The new function
	 */
	export function createDelegate(fn: Function, scope?: object, args?: Array<any>, appendArgs?: boolean | number): Function;
	/**
	 * Creates an interceptor function. The passed function is called before the original one. If it returns false,
	 * the original one is not called. The resulting function returns the results of the original function.
	 * The passed function is called with the parameters of the original function.
	 * @param {Function} origFn The original function.
	 * @param {Function} newFn The function to call before the original
	 * @param {Object} scope (optional) The scope (<b>this reference) in which the passed function is executed.
	 * <b>If omitted, defaults to the scope in which the original function is called or the browser window.
	 * @return {Function} The new function
	 */
	export function createInterceptor(origFn: Function, newFn: Function, scope?: object): Function;
	/**
	 * Create a combined function call sequence of the original function + the passed function.
	 * The resulting function returns the results of the original function.
	 * The passed fcn is called with the parameters of the original function.
	 * @param {Function} origFn The original function.
	 * @param {Function} newFn The function to sequence
	 * @param {Object} scope (optional) The scope (this reference) in which the passed function is executed.
	 * If omitted, defaults to the scope in which the original function is called or the browser window.
	 * @return {Function} The new function
	 */
	export function createSequence(origFn: Function, newFn: Function, scope?: object): Function;
	/**
	 * Decodes (parses) a JSON string to an object. If the JSON is invalid, this function throws a SyntaxError unless the safe option is set.
	 * @param {String} json The JSON string
	 * @return {Object} The resulting object
	 */
	export function decode(json: string, safe?: boolean): object;
	/**
	 * Calls this function after the number of millseconds specified, optionally in a specific scope.
	 * Shorthand for {@link Ext.util.Functions#defer}
	 * @param {Function} fn The function to defer.
	 * @param {Number} millis The number of milliseconds for the setTimeout call (if less than or equal to 0 the function is executed immediately)
	 * @param {Object} scope (optional) The scope (<b>this reference) in which the function is executed.
	 * <b>If omitted, defaults to the browser window.
	 * @param {Array} args (optional) Overrides arguments for the call. (Defaults to the arguments passed by the caller)
	 * @param {Boolean/Number} appendArgs (optional) if True args are appended to call args instead of overriding,
	 * if a number the args are inserted at the specified position
	 * @return {Number} The timeout id that can be used with clearTimeout
	 * @member Ext
	 * @method defer
	 */
	export function defer(fn: Function, millis: number, scope?: object, args?: Array<any>, appendArgs?: boolean | number): number;
	/**
	 * @method
	 * Defines a class or override. A basic class is defined like this:
	 * @param {String} className The class name to create in string dot-namespaced format:
	 * @param {Object} data The key - value pairs of properties to apply to this class. Property names can be of any valid
	 * @param {Function} createdFn Optional callback to execute after the class is created, the execution scope of which (`this`) will be the newly created class itself.
	 * @return {Ext.Base}
	 * @markdown
	 * @member Ext
	 * @method define
	 */
	export function define(className: string, data: object, createdFn?: Function): any; //Returns Ext.base, says documentation. This doesn't exist.
	/**
	 * Attempts to destroy any objects passed to it by removing all event listeners, removing them from the
	 * DOM (if applicable) and calling their destroy functions (if available).  This method is primarily
	 * intended for arguments of type {@link Ext.Element} and {@link Ext.Component}, but any subclass of
	 * {@link Ext.util.Observable} can be passed in.  Any number of elements and/or components can be
	 * passed into this function in a single call as separate arguments.
	 * @param {Mixed...} args An {@link Ext.Element}, {@link Ext.Component}, or an Array of either of these to destroy
	 */
	export function destroy(args: Element | Component | Array<any>): void;
	/**
	 * Attempts to destroy and then remove a set of named properties of the passed object.
	 * @param {Object} o The object (most likely a Component) who's properties you wish to destroy.
	 * @param {Mixed} arg1 The name of the property to destroy and remove from the object.
	 * @param {Mixed} etc... More property names to destroy and remove.
	 */
	export function destroyMembers(o: object, arg1: string, ...etc: string[]): any;
	/**
	 * Iterates an array calling the supplied function.
	 * @param {Array/NodeList/Mixed} array The array to be iterated. If this
	 * argument is not really an array, the supplied function is called once.
	 * @param {Function} fn The function to be called with each item. If the
	 * supplied function returns false, iteration stops and this method returns
	 * the current index.
	 * @param {Object} scope The scope (this reference) in which the specified function is executed.
	 * Defaults to the item at the current index
	 * within the passed array.
	 * @return See description for the fn parameter.
	 */
	export function each(array: Array<any>, fn: (item: any, index: number, allItems: Array<any>) => boolean | void, scope?: object): number | void;
	/**
	 * Encodes an Object, Array or other value
	 * @param {Mixed} o The variable to encode
	 * @return {String} The JSON string
	 */
	export function encode(o: object): string;
	/**
	 * Escapes the passed string for use in a regular expression
	 * @param {String} str
	 * @return {String}
	 */
	export function escapeRe(str: string): string;
	/**
	 * Extends one class to create a subclass and optionally overrides members with the passed literal. This method
	 * also adds the function "override()" to the subclass that can be used to override members of the class.
	 * @param {Function} superclass The constructor of class being extended.
	 * @param {Object} overrides A literal with members which are copied into the subclass's
	 * @return {Function} The subclass constructor from the overrides parameter, or a generated one if not provided.
	 */
	export function extend<T>(superclass: Function, overrides: T): T;
	/**
	 * Recursively flattens into 1-d Array. Injects Arrays inline.
	 * @param {Array} arr The array to flatten
	 * @return {Array} The new, flattened array.
	 */
	export function flatten(arr: Array<any>): Array<any>;
	/**
	 * Gets the globally shared flyweight Element, with the passed node as the active element. Do not store a reference to this element -
	 * the dom node can be overwritten by other code. Shorthand of {@link Ext.Element#fly}
	 * Use this to make one-time references to DOM elements which are not going to be accessed again either by
	 * application code, or by Ext's classes. If accessing an element which will be processed regularly, then {@link Ext#get}
	 * will be more appropriate to take advantage of the caching provided by the Ext.Element class.
	 * @param {String/HTMLElement} el The dom node or id
	 * @param {String} named (optional) Allows for creation of named reusable flyweights to prevent conflicts
	 * (e.g. internally Ext uses "_global")
	 * @return {Element} The shared Element object (or null if no matching element was found)
	 * @member Ext
	 * @method fly
	 */
	export function fly(el: string | HTMLElement | Node, named?: string): Element;
	/**
	 * Retrieves Ext.Element objects.
	 * <b>This method does not retrieve {@link Ext.Component Component}s. This method
	 * retrieves Ext.Element objects which encapsulate DOM elements. To retrieve a Component by
	 * its ID, use {@link Ext.ComponentMgr#get}.
	 * Uses simple caching to consistently return the same object. Automatically fixes if an
	 * object was recreated with the same id via AJAX or DOM.
	 * Shorthand of {@link Ext.Element#get}
	 * @param {Mixed} el The id of the node, a DOM Node or an existing Element.
	 * @return {Element} The Element object (or null if no matching element was found)
	 * @member Ext
	 * @method get
	 */
	export function get(el: string | Element | HTMLElement | Node): Element;
	/**
	 * Returns the current document body as an {@link Ext.Element}.
	 * @return Ext.Element The document body
	 */
	export function getBody(): Element;
	/**
	* This is shorthand reference to {@link Ext.ComponentMgr#get}.
	* Looks up an existing {@link Ext.Component Component} by {@link Ext.Component#id id}
	* @param {String} id The component {@link Ext.Component#id id}
	* @return Ext.Component The Component, <tt>undefined</tt> if not found, or <tt>null</tt> if a
	* Class was found.
	*/
	export function getCmp(id: string): Component;
	/**
	* This is shorthand reference to {@link Ext.ComponentMgr#get}.
	* Looks up an existing {@link Ext.Component Component} by {@link Ext.Component#id id}
	* @param {String} id The component {@link Ext.Component#id id}
	* @return Ext.Component The Component, <tt>undefined</tt> if not found, or <tt>null</tt> if a
	* Class was found.
	*/
	export function getCmp<T>(id: string): T;
	/**
	 * Returns the current HTML document object as an {@link Ext.Element}.
	 * @return Ext.Element The document
	 */
	export function getDoc(): Element;
	/**
	 * Return the dom node for the passed String (id), dom node, or Ext.Element.
	 * Optional 'strict' flag is needed for IE since it can return 'name' and
	 * 'id' elements by using getElementById.
	 * <b>Note: the dom node to be found actually needs to exist (be rendered, etc)
	 * when this method is called to be successful.
	 * @param {Mixed} el
	 * @return HTMLElement
	 */
	export function getDom<T extends HTMLElement = HTMLElement>(el: string | Element | HTMLElement): T;
	/**
	 * Returns the current document body as an {@link Ext.Element}.
	 * @return Ext.Element The document body
	 * @method
	 */
	export function getHead(): Element;
	/**
	 * Utility method for getting the width of the browser scrollbar. This can differ depending on
	 * operating system settings, such as the theme or font size.
	 * @param {Boolean} force (optional) true to force a recalculation of the value.
	 * @return {Number} The width of the scrollbar.
	 */
	export function getScrollBarWidth(force?: boolean): number;
	/**
	 * Framework-wide error-handler.  Developers can override this method to provide
	 * custom exception-handling.  Framework errors will often extend from the base
	 * Ext.Error class.
	 * @param {Object/Error} e The thrown exception object.
	 * @member Ext
	 */
	export function handleError(e: Error): void;
	/**
	 * Generates unique ids. If the element already has an id, it is unchanged
	 * @param {Mixed} el (optional) The element to generate an id for
	 * @param {String} prefix (optional) Id prefix (defaults "ext-gen")
	 * @return {String} The generated Id.
	 */
	export function id(el?: Element | HTMLElement, prefix?: string): string;
	/**
	 * Invokes a method on each item in an Array.
	 * @param {Array|NodeList} arr The Array of items to invoke the method on.
	 * @param {String} methodName The method name to invoke.
	 * @param {...*} args Arguments to send into the method invocation.
	 * @return {Array} The results of invoking the method on each item in the array.
	 */
	export function invoke(arr: Array<any>, methodName: string, ...args: Array<any>): Array<any>;
	/**
	 * Returns true if the passed value is a JavaScript array, otherwise false.
	 * @param {Mixed} value The value to test
	 * @return {Boolean}
	 */
	export function isArray(value: any): value is any[];
	/**
	 * Returns true if the passed value is a boolean.
	 * @param {Mixed} value The value to test
	 * @return {Boolean}
	 */
	export function isBoolean(value: any): value is boolean;
	/**
	 * Returns true if the passed object is a JavaScript date object, otherwise false.
	 * @param {Object} object The object to test
	 * @return {Boolean}
	 */
	export function isDate(value: any): value is Date;
	/**
	 * Returns true if the passed value is not undefined.
	 * @param {Mixed} value The value to test
	 * @return {Boolean}
	 */
	export function isDefined(value: any): boolean;
	/**
	 * Returns true if the passed value is an HTMLElement
	 * @param {Mixed} value The value to test
	 * @return {Boolean}
	 */
	export function isElement(value: any): boolean;
	/**
	 * Returns true if the passed value is empty.
	 * The value is deemed to be empty if it is: null, undefined, an empty array, or a zero length string (Unless the allowBlank parameter is true).
	 * @param {Mixed} value The value to test
	 * @param {Boolean} allowBlank (optional) true to allow empty strings (defaults to false)
	 * @return {Boolean}
	 */
	export function isEmpty(value: any, allowBlank?: boolean): boolean;
	/**
	 * Returns true if the passed value is a JavaScript Function, otherwise false.
	 * @param {Mixed} value The value to test
	 * @return {Boolean}
	 */
	export function isFunction(value: any): value is Function;
	/**
	 * Returns true if the passed value is a number. Returns false for non-finite numbers.
	 * @param {Mixed} value The value to test
	 * @return {Boolean}
	 */
	export function isNumber(value: any): value is number;
	/**
	 * Returns true if the passed value is a JavaScript Object, otherwise false.
	 * @param {Mixed} value The value to test
	 * @return {Boolean}
	 */
	export function isObject(value: any): value is object;
	/**
	 * Returns true if the passed value is a JavaScript 'primitive', a string, number or boolean.
	 * @param {Mixed} value The value to test
	 * @return {Boolean}
	 */
	export function isPrimitive(value: any): boolean;
	/**
	 * Returns true if the passed value is a string.
	 * @param {Mixed} value The value to test
	 * @return {Boolean}
	 */
	export function isString(value: any): value is string;
	/**
	 * Iterates either the elements in an array.
	 * Note: If you are only iterating arrays, it is better to call Ext.each.
	 * @param {Object/Array} object The array to be iterated
	 * @param {Function} fn The function to be called for each iteration.
	 * The iteration will stop if the supplied function returns false, or
	 * all array elements have been covered.
	 * @param {Object} scope The scope (this reference) in which the specified function is executed. Defaults to the object being iterated.
	 */
	export function iterate<T>(object: Array<T>, fn: (item: T, index: number, allItems: Array<T>) => boolean | void, scope?: object): void;
	/**
	 * Iterates each of the properties in an object.
	 * Note: If you are only iterating arrays, it is better to call Ext.each.
	 * @param {Object/Array} object The object to be iterated
	 * @param {Function} fn The function to be called for each iteration.
	 * The iteration will stop if the supplied function returns false, or
	 * all object properties have been covered.
	 * @param {Object} scope The scope (this reference) in which the specified function is executed. Defaults to the object being iterated.
	 */
	export function iterate<T>(object: T, fn: (key: string, value: T) => boolean | void, scope?: object): void;
	/**
	 * Returns the maximum value in the Array
	 * @param {Array|NodeList} arr The Array from which to select the maximum value.
	 * @param {Function} comp (optional) a function to perform the comparision which determines maximization. If omitted the ">" operator will be used. Note: gt = 1; eq = 0; lt = -1
	 * @return {Object} The maximum value in the Array.
	 */
	export function max<T = number>(arr: Array<T>, comp?: (a: T, b: T) => boolean): T;
	/**
	 * Calculates the mean of the Array
	 * @param {Array} arr The Array to calculate the mean value of.
	 * @return {Number} The mean.
	 */
	export function mean<T = number>(arr: Array<T>): T;
	/**
	 * Returns the minimum value in the Array.
	 * @param {Array|NodeList} arr The Array from which to select the minimum value.
	 * @param {Function} comp (optional) a function to perform the comparision which determines minimization. If omitted the "<" operator will be used. Note: gt = 1; eq = 0; lt = -1
	 * @return {Object} The minimum value in the Array.
	 */
	export function min<T = number>(arr: Array<T>, comp?: (a: T, b: T) => boolean): T;
	/**
	 * Creates namespaces to be used for scoping variables and classes so that they are not global.
	 * Specifying the last node of a namespace implicitly creates all other nodes.
	 * @param {String} namespace1
	 * @param {String} namespace2
	 * @param {String} etc
	 * @return {Object} The namespace object. (If multiple arguments are passed, this will be the last namespace created)
	 */
	export function namespace(namespace1: string, ...namespace2: Array<string>): object;
	/**
	 * Creates namespaces to be used for scoping variables and classes so that they are not global.
	 * Specifying the last node of a namespace implicitly creates all other nodes.
	 * @param {String} namespace1
	 * @param {String} namespace2
	 * @param {String} etc
	 * @return {Object} The namespace object. (If multiple arguments are passed, this will be the last namespace created)
	 */
	export function ns(namespace1: string, ...namespace2: Array<string>): object;
	/**
	 * Utility method for validating that a value is numeric, returning the specified default value if it is not.
	 * @param {Mixed} value Should be a number, but any type will be handled appropriately
	 * @param {Number} defaultValue The value to return if the original value is non-numeric
	 * @return {Number} Value, if numeric, else defaultValue
	 */
	export function num(value: any, defaultValue: number): number;
	/**
	 * Adds a listener to be notified when the document is ready (before onload and before images are loaded). Shorthand of {@link Ext.EventManager#onDocumentReady}.
	* @param {Function} fn The method the event invokes.
	* @param {Object} scope (optional) The scope (this reference) in which the handler function executes. Defaults to the browser window.
	* @param {boolean} options (optional) Options object as passed to {@link Ext.Element#addListener}.
	* @member Ext
	* @method onReady
	*/
	export function onReady(fn: Function, scope?: object, options?: boolean): void;
	/**
	 * Overrides members of the specified `target` with the given values.
	 * If the `target` is a function, it is assumed to be a constructor and the contents
	 * of `overrides` are applied to its `prototype` using {@link Ext#apply Ext.apply}.
	 *
	 * If the `target` is an instance of a class created using {@link #define},
	 * the `overrides` are applied to only that instance. In this case, methods are
	 * specially processed to allow them to use {@link Ext.Base#callParent}.
	 *
	 * If the `target` is none of these, the `overrides` are applied to the `target`
	 * using {@link Ext#apply Ext.apply}.
	 *
	 * Please refer to {@link Ext#define Ext.define} for further details.
	 *
	 * @param {Object} target The target to override.
	 * @param {Object} overrides The properties to add or replace on `target`.
	 * @method override
	 */
	export function override(target: object, overrides: object): void;
	/**
	 * Partitions the set into two sets: a true set and a false set.
	 * @param {Array|NodeList} arr The array to partition
	 * @param {Function} truth (optional) a function to determine truth.  If this is omitted the element itself must be able to be evaluated for its truthfulness.
	 * @return {Array} [true<Array>,false<Array>]
	 */
	export function partition(arr: Array<any>, truth?: Function): Array<any>;
	/**
	 * Plucks the value of a property from each item in the Array
	 * @param {Array|NodeList} arr The Array of items to pluck the value from.
	 * @param {String} prop The property name to pluck from each element.
	 * @return {Array} The value from each item in the Array.
	 */
	export function pluck(arr: Array<any>, prop: string): Array<any>;
	/**
	 * Shorthand for {@link Ext.ComponentMgr#registerPlugin}
	 * @param {String} ptype The {@link Ext.component#ptype mnemonic string} by which the Plugin class
	 * may be looked up.
	 * @param {Constructor} cls The new Plugin class.
	 * @member Ext
	 * @method preg
	 */
	export function preg(ptype: string, cls: string): void;
	/**
	 * Selects an array of DOM nodes by CSS/XPath selector. Shorthand of {@link Ext.DomQuery#select}
	 * @param {String} path The selector/xpath query
	 * @param {Node} root (optional) The start of the query (defaults to document).
	 * @return {Array}
	 * @member Ext
	 * @method query
	 */
	export function query<T extends HTMLElement>(path: string, root?: HTMLElement | Document): Array<T>;
	/**
	 * Shorthand for {@link Ext.ComponentMgr#registerType}
	 * @param {String} xtype The {@link Ext.component#xtype mnemonic string} by which the Component class
	 * may be looked up.
	 * @param {Constructor} cls The new Component class.
	 * @member Ext
	 * @method reg
	 */
	export function reg(xtype: string, cls: Function): any;
	/**
	 * Removes this element from the document, removes all DOM event listeners, and deletes the cache reference.
	 * All DOM event listeners are removed from this element. If {@link Ext#enableNestedListenerRemoval} is
	 * true, then DOM event listeners are also removed from all child nodes. The body node
	 * will be ignored if passed in.
	 * @param {HTMLElement} node The node to remove
	 * @method
	 */
	export function removeNode(node: HTMLElement): HTMLElement;
	/**
	 * Selects elements based on the passed CSS selector to enable {@link Ext.Element Element} methods
	 * to be applied to many related elements in one statement through the returned {@link Ext.CompositeElement CompositeElement} or
	 * {@link Ext.CompositeElementLite CompositeElementLite} object.
	 * @param {String/Array} selector The CSS selector or an array of elements
	 * @param {HTMLElement/String} root (optional) The root element of the query or id of the root
	 * @return {CompositeElementLite/CompositeElement}
	 * @member Ext
	 * @method select
	 */
	export function select(selector: string | Array<any>, root?: HTMLElement | string): CompositeElement | CompositeElementLite;
	/**
	 * Calculates the sum of the Array
	 * @param {Array} arr The Array to calculate the sum value of.
	 * @return {Number} The sum.
	 */
	export function sum(arr: Array<number>): number;
	/**
	 * Converts any iterable (numeric indices and a length property) into a true array
	 * Don't use this on strings. IE doesn't support "abc"[0] which this implementation depends on.
	 * For strings, use this instead: "abc".match(/./g) => [a,b,c];
	 * @param {Iterable} the iterable object to be turned into a true Array.
	 * @return (Array) array
	 */
	export function toArray(the: any): Array<any>;
	type TypeString = 'string' | 'number' | 'boolean' | 'date' | 'function' | 'object' | 'array' | 'regexp' | 'element' | 'nodelist' | 'textnode' | 'whitespace';
	/**
	 * Returns the type of object that is passed in. If the object passed in is null or undefined it
	 * return false otherwise it returns one of the following values:
	 * string: If the object passed is a string
	 * number: If the object passed is a number
	 * boolean: If the object passed is a boolean value
	 * date: If the object passed is a Date object
	 * function: If the object passed is a function reference
	 * object: If the object passed is an object
	 * array: If the object passed is an array
	 * regexp: If the object passed is a regular expression
	 * element: If the object passed is a DOM Element
	 * nodelist: If the object passed is a DOM NodeList
	 * textnode: If the object passed is a DOM text node and contains something other than whitespace
	 * whitespace: If the object passed is a DOM text node and contains only whitespace
	 * @param {Mixed} object
	 * @return {String}
	 */
	export function type(object: object): false | TypeString;
	/**
	 * Creates a copy of the passed Array, filtered to contain only unique values.
	 * @param {Array} arr The Array to filter
	 * @return {Array} The new Array containing unique values.
	 */
	export function unique<T>(arr: Array<T>): Array<T>;
	/**
	 * Appends content to the query string of a URL, handling logic for whether to place
	 * a question mark or ampersand.
	 * @param {String} url The URL to append to.
	 * @param {String} s The content to append to the URL.
	 * @return (String) The resulting URL
	 */
	export function urlAppend(url: string, s: string): string;
	/**
	 * Takes an encoded URL and and converts it to an object.
	 * @param {String} string
	 * @param {Boolean} overwrite (optional) Items of the same name will overwrite previous values instead of creating an an array (Defaults to false).
	 * @return {Object} A literal with members
	 */
	export function urlDecode(string: string, overwrite?: boolean): object;
	/**
	 * Takes an object and converts it to an encoded URL.
	 * @param {Object} o
	 * @param {String} pre (optional) A prefix to add to the url encoded string
	 * @return {String}
	 */
	export function urlEncode(o: object, pre?: string): string;
	/**
	 * Utility method for returning a default value if the passed value is empty.
	 * @param {Mixed} value The value to test
	 * @param {Mixed} defaultValue The value to return if the original value is empty
	 * @param {Boolean} allowBlank (optional) true to allow zero length strings to qualify as non-empty (defaults to false)
	 * @return {Mixed} value, if non-empty, else defaultValue
	 */
	export function value<T>(value: T, defaultValue: T, allowBlank?: boolean): T;
	/**
	 * Zips N sets together.
	 * @param {Arrays|NodeLists} arr This argument may be repeated. Array(s) to contribute values.
	 * @param {Function} zipper (optional) The last item in the argument list. This will drive how the items are zipped together.
	 * @return {Array} The zipped set.
	 */
	export function zip(arr: Array<any>): Array<any>;
	export interface ILoadMask {
		msg?: string;
		msgCls?: string;
		removeMask?: boolean;
		store?: data.Store;
	}
	export class LoadMask {
		readonly disabled: boolean;

		constructor(el: Element | HTMLElement | string, config?: ILoadMask);
		disable(): void;
		enable(): void;
		hide(): void;
		show(): void;
	}
	/**
	 * Provides a convenient wrapper for normalized keyboard navigation. KeyNav allows you to bind navigation keys to function calls that will get called when the keys are pressed, providing an easy way to implement custom navigation schemes for any UI component.
	 *
	 * The following are all of the possible keys that can be implemented: enter, left, right, up, down, tab, esc, pageUp, pageDown, del, home, end.
	 * @example
	 * var nav = new Ext.KeyNav("my-element", {
	 *	"left" : function(e){
	 *		this.moveLeft(e.ctrlKey);
	 *	},
	 *	"right" : function(e){
	 *		this.moveRight(e.ctrlKey);
	 *	},
	 *	"enter" : function(e){
	 *		this.save();
	 *	},
	 *	scope : this
	 *});
	 */
	export class KeyNav {
		/**
		 * The method to call on the Ext.EventObject after this KeyNav intercepts a key. Valid values are Ext.EventObject.stopEvent, Ext.EventObject.preventDefault and Ext.EventObject.stopPropagation
		 * @default "stopEvent"
		 */
		defaultEventAction: string;
		/**
		 * True to disable this KeyNav instance (defaults to false)
		 */
		disabled: boolean;
		/**
		 * Handle the keydown event instead of keypress (defaults to false). KeyNav automatically does this for IE since IE does not propagate special keys on keypress, but setting this to true will force other browsers to also handle keydown instead of keypress.
		 */
		forceKeyDown: boolean;

		/**
		 * Provides a convenient wrapper for normalized keyboard navigation. KeyNav allows you to bind navigation keys to function calls that will get called when the keys are pressed, providing an easy way to implement custom navigation schemes for any UI component.
		 * @param el The element to bind to
		 * @param config The config
		 */
		constructor(el: Element | HTMLElement, config: object);
		/**
		 * Destroy this KeyNav (this is the same as calling disable).
		 */
		destroy(): void;
		/**
		 * Disable this KeyNav
		 */
		disable(): void;
		/**
		 * Enable this KeyNav
		 */
		enable(): void;
		/**
		 * Convenience function for setting disabled/enabled by boolean.
		 * @param disabled
		 */
		setDisabled(disabled: boolean): void;
	}

	/**
	 * An Action is a piece of reusable functionality that can be abstracted out of any particular component so that it
	 * can be usefully shared among multiple components.  Actions let you share handlers, configuration options and UI
	 * updates across any components that support the Action interface (primarily {@link Ext.Toolbar}, {@link Ext.Button}
	 * and {@link Ext.menu.Menu} components).
	 * Aside from supporting the config object interface, any component that needs to use Actions must also support
	 * the following method list, as these will be called as needed by the Action class: setText(string), setIconCls(string),
	 * setDisabled(boolean), setVisible(boolean) and setHandler(function).
	 */
	export interface Action {
		disable(): void;
		each(fn: Function, scope: object): void;
		enable(): void;
		execute(...args: any[]): void;
		getIconClass(): string;
		getText(): string;
		hide(): void;
		isDisabled(): boolean;
		isHidden(): boolean;
		setDisabled(disabled: boolean): void;
		setHandler(fn: Function, scope: object): void;
		setHidden(hidden: boolean): void;
		setIconClass(cls: string): void;
		setText(text: string): void;
		show(): void;
	}
	export class WindowMgr extends WindowGroup {
		static bringToFront(win: string | Window): boolean;
		static each(fn: Function, scope?: Window): void;
		static get(id: string | Window): Window;
		static getActive(): Window;
		static getBy(fn: Function, scope?: object): Window[];
		static hideAll(): void;
		static register(win: Window): void;
		static sendToBack(win: string | Window): Window;
		static unregister(win: Window): void;
	}
	type HideMode = 'visibility' | 'offsets' | 'display';
	export interface IColorPalette extends IComponent {
		allowReselect?: boolean;
		clickEvent?: string;
		handler?: Function;
		itemCls?: string;
		scope?: object;
		tpl?: string;
		value?: string;
	}
	export class ColorPalette extends Component {
		colors: string[];
		constructor(cfg?: IColorPalette);
	}
	export var TaskMgr: Ext.util.TaskRunner;
	export interface Listeners {
		[x: string]: Function;
	}
	/**
	 * A base error class.
	 */
	export class Error {
		public getMessage(): string;
		public getName(): string;
		public toJson(): string;
	}
	export interface ILayer {
		cls?: string;
		constrain?: boolean;
		dh?: any;
		shadow?: string | boolean;
		shadowOffset?: number;
		shim?: boolean;
		useDisplay?: boolean;
		zindex?: number;
	}
	/**
	 * An extended Ext.Element object that supports a shadow and shim, constrain to viewport and automatic maintaining of shadow/shim positions.
	 */
	export class Layer extends Element {
		constructor(cfg: ILayer, existingEl?: boolean);
	}
}
interface Function {
	/**
	* Creates a callback that passes arguments[0], arguments[1], arguments[2], ...
	* Call directly on any function. Example: myFunction.createCallback(arg1, arg2)
	* Will create a function that is bound to those 2 args. If a specific scope is required in the
	* callback, use {@link #createDelegate} instead. The function returned by createCallback always
	* executes in the window scope.
	* This method is required when you want to pass arguments to a callback function.  If no arguments
	* are needed, you can simply pass a reference to the function as a callback (e.g., callback: myFn).
	* However, if you tried to pass a function with arguments (e.g., callback: myFn(arg1, arg2)) the function
	* would simply execute immediately when the code is parsed. Example usage:
	*
	var sayHi = function(name){
		alert('Hi, ' + name);
	}

	// clicking the button alerts "Hi, Fred"
	new Ext.Button({
	text: 'Say Hi',
	renderTo: Ext.getBody(),
		handler: sayHi.createCallback('Fred')
	});

	* @return {Function} The new function
	*/
	createCallback(): Function;
	/**
	* Creates a delegate (callback) that sets the scope to obj.
	* Call directly on any function. Example: this.myFunction.createDelegate(this, [arg1, arg2])
	* Will create a function that is automatically scoped to obj so that the this variable inside the
	* callback points to obj. Example usage:
	*
	var sayHi = function(name){
	// Note this use of "this.text" here.  This function expects to
	// execute within a scope that contains a text property.  In this
	// example, the "this" variable is pointing to the btn object that
	// was passed in createDelegate below.
	alert('Hi, ' + name + '. You clicked the "' + this.text + '" button.');
	}
	var btn = new Ext.Button({
		text: 'Say Hi',
		renderTo: Ext.getBody()
	});
	// This callback will execute in the scope of the
	// button instance. Clicking the button alerts
	// "Hi, Fred. You clicked the "Say Hi" button."
	btn.on('click', sayHi.createDelegate(btn, ['Fred']));

	* @param {Object} scope (optional) The scope (this reference) in which the function is executed.
	* If omitted, defaults to the browser window.
	* @param {Array} args (optional) Overrides arguments for the call. (Defaults to the arguments passed by the caller)
	* @param {Boolean/Number} appendArgs (optional) if True args are appended to call args instead of overriding,
	* if a number the args are inserted at the specified position
	* @return {Function} The new function
	*/
	createDelegate(scope?: object, args?: any[], appendArgs?: boolean | number): Function;
	/**
	* Creates an interceptor function. The passed function is called before the original one. If it returns false,
	* the original one is not called. The resulting function returns the results of the original function.
	* The passed function is called with the parameters of the original function. Example usage:
	*
	var sayHi = function(name){
	alert('Hi, ' + name);
	}
	sayHi('Fred'); // alerts "Hi, Fred"
	// create a new function that validates input without
	// directly modifying the original function:
	var sayHiToFriend = sayHi.createInterceptor(function(name){
	return name == 'Brian';
	});
	sayHiToFriend('Fred');  // no alert
	sayHiToFriend('Brian'); // alerts "Hi, Brian"

	* @param {Function} fcn The function to call before the original
	* @param {Object} scope (optional) The scope (this reference) in which the passed function is executed.
	* If omitted, defaults to the scope in which the original function is called or the browser window.
	* @return {Function} The new function
	*/
	createInterceptor(fcn: Function, scope?: object): Function;
	/**
	* Create a combined function call sequence of the original function + the passed function.
	* The resulting function returns the results of the original function.
	* The passed fcn is called with the parameters of the original function. Example usage:
	*
	var sayHi = function(name){
		alert('Hi, ' + name);
	}
	sayHi('Fred'); // alerts "Hi, Fred"
	var sayGoodbye = sayHi.createSequence(function(name){
		alert('Bye, ' + name);
	});

	sayGoodbye('Fred'); // both alerts show

	* @param {Function} fcn The function to sequence
	* @param {Object} scope (optional) The scope (this reference) in which the passed function is executed.
	* If omitted, defaults to the scope in which the original function is called or the browser window.
	* @return {Function} The new function
	*/
	createSequence(fcn: Function, scope?: object): Function;
	/**
	* Calls this function after the number of millseconds specified, optionally in a specific scope. Example usage:
	*
	var sayHi = function(name){
	alert('Hi, ' + name);
	}

	// executes immediately:
	sayHi('Fred');

	// executes after 2 seconds:
	sayHi.defer(2000, this, ['Fred']);

	// this syntax is sometimes useful for deferring
	// execution of an anonymous function:
	(function(){
	alert('Anonymous');
	}).defer(100);

	* @param {Number} millis The number of milliseconds for the setTimeout call (if less than or equal to 0 the function is executed immediately)
	* @param {Object} scope (optional) The scope (this reference) in which the function is executed.
	* If omitted, defaults to the browser window.
	* @param {Array} args (optional) Overrides arguments for the call. (Defaults to the arguments passed by the caller)
	* @param {Boolean/Number} appendArgs (optional) if True args are appended to call args instead of overriding,
	* if a number the args are inserted at the specified position
	* @return {Number} The timeout id that can be used with clearTimeout
	*/
	defer(millis: number, scope?: object, args?: any[], appendArgs?: boolean | number): number;
}
interface StringConstructor {
	/**
	* Allows you to define a tokenized string and pass an arbitrary number of arguments to replace the tokens.  Each
	* token must be unique, and must increment in the format {0}, {1}, etc.  Example usage:
	*
	var cls = 'my-class', text = 'Some text';
	var s = String.format('&lt;div class="{0}">{1}&lt;/div>', cls, text);
	// s now contains the string: '&lt;div class="my-class">Some text&lt;/div>'
	*
	* @param {String} string The tokenized string to be formatted
	* @param {String} value1 The value to replace token {0}
	* @param {String} value2 Etc...
	* @return {String} The formatted string
	* @static
	*/
	format(string: string, value1: string, ...value2: string[]): string;
	/**
	* Escapes the passed string for ' and \
	* @param {String} string The string to escape
	* @return {String} The escaped string
	* @static
	*/
	escape(string: string): string;
	/**
	* Pads the left side of a string with a specified character.  This is especially useful
	* for normalizing number and date strings.  Example usage:
	*
	var s = String.leftPad('123', 5, '0');
	// s now contains the string: '00123'
	*
	* @param {String} string The original string
	* @param {Number} size The total length of the output string
	* @param {String} char (optional) The character with which to pad the original string (defaults to empty string " ")
	* @return {String} The padded string
	* @static
	*/
	leftPad(string: string, size: number, char?: string): string;
	/**
	* Utility function that allows you to easily switch a string between two alternating values.  The passed value
	* is compared to the current string, and if they are equal, the other value that was passed in is returned.  If
	* they are already different, the first value passed in is returned.  Note that this method returns the new value
	* but does not change the current string.
	*
	// alternate sort directions
	sort = sort.toggle('ASC', 'DESC');

	// instead of conditional logic:
	sort = (sort == 'ASC' ? 'DESC' : 'ASC');

	* @param {String} value The value to compare to the current string
	* @param {String} other The new value to use if the string already equals the first value passed in
	* @return {String} The new value
	*/
	toggle(value: string, other: string): string;
	/**
	* Trims whitespace from either end of a string, leaving spaces within the string intact.  Example:
	*
	var s = '  foo bar  ';
	alert('-' + s + '-');		 //alerts "- foo bar -"
	alert('-' + s.trim() + '-');  //alerts "-foo bar-"

	* @return {String} The trimmed string
	*/
	trim(): string;
}
interface Date {
	/** In the am/pm parsing routines, we allow both upper and lower case even though it doesn't exactly match the spec. It gives much more flexibility in being able to specify case insensitive regexes. */
	a(): void;
	/**
	 * Provides a convenient method for performing basic date arithmetic. This method does not modify the Date instance being called - it creates and returns a new Date instance containing the resulting date value.
	 * @param interval A valid date interval enum value.
	 * @param value The amount to add to the current date.
	 * @returns {} The new Date instance.
	 */
	add(interval: string, value: number): Date;
	/**
	 * Checks if this date falls on or between the given start and end dates.
	 * @param start Start date
	 * @param end End date
	 * @returns {boolean} true if this date falls on or between the given start and end dates.
	 */
	between(start: Date, end: Date): boolean;
	/**
	 * Attempts to clear all time information from this Date by setting the time to midnight of the same day, automatically adjusting for Daylight Saving Time (DST) where applicable. (note: DST timezone information for the browser's host operating system is assumed to be up-to-date)
	 * @param clone true to create a clone of this date, clear the time and return it (defaults to false).
	 * @returns {} this or the clone.
	 */
	clearTime(clone?: boolean): Date;
	/**
	 * Creates and returns a new Date instance with the exact same date value as the called instance. Dates are copied and passed by reference, so if a copied date variable is modified later, the original variable will also be changed. When the intention is to create a new variable that will not modify the original instance, you should create a clone.
	 * @example Example of correctly cloning a date:
	 * //wrong way:
		var orig = new Date('10/1/2006');
		var copy = orig;
		copy.setDate(5);
		document.write(orig); //returns 'Thu Oct 05 2006'!

		//correct way:
		var orig = new Date('10/1/2006');
		var copy = orig.clone();
		copy.setDate(5);
		document.write(orig); //returns 'Thu Oct 01 2006'
	 * @returns {} The new Date instance.
	 */
	clone(): Date;
	/**
	 * Formats a date given the supplied format string.
	 * @param format The format string.
	 * @returns {} The formatted date.
	 */
	format(format: string): string;
	/**
	 * Get the numeric day number of the year, adjusted for leap year.
	 * @returns {} 0 to 364 (365 in leap years).
	 */
	getDayOfYear(): number;
	/**
	 * Get the number of days in the current month, adjusted for leap year.
	 * @returns {} The number of days in the month.
	 */
	getDaysInMonth(): number;
	/**
	 * Returns the number of milliseconds between this date and date getElapsed
	 * @param date Defaults to now
	 * @returns {} The diff in milliseconds
	 */
	getElapsed(date?: Date): number;
	/**
	 * Get the date of the first day of the month in which this date resides.
	 * @returns {Date}
	 */
	getFirstDateOfMonth(): Date;
	/**
	 * Get the first day of the current month, adjusted for leap year. The returned value is the numeric day index within the week (0-6) which can be used in conjunction with the monthNames array to retrieve the textual day name.
	 * @example
	 * var dt = new Date('1/10/2007');
	 * document.write(Date.dayNames[dt.getFirstDayOfMonth()]); //output: 'Monday'
	 * @returns {} The day number (0-6).
	 */
	getFirstDayOfMonth(): number;
	/**
	 * Get the offset from GMT of the current date (equivalent to the format specifier 'O').
	 * @param colon true to separate the hours and minutes with a colon (defaults to false).
	 * @returns {string} The 4-character offset string prefixed with + or - (e.g. '-0600').
	 */
	getGMTOffset(colon?: boolean): string;
	/**
	 * Get the date of the last day of the month in which this date resides.
	 * @returns {Date}
	 */
	getLastDateOfMonth(): Date;
	/**
	 * Get the last day of the current month, adjusted for leap year. The returned value is the numeric day index within the week (0-6) which can be used in conjunction with the monthNames array to retrieve the textual day name.
	 * @example
	 * var dt = new Date('1/10/2007');
	 * document.write(Date.dayNames[dt.getLastDayOfMonth()]); //output: 'Wednesday'
	 * @returns {} The day number (0-6).
	 */
	getLastDayOfMonth(): number;
	/**
	 * Get the English ordinal suffix of the current day (equivalent to the format specifier 'S').
	 * @returns {string} 'st, 'nd', 'rd' or 'th'.
	 */
	getSuffix(): 'st' | 'nd' | 'rd' | 'th';
	/**
	 * Get the timezone abbreviation of the current date (equivalent to the format specifier 'T').
	 * Note: The date string returned by the javascript Date object's toString() method varies between browsers (e.g. FF vs IE) and system region settings (e.g. IE in Asia vs IE in America). For a given date string e.g. "Thu Oct 25 2007 22:55:35 GMT+0800 (Malay Peninsula Standard Time)", getTimezone() first tries to get the timezone abbreviation from between a pair of parentheses (which may or may not be present), failing which it proceeds to get the timezone abbreviation from the GMT offset portion of the date string.
	 * @returns {string} The abbreviated timezone name (e.g. 'CST', 'PDT', 'EDT', 'MPST' ...).
	 */
	getTimezone(): string;
	/**
	 * Get the numeric ISO-8601 week number of the year. (equivalent to the format specifier 'W', but without a leading zero).
	 * @returns {number} 1 to 53
	 */
	getWeekOfYear(): number;
	/**
	 * Checks if the current date is affected by Daylight Saving Time (DST).
	 * @returns {boolean} True if the current date is affected by DST.
	 */
	isDST(): boolean;
	/**
	 * Checks if the current date falls within a leap year.
	 * @returns {boolean} True if the current date falls within a leap year, false otherwise.
	 */
	isLeapYear(): boolean;
}
interface DateConstructor {
	/**
	 * Checks if the specified format contains hour information
	 * @static
	 * @param format The format to check
	 * @returns {} True if the format contains hour information
	 */
	formatContainsHourInfo(format: object): boolean;
	/**
	 * Get the zero-based javascript month number for the given short/full month name. Override this function for international dates.
	 * @static
	 * @param name The short/full month name.
	 * @returns {number} The zero-based javascript month number.
	 */
	getMonthNumber(name: string): number;
	/**
	 * Get the short day name for the given day number. Override this function for international dates.
	 * @static
	 * @param day A zero-based javascript day number.
	 * @returns {} The short day name.
	 */
	getShortDayName(day: number): string;
	/**
	 * Get the short month name for the given month number. Override this function for international dates.
	 * @static
	 * @param month A zero-based javascript month number.
	 * @returns {} The short month name.
	 */
	getShortMonthName(month: number): string;
	/**
	 * Checks if the passed Date parameters will cause a javascript Date "rollover".
	 * @static
	 * @param year 4-digit year
	 * @param month 1-based month-of-year
	 * @param day Day of month
	 * @param hour Hour
	 * @param minute Minute
	 * @param second Second
	 * @param millisecond Millisecond
	 * @returns {} true if the passed parameters do not cause a Date "rollover", false otherwise.
	 */
	isValid(year: number, month: number, day: number, hour?: number, minute?: number, second?: number, millisecond?: number): boolean;
	/**
	 * Parses the passed string using the specified date format. Note that this function expects normal calendar dates, meaning that months are 1-based (i.e. 1 = January). The defaults hash will be used for any date value (i.e. year, month, day, hour, minute, second or millisecond) which cannot be found in the passed string. If a corresponding default date value has not been specified in the defaults hash, the current date's year, month, day or DST-adjusted zero-hour time value will be used instead. Keep in mind that the input date string must precisely match the specified format string in order for the parse operation to be successful (failed parse operations return a null value).
	 * @static
	 * @param input The raw date string.
	 * @param format The expected date string format.
	 * @param strict True to validate date strings while parsing (i.e. prevents javascript Date "rollover")
	 * @returns {} The parsed Date.
	 * @example
	 * //dt = Fri May 25 2007 (current date)
	 * var dt = new Date();
	 *
	 * //dt = Thu May 25 2006 (today's month/day in 2006)
	 * dt = Date.parseDate("2006", "Y");
	 *
	 * //dt = Sun Jan 15 2006 (all date parts specified)
	 * dt = Date.parseDate("2006-01-15", "Y-m-d");
	 *
	 * //dt = Sun Jan 15 2006 15:20:01
	 * dt = Date.parseDate("2006-01-15 3:20:01 PM", "Y-m-d g:i:s A");
	 *
	 * // attempt to parse Sun Feb 29 2006 03:20:01 in strict mode
	 * dt = Date.parseDate("2006-02-29 03:20:01", "Y-m-d H:i:s", true); // returns null
	 */
	parseDate(input: string, format: string, strict?: boolean): Date;
}
interface Array<T> {
	/**
	 * Checks whether or not the specified object exists in the array.
	 * @param o The object to check for
	 * @param from The index at which to begin the search
	 * @returns {} The index of o in the array (or -1 if it is not found)
	 */
	indexOf(o: T, from?: number): number;
	/**
	 * Removes the specified object from the array. If the object is not found nothing happens.
	 * @param o The object to remove
	 * @returns {} this array
	 */
	remove(o: T): this;
}
interface Date {
	/** In the am/pm parsing routines, we allow both upper and lower case even though it doesn't exactly match the spec. It gives much more flexibility in being able to specify case insensitive regexes. */
	a(): void;
	/**
	 * Provides a convenient method for performing basic date arithmetic. This method does not modify the Date instance being called - it creates and returns a new Date instance containing the resulting date value.
	 * @param interval A valid date interval enum value.
	 * @param value The amount to add to the current date.
	 * @returns {} The new Date instance.
	 */
	add(interval: string, value: number): Date;
	/**
	 * Checks if this date falls on or between the given start and end dates.
	 * @param start Start date
	 * @param end End date
	 * @returns {boolean} true if this date falls on or between the given start and end dates.
	 */
	between(start: Date, end: Date): boolean;
	/**
	 * Attempts to clear all time information from this Date by setting the time to midnight of the same day, automatically adjusting for Daylight Saving Time (DST) where applicable. (note: DST timezone information for the browser's host operating system is assumed to be up-to-date)
	 * @param clone true to create a clone of this date, clear the time and return it (defaults to false).
	 * @returns {} this or the clone.
	 */
	clearTime(clone?: boolean): Date;
	/**
	 * Creates and returns a new Date instance with the exact same date value as the called instance. Dates are copied and passed by reference, so if a copied date variable is modified later, the original variable will also be changed. When the intention is to create a new variable that will not modify the original instance, you should create a clone.
	 * @example Example of correctly cloning a date:
	 * //wrong way:
		var orig = new Date('10/1/2006');
		var copy = orig;
		copy.setDate(5);
		document.write(orig); //returns 'Thu Oct 05 2006'!

		//correct way:
		var orig = new Date('10/1/2006');
		var copy = orig.clone();
		copy.setDate(5);
		document.write(orig); //returns 'Thu Oct 01 2006'
	 * @returns {} The new Date instance.
	 */
	clone(): Date;
	/**
	 * Formats a date given the supplied format string.
	 * @param format The format string.
	 * @returns {} The formatted date.
	 */
	format(format: string): string;
	/**
	 * Get the numeric day number of the year, adjusted for leap year.
	 * @returns {} 0 to 364 (365 in leap years).
	 */
	getDayOfYear(): number;
	/**
	 * Get the number of days in the current month, adjusted for leap year.
	 * @returns {} The number of days in the month.
	 */
	getDaysInMonth(): number;
	/**
	 * Returns the number of milliseconds between this date and date getElapsed
	 * @param date Defaults to now
	 * @returns {} The diff in milliseconds
	 */
	getElapsed(date?: Date): number;
	/**
	 * Get the date of the first day of the month in which this date resides.
	 * @returns {Date}
	 */
	getFirstDateOfMonth(): Date;
	/**
	 * Get the first day of the current month, adjusted for leap year. The returned value is the numeric day index within the week (0-6) which can be used in conjunction with the monthNames array to retrieve the textual day name.
	 * @example
	 * var dt = new Date('1/10/2007');
	 * document.write(Date.dayNames[dt.getFirstDayOfMonth()]); //output: 'Monday'
	 * @returns {} The day number (0-6).
	 */
	getFirstDayOfMonth(): number;
	/**
	 * Get the offset from GMT of the current date (equivalent to the format specifier 'O').
	 * @param colon true to separate the hours and minutes with a colon (defaults to false).
	 * @returns {string} The 4-character offset string prefixed with + or - (e.g. '-0600').
	 */
	getGMTOffset(colon?: boolean): string;
	/**
	 * Get the date of the last day of the month in which this date resides.
	 * @returns {Date}
	 */
	getLastDateOfMonth(): Date;
	/**
	 * Get the last day of the current month, adjusted for leap year. The returned value is the numeric day index within the week (0-6) which can be used in conjunction with the monthNames array to retrieve the textual day name.
	 * @example
	 * var dt = new Date('1/10/2007');
	 * document.write(Date.dayNames[dt.getLastDayOfMonth()]); //output: 'Wednesday'
	 * @returns {} The day number (0-6).
	 */
	getLastDayOfMonth(): number;
	/**
	 * Get the English ordinal suffix of the current day (equivalent to the format specifier 'S').
	 * @returns {string} 'st, 'nd', 'rd' or 'th'.
	 */
	getSuffix(): 'st' | 'nd' | 'rd' | 'th';
	/**
	 * Get the timezone abbreviation of the current date (equivalent to the format specifier 'T').
	 * Note: The date string returned by the javascript Date object's toString() method varies between browsers (e.g. FF vs IE) and system region settings (e.g. IE in Asia vs IE in America). For a given date string e.g. "Thu Oct 25 2007 22:55:35 GMT+0800 (Malay Peninsula Standard Time)", getTimezone() first tries to get the timezone abbreviation from between a pair of parentheses (which may or may not be present), failing which it proceeds to get the timezone abbreviation from the GMT offset portion of the date string.
	 * @returns {string} The abbreviated timezone name (e.g. 'CST', 'PDT', 'EDT', 'MPST' ...).
	 */
	getTimezone(): string;
	/**
	 * Get the numeric ISO-8601 week number of the year. (equivalent to the format specifier 'W', but without a leading zero).
	 * @returns {number} 1 to 53
	 */
	getWeekOfYear(): number;
	/**
	 * Checks if the current date is affected by Daylight Saving Time (DST).
	 * @returns {boolean} True if the current date is affected by DST.
	 */
	isDST(): boolean;
	/**
	 * Checks if the current date falls within a leap year.
	 * @returns {boolean} True if the current date falls within a leap year, false otherwise.
	 */
	isLeapYear(): boolean;
}
interface Array<T> {
	/**
	 * Checks whether or not the specified object exists in the array.
	 * @param o The object to check for
	 * @param from The index at which to begin the search
	 * @returns {} The index of o in the array (or -1 if it is not found)
	 */
	indexOf(o: T, from?: number): number;
	/**
	 * Removes the specified object from the array. If the object is not found nothing happens.
	 * @param o The object to remove
	 * @returns {} this array
	 */
	remove(o: T): this;
}
interface Number {
	/**
	* Checks whether or not the current number is within a desired range.  If the number is already within the
	* range it is returned, otherwise the min or max value is returned depending on which side of the range is
	* exceeded.  Note that this method returns the constrained value but does not change the current number.
	* @param {Number} min The minimum number in the range
	* @param {Number} max The maximum number in the range
	* @return {Number} The constrained value if outside the range, otherwise the current value
	*/
	constrain(min: number, max: number): number;
}
declare namespace Ext.dd {
	export class DD<T = any> extends DragDrop { }
	export class DDProxy extends DD { }
	export interface IStatusProxy {
		dropAllowed?: string;
		dropNotAllowed?: string;
	}
	export class StatusProxy {
		constructor(cfg: IStatusProxy);
		getEl(): Ext.Layer;
		getGhost(): Ext.Element;
		hide(clear: boolean): void;
		repair(xy: [number, number], callback: Function, scope: object): void;
		reset(clearGhost: boolean): void;
		setStatus(cssClass: string): void;
		show(): void;
		stop(): void;
		sync(): void;
		update(html: string | HTMLElement): void;
	}
	export interface IDragSource {
		ddGroup?: string;
		dropAllowed?: string;
		dropNotAllowed?: string;
	}
	export class DragSource<T = any> extends DDProxy {
		constructor(el: Ext.Element, cfg: IDragSource);

		afterDragDrop(target: DragDrop, e: EventObject, id: string): void;
		afterDragEnter(target: DragDrop, e: EventObject, id: string): void;
		afterDragOut(target: DragDrop, e: EventObject, id: string): void;
		afterDragOver(target: DragDrop, e: EventObject, id: string): void;
		afterInvalidDrop(e: EventObject, id: string): void;
		afterValidDrop(target: DragDrop, e: EventObject, id: string): void;
		beforeDragDrop(target: DragDrop, e: EventObject, id: string): boolean;
		beforeDragEnter(target: DragDrop, e: EventObject, id: string): boolean;
		beforeDragOut(target: DragDrop, e: EventObject, id: string): boolean;
		beforeDragOver(target: DragDrop, e: EventObject, id: string): boolean;
		beforeInvalidDrop(target: DragDrop, e: EventObject, id: string): boolean;
		getDragData(e: EventObject): T;
		getProxy(): StatusProxy;
		hideProxy(): void;
		onBeforeDrag(data: T, e: EventObject): boolean;
		onStartDrag(x: number, y: number): void;
	}
	export interface IDragZone extends IDragSource {
		containerScroll?: boolean;
		hlColor?: string;
	}
	/**
	 * This class provides a container DD instance that allows dragging of multiple child source nodes.
	 */
	export class DragZone<T = any> extends DragSource {
		public dragData: T;

		constructor(el: Ext.Element, cfg: IDragZone);

		/**
		 * Called after a repair of an invalid drop. By default, highlights this.dragData.ddel
		 */
		afterRepair(): any;
		/**
		 * Called when a mousedown occurs in this container. Looks in Ext.dd.Registry for a valid target to drag based on the mouse down. Override this method to provide your own lookup logic (e.g. finding a child by class name). Make sure your returned object has a "ddel" attribute (with an HTML Element) for other functions to work.
		 * @param e The mouse down event
		 * @returns The dragData
		 */
		getDragData(e: EventObject): T;
		/**
		 * Called before a repair of an invalid drop to get the XY to animate to. By default returns the XY of this.dragData.ddel
		 * @param e The mouse up event
		 * @returns The xy location
		 */
		getRepairXY(e: EventObject): [number, number];
		/**
		 * Called once drag threshold has been reached to initialize the proxy element. By default, it clones the this.dragData.ddel
		 * @param x The x position of the click on the dragged object
		 * @param y The y position of the click on the dragged object
		 * @returns true to continue the drag, false to cancel
		 */
		onInitDrag(x: number, y: number): boolean;
	}
	/**
	 * This class provides a container DD instance that allows dropping on multiple child target nodes.
	 */
	export class DropZone<T = any> extends DropTarget {
		constructor(el: Ext.Element, config: IDropTarget);
		/**
		 * Returns a custom data object associated with the DOM node that is the target of the event. By default this looks up the event target in the Ext.dd.Registry, although you can override this method to provide your own custom lookup.
		 * @param e The event
		 * @returns The custom data
		 */
		getTargetFromEvent(e: Ext.EventObject): HTMLElement;
		/**
		 * The function a Ext.dd.DragSource calls once to notify this drop zone that the dragged item has been dropped on it. The drag zone will look up the target node based on the event passed in, and if there is a node registered for that event, it will delegate to onNodeDrop for node-specific handling, otherwise it will call onContainerDrop.
		 * @param source The drag source that was dragged over this drop zone
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 * @returns True if the drop was valid, else false
		 */
		notifyDrop(source: DragSource, e: Ext.EventObject, data: T): boolean;
		/**
		 * The function a Ext.dd.DragSource calls once to notify this drop zone that the source is now over the zone. The default implementation returns this.dropNotAllowed and expects that only registered drop nodes can process drag drop operations, so if you need the drop zone itself to be able to process drops you should override this method and provide a custom implementation.
		 * @param source The drag source that was dragged over this drop zone
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 * @returns status The CSS class that communicates the drop status back to the source so that the underlying Ext.dd.StatusProxy can be updated
		 */
		notifyEnter(source: DragSource, e: Ext.EventObject, data: T): string;
		/**
		 * The function a Ext.dd.DragSource calls once to notify this drop zone that the source has been dragged out of the zone without dropping. If the drag source is currently over a registered node, the notification will be delegated to onNodeOut for node-specific handling, otherwise it will be ignored.
		 * @param source The drag source that was dragged over this drop target
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 */
		notifyOut(source: DragSource, e: Ext.EventObject, data: T): void;
		/**
		 * The function a Ext.dd.DragSource calls continuously while it is being dragged over the drop zone. This method will be called on every mouse movement while the drag source is over the drop zone. It will call onNodeOver while the drag source is over a registered node, and will also automatically delegate to the appropriate node-specific methods as necessary when the drag source enters and exits registered nodes (onNodeEnter, onNodeOut). If the drag source is not currently over a registered node, it will call onContainerOver.
		 * @param source The drag source that was dragged over this drop target
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 * @returns status The CSS class that communicates the drop status back to the source so that the underlying Ext.dd.StatusProxy can be updated
		 */
		notifyOver(source: DragSource, e: Ext.EventObject, data: T): string;
		/**
		 * Called when the DropZone determines that a Ext.dd.DragSource has been dropped on it, but not on any of its registered drop nodes. The default implementation returns false, so it should be overridden to provide the appropriate processing of the drop event if you need the drop zone itself to be able to accept drops. It should return true when valid so that the drag source's repair action does not run.
		 * @param source The drag source that was dragged over this drop target
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 * @returns True if the drop was valid, else false
		 */
		onContainerDrop(source: DragSource, e: Ext.EventObject, data: T): boolean;
		/**
		 * Called while the DropZone determines that a Ext.dd.DragSource is being dragged over it, but not over any of its registered drop nodes. The default implementation returns this.dropNotAllowed, so it should be overridden to provide the proper feedback if necessary.
		 * @param source The drag source that was dragged over this drop target
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 * @returns status The CSS class that communicates the drop status back to the source so that the underlying Ext.dd.StatusProxy can be updated
		 */
		onContainerOver(source: DragSource, e: Ext.EventObject, data: T): string;
		/**
		 * Called when the DropZone determines that a Ext.dd.DragSource has been dropped onto the drop node. The default implementation returns false, so it should be overridden to provide the appropriate processing of the drop event and return true so that the drag source's repair action does not run.
		 * @param nodeData The custom data associated with the drop node (this is the same value returned from getTargetFromEvent for this node)
		 * @param source The drag source that was dragged over this drop target
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 * @returns True if the drop was valid, else false
		 */
		onNodeDrop(nodeData: HTMLElement, source: DragSource, e: Ext.EventObject, data: T): boolean;
		/**
		 * Called when the DropZone determines that a Ext.dd.DragSource has entered a drop node that has either been registered or detected by a configured implementation of getTargetFromEvent. This method has no default implementation and should be overridden to provide node-specific processing if necessary.
		 * @param nodeData The custom data associated with the drop node (this is the same value returned from getTargetFromEvent for this node)
		 * @param source The drag source that was dragged over this drop target
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 */
		onNodeEnter(nodeData: HTMLElement, source: DragSource, e: Ext.EventObject, data: T): void;
		/**
		 * Called when the DropZone determines that a Ext.dd.DragSource has been dragged out of the drop node without dropping. This method has no default implementation and should be overridden to provide node-specific processing if necessary.
		 * @param nodeData The custom data associated with the drop node (this is the same value returned from getTargetFromEvent for this node)
		 * @param source The drag source that was dragged over this drop target
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 */
		onNodeOut(nodeData: HTMLElement, source: DragSource, e: Ext.EventObject, data: T): void;
		/**
		 * Called while the DropZone determines that a Ext.dd.DragSource is over a drop node that has either been registered or detected by a configured implementation of getTargetFromEvent. The default implementation returns this.dropNotAllowed, so it should be overridden to provide the proper feedback.
		 * @param nodeData The custom data associated with the drop node (this is the same value returned from getTargetFromEvent for this node)
		 * @param source The drag source that was dragged over this drop target
		 * @param e The event
		 * @param data An object containing arbitrary data supplied by the drag source
		 * @returns status The CSS class that communicates the drop status back to the source so that the underlying Ext.dd.StatusProxy can be updated
		 */
		onNodeOver(nodeData: HTMLElement, source: DragSource, e: Ext.EventObject, data: T): string;
	}
	export interface IDropTarget {
		ddGroup?: string;
		dropAllowed?: string;
		dropNotAllowed?: string;
		overClass?: string;
	}
	export class DropTarget<T = any> extends DDTarget {
		protected isTarget: boolean;

		constructor(el: Ext.Element, config: IDropTarget);

		notifyDrop(source: DragSource, e: Ext.EventObject, data: T): boolean;
		notifyEnter(source: DragSource, e: Ext.EventObject, data: T): string;
		notifyOut(source: DragSource, e: Ext.EventObject, data: T): void;
		notifyOver(source: DragSource, e: Ext.EventObject, data: T): string;
	}
	export class DDTarget extends DragDrop { }
	export interface IDragDrop {
		padding?: [number, number];
		isTarget?: boolean;
		maintainOffset?: boolean;
		primaryButtonOnly?: boolean;
	}
	export class DragDrop {
		protected available: boolean;
		protected config: IDragDrop;
		protected defaultPadding: Margins;
		protected groups: any;
		protected hasOuterHandles: boolean;
		protected id: string;
		protected ignoreSelf: boolean;
		protected invalidHandleClasses: string[];
		protected invalidHandleIds: any[];
		protected invalidHandleTypes: any[];
		protected isTarget: boolean;
		protected maintainOffset: boolean;
		protected moveOnly: boolean;
		protected padding: number[];
		protected primaryButtonOnly: boolean;
		protected xTicks: number[];
		protected yTicks: number[];

		constructor(id: string, sGroup: string, config: IDragDrop);

		addInvalidHandleClass(cssClass: string): void;
		addInvalidHandleId(id: string): void;
		addInvalidHandleType(tagName: string): void;
		addToGroup(sGroup: string): void;
		applyConfig(): void;
		clearConstraints(): void;
		clearTicks(): void;
		constrainTo(constrainTo: string | Element, pad?: number, inContent?: boolean): void;
		endDrag(e: EventObject): void;
		getDragEl(): HTMLElement;
		getEl(): HTMLElement;
		init(id: string, sGroup: string, config: IDragDrop): void;
		initTarget(id: string, sGroup: string, config: IDragDrop): void;
		isLocked(): boolean;
		isValidHandleChild(node: HTMLElement): boolean;
		lock(): void;
		onAvailable(): void;
		onDrag(e: EventObject): void;
		onDragDrop(e: EventObject, id: string | DragDrop[]): void;
		onDragEnter(e: EventObject, id: string | DragDrop[]): void;
		onDragOut(e: EventObject, id: string | DragDrop[]): void;
		onDragOver(e: EventObject, id: string | DragDrop[]): void;
		onInvalidDrop(e: EventObject): void;
		onMouseDown(e: EventObject): void;
		onMouseUp(e: EventObject): void;
		removeFromGroup(sGroup: string): void;
		removeInvalidHandleClass(cssClass: string): void;
		removeInvalidHandleId(id: string): void;
		removeInvalidHandleType(tagName: string): void;
		resetConstraints(maintainOffset: boolean): void;
		setDragElId(id: string): void;
		setHandleElId(id: string): void;
		setInitPosition(diffX: number, diffY: number): void;
		setOuterHandleElId(id: string): void;
		setPadding(iTop: number, iRight: number, iBot: number, iLeft: number, ): void;
		setXConstraint(iLeft: number, iRight: number, iTickSize: number, ): void;
		setYConstraint(iUp: number, iDown: number, iTickSize: number, ): void;
		startDrag(X: number, Y: number, ): void;
		toString(): string;
		unlock(): void;
		unreg(): void;
	}
}
declare namespace Ext.form {
	export enum Action {
		CLIENT_INVALID,
		SERVER_INVALID,
		CONNECT_FAILURE,
		LOAD_FAILURE
	}
	export interface BasicFormResponse<T> {
		failureType: form.Action;
		response: XMLHttpRequest;
		result: T;
		type: 'default' | 'submit' | 'load';
	}
	export interface BasicFormActions {
		url?: string;
		method?: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'OPTION' | 'HEAD' | 'CONNECT' | 'TRACE' | 'PATCH';
		params?: object | string;
		headers?: object;
		success?: (form: BasicForm, action: BasicFormResponse<any>) => void;
		failure?: (form: BasicForm, action: BasicFormResponse<any>) => void;
		scope?: object;
		clientValidation?: boolean;
	}
	export interface IBasicForm extends util.IObservable {
		api?: any;
		baseParams?: any;
		errorReader?: any;
		fileUpload?: any;
		method?: any;
		paramOrder?: any;
		paramsAsHash?: any;
		reader?: any;
		standardSubmit?: any;
		timeout?: any;
		trackResetOnLoad?: any;
		url?: any;
		waitTitle?: any;
	}
	export class BasicForm extends util.Observable {
		items: util.MixedCollection<any>;
		waitMsgTarget: any;

		constructor(cfg?: IBasicForm);

		add(...fields: Field[]): this;
		applyIfToFields(values: object): this;
		applyToFields(values: object): this;
		cleanDestroyed(): void;
		clearInvalid(): this;
		doAction(actionName: string | object, options?: BasicFormActions): this;
		findField(id: string): Field;
		findField<T>(id: string): T;
		getEl(): Element;
		getFieldValues(dirtyOnly?: boolean): object;
		getValues(asString: true): string;
		getValues<T = any>(): T;
		isDirty(): boolean;
		isValid(): boolean;
		load(options: BasicFormActions): this;
		loadRecord(record: Ext.data.Record<any>): this;
		markInvalid(errors: { id: string; msg: string; }[]): this;
		remove(field: Field): this;
		render(): this;
		reset(): this;
		setValues(values: { id: string; value: string | number; }[]): this;
		setValues(values: any): this;
		submit(options?: BasicFormActions): this;
		updateRecord(record: Ext.data.Record<any>): this;
	}
	export interface IFormPanel extends IPanel {
		formId?: string;
		hideLabels?: boolean;
		itemCls?: string;
		labelAlign?: Align;
		labelPad?: number;
		labelSeparator?: string;
		labelWidth?: number;
		minButtonWidth?: number;
		monitorPoll?: number;
		monitorValid?: boolean;
		fileUpload?: boolean;
	}
	export class FormPanel extends Panel {
		constructor(cfg?: IFormPanel & IBasicForm);
		getForm(): BasicForm;
		getLayoutTarget(): Element;
		startMonitoring(): void;
		stopMonitoring(): void;
	}
	export interface IField extends IBoxComponent {
		allowBlank?: boolean;
		disabled?: boolean;
		name?: string;
		readOnly?: boolean;
		tabIndex?: number;
		value?: any;
		defaultAutoCreate?: any;
	}
	export abstract class Field<T = any> extends BoxComponent {
		/**
		 * Returns the normalized data value (undefined or emptyText will be returned as ''). ...
		 */
		getValue(): T;
		/**
		 * Sets a data value into the field and validates it. To set the value directly without validation see setRawValue.
		 * @param value The value to set
		 */
		setValue(value: T): this;
		/**
		 * Returns whether or not the field value is currently valid by validating the processed value of the field. Note: disabled fields are ignored.
		 * @param preventMark True to disable marking the field invalid
		 */
		isValid(preventMark?: boolean): boolean;
		/**
		 * Sets the underlying DOM field's value directly, bypassing validation. To set the value with validation see setValue.
		 * @param value The value to set
		 */
		setRawValue(value: any): any;
		/**
		 * Display an error message associated with this field, using msgTarget to determine how to display the message and applying invalidClass to the field's UI element.
		 * @param msg The validation message (defaults to invalidText)
		 */
		markInvalid(msg?: string): void;
		/**
		 * Sets the read only state of this field.
		 * @param {Boolean} readOnly Whether the field should be read only.
		 */
		setReadOnly(readOnly: boolean): void;
		/**
		 * Validates the field value
		 * @return {Boolean} True if the value is valid, else false
		 */
		validate(): boolean;

		constructor(cfg?: IField);

		protected afterRender(): void;
		protected initEvents(): void;
	}
	export interface ICheckbox extends IField {
		boxLabel?: string;
		handler?: (checkbox: Checkbox, checked: boolean) => void;
		checked?: boolean;
	}
	export class Checkbox extends Field<boolean> {
		boxLabel: string;
		constructor(cfg?: ICheckbox);
	}
	export interface ICheckboxGroup extends IField {
		allowBlank?: boolean;
		blankText?: string;
		columns?: 'auto' | number | object[];
		items?: Checkbox[] | ICheckbox[];
		vertical?: boolean;
	}
	export class CheckboxGroup extends Field {
		allowBlank: boolean;
		blankText: string;
		columns: 'auto' | number | object[];
		readonly items: util.MixedCollection<any>;
		vertical: boolean;

		constructor(cfg?: ICheckboxGroup);
		getErrors(): any[];
		getValue(): Radio | Checkbox[];
		isDirty(): boolean;
		reset(): void;
		setValue(id: Checkbox | string, value?: boolean): this;
	}
	export interface IRadioGroup extends ICheckboxGroup {
		items?: Radio[] | IRadio[];
	}
	export class RadioGroup extends CheckboxGroup {
		constructor(cfg?: IRadioGroup);
		readonly items: util.MixedCollection<any>;
		getValue(): Radio;
		onSetValue(id: string | Radio, value: boolean): this;
	}
	export interface ITextField extends IField {
		regex?: RegExp;
		regexText?: string;
		validator?: (value: any) => boolean | string;
		vtype?: string;
		vtypeText?: string;
		disableKeyFilter?: boolean;
		maskRe?: RegExp | object;
		enableKeyEvents?: boolean;
		emptyText?: string;
		grow?: boolean;
	}
	/**
	 * Basic text field. Can be used as a direct replacement for traditional text inputs, or as the base class for more sophisticated input controls (like Ext.form.TextArea and Ext.form.ComboBox).
	 */
	export class TextField extends Field {
		/**
		 * A JavaScript RegExp object to be tested against the field value during validation (defaults to null). If the test fails, the field will be marked invalid using regexText.
		 */
		regex: RegExp;
		/**
		 * The error text to display if regex is used and the test fails during validation (defaults to '')
		 */
		regexText: string;
		/**
		 * A custom validation function to be called during field validation (validateValue) (defaults to null). If specified, this function will be called first, allowing the developer to override the default validation process.
		 */
		validator: (value: any) => boolean | string;
		/**
		 * True to disable input keystroke filtering (defaults to false)
		 */
		disableKeyFilter: boolean;
		/**
		 * A validation type name as defined in Ext.form.VTypes (defaults to null)
		 */
		vtype: string;
		/**
		 * A custom error message to display in place of the default message provided for the vtype currently set for this field (defaults to ''). Note: only applies if vtype is set, else ignored.
		 */
		vtypeText: string;

		constructor(cfg?: ITextField);
		/**
		 * Sets a data value into the field and validates it. To set the value directly without validation see setRawValue.
		 * @param value The value to set
		 */
		setValue(value: any): this;
		/**
		 * Selects text in this field
		 * @param start The index where the selection should start (defaults to 0)
		 * @param end The index where the selection should end (defaults to the text length)
		 */
		selectText(start?: number, end?: number): void;
		/**
		* Returns the normalized data value (undefined or emptyText will be returned as '').  To return the raw value see {@link #getRawValue}.
		* @return {string} value The field value
		*/
		getValue(): string;
		getValue<T>(): T;

		protected onDestroy();
	}
	export interface ITextArea extends ITextField { }
	export class TextArea extends TextField { }
	export interface IRadio extends ICheckbox {
		inputType?: string;
	}
	export class Radio extends Checkbox {
		readonly inputType: string;

		constructor(cfg?: IRadio);
		getGroupValue(): string;
		setValue(value: string | boolean): this;
	}
	export interface ITriggerField extends ITextField {
		/**
		 * true to hide the trigger element and display only the base text field
		 * @default false
		 */
		hideTrigger?: boolean;
		editable?: boolean;
		autoCreate?: string | IBodyCfg;
		readOnly?: boolean;
		triggerClass?: string;
		triggerConfig?: object;
		wrapFocusClass?: string;
		onTriggerClick?: (e: EventObject) => void;
	}
	export class TriggerField extends TextField {
		constructor(cfg?: ITriggerField);
		getWidth(): number;
		protected onTriggerClick(e: EventObject): void;
		setEditable(value: boolean): void;
		setHideTrigger(hideTrigger: boolean): void;
		setReadOnly(value: boolean): void;
	}
	export interface IDateField extends ITriggerField {
		altFormats?: string;
		autoCreate?: string | IBodyCfg;
		disabledDates?: string[];
		disabledDatesText?: string;
		disabledDays?: [number, number];
		disabledDaysText?: string;
		format?: string;
		invalidText?: string;
		maxText?: string;
		maxValue?: Date | string;
		minText?: string;
		minValue?: Date | string;
		showToday?: boolean;
		startDay?: number;
		triggerClass?: string;
	}
	export class DateField extends TriggerField {
		constructor(cfg?: IDateField);
		getErrors(value: any): any[];
		onTriggerClick(e: EventObject): void;
		setDisabledDates(disabledDates: string[]): void;
		setDisabledDays(disabledDays: number[]): void;
		setMaxValue(value: Date): void;
		setMinValue(value: Date): void;
		setValue(value: string | Date): this;
	}
	export interface IComboBox extends ITriggerField {
		allQuery?: string;
		autoCreate?: string | IBodyCfg;
		autoSelect?: boolean;
		clearFilterOnReset?: boolean;
		displayField?: string;
		forceSelection?: boolean;
		handleHeight?: number;
		hiddenId?: string;
		hiddenName?: string;
		hiddenValue?: string;
		itemSelector?: string;
		lazyInit?: boolean;
		lazyRender?: boolean;
		listAlign?: AlignTo[] | [number, number];
		listClass?: string;
		listEmptyText?: string;
		listWidth?: number;
		loadingText?: string;
		maxHeight?: number;
		minChars?: number;
		minHeight?: number;
		minListWidth?: number;
		mode?: 'remote' | 'local';
		pageSize?: number;
		queryDelay?: number;
		queryParam?: string;
		resizable?: boolean;
		selectOnFocus?: boolean;
		selectedClass?: string;
		shadow?: boolean | string;
		store?: data.Store | string[] | (string[])[];
		submitValue?: boolean;
		title?: string;
		tpl?: string | XTemplate;
		transform?: string | HTMLElement | Element;
		triggerAction?: 'query' | 'all';
		triggerClass?: string;
		typeAhead?: boolean;
		typeAheadDelay?: number;
		valueField?: string;
		valueNotFoundText?: string;
	}
	export class ComboBox extends TriggerField {
		keyNav: KeyNav;

		readonly lastSelectionText: string;

		constructor(cfg?: IComboBox);
		getValue(): string;
		setValue(value: string): this;
		getStore(): data.Store;
		clearValue(): void;
		collapse(): void;
		doQuery(query: string, forceAll?: boolean): void;
		expand(): void;
		getListParent(): Element;
		getName(): string;
		isExpanded(): boolean;
		protected onTriggerClick(e: EventObject): void;
		reset(): void;
		select(index: number, scrollIntoView?: boolean): void;
		selectByValue(value: string, scrollIntoView?: boolean): void;

		getRawValue(): string;
	}
	export interface ITimeField extends IComboBox {
		altFormats?: string;
		format?: string;
		increment?: number;
		invalidText?: string;
		maxText?: string;
		maxValue?: Date | string;
		minText?: string;
		minValue?: Date | string;
		mode?: 'remote' | 'local';
		triggerAction?: 'query' | 'all';
		typeAhead?: boolean;
	}
	export class TimeField extends ComboBox {
		/**
		 * Provides a time input field with a time dropdown and automatic time validation.
		 * @param config
		 * @example
		 * new Ext.form.TimeField({
		 *	minValue: '9:00 AM',
		 *	maxValue: '6:00 PM',
		 *	increment: 30
		 * });
		 */
		constructor(config?: ITimeField);
		/**
		 * Returns the currently selected field value or empty string if no value is set.
		 */
		getValue(): string;
		/**
		 * Replaces any existing maxValue with the new time and refreshes the store.
		 * @param value The selected value
		 */
		setMaxValue(value: Date | string): this;
		/**
		 * Replaces any existing minValue with the new time and refreshes the store.
		 * @param value The minimum time that can be selected
		 */
		setMinValue(value: Date | string): this;
		/**
		 * Sets the specified value into the field. If the value finds a match, the corresponding record text will be displayed in the field. If the value does not match the data value of an existing item, and the valueNotFoundText config option is defined, it will be displayed as the default field text. Otherwise the field will be blank (although the value will still be set).
		 * @param value The value to match
		 */
		setValue(value: string): this;
	}
	export interface IHtmlEditor extends IField {
		createLinkText?: string;
		defaultLinkValue?: string;
		defaultValue?: string;
		enableAlignments?: boolean;
		enableColors?: boolean;
		enableFont?: boolean;
		enableFontSize?: boolean;
		enableFormat?: boolean;
		enableLinks?: boolean;
		enableLists?: boolean;
		enableSourceEdit?: boolean;
		fontFamilies?: string[];
		hideMode?: HideMode;
	}
	/**
	 * Provides a lightweight HTML Editor component.
	 */
	export class HtmlEditor extends Field {
		/**
		 * The default text for the create link prompt
		 */
		createLinkText: string;
		/**
		 * The default value for the create link prompt (defaults to http:/ /)
		 */
		defaultLinkValue: string;
		/**
		 * A default value to be put into the editor to resolve focus issues (defaults to &#160; (Non-breaking space) in Opera and IE6, &#8203; (Zero-width space) in all other browsers).
		 */
		defaultValue: string;
		/**
		 * Enable the left, center, right alignment buttons (defaults to true)
		 */
		enableAlignments: boolean;
		/**
		 * Enable the fore/highlight color buttons (defaults to true)
		 */
		enableColors: boolean;
		/**
		 * Enable font selection. Not available in Safari. (defaults to true)
		 */
		enableFont: boolean;
		/**
		 * Enable the increase/decrease font size buttons (defaults to true)
		 */
		enableFontSize: boolean;
		/**
		 * Enable the bold, italic and underline buttons (defaults to true)
		 */
		enableFormat: boolean;
		/**
		 * Enable the create link button. Not available in Safari. (defaults to true)
		 */
		enableLinks: boolean;
		/**
		 * Enable the bullet and numbered list buttons. Not available in Safari. (defaults to true)
		 */
		enableLists: boolean;
		/**
		 * Enable the switch to source edit button. Not available in Safari. (defaults to true)
		 */
		enableSourceEdit: boolean;
		/**
		 * An array of available font families
		 */
		fontFamilies: string[];
		/**
		 * How this component should be hidden. Supported values are 'visibility' (css visibility), 'offsets' (negative offset position) and 'display' (css display). The default of 'display' is generally preferred since items are automatically laid out when they are first shown (no sizing is done while hidden).
		 * @default 'offsets'
		 */
		hideMode: HideMode;
		/**
		 * Default font
		 * @default: 'tahoma'
		 */
		defaultFont: string;
		/**
		 * Object collection of toolbar tooltips for the buttons in the editor.
		 */
		buttonTips: object;

		constructor(cfg?: IHtmlEditor);

		/**
		 * Protected method that will not generally be called directly. If you need/want
		 * custom HTML cleanup, this is the method you should override.
		 * @param {String} html The HTML to be cleaned
		 * @return {String} The cleaned HTML
		 */
		cleanHtml(html: string): string;
		/**
		 * Executes a Midas editor command directly on the editor document.
		 * For visual commands, you should use {@link #relayCmd} instead.
		 * This should only be called after the editor is initialized.
		 * @param {String} cmd The Midas command
		 * @param {String/Boolean} value (optional) The value to pass to the command (defaults to null)
		 */
		execCmd(cmd: string, value?: string | boolean): void;
		/**
		 * Try to focus this component.
		 * @param selectText If applicable, true to also select the text in this component
		 * @param delay Delay the focus this number of milliseconds (true for 10 milliseconds)
		 */
		focus(selectText?: boolean, delay?: boolean | number): this;
		/**
		 * Protected method that will not generally be called directly. It
		 * is called when the editor initializes the iframe with HTML contents. Override this method if you
		 * want to change the initialization markup of the iframe (e.g. to add stylesheets).
		 *
		 * Note: IE8-Standards has unwanted scroller behavior, so the default meta tag forces IE7 compatibility
		 */
		protected getDocMarkup(): string;
		/**
		 * Returns the editor's toolbar. This is only available after the editor has been rendered.
		 * @return {Ext.Toolbar}
		 */
		getToolbar(): Toolbar;
		/**
		 * Returns the normalized data value (undefined or emptyText will be returned as ''). To return the raw value see getRawValue.
		 */
		getValue(): string;
		/**
		 * Inserts the passed text at the current cursor position. Note: the editor must be initialized and activated
		 * to insert text.
		 * @param {String} text
		 */
		insertAtCursor(text: string): void;
		/**
		* Protected method that will not generally be called directly. Pushes the value of the textarea
		* into the iframe editor.
		*/
		protected pushValue(): void;
		/**
		 * Executes a Midas editor command on the editor document and performs necessary focus and
		 * toolbar updates. This should only be called after the editor is initialized.
		 * @param {String} cmd The Midas command
		 * @param {String/Boolean} value (optional) The value to pass to the command (defaults to null)
		 */
		relayCmd(cmd: string, value?: string | boolean): void;
		/**
		 * Sets the read only state of this field.
		 * @param readOnly Whether the field should be read only.
		 */
		setReadOnly(readOnly: boolean): void;
		/**
		 * Sets a data value into the field and validates it. To set the value directly without validation see setRawValue.
		 * @param value The value to set
		 */
		setValue(value: string): this;
		/**
		* Protected method that will not generally be called directly. Syncs the contents
		* of the editor iframe with the textarea.
		*/
		protected syncValue(): void;
		/**
		 * Toggles the editor between standard and source edit mode.
		 * @param {Boolean} sourceEdit (optional) True for source edit, false for standard
		 */
		toggleSourceEdit(sourceEdit?: boolean): void;
		/**
		 * Protected method that will not generally be called directly. It triggers
		 * a toolbar update by reading the markup state of the current selection in the editor.
		 */
		protected updateToolbar(): void;
		/**
		 * The div that surrounds the text editor.
		 * It is created in the onRender method.
		 */
		protected wrap: Element;
		/**
		 * Returns the body of the Iframe editor
		 */
		protected getEditorBody(): HTMLElement;
		/**
		 * Returns the iframe document
		 */
		protected getDoc(): Document;
		protected deferFocus(): any;
		protected win: any;	//Should be a normal Window object, NOT Ext.Window, but can't reference it.
		protected initEditor(): void;
		protected createToolbar(editor: this): void;
		protected disableItems(value: boolean): void;
	}
	export interface ILabel extends IBoxComponent {
		forId?: string;
		html?: string;
		text?: string;
		columnWidth?: number;
	}
	/**
	 * Basic Label field.
	 */
	export class Label extends BoxComponent {
		readonly text: string;
		constructor(cfg?: ILabel);
		/**
		* Updates the label's innerHTML with the specified string.
		* @param {String} text The new label text
		* @param {Boolean} encode (optional) False to skip HTML-encoding the text when rendering it
		* to the label (defaults to true which encodes the value). This might be useful if you want to include
		* tags in the label's innerHTML rather than rendering them as string literals per the default logic.
		* @return {Label} this
		*/
		setText(text: string, encode?: boolean): this;
	}
	export interface IHidden extends IField {
		inputType?: string;
	}
	export class Hidden extends Field {
		constructor(cfg?: IHidden);
	}
}
declare namespace Ext.data {
	export interface INode extends util.IObservable {
		/**
		 * The id for this node. If one is not specified, one is generated.
		 */
		id?: string;
		/**
		 * true if this node is a leaf and does not have children
		 */
		leaf?: boolean;
		/**
		 * Index signature! Allows custom parameter on this object.
		 * Any custom parameters added to this object will be available in Node.attributes.
		 */
		[x: string]: any;
	}
	export class Node extends util.Observable {
		/**
		 * The id for this node
		 */
		id: string;
		/**
		 * true if this node is a leaf and does not have children
		 */
		leaf: boolean;
		/**
		 * The first direct child node of this node, or null if this node has no child nodes.
		 */
		firstChild: this;
		/**
		 * The attributes supplied for the node. You can use this property to access any custom attributes you supplied.
		 */
		attributes: any;
		/**
		 * All child nodes of this node.
		 */
		childNodes: this[];
		/**
		 * The last direct child node of this node, or null if this node has no child nodes.
		 */
		lastChild: this;
		/**
		 * The node immediately following this node in the tree, or null if there is no sibling node.
		 */
		nextSibling: this;
		/**
		 * The parent node for this node.
		 */
		parentNode: this;
		/**
		 * The node immediately preceding this node in the tree, or null if there is no sibling node.
		 */
		previousSibling: this;

		constructor(cfg?: INode);

		/**
		 * Insert node(s) as the last child node of this node.
		 * @param node The node or Array of nodes to append
		 * @returns The appended node if single append, or null if an array was passed
		 */
		appendChild(node: this[] | this): this;
		/**
		 * Bubbles up the tree from this node, calling the specified function with each node. The arguments to the function will be the args provided or the current node. If the function returns false at any point, the bubble is stopped.
		 * @param fn The function to call
		 * @param scope The scope (this reference) in which the function is executed. Defaults to the current Node.
		 * @param args The args to call the function with (default to passing the current Node)
		 */
		bubble(fn: () => void, scope?: object, ...args: any[]): void;
		/**
		 * Cascades down the tree from this node, calling the specified function with each node. The arguments to the function will be the args provided or the current node. If the function returns false at any point, the cascade is stopped on that branch.
		 * @param fn
		 * @param scope
		 * @param args
		 */
		cascade(fn: () => void, scope?: object, ...args: any[]): void;
		/**
		* Returns true if this node is an ancestor (at any point) of the passed node.
		* @param {Node} node
		* @return {Boolean}
		*/
		contains(node: this): boolean;
		/**
		* Destroys the node.
		*/
		destroy(silent?: boolean): void;
		/**
		* Interates the child nodes of this node, calling the specified function with each node. The arguments to the function
		* will be the args provided or the current node. If the function returns false at any point,
		* the iteration stops.
		* @param {Function} fn The function to call
		* @param {Object} scope (optional) The scope (this reference) in which the function is executed. Defaults to the current Node in the iteration.
		* @param {Array} args (optional) The args to call the function with (default to passing the current Node)
		*/
		eachChild(fn: (node: this) => boolean, scope?: object, ...args: any[]): void;
		/**
		* Finds the first child that has the attribute with the specified value.
		* @param {String} attribute The attribute name
		* @param {Mixed} value The value to search for
		* @param {Boolean} deep (Optional) True to search through nodes deeper than the immediate children
		* @return {Node} The found child or null if none was found
		*/
		findChild(attribute: string, value: any, deep: boolean): this;
		/**
		* Finds the first child by a custom function. The child matches if the function passed returns true.
		* @param {Function} fn A function which must return true if the passed Node is the required Node.
		* @param {Object} scope (optional) The scope (this reference) in which the function is executed. Defaults to the Node being tested.
		* @param {Boolean} deep (Optional) True to search through nodes deeper than the immediate children
		* @return {Node} The found child or null if none was found
		*/
		findChildBy(fn: (node: this) => boolean, scope?: object, deep?: boolean): this;
		/**
		* Returns depth of this node (the root node has a depth of 0)
		* @return {Number}
		*/
		getDepth(): number;
		/**
		* Returns the tree this node is in.
		* @return {Tree}
		*/
		getOwnerTree(): any;	//Returns a "Tree", cant find that type in the documentation. Untyped?
		/**
		* Returns the path for this node. The path can be used to expand or select this node programmatically.
		* @param {String} attr (optional) The attr to use for the path (defaults to the node's id)
		* @return {String} The path
		*/
		getPath(attr?: string): string;
		/**
		* Returns true if this node has one or more child nodes, else false.
		* @return {Boolean}
		*/
		hasChildNodes(): boolean;
		/**
		* Returns the index of a child node
		* @param {Node} node
		* @return {Number} The index of the node or -1 if it was not found
		*/
		indexOf(node: this): number;
		/**
		* Inserts the first node before the second node in this nodes childNodes collection.
		* @param {Node} node The node to insert
		* @param {Node} refNode The node to insert before (if null the node is appended)
		* @return {Node} The inserted node
		*/
		insertBefore(node: this, refNode: this): this;
		/**
	 * Returns true if the passed node is an ancestor (at any point) of this node.
	 * @param {Node} node
	 * @return {Boolean}
	 */
		isAncestor(node: this): boolean;
		/**
	 * Returns true if this node has one or more child nodes, or if the expandable
	 * node attribute is explicitly specified as true (see {@link #attributes}), otherwise returns false.
	 * @return {Boolean}
	 */
		isExpandable(): boolean;
		/**
	 * Returns true if this node is the first child of its parent
	 * @return {Boolean}
	 */
		isFirst(): boolean;
		/**
	 * Returns true if this node is the last child of its parent
	 * @return {Boolean}
	 */
		isLast(): boolean;
		/**
	 * Returns true if this node is a leaf
	 * @return {Boolean}
	 */
		isLeaf(): boolean;
		/**
	 * Returns the child node at the specified index.
	 * @param {Number} index
	 * @return {Node}
	 */
		item(index: number): this;
		/**
	 * Removes this node from its parent
	 * @param {Boolean} destroy true to destroy the node upon removal. Defaults to false.
	 * @return {Node} this
	 */
		remove(destroy?: boolean): this;
		/**
	 * Removes all child nodes from this node.
	 * @param {Boolean} destroy true to destroy the node upon removal. Defaults to false.
	 * @return {Node} this
	 */
		removeAll(destroy?: boolean): this;
		/**
		 * Removes a child node from this node.
		 * @param node The node to remove
		 * @param destroy true to destroy the node upon removal. Defaults to false.
		 * @returns The removed node
		 */
		removeChild(node: this, destroy?: boolean): this;
		/**
	 * Replaces one child node in this node with another.
	 * @param {Node} newChild The replacement node
	 * @param {Node} oldChild The node to replace
	 * @return {Node} The replaced node
	 */
		replaceChild(newChild: this, oldChild: this): this;
		/**
	 * Changes the id of this node.
	 * @param {String} id The new id for the node.
	 */
		setId(id: string): void;
		/**
	 * Sorts this nodes children using the supplied sort function.
	 * @param {Function} fn A function which, when passed two Nodes, returns -1, 0 or 1 depending upon required sort order.
	 * @param {Object} scope (optional)The scope (this reference) in which the function is executed. Defaults to the browser window.
	 */
		sort(fn: (a: this, b: this) => number, scope?: object): void;
	}
	export class DataProxy extends util.Observable {
		constructor(cfg?: any);
	}
	export type Action = 'create' | 'read' | 'update' | 'delete';
	export class DataWriter { }
	interface SortInfo {
		field: string;
		direction: Direction;
	}
	export interface IStore extends util.IObservable {
		autoDestroy?: boolean;
		autoLoad?: boolean | object;
		autoSave?: boolean;
		baseParams?: object;
		batch?: boolean;
		data?: any[];
		defaultParamNames?: object;
		paramNames?: object;
		proxy?: DataProxy;
		pruneModifiedRecords?: boolean;
		reader?: DataReader;
		remoteSort?: boolean;
		restful?: boolean;
		sortInfo?: SortInfo;
		storeId?: string;
		url?: string;
		writer?: DataWriter;
		fields?: IField[] | string[];
		root?: string;
		idProperty?: string;
		loadData?: Function;
	}
	export class Store extends util.Observable {
		sortInfo: SortInfo;

		constructor(cfg?: IStore);
		/**
		 * Get the Record at the specified index.
		 * @param {Number} index The index of the Record to find.
		 * @return {Ext.data.Record} The Record at the passed index. Returns undefined if not found.
		 */
		getAt<T>(index: number): Record<T>;
		/**
		 * Loads the Record cache from the configured {@link #proxy} using the configured {@link #reader}.
		 * @param {Object} options An object containing properties which control loading options:
		 * @return {Boolean} If the developer provided {@link #beforeload} event handler returns
		 * false, the load call will abort and will return false; otherwise will return true.
		 */
		load(options?: object): boolean;
		/**
		 * Loads data from a passed data block and fires the {@link #load} event. A {@link Ext.data.Reader Reader}
		 * which understands the format of the data must have been configured in the constructor.
		 * @param {Object} data The data block from which to read the Records.
		 * @param {Boolean} append (Optional) true to append the new Records rather the default to replace
		 * the existing cache.
		 */
		loadData<T>(object: T, append?: boolean): void;
		/**
		 * Get the Record with the specified id.
		 * @param {String} id The id of the Record to find.
		 * @return {Ext.data.Record} The Record with the passed id. Returns undefined if not found.
		 */
		getById<T>(id: string): Record<T>;
		/**
		 * Remove Records from the Store and fires the {@link #remove} event.
		 * @param {Ext.data.Record/Ext.data.Record[]} record The record object or array of records to remove from the cache.
		 */
		remove<T>(record: Record<T>): void;
		/**
		 * Remove all Records from the Store and fires the {@link #clear} event.
		 * @param {Boolean} silent [false] Defaults to false.  Set true to not fire clear event.
		 */
		removeAll(silent?: boolean): void;
		/**
		 * This should be private.
		 */
		clearData(): void;
		/**
		 * The {@link Ext.data.Record Record} constructor as supplied to (or created by) the
		 * {@link Ext.data.DataReader Reader}. Read-only.
		 * This property may be used to create new Records of the type held in this Store
		*/
		readonly recordType: RecordConstructor;
		/**
		 * Add Records to the Store and fires the {@link #add} event.  To add Records
		 * to the store from a remote source use {@link #load}({add:true}).
		 * See also {@link #recordType} and {@link #insert}.
		 * @param {Ext.data.Record[]} records An Array of Ext.data.Record objects
		 * to add to the cache. See {@link #recordType}.
		 */
		add<T = any>(records: Record<T>[]): void;
		add<T = any>(record: Record<T>): void;
		/**
		 * Gets all {@link Ext.data.Record records} modified since the last commit.  Modified records are
		 * persisted across load operations (e.g., during paging). Note: deleted records are not
		 * included.  See also {@link #pruneModifiedRecords} and
		 * {@link Ext.data.Record}{@link Ext.data.Record#markDirty markDirty}..
		 * @return {Ext.data.Record[]} An array of {@link Ext.data.Record Records} containing outstanding
		 * modifications.  To obtain modified fields within a modified record see
		 *{@link Ext.data.Record}{@link Ext.data.Record#modified modified}..
		 */
		getModifiedRecords<T>(): Record<T>[];
		/**
		 * Calls the specified function for each of the {@link Ext.data.Record Records} in the cache.
		 * @param {Function} fn The function to call. The {@link Ext.data.Record Record} is passed as the first parameter.
		 * Returning false aborts and exits the iteration.
		 * @param {Object} scope (optional) The scope (this reference) in which the function is executed.
		 * Defaults to the current {@link Ext.data.Record Record} in the iteration.
		 */
		each(fn: Function, scope?: object): void;
		/**
		* Reloads the Record cache from the configured Proxy using the configured
		* {@link Ext.data.Reader Reader} and the options from the last load operation
		* performed.
		* Note: see the Important note in {@link #load}.
		* @param {Object} options (optional) An Object containing
		* {@link #load loading options} which may override the {@link #lastOptions options}
		* used in the last {@link #load} operation. See {@link #load} for details
		* (defaults to null, in which case the {@link #lastOptions} are
		* used).
		* To add new params to the existing params:
			lastOptions = myStore.lastOptions;
			Ext.apply(lastOptions.params, {
				myNewParam: true
			});
			myStore.reload(lastOptions);
		*
		*/
		reload(options?: object): void;
		/**
		 * (Local sort only) Inserts the passed Record into the Store at the index where it
		 * should go based on the current sort information.
		 * @param {Ext.data.Record} record
		 */
		addSorted<T>(record: Record<T>): void;
		/**
		 * Commit all Records with {@link #getModifiedRecords outstanding changes}. To handle updates for changes,
		 * subscribe to the Store's {@link #update update event}, and perform updating when the third parameter is
		 * Ext.data.Record.COMMIT.
		 */
		commitChanges(): void;
		/**
		 * Sort the Records.
		 * If remote sorting is used, the sort is performed on the server, and the cache is reloaded. If local
		 * sorting is used, the cache is sorted internally. See also {@link #remoteSort} and {@link #paramNames}.
		 * This function accepts two call signatures - pass in a field name as the first argument to sort on a single
		 * field, or pass in an array of sort configuration objects to sort by multiple fields.
		 * Single sort example:
		 * store.sort('name', 'ASC');
		 * Multi sort example:
		 * store.sort([
		 *   {
		 *     field    : 'name',
		 *     direction: 'ASC'
		 *   },
		 *   {
		 *     field    : 'salary',
		 *     direction: 'DESC'
		 *   }
		 * ], 'ASC');
		 * In this second form, the sort configs are applied in order, with later sorters sorting within earlier sorters' results.
		 * For example, if two records with the same name are present they will also be sorted by salary if given the sort configs
		 * above. Any number of sort configs can be added.
		 * @param {String/Array} fieldName The name of the field to sort by, or an array of ordered sort configs
		 * @param {String} dir (optional) The sort order, 'ASC' or 'DESC' (case-sensitive, defaults to <tt>'ASC'</tt>)
		 */
		sort(fieldName: string, dir?: Direction): void;
		sort(sortField: ({
			field: string;
			direction: Direction;
		} | Direction)[]): void;
		/**
		 * Set the value for a property name in this store's {@link #baseParams}.
		 * @param {String} name Name of the property to assign
		 * @param {Mixed} value Value to assign the <tt>name</tt>d property
		 **/
		setBaseParam(name: string, value: any): void;
		/**
		 * Revert to a view of the Record cache with no filtering applied.
		 * @param {Boolean} suppressEvent If true the filter is cleared silently without firing the {@link #datachanged} event.
		 */
		clearFilter(suppressEvent?: boolean): void;
		/**
		 * Query the cached records in this Store using a filtering function. The specified function
		 * will be called with each record in this Store. If the function returns <tt>true</tt> the record is
		 * included in the results.
		 * @param {Function} fn The function to be called. It will be passed the following parameters:<ul>
		 * <li><b>record : Ext.data.Record<p class="sub-desc">The {@link Ext.data.Record record}
		 * to test for filtering. Access field values using {@link Ext.data.Record#get}.
		 * <li><b>id : Object<p class="sub-desc">The ID of the Record passed.
		 * </ul>
		 * @param {Object} scope (optional) The scope (this reference) in which the function is executed. Defaults to this Store.
		 * @return {MixedCollection} Returns an Ext.util.MixedCollection of the matched records
		 **/
		queryBy(fn: (record: Record<any>, scope?: object) => boolean | any, scope?: object): util.MixedCollection<any>;
		queryBy<T>(fn: (record: Record<T>, scope?: object) => boolean | any, scope?: object): util.MixedCollection<T>;
		/**
		 * Filter by a function. The specified function will be called for each
		 * Record in this Store. If the function returns <tt>true</tt> the Record is included,
		 * otherwise it is filtered out.
		 * @param {Function} fn The function to be called. It will be passed the following parameters:<ul>
		 * <li><b>record : Ext.data.Record<p class="sub-desc">The {@link Ext.data.Record record}
		 * to test for filtering. Access field values using {@link Ext.data.Record#get}.
		 * <li><b>id : Object<p class="sub-desc">The ID of the Record passed.
		 * </ul>
		 * @param {Object} scope (optional) The scope (this reference) in which the function is executed. Defaults to this Store.
		 */
		filterBy(fn: (record: Record<any>, scope?: object) => boolean, scope?: object): void;
		filterBy<T>(fn: (record: Record<T>, scope?: object) => boolean, scope?: object): void;
		/**
		 * Gets the number of cached records.
		 * If using paging, this may not be the total size of the dataset. If the data object
		 * used by the Reader contains the dataset size, then the {@link #getTotalCount} function returns
		 * the dataset size.  <b>Note: see the Important note in {@link #load}.
		 * @return {Number} The number of Records in the Store's cache.
		 */
		getCount(): number;
		indexOf(record: Record<any>): number;
		/**
		 * Finds the index of the first matching Record in this store by a specific field value.
		 * @param {String} fieldName The name of the Record field to test.
		 * @param {Mixed} value The value to match the field against.
		 * @param {Number} startIndex (optional) The index to start searching at
		 * @return {Number} The matched index or -1
		 */
		findExact(fieldName: string, value: any, startIndex?: number): number;
		getRange<T = any>(startIndex?: number, endIndex?: number): Ext.data.Record<T>[];
	}
	export interface IArrayReader extends IStore {
		id?: number;
		idIndex?: number;
	}
	export class ArrayReader extends JsonReader {
		constructor(cfg?: IArrayReader);
		/**
		 * Create a data block containing Ext.data.Records from an Array.
		 * @param o An Array of row objects which represents the dataset.
		 * @returns A data block which is used by an Ext.data.Store object as a cache of Ext.data.Records.
		 */
		readRecords<T>(o: T[]): T[];
	}
	export class ArrayStore extends Store {
		constructor(cfg?: IStore & IArrayReader);
	}
	export class JsonStore extends Store { }
	export class SimpleStore extends ArrayStore {
		constructor(cfg?: any);
	}
	export interface IConnection extends util.IObservable { }
	interface IResponseObject {
		responseText: string;
		responseXML: XMLDocument;
		status: number;
		statusText: string;
	}
	interface IRequest {
		url?: string | Function;
		params?: any;
		method?: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'OPTION' | 'HEAD' | 'CONNECT' | 'TRACE' | 'PATCH';
		callback?: (options: object, success: boolean, response: XMLHttpRequest) => void;
		success?: (response: XMLHttpRequest, options: object) => void;
		failure?: (response: XMLHttpRequest, options: object) => void;
		scope?: object;
		timeout?: number;
		form?: Element | HTMLElement | string;
		isUpload?: boolean;
		headers?: object;
		xmlData?: object;
		jsonData?: object;
		disableCaching?: boolean;
		useDefaultXhrHeader?: boolean;
		defaultHeaders?: boolean;
	}
	export class Connection extends util.Observable {
		static abort(transactionId?: number): void;
		static isLoading(transactionId?: number): boolean;
		static request(options: IRequest): number;
	}
	/**
	 * Defines the default sorting (casting?) comparison functions used when sorting data.
	 */
	export class SortTypes {
		static asDate: Function;
		static asFloat: Function;
		static asInt: Function;
		static asText: Function;
		static asUCString: Function;
		static asUCText: Function;
		static none: Function;
		static stripTagsRE: RegExp;
	}
	/**
	 * This is s static class containing the system-supplied data types which may be given to a Field.
	 * @static
	 */
	export class Types {
		static AUTO: Types;
		static BOOL: Types;
		static BOOLEAN: Types;
		static DATE: Types;
		static FLOAT: Types;
		static INT: Types;
		static INTEGER: Types;
		static NUMBER: Types;
		static STRING: Types;
	}
	type FieldType = 'auto' | 'string' | 'int' | 'float' | 'boolean' | 'date';
	export interface IField {
		name: string;
		allowBlank?: boolean;
		convert?: Function;
		dateFormat?: string;
		defaultValue?: any;
		mapping?: string | number;
		sortDir?: Direction;
		sortType?: (value: any) => number | string;
		type?: FieldType | Types;
		useNull?: boolean;
	}
	/**
	 * This class encapsulates the field definition information specified in the field definition objects passed to Ext.data.Record.create.
	 * Developers do not need to instantiate this class. Instances are created by Ext.data.Record.create and cached in the fields property of the created Record constructor's prototype.
	 */
	export class Field {
		constructor(cfg?: IField);
	}
	export interface IRecord { }
	export abstract class Record<T = any> {
		data: T;
		json: T;
		dirty: boolean;
		readonly fields: util.MixedCollection<any>;
		id: string;
		modified?: T;
		node: XMLDocument;
		phantom: boolean;
		store: Store;
		static COMMIT: string;
		static REMOVE: string;
		static REJECT: string;
		static EDIT: string;

		constructor(data?: T, id?: string);

		beginEdit(): void;
		cancelEdit(): void;
		commit(silent?: boolean): void;
		copy(id?: string): this;

		static create(o: Field[] | IField[]): Function;

		endEdit(): void;
		/**
		 * Get the value of the named field.
		 * @param name The name of the field to get the value of.
		 * @returns The value of the field.
		 */
		get<U = string>(name: string): U;
		getChanges(): object;
		isModified(fieldName: string): boolean;
		isValid(): boolean;
		markDirty(): void;
		reject(silent?: boolean): void;
		set(name: string, value: any): void;
	}

	/**
	 * Abstract base class for reading structured data from a data source and converting it into an object containing Ext.data.Record objects and metadata for use by an Ext.data.Store. This class is intended to be extended and should not be created directly. For existing implementations, see Ext.data.ArrayReader, Ext.data.JsonReader and Ext.data.XmlReader.
	 */
	export abstract class DataReader {
		fields: any[] | object;
		messageProperty: string;
		meta: any;

		constructor(meta?: object, recordType?: any[] | object);

		buildExtractors(): any;
		extractValues(): any;
		getId: (...args: any[]) => any;
		getMessage: (...args: any[]) => any;
		getRoot: (...args: any[]) => any;
		getSuccess: (...args: any[]) => any;
		getTotal: (...args: any[]) => any;
		isData(data: object): boolean;
		realize<T>(record: Record<T> | Record<T>[], data: object | object[]): void;
		update<T>(rs: Record<T> | Record<T>[]): void;

		protected extractData<T>(dataRoot: any, returnRecords: boolean): T[];
	}
	export class JsonReader extends DataReader {
		readRecords(o: any[]): any[];
	}
	export interface IJsonWriter {
		encode?: boolean;
		encodeDelete?: boolean;
	}
	export class JsonWriter extends DataWriter {
		constructor(meta: IJsonWriter, recordType: Record<any>);
	}
	export class XmlReader extends DataReader { }
	export class HttpProxy extends DataProxy {
		constructor(conn?: IConnection | IRequest);
	}
	/**
	 * Ext.data.Response Experimental. Do not use directly.
	 */
	export class Response {
		action: string;
		data: any[] | object;
		message: string;
		raw: object;
		records: Ext.data.Record<any>[] | Ext.data.Record<any>;
		success: boolean;
		constructor(config: ResponseConfig);
	}
	export interface ResponseConfig {
		action?: string;
		data?: any[];
		message?: string;
		raw?: object;
		records?: Ext.data.Record<any>[];
		success?: boolean;
	}
}
declare namespace Ext.data.JsonReader {
	/**
	 * Error class for JsonReader
	 */
	export class Error extends Ext.Error {
		constructor(message: string, ...arg: object[]);
	}
}
declare namespace Ext.layout {
	export interface IContainerLayout {
		extraCls?: string;
		renderHidden?: boolean;
	}
	export class FitLayout extends ContainerLayout {
		getLayoutTargetSize(): { width: number; height: number; };
	}
	export class AccordionLayout extends FitLayout {
		protected fill: boolean;
		protected setItemSize(item: object, size: object): void;
	}
	export class CardLayout extends FitLayout {
		setActiveItem(item: string | number): void;
	}
	export type Pack = 'start' | 'center' | 'end';
	export abstract class BoxLayout extends Ext.layout.ContainerLayout {
		protected scrollOffset: number;
		protected padding: Margins;
		protected pack: Pack;
		protected align: 'stretch' | 'stretchmax' | 'middle';
		getLayoutTargetSize(): Size;
		updateChildBoxes(boxes?: any[]): void;
	}
	export class HBoxLayout extends Ext.layout.BoxLayout {

	}
	/**
	 * This class is intended to be extended or created via the layout configuration property. See Ext.Container.layout for additional details.
	 */
	export abstract class ContainerLayout extends Object {
		fieldTpl: Ext.Template;
		constructor(config?: IContainerLayout);
		/**
		 * Parses a number or string representing margin sizes into an object. Supports CSS-style margin declarations (e.g. 10, "10", "10 10", "10 10 10" and "10 10 10 10" are all valid options and would return the same result)
		 * @param v The encoded margins
		 * @returns An object with margin sizes for top, right, bottom and left
		 */
		parseMargins(v: number | string): object;
		/**
		 * Placeholder for the derived layouts
		 */
		abstract getLayoutTargetSize(): Size;
		protected readonly container: Container;
		/**
		 * Workaround for how IE measures autoWidth elements. It prefers bottom-up measurements whereas other browser prefer top-down. We will hide all target child elements before we measure and put them back to get an accurate measurement.
		 * @param target
		 * @param viewFlag
		 */
		protected IEMeasureHack(target: object, viewFlag: object): void;
		/**
		 *
		 * @param c
		 */
		protected afterRemove(c: object): void;
		/**
		 * Applies extraCls and hides the item if renderHidden is true
		 * @param c
		 */
		protected configureItem(c: object): void;
		/**
		 * private. Get all rendered items to lay out.
		 * @param ct
		 */
		protected getRenderedItems(ct: object): Panel[];
		/**
		 *
		 * @param c
		 * @param target
		 */
		protected isValidParent(c: object, target: object): void;
		/**
		 *
		 */
		protected layout(): void;
		/**
		 *
		 * @param ct
		 * @param target
		 */
		protected onLayout(ct: Ext.BoxComponent, target: Ext.Element): void;
		/**
		 *
		 * @param c
		 */
		protected onRemove(c: object): void;
		/**
		 *
		 */
		protected onResize(): void;
		/**
		 *
		 * @param ct
		 * @param target
		 */
		protected renderAll(ct: object, target: object): void;
		/**
		 * Renders the given Component into the target Element. If the Component is already rendered, it is moved to the provided target instead.
		 * @param c The Component to render
		 * @param position The position within the target to render the item to
		 * @param target The target Element
		 */
		protected renderItem(c: Component, position: number, target: Element): void;
		/**
		 *
		 */
		protected runLayout(): void;
		/**
		 *
		 * @param ct
		 */
		protected setContainer(ct: object): void;

		/**
		 * Destroys this layout. This is a template method that is empty by default, but should be implemented by subclasses that require explicit destruction to purge event handlers or remove DOM nodes.
		 */
		protected destroy(): void;
	}
}

declare namespace Ext.tree {
	export class TreeDragZone extends dd.DragZone { }
	export class TreeDropZone extends dd.DropZone { }
	export class TreeNodeUI {
		constructor(node?: any);
		addClass(className: string): void;
		getAnchor(): HTMLLinkElement;
		getEl(): HTMLLIElement;
		getIconEl(): HTMLImageElement;
		getTextEl(): Node;
		hide(): void;
		isChecked(): boolean;
		removeClass(className: string): void;
		show(): void;
		toggleCheck(value?: boolean): void;
	}
	export interface ITreeNode extends data.INode {
		allowChildren?: boolean;
		allowDrag?: boolean;
		allowDrop?: boolean;
		checked?: boolean;
		cls?: string;
		disabled?: boolean;
		draggable?: boolean;
		editable?: boolean;
		expandable?: boolean;
		expanded?: boolean;
		hidden?: boolean;
		href?: string;
		hrefTarget?: string;
		icon?: string;
		iconCls?: string;
		isTarget?: boolean;
		qtip?: string;
		qtipCfg?: string;
		singleClickExpand?: boolean;
		text?: string;
		uiProvider?: Function;
	}
	export class TreeNode extends data.Node {
		readonly disabled: boolean;
		readonly hidden: boolean;
		readonly text: string;
		readonly ui: TreeNodeUI;
		attributes: any;
		allowChildren: boolean;
		allowDrag: boolean;
		allowDrop: boolean;
		checked: boolean;
		cls: string;
		draggable: boolean;
		editable: boolean;
		expandable: boolean;
		expanded: boolean;
		href: string;
		hrefTarget: string;
		icon: string;
		iconCls: string;
		isTarget: boolean;
		qtip: string;
		qtipCfg: string;
		singleClickExpand: boolean;

		constructor(name: string);
		constructor(cfg?: ITreeNode);

		/**
		 * Insert node(s) as the last child node of this node.
		 * @param node The node or Array of nodes to append
		 * @returns The appended node if single append, or null if an array was passed
		 */
		appendChild(node: this): this;
		/**
		 * Collapse this node.
		 * @param deep True to collapse all children as well
		 * @param anim false to cancel the default animation
		 * @param callback A callback to be called when expanding this node completes (does not wait for deep expand to complete). Called with 1 parameter, this node.
		 * @param scope The scope (this reference) in which the callback is executed. Defaults to this TreeNode.
		 */
		collapse(deep?: boolean, anim?: boolean, callback?: (node: this) => void, scope?: object): void;
		/**
		 * Collapse all child nodes
		 * @param deep true if the child nodes should also collapse their child nodes
		 */
		collapseChildNodes(deep?: boolean): void;
		/**
		 * Destroys the node.
		 * @param silent
		 */
		destroy(silent?: boolean): void;
		/**
		 * Disables this node
		 */
		disable(): void;
		/**
		 * Enables this node
		 */
		enable(): void;
		/**
		 * Ensures all parent nodes are expanded, and if necessary, scrolls the node into view.
		 * @param callback A function to call when the node has been made visible.
		 * @param scope The scope (this reference) in which the callback is executed. Defaults to this TreeNode.
		 */
		ensureVisible(callback?: () => void, scope?: object): void;
		/**
		 * Expand this node.
		 * @param deep True to expand all children as well
		 * @param anim false to cancel the default animation
		 * @param callback A callback to be called when expanding this node completes (does not wait for deep expand to complete). Called with 1 parameter, this node.
		 * @param scope The scope (this reference) in which the callback is executed. Defaults to this TreeNode.
		 */
		expand(deep?: boolean, anim?: boolean, callback?: (node: TreeNode) => void, scope?: object): void;
		/**
		 * Expand all child nodes
		 * @param deep true if the child nodes should also expand their child nodes
		 */
		expandChildNodes(deep?: boolean): void;
		/**
		 * Returns the UI object for this node.
		 * @returns The object which is providing the user interface for this tree node. Unless otherwise specified in the uiProvider, this will be an instance of Ext.tree.TreeNodeUI
		 */
		getUI(): TreeNodeUI;
		/**
		 * Inserts the first node before the second node in this nodes childNodes collection.
		 * @param node The node to insert
		 * @param refNode The node to insert before (if null the node is appended)
		 */
		insertBefore(node: this, refNode: this): this;
		/**
		 * Returns true if this node is expanded
		 */
		isExpanded(): boolean;
		/**
		 * Returns true if this node is selected
		 */
		isSelected(): boolean;
		/**
		 * Removes a child node from this node.
		 * @param node The node to remove
		 * @param destroy true to destroy the node upon removal. Defaults to false.
		 * @returns The removed node
		 */
		removeChild(node: this, destroy?: boolean): this;
		/**
		 * Triggers selection of this node
		 */
		select(): void;
		/**
		 * Sets the class on this node.
		 * @param cls
		 */
		setCls(cls: string): void;
		/**
		 * Sets the href for the node.
		 * @param href The href to set
		 * @param target target The target of the href
		 */
		setHref(href: string, target?: string): void;
		/**
		 * Sets the icon class for this node.
		 * @param icon
		 */
		setIcon(icon: string): void;
		/**
		 * Sets the icon class for this node.
		 * @param cls
		 */
		setIconCls(cls: string): void;
		/**
		 * Sets the text for this node
		 * @param text
		 */
		setText(text: string): void;
		/**
		 * Sets the tooltip for this node.
		 * @param tip The text for the tip
		 * @param title The title for the tip
		 */
		setTooltip(tip: string, title?: string): void;
		/**
		 * Sorts this nodes children using the supplied sort function.
		 * @param fn A function which, when passed two Nodes, returns -1, 0 or 1 depending upon required sort order.
		 * @param scope The scope (this reference) in which the function is executed. Defaults to the browser window.
		 */
		sort(fn: (a: this, b: this) => number, scope?: object): void;
		/**
		 * Toggles expanded/collapsed state of the node
		 */
		toggle(): void;
		/**
		 * Triggers deselection of this node
		 * @param silent True to stop selection change events from firing.
		 */
		unselect(silent?: boolean): void;
	}
	interface TreeNodeSelectionModel {
		clearSelections(silent: boolean): any;
		isSelected(TreeNode): boolean;
		select(node: TreeNode): TreeNode;
		unselect(node: TreeNode): TreeNode;
	}
	export class DefaultSelectionModel extends util.Observable implements TreeNodeSelectionModel {
		constructor(config?: util.IObservable);
		clearSelections(silent: boolean): any;
		getSelectedNode(): TreeNode;
		isSelected(TreeNode): boolean;
		select(node: TreeNode): TreeNode;
		selectNext(s: TreeNode): TreeNode;
		selectPrevious(s: TreeNode): TreeNode;
		unselect(node: TreeNode, silent?: boolean): TreeNode;
	}
	export class MultiSelectionModel extends util.Observable implements TreeNodeSelectionModel {
		constructor(config?: util.IObservable);
		clearSelections(supressEvent: boolean): any;
		getSelectedNodes(): TreeNode[];
		isSelected(TreeNode): boolean;
		select(node: TreeNode, e?: EventObject, keepExisting?: boolean): TreeNode;
		unselect(node: TreeNode): TreeNode;
	}
	export class TreeLoader extends util.Observable {

	}
	export interface ITreePanel extends IPanel {
		root?: TreeNode;
		selModel?: TreeNodeSelectionModel;
		rootVisible?: boolean;
		animate?: boolean;
		useArrows?: boolean;
	}
	/**
	 * The TreePanel provides tree-structured UI representation of tree-structured data.
	 */
	export class TreePanel extends Panel {
		readonly dragZone: TreeDragZone;
		readonly dropZone: TreeDropZone;
		readonly root: TreeNode;

		constructor(cfg?: ITreePanel);

		/**
		* Collapse all nodes
		*/
		collapseAll(): void;
		/**
		* Expand all nodes
		*/
		expandAll(): void;
		/**
		* Expands a specified path in this TreePanel. A path can be retrieved from a node with {@link Ext.data.Node#getPath}
		* @param {String} path
		* @param {String} attr (optional) The attribute used in the path (see {@link Ext.data.Node#getPath} for more info)
		* @param {Function} callback (optional) The callback to call when the expand is complete. The callback will be called with
		* (bSuccess, oLastNode) where bSuccess is if the expand was successful and oLastNode is the last node that was expanded.
		*/
		expandPath(path: string, attr?: string, callback?: Function): void;
		/**
		* Retrieve an array of checked nodes, or an array of a specific attribute of checked nodes (e.g. 'id')
		* @param {String} attribute (optional) Defaults to null (return the actual nodes)
		* @param {TreeNode} startNode (optional) The node to start from, defaults to the root
		* @return {Array}
		*/
		getChecked(attribute?: string, startNode?: TreeNode): TreeNode[];
		/**
		* Returns the default {@link Ext.tree.TreeLoader} for this TreePanel.
		* @return {Ext.tree.TreeLoader} The TreeLoader for this TreePanel.
		*/
		getLoader(): TreeLoader;
		/**
		* Gets a node in this tree by its id
		* @param {String} id
		* @return {Node}
		*/
		getNodeById(id: string): TreeNode;
		/**
		* Returns this root node for this tree
		* @return {Node}
		*/
		getRootNode(): TreeNode;
		/**
		* Returns the selection model used by this TreePanel.
		* @return {TreeSelectionModel} The selection model used by this TreePanel
		*/
		getSelectionModel(): DefaultSelectionModel;
		getSelectionModel<T>(): T;
		/**
		* Returns the underlying Element for this tree
		* @return {Ext.Element} The Element
		*/
		getTreeEl(): Element;
		/**
		* Selects the node in this tree at the specified path. A path can be retrieved from a node with {@link Ext.data.Node#getPath}
		* @param {String} path
		* @param {String} attr (optional) The attribute used in the path (see {@link Ext.data.Node#getPath} for more info)
		* @param {Function} callback (optional) The callback to call when the selection is complete. The callback will be called with
		* (bSuccess, oSelNode) where bSuccess is if the selection was successful and oSelNode is the selected node.
		*/
		selectPath(ath: string, attr?: string, callback?: Function): void;
		/**
		* Sets the root node for this tree. If the TreePanel has already rendered a root node, the
		* previous root node (and all of its descendants) are destroyed before the new root node is rendered.
		* @param {Node} node
		* @return {Node}
		*/
		setRootNode(node: TreeNode): TreeNode;
	}
}
declare namespace Ext.grid {
	export interface IEditorGridPanel extends IGridPanel {
		autoEncode?: any;
		clicksToEdit?: any;
		forceValidation?: any;
		selModel?: any;
		sm?: any;
		trackMouseOver?: any;
	}
	export class EditorGridPanel extends GridPanel {

	}
	export class AbstractSelectionModel extends util.Observable {
		readonly grid: GridPanel;
		isLocked: boolean;
		lock(): void;
		unlock(): void;
	}
	export class RowSelectionModel extends AbstractSelectionModel {

		constructor(cfg?: any);

		clearSelections(fast?: boolean): void;
		deselectRange(startRow: number, endRow: number): void;
		deselectRow(row: number, preventViewNotify: boolean): void;
		each(fn: Function, scope?: object): boolean;
		getCount(): number;
		getSelected<T>(): data.Record<T>;
		getSelections<T>(): data.Record<T>[];
		hasNext(): boolean;
		hasPrevious(): boolean;
		hasSelection(): boolean;
		isIdSelected(id: string): boolean;
		isSelected(index: number): boolean;
		selectAll(): void;
		selectFirstRow(): void;
		selectLastRow(keepExisting?: boolean): void;
		selectNext(keepExisting?: boolean): boolean;
		selectPrevious(keepExisting?: boolean): boolean;
		selectRange(startRow: number, endRow: number, keepExisting?: boolean): void;
		selectRecords<T>(records: data.Record<T>[], keepExisting?: boolean): void;
		selectRow(row: number, keepExisting?: boolean, preventViewNotify?: boolean): void;
		selectRows(rows: number[], keepExisting?: boolean): void;
	}
	export interface IColumn extends util.IObservable {
		header?: string;
		width?: number;
		fixed?: boolean;
		sortable?: boolean;
		dataIndex?: string;
		renderer?: Function | string | object;
	}
	export class Column extends util.Observable {
		align: string;
		css: string;
		dataIndex: string;
		editable: boolean;
		editor: form.Field;
		emptyGroupText: string;
		fixed: boolean;
		groupName: string;
		groupRenderer: Function;
		groupable: boolean;
		header: string;
		hidden: boolean;
		hideable: boolean;
		id: string;
		menuDisabled: boolean;
		renderer(v: any): string;
		resizable: boolean;
		scope: object;
		sortable: boolean;
		tooltip: string;
		width: number;
		xtype: string;
		constructor(cfg?: IColumn);

	}
	export interface IGridPanel extends IPanel {
		view?: GridView;
		viewConfig?: IGridView;
		store?: data.Store;
		columns?: any[];
		stripeRows?: boolean;
		colModel?: any;
		selModel?: any;
		loadMask?: boolean | ILoadMask | LoadMask;
		cm?: any;
		minColumnWidth?: number;
		autoExpandColumn?: string;
	}
	/**
	 * This class represents the primary interface of a component based grid control to represent data in a tabular format of rows and columns.
	 */
	export class GridPanel extends Panel {
		constructor(cfg?: IGridPanel);
		getSelectionModel(): RowSelectionModel;
		getStore(): data.Store;
		getColumnModel(): ColumnModel;
		getView(): GridView;
	}
	export interface IGridView extends util.IObservable {
		/**
		 * Defaults to false. Specify true to have the column widths re-proportioned at all times.
		 */
		forceFit?: boolean;
		/**
		 * True to show the dirty cell indicator when a cell has been modified. Defaults to true.
		 */
		markDirty?: boolean;
	}
	export class GridView extends util.Observable {
		constructor(cfg?: IGridView);
		findCellIndex(): any;
		findRow(): any;
		findRowBody(): any;
		findRowIndex(): any;
		focusCell(): any;
		focusRow(): any;
		getCell(): any;
		getGridInnerWidth(): any;
		getHeaderCell(): any;
		getRow(): any;
		getRowClass(): any;
		handleHdMenuClickDefault(): any;
		refresh(headersToo?: boolean): void;
		scrollToTop(): any;
	}
	export class ColumnModel extends util.Observable {
		constructor(cfg?: any);

		getDataIndex(col: number): string;
		findColumnIndex(col: string): number;
		setHidden(colIndex: number, hidden: boolean): void;
		getColumnsBy(fn: (col: Column) => boolean, scope?: object): Column[];
		moveColumn(oldIndex: number, newIndex: number): void;
		getColumnAt(index: number): Column;
		setColumnWidth(index: number, width: number, supressEvent?: boolean): void;
	}
	export class CheckboxSelectionModel extends RowSelectionModel {
		singleSelect?: boolean;
	}

}
declare namespace Ext.menu {
	export class MenuMgr {
		static get(menu: string | object): Ext.menu.Menu;
		static hideAll(): boolean;
	}
	export interface IMenu extends IContainer {
		minWidth?: number;
		focus?: Function;
		plain?: boolean;
	}
	export class Menu extends Container {
		constructor(cfg?: IMenu);

		/**
		 * Displays this menu at a specific xy position and fires the 'show' event if a handler for the 'beforeshow' event does not return false cancelling the operation.
		 * @param xyPosition Contains X & Y [x, y] values for the position at which to show the menu (coordinates are page-based)
		 * @param parentMenu This menu's parent menu, if applicable (defaults to undefined)
		 */
		showAt(xyPosition: [number, number], parentMenu?: this): this;
		add(...component: IItem[]): IItem[];
		add<T = Ext.menu.Item>(...component: T[]): T[];
		show(element: Element | HTMLElement, position?: string, parentMenu?: Ext.menu.Menu): this;
		focus(selectText?: boolean, delay?: boolean | number): this;
	}
	export interface IBaseItem extends IComponent {
		activeClass?: string;
		canActivate?: boolean;
		clickHideDelay?: number;
		handler?: (b: this, e: Ext.EventObject) => any;
		hideOnClick?: true;
		scope?: object;
	}
	export class BaseItem extends Component {
		constructor(cfg?: IBaseItem);
	}
	export interface IItem extends IBaseItem {
		altText?: string;
		canActivate?: boolean;
		href?: string;
		hrefTarget?: string;
		icon?: string;
		iconCls?: string;
		itemCls?: string;
		menu?: any;
		showDelay?: number;
		text?: string;
	}
	export class Item extends BaseItem {
		constructor(cfg?: IItem);
	}
	export interface IColorMenu extends IMenu { }
	export class ColorMenu extends Menu {
		constructor(cfg?: IColorMenu & IColorPalette)
	}
}
declare namespace Ext.state {
	/**
	 * Abstract base class for state provider implementations. This class provides methods for encoding and decoding typed variables including dates and defines the Provider interface.
	 */
	export abstract class Provider extends util.Observable {
		re: RegExp;
		protected constructor();
		clear(name: string): void;
		decodeValue(value: string): object;
		encodeValue(value: object): string;
		get<T>(name: string, defaultValue: T): T;
		set(name: string, value: any): void;
	}
}
/** Namespace alloted for extensions to the framework. */
declare namespace Ext.ux {
	export interface IGroupTabPanel extends ITabPanel {
		activeGroup: number;
	}
	export class GroupTabPanel extends TabPanel {
		constructor(cfg?: IGroupTabPanel);
	}
	export class VrTabPanel extends TabPanel {
		constructor(cfg?: any);
	}
}
declare namespace Ext.ux.form {
	export interface IFileUploadField extends Ext.form.ITextField {
		buttonText?: string;
		buttonOnly?: boolean;
		buttonOffset?: number;
		buttonCfg?: IButton;
		//listeners: FileUploadFieldEvents;
	}
	export interface FileUploadFieldEvents /* extends Ext.form.TextFieldEvents */ {
		fileselected: (cmp: IFileUploadField, v: string) => any;
	}
	export class FileUploadField extends Ext.form.TextField {
		constructor(cfg?: IFileUploadField);
	}
}
declare namespace Ext.ux.grid {
	export class CheckColumn {
		constructor(cfg?: any);
	}
	export class RowActions {
		constructor(cfg?: any);
	}
}
declare namespace Ext.Toolbar {
	export interface IItem extends IBoxComponent {
		hideParent?: boolean;
		overflowText?: boolean;
	}
	export class Item extends BoxComponent {
		constructor(el: HTMLElement | IItem);
		disable(silent?: boolean): this;
		enable(): this;
		focus(selectText?: boolean, delay?: boolean | number): this;
	}
}
/**
 * Ext.data.Api is a singleton designed to manage the data API including methods for validating a developer's DataProxy API. Defines variables for CRUD actions create, read, update and destroy in addition to a mapping of RESTful HTTP methods GET, POST, PUT and DELETE to CRUD actions.
 */
declare namespace Ext.data.Api {
	export const actions: {
		create: string;
		read: string;
		update: string;
		destroy: string;
	}
}
interface RecordConstructor {
	new <T = any>(data?: T, id?: any): Ext.data.Record<T>;
}