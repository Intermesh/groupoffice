Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
    onRender : function(ct, position){
        this.el = ct.createChild({
            tag: 'iframe',
            id: 'iframe-'+ this.id,
            frameBorder: 0,
            src: this.url
        });
    }
});

go.modules.community.dokuwiki.checkHost = function (wikiurl) {

    if (go.util.empty(wikiurl)) {
        return false;
    }

    var godomain = window.location.hostname,
        wikidomain = wikiurl.match(/http(s)?:\/\/([^/:]+)/i);

    return !(!wikidomain || godomain !== wikidomain[2]);
};

go.modules.community.dokuwiki.MainPanel = Ext.extend(go.modules.ModulePanel, {

    id: "dokuwiki",

    layout: 'responsive',
    layoutConfig: {
        triggerWidth: 1000
    },

    initComponent: function () {

        var me = this,
            settings = go.Modules.get('community', 'dokuwiki').settings || {};

        me.title = settings.title || t("title", 'dokuwiki');

        me.iFrameComponent = new Ext.ux.IFrameComponent ({
            url: (settings.externalUrl || '')
        });

        this.items = [
            {
                xtype: 'panel',
                region: 'center',
                layout: 'fit',
                tbar: [
                    new Ext.Button({
                        iconCls: 'ic-refresh',
                        text: t('Refresh'),
                        handler: function () {
                            var url = settings.externalUrl;
                            go.modules.community.dokuwiki.checkHost(url);
                            this.iFrameComponent.el.dom.src = url;
                        },
                        scope: me
                    })
                ],
                items: [
                    me.iFrameComponent
                ]
            }
        ];

        go.modules.community.dokuwiki.MainPanel.superclass.initComponent.call(me);
    }
});
