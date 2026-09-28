/* global go, Ext, t */

/**
 * A customer picker (for filtering): entity-backed combo on
 * MarketplaceServerCustomer. Shows the company name, else the owning user's
 * name/e-mail, else "#id" — computed in the dropdown template from the loaded
 * companyName + user relation (so it never depends on a relation resolving into
 * the value field). Optional: an empty selection means "all customers". Typing
 * searches server-side (the Customer text filter matches the user too).
 */
go.modules.community.marketplaceserver.CustomerCombo = Ext.extend(go.form.ComboBox, {
    valueField: 'id',
    displayField: 'displayName',
    triggerAction: 'all',
    pageSize: 50,
    forceSelection: true,
    allowBlank: true,
    minChars: 0,

    initComponent: function () {
        if (!this.emptyText) {
            this.emptyText = t("All customers", "marketplaceserver", "community");
        }

        Ext.applyIf(this, {
            store: new go.data.Store({
                fields: [
                    'id',
                    // userId is needed so the server getDisplayName() can resolve the
                    // owning user's name when companyName is empty — without it, that
                    // getter sees a null userId and returns "#id".
                    'userId',
                    'companyName',
                    // displayField MUST be non-empty on every record or forceSelection
                    // discards the pick on blur. Prefer the server displayName, then
                    // the resolved user relation, then companyName, then "#id" — so a
                    // no-company customer still shows a real name and selection sticks.
                    {
                        name: 'displayName',
                        convert: function (v, data) {
                            if (v) {
                                return v;
                            }
                            if (data.user && (data.user.displayName || data.user.email)) {
                                return data.user.displayName || data.user.email;
                            }
                            return data.companyName || ('#' + data.id);
                        }
                    },
                    {name: 'user', type: 'relation'}
                ],
                entityStore: "MarketplaceServerCustomer",
                sortInfo: {field: 'companyName', direction: 'ASC'}
            })
        });

        // Explicit list template (a bare go.form.ComboBox renders blank rows).
        // label() falls back company → user → displayName → #id so a row is never
        // empty regardless of which fields resolved.
        this.tpl = new Ext.XTemplate(
            '<tpl for="."><div class="x-combo-list-item">{[this.label(values)]}</div></tpl>',
            {
                label: function (v) {
                    var name = v.companyName
                        || (v.user && (v.user.displayName || v.user.email))
                        || v.displayName
                        || ('#' + v.id);
                    return Ext.util.Format.htmlEncode(name);
                }
            }
        );

        go.modules.community.marketplaceserver.CustomerCombo.superclass.initComponent.call(this);
    }
});

Ext.reg('marketplaceservercustomercombo', go.modules.community.marketplaceserver.CustomerCombo);
