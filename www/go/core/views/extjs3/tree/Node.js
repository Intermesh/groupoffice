/* global Ext, go */

go.tree.Node = Ext.extend(Ext.tree.AsyncTreeNode, {
	secondaryText: "",
	secondaryIconCls: "",

	constructor : function(attributes) {
		
		if(!attributes.uiProvider) {
			attributes.uiProvider = go.tree.TreeNodeUI;
		}		
		
	/**
		* Read-only. The text for this node. To change it use <code>{@link #setText}</code>.
		* @type String
		*/
		this.secondaryText = attributes.secondaryText;
		
		
		/**
		* Read-only. The text for this node. To change it use <code>{@link #setText}</code>.
		* @type String
		*/
		this.secondaryIconCls = attributes.secondaryIconCls;
		
		go.tree.Node.superclass.constructor.call(this, attributes);		
	},
	
	 /**
     * Sets the text for this node
     * @param {String} text
     */
    setSecondaryText : function(text){
        var oldText = this.secondaryText;
        this.secondaryText = this.attributes.secondaryText = text;
        if(this.rendered){ // event without subscribing
            this.ui.onSecondaryTextChange(this, text, oldText);
        }
        this.fireEvent('textchange', this, text, oldText);
    },
    
    /**
     * Sets the icon class for this node.
     * @param {String} cls
     */
    setIconCls : function(cls){
        var old = this.attributes.iconCls;
        this.attributes.iconCls = cls;
        if(this.rendered){
            this.ui.onSecondaryIconClsChange(this, cls, old);
        }
    }

});

Ext.tree.TreePanel.nodeTypes.groupoffice = go.tree.Node;