/* global go, Ext, dp, t */

/**
 * The Activity tab: the global activity/audit grid ({@see ActivityGrid}) with a
 * history-log-style filter side panel — a date-range Period, a Customer picker,
 * and an event-type (Actions) checkbox group. All three drive the grid's own
 * server-side filters (createdAt / customerId / actions).
 */
go.modules.community.marketplaceserver.ActivityPanel = Ext.extend(Ext.Panel, {
    layout: 'border',
    border: false,

    initComponent: function () {
        var me = this;

        me.grid = new go.modules.community.marketplaceserver.ActivityGrid({
            region: 'center',
            border: false
        });

        me.sidePanel = new Ext.Panel({
            region: 'west',
            width: dp(280),
            split: true,
            cls: 'go-sidenav',
            autoScroll: true,
            border: false,
            items: [
                {
                    xtype: 'panel',
                    border: false,
                    layout: 'form',
                    padding: dp(12),
                    items: [
                        me.dateRange = new go.form.DateRangeField({
                            xtype: 'godaterangefield',
                            fieldLabel: t("Date"),
                            anchor: '100%',
                            listeners: {
                                change: function (f, v) {
                                    me.grid.store.setFilter('createdAt', {createdAt: v}).load();
                                }
                            }
                        }),
                        me.customerCombo = new go.modules.community.marketplaceserver.CustomerCombo({
                            fieldLabel: t("Customer", "marketplaceserver", "community"),
                            anchor: '100%',
                            listeners: {
                                select: function (c, rec) {
                                    me.grid.store.setFilter('customerId', {customerId: rec.get('id')}).load();
                                },
                                change: function (c, v) {
                                    if (v === '' || v === null || v === undefined) {
                                        me.grid.store.setFilter('customerId', null).load();
                                    }
                                }
                            }
                        })
                    ]
                },
                {
                    xtype: 'panel',
                    border: false,
                    title: t("Actions"),
                    padding: '0 ' + dp(12) + 'px ' + dp(12) + 'px',
                    defaults: {
                        xtype: 'checkbox',
                        hideLabel: true,
                        listeners: {
                            check: function (cb) {
                                var boxes = cb.ownerCt.findByType('checkbox'),
                                    arr = [];
                                Ext.each(boxes, function (b) {
                                    if (b.getValue()) {
                                        arr.push(b.activityType);
                                    }
                                });
                                me.grid.store.setFilter('actions', arr.length ? {actions: arr} : null).load();
                            }
                        }
                    },
                    items: [
                        {activityType: 'download', boxLabel: me.grid.typeLabel('download')},
                        {activityType: 'purchase', boxLabel: me.grid.typeLabel('purchase')},
                        {activityType: 'refund', boxLabel: me.grid.typeLabel('refund')},
                        {activityType: 'subscription_canceled', boxLabel: me.grid.typeLabel('subscription_canceled')},
                        {activityType: 'register', boxLabel: me.grid.typeLabel('register')},
                        {activityType: 'verify', boxLabel: me.grid.typeLabel('verify')},
                        {activityType: 'grant', boxLabel: me.grid.typeLabel('grant')},
                        {activityType: 'revoke', boxLabel: me.grid.typeLabel('revoke')},
                        {activityType: 'restore', boxLabel: me.grid.typeLabel('restore')}
                    ]
                }
            ]
        });

        me.items = [me.grid, me.sidePanel];

        go.modules.community.marketplaceserver.ActivityPanel.superclass.initComponent.call(me);

        // Default the period to the current week on open (bounds the initial load
        // to recent activity, like the history log). setThisWeek() fires the
        // field's change → applies the createdAt filter → loads the grid.
        me.grid.on('viewready', function () {
            me.dateRange.setThisWeek();
        }, me, {single: true});
    }
});
