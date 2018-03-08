/**
 * @class GO.layout.ColumnFitLayout
 * @extends Ext.layout.ColumnLayout
 */
GO.layout.ColumnFitLayout  = Ext.extend(Ext.layout.ColumnLayout, {
    onLayout:function(ct, target) {
        // call parent
        GO.layout.ColumnFitLayout.superclass.onLayout.apply(this, arguments);

        // get columns and height
        var cs = ct.items.items, len = cs.length, c, i;
        var size = Ext.isIE && target.dom != Ext.getBody().dom ? target.getStyleSize() : target.getViewSize();
        var h = size.height - target.getPadding('tb');

        // set height of columns
        for(i = 0; i < len; i++) {
            c = cs[i];
            c.setHeight(h + (c.footer ? c.footer.getHeight() : 0));
        }
    }
});

// register layout
Ext.Container.LAYOUTS['columnfit'] = GO.layout.ColumnFitLayout; 