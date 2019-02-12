
/* global Ext, go */

go.tree.TreeNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
	// private
    renderElements : function(n, a, targetNode, bulkRender){
        // add some indent caching, this helps performance when rendering a large tree
        this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';
								
				var cb = Ext.isBoolean(a.checked),
            nel,
            href = this.getHref(a.href),
            buf = ['<li class="x-tree-node"><div ext:tree-node-id="',n.id,'" class="x-tree-node-el x-tree-node-leaf x-unselectable ', a.cls,'" unselectable="on">',
            '<span class="x-tree-node-indent">',this.indentMarkup,"</span>",
            '<span class="x-tree-ec-icon x-tree-elbow"></span>',
            '<span style="background-image:url(', a.icon || this.emptyIcon, ');" class="x-tree-node-icon',(a.icon ? " x-tree-node-inline-icon" : ""),(a.iconCls ? " "+a.iconCls : ""),'" unselectable="on"></span>',
            cb ? ('<span class="x-tree-node-cb"><input type="checkbox" ' + (a.checked ? 'checked="checked" /></span>' : '/></span>')) : '',
            '<a hidefocus="on" class="x-tree-node-anchor" href="',href,'" tabIndex="1" ',
             a.hrefTarget ? ' target="'+a.hrefTarget+'"' : "", '><span unselectable="on"><div class="secondary ',n.secondaryIconCls,'">',n.secondaryText,'</div><div class="ellipsis">',n.text,'</div></span></a></div>',
            '<ul class="x-tree-node-ct" style="display:none;"></ul>',
            "</li>"].join('');

        if(bulkRender !== true && n.nextSibling && (nel = n.nextSibling.ui.getEl())){
            this.wrap = Ext.DomHelper.insertHtml("beforeBegin", nel, buf);
        }else{
            this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf);
        }

        this.elNode = this.wrap.childNodes[0];
        this.ctNode = this.wrap.childNodes[1];
        var cs = this.elNode.childNodes;
        this.indentNode = cs[0];
        this.ecNode = cs[1];
        this.iconNode = cs[2];
        var index = 3;
        if(cb){
            this.checkbox = cs[3];
            // fix for IE6
            this.checkbox.defaultChecked = this.checkbox.checked;
            index++;
        }
        this.anchor = cs[index];
				this.span = cs[index].firstChild;
        this.textNode = cs[index].firstChild.childNodes[1];
				this.secondaryTextNode = cs[index].firstChild.childNodes[0];
    },
		
		// private
    onSecondaryTextChange : function(node, text, oldText){
        if(this.rendered){
            this.secondaryTextNode.innerHTML = text;
        }
    },
    
    // private
    onSecondaryIconClsChange : function(node, cls, oldCls){
        if(this.rendered){
            Ext.fly(this.secondaryTextNode).replaceClass(oldCls, cls);
        }
    },
		
		// private
    getDDHandles : function(){
        return [this.iconNode, this.textNode, this.secondaryTextNode, this.elNode, this.span, this.anchor ];
    }
	
});