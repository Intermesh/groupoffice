GO.files.TreeFilePanel = function (config){
    config = config || {};


    config.loader =  new GO.base.tree.TreeLoader(
    {
        dataUrl:GO.url('files/folder/tree'),
        baseParams:{
            root_folder_id:0,
            expand_folder_id:0,
            showFiles: true
        },
        preloadChildren:true
    });

    config.loader.on('beforeload', function(){
        var el =this.getEl();
        if(el){
            el.mask(GO.lang.waitMsgLoad);
        }
    }, this);

    config.loader.on('load', function(){
        var el =this.getEl();
        if(el){
            el.unmask();
        }
    }, this);

    Ext.applyIf(config, {
        layout:'fit',
        split:true,
        autoScroll:true,
        width: 200,
        animate:true,
        rootVisible:false,
        containerScroll: true,
        selModel:new Ext.tree.MultiSelectionModel()
    });


    GO.files.TreeFilePanel.superclass.constructor.call(this, config);

    // set the root node
    var rootNode = new Ext.tree.AsyncTreeNode({
        text: '',
        draggable:false,
        id: 'root',
        iconCls : 'folder-default'
    });

    this.setRootNode(rootNode);
}

Ext.extend(GO.files.TreeFilePanel, Ext.tree.TreePanel,{
});
