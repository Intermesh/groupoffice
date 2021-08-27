Ext.define('go.community.tasks.ParticipantField', {
    extends: Ext.Container,

    initComponent: function() {
        this.addField = new go.users.UserCombo();

        this.list = new Ext.DataView({
            autoHeight:true,
            multiSelect: true,
            overClass:'x-view-over',
            itemSelector:'div.participant-wrap',
            tpl: new Ext.XTemplate(
                '<tpl for=".">',
                '<div class="participant" id="{name}">',
                '<div class="thumb"><img src="{url}" title="{name}"></div>',
                '<span class="x-editable">{shortName}</span></div>',
                '</tpl>',
                '<div class="x-clear"></div>'
            ),
            store:new Ext.data.ArrayStore({
                fields : ['id', 'name', 'email', 'roles', 'userId'],
                idIndex: 0,
                data : [
                    ['1', 'Michael de Hart', 'Organizer', 'email@intermesh.nl', ''],
                    ['2', t("week"), t('weeks'), 13, '91-d', t('Weekly')]
                ]
            })
        });
    }
})