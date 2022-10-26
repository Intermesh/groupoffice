declare module Ext {
	class Container {
		static AUTO_ID: number;
		protected scope: any
		//protected fireEvent: any;

		on(...args: any[]);
		el:any
		off: any;
		add: (items: any[]) => void

		protected ownerCt: Ext.Container

		hasListener(name: string): boolean

		addEvents(...args: string[])

		protected mon(item, ename, fn, scope, opt?);

		protected initialConfig: any

		constructor(config?);

		initComponent(...args: any[])
	}
	class Window extends Container {
		close()
	}
}

interface BaseCfg {
	id?: string
	cls?: string
	dom?: HTMLElement
	style?: string
	tag?: keyof HTMLElementTagNameMap
	width?: number
	height?: number
	title?: string
	tooltip?: string
	active?: boolean
	hidden?: boolean
	collapsed?: boolean
	html?: string
	icon?: string | IconCfg
	flex?: boolean
	parent?: Component
	data?: any
	sash?: boolean
	attr?: any // href, download, target
	on?: any // override me
	items?: ItemsCfg[]
	itemDefaults?: any
	onDrop?: (e: any) => void
}

interface ComponentCfg extends BaseCfg {
}

type ItemsCfg = Component | ComponentCfg | '-' | '->' // ButtonCfg | TabItemCfg

type IconCfg = {
	name: string
	style?: string
	cls?: string
	title?: string
}

class Dialog extends Ext.Window {
	setItems(...items) {
		for(const cmp of items)
			this.add(cmp);
	}
}

class Component extends Ext.Container {

	protected e: any = {}

	get parent() {
		return this.ownerCt!;
	}
	get dom() {
		return this.el.dom;
	}

	setItems(...items) {
		for(const cmp of items)
			this.add(cmp);
	}


	// The below functions is just for Extjs compatibility
	// setSize(size) {}
	// getItemId() {  return this.getId();}
	// onAdded(){}
	// initRef(){}
	// getPositionEl() {return {dom:this.dom}}
	// getId(){return this.id || (this.id = 'go-ui-' + (++Ext.Component.AUTO_ID));}
	// get el() {return Ext.get(this.dom);}
	// //get tabEl() {return this.dom;}
	// tabEl
	// fireEvent(name, ...args) {
	// 	//this.fire(name, ...args);
	// } // pass the events anyway
	// onRender(ct) {this.render(ct);}
}