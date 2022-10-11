/* global Ext */

go.customfields.type.SelectOptionsTree = function(config){

	config = config || {};

	Ext.apply(config, {
		animate: false,
		enableDD: true,
		autoScroll: true,
	});

	config.bbar = [ '->',{
		iconCls: 'ic-add',
		handler: function() {
			let node = this.selModel.getSelectedNode();
			if(!node) {
				node = this.getRootNode();
			}

			let newNode = new go.tree.Node({
				text: '',
				expanded: true,
				children:[]
			});

			newNode = node.appendChild(newNode);
			const dlg = new go.customfields.type.OptionDialog();
			dlg.load(newNode);
			dlg.show();
		},
		scope:this
	},
	{
		iconCls: 'ic-delete',
		handler: function() {
			let node = this.selModel.getSelectedNode();
			if(!node) {
				return false;
			}
			node.destroy();
		},
		scope:this
	}];

	go.customfields.type.SelectOptionsTree.superclass.constructor.call(this, config);

	this.on("click",  (node, e) => {
		if (e.target.tagName === "BUTTON") {
			const dlg = new go.customfields.type.OptionDialog();
			dlg.load(node);
			dlg.show();
			dlg.on('beforeclose', () => {
				// debugger;
				if(dlg.doSave) {
					node.text = dlg.nodeAttributes.text;
					node.attributes.text = dlg.nodeAttributes.text;
					node.attributes.backgroundColor = dlg.nodeAttributes.backgroundColor;
					node.attributes.foregroundColor = dlg.nodeAttributes.foregroundColor;
					node.attributes.renderMode = dlg.nodeAttributes.renderMode;
					node.setText(dlg.nodeAttributes.text);
				}
			});
		}
	});

	this.setValue([]);
}

Ext.extend(go.customfields.type.SelectOptionsTree, Ext.tree.TreePanel, {
	setValue : function(options) {
		// set the root node
		const root = new go.tree.Node({
			text: 'Root',
			draggable: false,
			id: 'root',
			children: this.apiToTree(options),
			expanded: true,
			checked: true,
			editable: false
		});
		this.setRootNode(root);
	},
	
	apiToTree : function(options) {
		const me = this;
		options.forEach(function(o) {
			o.nodeType = 'groupoffice';
			o.secondaryText = '<button class="icon">edit</button>';
			o.expanded = true; //always expand or they won't be submitted and thus deleted on the server!
			o.children = me.apiToTree(o.children);
			o.serverId = o.id;
			o.checked = !!o.enabled;
			o.loader = this.loader;
			delete o.id;
		});

		return options;
	},
	
	name: "options",
	
	isFormField: true,
	getName: function () {
		return this.name;
	},
	_isDirty: true,
	isDirty: function () {
		return this.rendered;
	},
	getValue: function () {
		return this.treeToAPI(this.getRootNode());	
	},
	
	treeToAPI : function(node) {
		let v = [];
		node.childNodes.forEach((child) => {
			v.push({
				id: child.attributes.serverId || null,
				text: child.text,
				sortOrder: child.attributes.sortOrder,
				enabled: child.attributes.checked,
				children: this.treeToAPI(child),
				foregroundColor: child.attributes.foregroundColor || null,
				backgroundColor: child.attributes.backgroundColor || null,
				renderMode: child.attributes.renderMode || null,
				allowChildren: false
			});
		});
		
		return v;
	},
	
	markInvalid: function (msg) {
		this.getEl().addClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.mark(this, msg);
	},
	clearInvalid: function () {
		this.getEl().removeClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.clear(this);
	},
	isValid : function(preventMark){
		return true;
	},
	validate : function() {
		return true;
	}
});
