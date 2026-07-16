/* global go, Ext, dp, t */

/**
 * Read-only audit log of marketplace activity (downloads, purchases, refunds,
 * subscription cancels, registrations, verifications, grants/revokes). Rows are
 * written only by the system (model\Activity::record); there is no add/edit/
 * delete here. A type filter + free-text search narrow the list.
 */
go.modules.community.marketplaceserver.ActivityGrid = Ext.extend(go.grid.GridPanel, {

    entityStore: "MarketplaceServerActivity",
    stateId: 'community-marketplaceserver-activity-grid',

    initComponent: function () {
        var me = this;

        me.store = new go.data.Store({
            fields: [
                'id',
                {name: 'createdAt', type: 'date'},
                'type', 'customerId', 'productId', 'moduleName', 'version',
                'hostname', 'amount', 'currency', 'ref', 'ip', 'detail',
                {name: 'customer', type: 'relation'},
                {name: 'product', type: 'relation'}
            ],
            entityStore: "MarketplaceServerActivity",
            sortInfo: {field: 'createdAt', direction: 'DESC'}
        });

        // The period / customer / event-type filters live in the ActivityPanel's
        // side panel (history-log style); this grid just carries free-text search.
        me.tbar = ['->', {xtype: 'tbsearch'}];

        me.columns = [
            {
                header: t("When", "marketplaceserver", "community"),
                dataIndex: 'createdAt',
                width: dp(150),
                sortable: true,
                renderer: function (v) {
                    return v ? Ext.util.Format.date(v, 'Y-m-d H:i') : '';
                }
            },
            {
                header: t("Event", "marketplaceserver", "community"),
                dataIndex: 'type',
                width: dp(150),
                sortable: true,
                renderer: function (v) {
                    return Ext.util.Format.htmlEncode(me.typeLabel(v));
                }
            },
            {
                header: t("Customer", "marketplaceserver", "community"),
                dataIndex: 'customer',
                sortable: false,
                width: dp(160),
                renderer: function (customer, meta, rec) {
                    if (customer && customer.companyName) {
                        return Ext.util.Format.htmlEncode(customer.companyName);
                    }
                    return rec.get('customerId') ? '#' + rec.get('customerId') : '-';
                }
            },
            {
                id: 'item',
                header: t("Item", "marketplaceserver", "community"),
                dataIndex: 'moduleName',
                sortable: false,
                renderer: function (v, meta, rec) {
                    var product = rec.get('product'),
                        label = v || (product ? product.title : null),
                        version = rec.get('version');
                    if (!label) {
                        return '-';
                    }
                    return Ext.util.Format.htmlEncode(version ? (label + ' ' + version) : label);
                }
            },
            {
                header: t("Amount", "marketplaceserver", "community"),
                dataIndex: 'amount',
                width: dp(110),
                sortable: true,
                align: 'right',
                renderer: function (v, meta, rec) {
                    if (v === null || v === undefined || v === '') {
                        return '';
                    }
                    return Ext.util.Format.htmlEncode((v / 100).toFixed(2) + ' ' + (rec.get('currency') || ''));
                }
            },
            {
                header: t("Reference", "marketplaceserver", "community"),
                dataIndex: 'ref',
                width: dp(180),
                sortable: false,
                renderer: function (v, meta, rec) {
                    var s = v || rec.get('ip') || '';
                    return Ext.util.Format.htmlEncode(s);
                }
            }
        ];

        me.viewConfig = {
            emptyText: '<i class="icon ic-history"></i><p>' +
                t("No activity for the selected filters. Widen the date range or clear the filters.", "marketplaceserver", "community") + '</p>'
        };

        go.modules.community.marketplaceserver.ActivityGrid.superclass.initComponent.call(me);

        me.on('render', function () { me.store.load(); }, me);
    },

    /**
     * Human label for an activity type.
     *
     * @param {String} type
     * @return {String}
     */
    typeLabel: function (type) {
        var map = {
            download: t("Download", "marketplaceserver", "community"),
            purchase: t("Purchase", "marketplaceserver", "community"),
            refund: t("Refund", "marketplaceserver", "community"),
            subscription_canceled: t("Subscription canceled", "marketplaceserver", "community"),
            register: t("Registration", "marketplaceserver", "community"),
            verify: t("Verification", "marketplaceserver", "community"),
            grant: t("Grant", "marketplaceserver", "community"),
            revoke: t("Revoke", "marketplaceserver", "community"),
            restore: t("Restore", "marketplaceserver", "community")
        };
        return map[type] || type;
    }
});
