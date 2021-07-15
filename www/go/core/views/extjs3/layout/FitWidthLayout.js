/**
 * @class Ext.layout.FitLayout
 * @extends Ext.layout.ContainerLayout
 * <p>This is a base class for layouts that contain <b>a single item</b> that automatically expands to fill the layout's
 * container.  This class is intended to be extended or created via the <tt>layout:'fit'</tt> {@link Ext.Container#layout}
 * config, and should generally not need to be created directly via the new keyword.</p>
 * <p>FitLayout does not have any direct config options (other than inherited ones).  To fit a panel to a container
 * using FitLayout, simply set layout:'fit' on the container and add a single panel to it.  If the container has
 * multiple panels, only the first one will be displayed.  Example usage:</p>
 * <pre><code>
var p = new Ext.Panel({
    title: 'Fit Layout',
    layout:'fit',
    items: {
        title: 'Inner Panel',
        html: '&lt;p&gt;This is the inner panel content&lt;/p&gt;',
        border: false
    }
});
</code></pre>
 */
Ext.layout.FitWidthLayout = Ext.extend(Ext.layout.ContainerLayout, {
	// private
	monitorResize:true,

	type: 'fitwidth',

	getLayoutTargetSize : function() {
		var target = this.container.getLayoutTarget();
		if (!target) {
			return {};
		}
		// Style Sized (scrollbars not included)
		return target.getStyleSize();
	},

	// private
	onLayout : function(ct, target){
		Ext.layout.FitLayout.superclass.onLayout.call(this, ct, target);
		if(!ct.collapsed){
			const size = this.getLayoutTargetSize(), sbs = Ext.getScrollBarWidth();

			var cs = this.getRenderedItems(ct), len = cs.length, i, c;
			for(i = 0; i < len; i++){
				c = cs[i];
				c.setWidth(size.width - sbs);
			}

		}
	}
});
Ext.Container.LAYOUTS['fitwidth'] = Ext.layout.FitWidthLayout;