/* global go, Ext, dp, t, GO */

/**
 * One collapsible section per configured marketplace Repository (the GO-Modules
 * style: a stack of sections instead of a west selector + center split). The
 * section body is a card layout that swaps between:
 *   - a loading placeholder (while its catalog is fetched),
 *   - an error placeholder (unreachable server / signing-key mismatch), or
 *   - the CatalogGrid render (table/cards) of that repository's catalogue.
 *
 * The parent SystemSettingsPanel creates a section per repository, calls
 * loadCatalog() and only ADDS the section to the layout once the request has
 * SETTLED (success or error) — so a section appears when its repo is ready and
 * a slow/broken repo never blocks the others. The catalog response stays in the
 * CatalogGrid store, so collapsing/expanding and table<->cards never refetch;
 * only an explicit Refresh (gear) or a post-download reload refetches.
 *
 * Per-repository actions (Refresh, My account, Edit/token, Remove) live behind
 * the header gear tool — the old standalone RepositoryGrid is gone.
 */
go.modules.community.marketplace.RepositorySection = Ext.extend(Ext.Panel, {

    // Auto-height so the section grows to fit its catalogue (the inner
    // CatalogGrid computes its own height) instead of a fixed box with wasted
    // whitespace under a small catalogue.
    autoHeight: true,
    collapsible: true,
    titleCollapse: true,
    animCollapse: false,

    // Remember the collapsed state across sessions (per repository).
    stateful: true,
    stateEvents: ['collapse', 'expand'],

    // Body-card indexes (shown one at a time via show()/hide(), NOT a card
    // layout — a card layout wants to fill a fixed height, which fights
    // autoHeight).
    CARD_LOADING: 0,
    CARD_ERROR: 1,
    CARD_CATALOG: 2,

    /**
     * @cfg {Object} repo repository record data {id, name, url, lastSyncAt,
     *      keyMismatch, lastError, permissionLevel}
     * @cfg {String} viewMode initial 'table' | 'cards'
     * @cfg {Boolean} ownedOnly initial owned-filter state
     */
    initComponent: function () {
        var me = this;

        me.stateId = 'community-marketplace-section-' + me.repo.id;
        me.title = me.buildTitle(me.repo);

        me.loadingBox = new Ext.Panel({
            border: false,
            bodyStyle: 'padding:24px; text-align:center; color:var(--fg-secondary-text);',
            html: '<i class="icon ic-refresh" style="font-size:28px; display:block; margin-bottom:8px;"></i>' +
                Ext.util.Format.htmlEncode(t("Loading…", "marketplace", "community"))
        });

        me.errorBox = new Ext.Panel({
            border: false,
            hidden: true,
            bodyStyle: 'padding:24px; text-align:center; color:var(--fg-secondary-text);',
            bbar: [
                '->',
                {
                    text: t("Refresh", "marketplace", "community"),
                    iconCls: 'ic-refresh',
                    handler: function () { me.loadCatalog(); }
                },
                {
                    text: t("Edit"),
                    iconCls: 'ic-edit',
                    handler: function () { me.onEdit(); }
                },
                '->'
            ]
        });

        me.catalogGrid = new go.modules.community.marketplace.CatalogGrid({
            hidden: true,
            repositoryId: me.repo.id,
            repositoryUrl: me.repo.url,
            viewMode: me.viewMode || 'table',
            ownedOnly: !!me.ownedOnly,
            typeFilter: me.typeFilter || 'all',
            onReload: function () { me.loadCatalog(); }
        });

        me.items = [me.loadingBox, me.errorBox, me.catalogGrid];

        // Header gear -> per-repository menu. (The collapse toggle tool is added
        // automatically by collapsible:true.)
        me.tools = [
            {
                id: 'gear',
                qtip: t("Repository", "marketplace", "community"),
                handler: function (e) { me.showGearMenu(e); }
            }
        ];

        go.modules.community.marketplace.RepositorySection.superclass.initComponent.call(me);
    },

    /**
     * Persist only the collapsed flag.
     * @return {Object}
     */
    getState: function () {
        return {collapsed: this.collapsed};
    },

    /**
     * Header title with a leading font-glyph icon. iconCls on a non-framed
     * Ext.Panel renders as a blank <img class="x-panel-inline-icon">, so GO's
     * ::before font glyphs never show — an inline <i class="icon …"> in the
     * title html is the reliable way to get a glyph in the header.
     *
     * @param {Object} repo
     * @return {String} html
     */
    buildTitle: function (repo, updateCount) {
        var badge = '';
        if (updateCount > 0) {
            // A small pill on the section header: "N updates" — the at-a-glance
            // "this repository has newer versions" cue, computed on catalog load.
            badge = ' <span style="display:inline-block; margin-left:8px; padding:0 7px; border-radius:9px; ' +
                'background:var(--hue-orange); color:#fff; font-size:11px; line-height:16px; vertical-align:middle;" ext:qtip="' +
                Ext.util.Format.htmlEncode(t("Updates available", "marketplace", "community")) + '">' +
                updateCount + ' ' + Ext.util.Format.htmlEncode(t("updates", "marketplace", "community")) + '</span>';
        }
        return '<i class="icon ic-store" style="margin-right:6px;"></i>' +
            Ext.util.Format.htmlEncode(repo.name || repo.url || t("Repository", "marketplace", "community")) + badge;
    },

    /**
     * @return {Number} update count in this section's loaded catalog
     */
    getUpdateCount: function () {
        return this.catalogGrid ? this.catalogGrid.getUpdateCount() : 0;
    },

    /**
     * Update the repository metadata (name / key-mismatch) in place, e.g. after
     * an edit — without recreating the section (keeps its collapsed state and
     * loaded catalogue).
     *
     * @param {Object} repo
     * @return {void}
     */
    updateRepo: function (repo) {
        this.repo = repo;
        this.setTitle(this.buildTitle(repo));
    },

    /**
     * Show one body card (loading / error / catalog) and hide the others.
     * Works whether or not the panel is rendered yet (during the initial
     * "appear when settled" flow the section is still detached — show()/hide()
     * just toggles the hidden flag until render).
     *
     * @param {Number} idx one of CARD_LOADING / CARD_ERROR / CARD_CATALOG
     * @return {void}
     */
    setActiveCard: function (idx) {
        var me = this,
            cards = [me.loadingBox, me.errorBox, me.catalogGrid];
        Ext.each(cards, function (c, i) {
            if (!c) {
                return;
            }
            if (i === idx) {
                c.show();
            } else {
                c.hide();
            }
        });
        if (idx === me.CARD_CATALOG && me.catalogGrid.rendered) {
            me.catalogGrid.reflow();
        }
        if (me.rendered) {
            me.doLayout();
            if (me.ownerCt) {
                me.ownerCt.doLayout();
            }
        }
    },

    /**
     * Fetch this repository's catalog and route the result into the body cards.
     *
     * @param {Function} [onSettle] called with (this) once the request resolves
     *        or errors — the parent uses it to insert the section on first load.
     * @return {void}
     */
    loadCatalog: function (onSettle) {
        var me = this;
        me.setActiveCard(me.CARD_LOADING);

        go.Jmap.request({
            method: "MarketplaceRepository/catalog",
            params: {repositoryId: me.repo.id},
            callback: function (options, success, response) {
                if (!success || !response) {
                    me.showError(response);
                } else {
                    me.catalogGrid.setData(response);
                    me.setActiveCard(me.CARD_CATALOG);
                    // Refresh the header badge with the freshly-loaded update count
                    // and let the parent re-aggregate its global total.
                    me.setTitle(me.buildTitle(me.repo, me.catalogGrid.getUpdateCount()));
                    if (me.onUpdateCount) {
                        me.onUpdateCount();
                    }
                }
                if (onSettle) {
                    onSettle(me);
                }
            },
            scope: me
        });
    },

    /**
     * Render the error card. Signing-key rotation gets a tailored hint (re-enter
     * the token via Edit); everything else surfaces the server message.
     *
     * @param {Object} [response] the failed JMAP response ({message})
     * @return {void}
     */
    showError: function (response) {
        var msg = this.repo.keyMismatch
            ? t("The repository's signing key changed. Re-confirm it to resume syncing.", "marketplace", "community")
            : ((response && response.message) || t("Could not load this repository's catalog.", "marketplace", "community"));

        this.errorBox.update(
            '<i class="icon ic-warning" style="font-size:28px; color:var(--hue-orange); display:block; margin-bottom:8px;"></i>' +
            Ext.util.Format.htmlEncode(msg)
        );
        this.setActiveCard(this.CARD_ERROR);
    },

    /**
     * @param {String} mode 'table' | 'cards'
     * @return {void}
     */
    setViewMode: function (mode) {
        this.viewMode = mode;
        this.catalogGrid.setViewMode(mode);
    },

    /**
     * @param {Boolean} ownedOnly
     * @return {void}
     */
    applyOwnedFilter: function (ownedOnly) {
        this.ownedOnly = ownedOnly;
        this.catalogGrid.applyOwnedFilter(ownedOnly);
    },

    /**
     * @param {String} type 'all' | 'module' | 'collection'
     * @return {void}
     */
    applyTypeFilter: function (type) {
        this.typeFilter = type;
        this.catalogGrid.applyTypeFilter(type);
    },

    /**
     * @return {Array} installed module rows for upgrade-readiness aggregation
     */
    getInstalledModules: function () {
        return this.catalogGrid.getInstalledModules();
    },

    /**
     * @return {Array} the GO branches this repository publishes for
     */
    getBranches: function () {
        return this.catalogGrid.getBranches();
    },

    /**
     * Build (once) and show the per-repository gear menu at the click point.
     *
     * @param {Ext.EventObject} e
     * @return {void}
     */
    showGearMenu: function (e) {
        var me = this;
        // Stop the header click from also toggling collapse (titleCollapse).
        if (e && e.stopEvent) {
            e.stopEvent();
        }
        if (!me.gearMenu) {
            me.gearMenu = new Ext.menu.Menu({
                items: [
                    {
                        // Just reload the catalogue — the everyday "is there a new
                        // module?" action. No license/key sync, so it never asks
                        // the user to re-confirm anything.
                        iconCls: 'ic-refresh',
                        text: t("Refresh", "marketplace", "community"),
                        handler: function () { me.loadCatalog(); }
                    },
                    {
                        // The license/key re-sync is a separate, deliberate action —
                        // only this one can surface the "security key changed"
                        // prompt, and only when the user explicitly asked to sync
                        // licenses.
                        iconCls: 'ic-verified-user',
                        text: t("Refresh licenses", "marketplace", "community"),
                        handler: function () { me.onRefreshLicenses(); }
                    },
                    {
                        iconCls: 'ic-account-circle',
                        text: t("My account", "marketplace", "community"),
                        handler: function () { me.onMyAccount(); }
                    },
                    {
                        iconCls: 'ic-edit',
                        text: t("Edit"),
                        handler: function () { me.onEdit(); }
                    },
                    {
                        iconCls: 'ic-delete',
                        text: t("Delete"),
                        handler: function () { me.onDelete(); }
                    }
                ]
            });
        }
        me.gearMenu.showAt(e.getXY());
    },

    /**
     * Re-fetch /info + /license on the server (updates lastSync/keyMismatch/
     * lastError on the entity), then reload this section's catalog. This is the
     * ONLY path that can hit a signing-key mismatch, so it's a deliberate,
     * separately-named menu action — the everyday "Refresh" just reloads the
     * catalogue and never bothers the user about keys.
     *
     * @return {void}
     */
    onRefreshLicenses: function () {
        var me = this;
        go.Jmap.request({
            method: "MarketplaceRepository/refresh",
            params: {repositoryId: me.repo.id},
            callback: function (options, success, response) {
                if (!success) {
                    // The commonest refresh failure is a signing-key rotation
                    // (e.g. the server was reinstalled) — the fix is to re-enter
                    // the API token, so offer to open the dialog directly instead
                    // of a dead-end error message.
                    Ext.MessageBox.confirm(
                        me.repo.name || t("Repository", "marketplace", "community"),
                        ((response && response.message) || t("Could not refresh this repository.", "marketplace", "community")) + '<br><br>' +
                            t("Open it to re-enter the API token and re-confirm?", "marketplace", "community"),
                        function (btn) {
                            if (btn === 'yes') { me.onEdit(); }
                        }
                    );
                }
                // The catalogue itself is independent of the license/key sync, so
                // reload it regardless (it usually still works).
                me.loadCatalog();
            },
            scope: me
        });
    },

    /**
     * Open the edit dialog; on a successful save let the parent rebuild (name /
     * key-mismatch / token may have changed).
     *
     * @return {void}
     */
    onEdit: function () {
        var me = this,
            dlg = new go.modules.community.marketplace.RepositoryDialog();
        // On save, the bound entity store fires an update -> the panel's reconcile
        // refreshes this section's title; reload the catalogue here since a token
        // or URL change means the catalogue may now differ.
        dlg.on('submit', function (d, success) {
            if (success) {
                me.loadCatalog();
            }
        });
        dlg.load(me.repo.id).show();
    },

    /**
     * Fetch the customer's own account (companyName + entitlements) and show it
     * read-only.
     *
     * @return {void}
     */
    onMyAccount: function () {
        var me = this;
        go.Jmap.request({
            method: "MarketplaceRepository/account",
            params: {repositoryId: me.repo.id},
            callback: function (options, success, response) {
                if (!success) {
                    GO.errorDialog.show((response && response.message) || t("Error"));
                    return;
                }
                var ents = response.entitlements || [],
                    rows = ents.length
                        ? ents.map(function (en) {
                            var exp = en.expiresAt
                                ? Ext.util.Format.date(new Date(en.expiresAt * 1000))
                                : t("Never", "marketplace", "community");
                            var product = Ext.util.Format.htmlEncode(en.product || '');
                            if (en.expired) {
                                // Expired grant: mark it clearly. Access may still
                                // come from another active grant (e.g. a collection)
                                // — that shows on its own row.
                                return '<tr style="color:var(--hue-red);"><td style="padding:2px 12px 2px 0;text-decoration:line-through;">' + product +
                                    '</td><td style="padding:2px 0;">' + Ext.util.Format.htmlEncode(exp) + ' — ' +
                                    Ext.util.Format.htmlEncode(t("License expired", "marketplace", "community")) + '</td></tr>';
                            }
                            return '<tr><td style="padding:2px 12px 2px 0">' + product +
                                '</td><td style="padding:2px 0;color:var(--text-muted)">' + Ext.util.Format.htmlEncode(exp) + '</td></tr>';
                        }).join('')
                        : '<tr><td>' + t("No records to display") + '</td></tr>';

                new go.Window({
                    title: t("My account", "marketplace", "community") +
                        (response.companyName ? ' — ' + Ext.util.Format.htmlEncode(response.companyName) : ''),
                    width: dp(460),
                    autoHeight: true,
                    modal: true,
                    bodyStyle: 'padding:' + dp(12) + 'px',
                    html: '<table>' + rows + '</table>',
                    buttons: [{text: t("Close"), handler: function () { this.ownerCt.ownerCt.close(); }}]
                }).show();
            },
            scope: me
        });
    },

    /**
     * Confirm + delete this repository; on success let the parent drop the
     * section.
     *
     * @return {void}
     */
    onDelete: function () {
        var me = this;
        Ext.MessageBox.confirm(
            t("Confirm delete"),
            t("Are you sure you want to delete this item?"),
            function (btn) {
                if (btn !== "yes") {
                    return;
                }
                // The bound entity store fires a remove -> the panel's reconcile
                // drops this section; no explicit callback needed.
                go.Db.store("MarketplaceRepository").set({destroy: [me.repo.id]});
            },
            me
        );
    }
});
