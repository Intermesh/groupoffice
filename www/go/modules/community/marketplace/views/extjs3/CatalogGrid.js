/* global go, Ext, dp, t, GO */

/**
 * Embeddable render of ONE repository's catalog (modules/collections), with
 * per-row install state carried by the Installed cell (orange = update
 * available), a Price cell, availability chips per GO branch, and a single
 * Download/Update/Buy action.
 *
 * Presents the catalog two ways over ONE shared local store:
 *   - a classic table (go.grid.GridPanel over a local JsonStore), and
 *   - an app-store-style card view (Ext.DataView + Ext.XTemplate).
 * The active view is switched externally via setViewMode() — this component has
 * NO toolbar of its own. It lives inside a RepositorySection, and the global
 * toolbar (view toggle, Owned filter, Upgrade readiness, Refresh, Add) belongs
 * to the parent SystemSettingsPanel which broadcasts to every section.
 *
 * NOT entity-store bound: there is no client-side Product entity — the server
 * owns catalog data. The owning RepositorySection fetches the catalog via the
 * custom `MarketplaceRepository/catalog` JMAP method and pushes the response in
 * through setData(); this component never fetches on its own (except an
 * onReload() callback it fires after a successful download so the section can
 * refetch).
 */
go.modules.community.marketplace.CatalogGrid = Ext.extend(Ext.Panel, {

    // Auto-height: the active view (grid or dataview) sizes to its content, the
    // section sizes to the view, and the whole section stack scrolls as one
    // area. The two views are stacked and shown/hidden (not a card layout,
    // which fights autoHeight).
    autoHeight: true,
    border: false,

    // 'table' | 'cards' — set by the parent; no per-component persistence.
    viewMode: 'table',

    // When true the store is filtered to owned products only.
    ownedOnly: false,

    // 'all' | 'module' | 'collection' — filters the store by product type.
    typeFilter: 'all',

    repositoryId: null,
    repositoryUrl: null,
    goVersion: null,
    // All GO branches the server publishes for (e.g. ["6.8","25","26"]) — drives
    // the availability chips. Set from the catalog response via setData().
    branches: null,

    // Optional callback the parent supplies; fired after a successful download so
    // the section can refetch the catalog (installed versions changed).
    onReload: null,

    initComponent: function () {
        var me = this;

        me.branches = me.branches || [];

        me.store = new Ext.data.JsonStore({
            fields: [
                'productId',
                'moduleName', 'title', 'type', 'description', 'logoUrl',
                'latestVersion', 'installedVersion', 'goVersion',
                'owned', 'free', 'price', 'currency', 'state',
                'hasActions', 'hideActions',
                'release', 'availability', 'modules',
                'licenseExpiresAt', 'licenseExpired'
            ],
            data: []
        });

        me.gridPanel = me.buildGridPanel();
        me.cardView = me.buildCardView();

        // Start with the inactive view hidden; applyViewMode toggles them.
        if (me.viewMode === 'cards') {
            me.gridPanel.hidden = true;
        } else {
            me.cardView.hidden = true;
        }

        me.items = [me.gridPanel, me.cardView];

        go.modules.community.marketplace.CatalogGrid.superclass.initComponent.call(me);
    },

    /**
     * Fill the store from a `MarketplaceRepository/catalog` response and remember
     * the branch metadata. Re-applies the Owned filter so a refetch doesn't
     * silently reveal the whole catalogue.
     *
     * @param {Object} response {goVersion, branches, products, downloaded}
     * @return {void}
     */
    setData: function (response) {
        this.goVersion = response.goVersion;
        this.branches = response.branches || [];
        this.package = response.package || null;
        this.installed = response.installed || {}; // moduleName -> installed in this GO
        this.store.loadData(this.buildRows(response.products || [], response.downloaded || {}));
        this.applyFilters();
    },

    /**
     * Nudge the auto-height chain (view -> section -> scrolling container) to
     * recompute after the content changed.
     *
     * @return {void}
     */
    reflow: function () {
        if (!this.rendered) {
            return;
        }
        this.doLayout();
        if (this.ownerCt) {
            this.ownerCt.doLayout();
            if (this.ownerCt.ownerCt) {
                this.ownerCt.ownerCt.doLayout();
            }
        }
    },

    /**
     * @return {Array} the GO branches this repository publishes for
     */
    getBranches: function () {
        return this.branches || [];
    },

    /**
     * The INSTALLED module rows (moduleName + installed version + availability),
     * used by the parent's upgrade-readiness aggregation.
     *
     * @return {Array} [{title, moduleName, installed, availability}]
     */
    getInstalledModules: function () {
        var mods = [];
        this.store.each(function (rec) {
            if (rec.get('moduleName') && rec.get('installedVersion')) {
                mods.push({
                    title: rec.get('title') || rec.get('moduleName'),
                    moduleName: rec.get('moduleName'),
                    installed: rec.get('installedVersion'),
                    availability: rec.get('availability') || {}
                });
            }
        });
        return mods;
    },

    /**
     * The table view. Its RowActions plugin renders a single kebab (⋮) per row
     * that opens the module action menu (showModuleMenu).
     *
     * @return {go.grid.GridPanel}
     */
    buildGridPanel: function () {
        var me = this,
            actions = me.initRowActions();

        return new go.grid.GridPanel({
            store: me.store,
            autoHeight: true,
            autoExpandColumn: 'title',
            // The catalog store is a LOCAL JsonStore (data pushed in via setData),
            // not an entity store, so switch off the two go.grid.GridPanel features
            // that assume one: delete-key calls store.entityStore.set() (would throw
            // here) and the scroll loader expects server-side paging.
            enableDelete: false,
            scrollLoader: false,
            plugins: [actions],
            selModel: new Ext.grid.RowSelectionModel(),
            viewConfig: {
                emptyText: '<i class="icon ic-store"></i><p>' + t("No records to display") + '</p>'
            },
            columns: [
                {
                    id: 'title',
                    header: t("Name", "marketplace", "community"),
                    dataIndex: 'title',
                    sortable: true,
                    renderer: function (v, meta, rec) {
                        var d = rec.get('description');
                        if (d) {
                            meta.attr = 'ext:qtip="' +
                                Ext.util.Format.htmlEncode(d).replace(/"/g, '&quot;') + '"';
                        }
                        return Ext.util.Format.htmlEncode(v || '');
                    }
                },
                {
                    header: t("Type", "marketplace", "community"),
                    dataIndex: 'type',
                    width: dp(110),
                    sortable: true,
                    renderer: function (v) {
                        return Ext.util.Format.htmlEncode(
                            v === 'collection' ? t("Collection", "marketplace", "community") : t("Module", "marketplace", "community")
                        );
                    }
                },
                {
                    header: t("Latest version", "marketplace", "community"),
                    dataIndex: 'latestVersion',
                    width: dp(120),
                    sortable: true,
                    renderer: function (v) {
                        return v ? Ext.util.Format.htmlEncode(v) : '-';
                    }
                },
                {
                    header: t("Availability", "marketplace", "community"),
                    dataIndex: 'availability',
                    width: dp(160),
                    sortable: false,
                    renderer: function (v, meta, rec) {
                        return me.availabilityChipsHtml(rec.get('availability'), rec.get('moduleName'));
                    }
                },
                {
                    header: t("Installed version", "marketplace", "community"),
                    dataIndex: 'installedVersion',
                    width: dp(140),
                    sortable: true,
                    renderer: function (v, meta, rec) {
                        // The installed cell doubles as the install state: empty =
                        // not installed; orange = an update is available (installed
                        // < latest). The action button + Price cell carry the rest.
                        if (!v) {
                            return '<span style="color:var(--fg-secondary-text);">-</span>';
                        }
                        var latest = rec.get('latestVersion');
                        if (latest && me.compareVersions(latest, v) > 0) {
                            meta.attr = 'ext:qtip="' + Ext.util.Format.htmlEncode(
                                t("Update available", "marketplace", "community")) + '"';
                            return '<span style="color:var(--hue-orange);">' + Ext.util.Format.htmlEncode(v) + '</span>';
                        }
                        return Ext.util.Format.htmlEncode(v);
                    }
                },
                {
                    header: t("Price", "marketplace", "community"),
                    dataIndex: 'price',
                    width: dp(120),
                    sortable: true,
                    renderer: function (v, meta, rec) {
                        return me.priceHtml(rec.data);
                    }
                },
                {
                    header: t("License", "marketplace", "community"),
                    dataIndex: 'licenseExpiresAt',
                    width: dp(150),
                    sortable: true,
                    renderer: function (v, meta, rec) {
                        return me.licenseHtml(rec.data);
                    }
                },
                actions
            ]
        });
    },

    /**
     * The card view — an Ext.DataView bound to the SAME store as the grid.
     * One card per product: logo/type icon, title, type label, latest version,
     * installed version (orange when an update is available), availability
     * chips, a price line, and a kebab (⋮) in the header that opens the module
     * action menu (showModuleMenu).
     *
     * @return {Ext.DataView}
     */
    buildCardView: function () {
        var me = this;

        var tpl = new Ext.XTemplate(
            '<tpl for=".">',
                // display:flex + the parent grid container (see the DataView style
                // below) gives every card in a visual row the SAME height — an
                // inline-flex card sizes to its own content, which made rows ragged.
                '<div class="mp-card" style="display:flex; flex-direction:column; box-sizing:border-box; min-height:200px; padding:12px; border:1px solid var(--fg-border); border-radius:8px; background:var(--bg-box);">',
                    '<div style="display:flex; align-items:flex-start;">',
                        '<tpl if="values.logoUrl">',
                            '<img src="{logoUrl}" style="width:36px; height:36px; object-fit:contain; margin-right:10px;" onerror="this.style.display=\'none\';"/>',
                        '</tpl>',
                        '<tpl if="!values.logoUrl">',
                            '<i class="icon {[this.typeIconCls(values)]}" style="font-size:32px; color:var(--c-primary); margin-right:10px; line-height:1;"></i>',
                        '</tpl>',
                        '<div style="min-width:0; flex:1;">',
                            '<div style="font-weight:bold; color:var(--fg-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{title:htmlEncode}</div>',
                            '<div style="font-size:11px; color:var(--fg-secondary-text);">{[this.typeLabel(values)]}</div>',
                        '</div>',
                        '<tpl if="values.hasActions">',
                            '<i class="icon ic-more-vert mp-card-menu" title="' + t("Actions", "marketplace", "community") + '" style="cursor:pointer; color:var(--fg-secondary-text); padding:2px; margin-left:4px;"></i>',
                        '</tpl>',
                    '</div>',
                    '<tpl if="values.description">',
                        '<div style="margin-top:8px; font-size:12px; color:var(--fg-secondary-text); max-height:54px; overflow:hidden;">{description:htmlEncode}</div>',
                    '</tpl>',
                    '<div style="margin-top:10px; font-size:12px; color:var(--fg-secondary-text);">',
                        '<div>' + t("Latest version", "marketplace", "community") + ': <span style="color:var(--fg-text);">{[this.versionText(values.latestVersion)]}</span></div>',
                        '<div>' + t("Installed version", "marketplace", "community") + ': {[this.installedHtml(values)]}</div>',
                    '</div>',
                    '<tpl if="values.moduleName">',
                        '<div style="margin-top:8px; font-size:12px; color:var(--fg-secondary-text);">',
                            t("Availability", "marketplace", "community") + ': {[this.availabilityChips(values)]}',
                        '</div>',
                    '</tpl>',
                    '<tpl if="values.licenseExpiresAt || values.licenseExpired">',
                        '<div style="margin-top:8px; font-size:12px;">{[this.licenseHtml(values)]}</div>',
                    '</tpl>',
                    '<div style="margin-top:auto; padding-top:8px; text-align:right; font-weight:bold;">{[this.priceHtml(values)]}</div>',
                '</div>',
            '</tpl>',
            {
                // ---- member functions (called as {[this.fn(...)]}) --------
                typeIconCls: function (values) {
                    return values.type === 'collection' ? 'ic-collections' : 'ic-extension';
                },
                typeLabel: function (values) {
                    return Ext.util.Format.htmlEncode(
                        values.type === 'collection'
                            ? t("Collection", "marketplace", "community")
                            : t("Module", "marketplace", "community")
                    );
                },
                versionText: function (v) {
                    return v ? Ext.util.Format.htmlEncode(v) : '-';
                },
                installedHtml: function (values) {
                    var v = values.installedVersion;
                    if (!v) {
                        return '<span style="color:var(--fg-text);">-</span>';
                    }
                    if (values.latestVersion && me.compareVersions(values.latestVersion, v) > 0) {
                        return '<span style="color:var(--hue-orange);">' + Ext.util.Format.htmlEncode(v) + '</span>';
                    }
                    return '<span style="color:var(--fg-text);">' + Ext.util.Format.htmlEncode(v) + '</span>';
                },
                priceHtml: function (values) {
                    return me.priceHtml(values);
                },
                licenseHtml: function (values) {
                    return me.licenseHtml(values);
                },
                availabilityChips: function (values) {
                    return me.availabilityChipsHtml(values.availability, values.moduleName);
                }
            }
        );

        var view = new Ext.DataView({
            store: me.store,
            tpl: tpl,
            itemSelector: 'div.mp-card',
            autoHeight: true,
            // CSS grid: auto-fill columns of equal width, and — crucially — each
            // grid row stretches its cards to the same height (align-items:stretch
            // is the grid default), so cards line up in a tidy matrix instead of
            // the ragged inline-flex flow.
            style: 'display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:10px; padding:8px; align-items:stretch;',
            emptyText: '<div style="padding:24px; text-align:center; color:var(--fg-secondary-text);">' +
                '<i class="icon ic-store" style="font-size:48px; display:block; margin-bottom:8px;"></i>' +
                t("No records to display") + '</div>'
        });

        view.on('click', function (dv, index, node, e) {
            if (e.getTarget('.mp-card-menu')) {
                e.stopEvent();
                var rec = me.store.getAt(index);
                if (rec) {
                    me.showModuleMenu(rec, e.getXY());
                }
            }
        });

        return view;
    },

    /**
     * Switch the active view (no persistence — the parent owns view state).
     *
     * @param {String} mode 'table' | 'cards'
     * @return {void}
     */
    setViewMode: function (mode) {
        this.viewMode = mode;
        if (this.rendered) {
            this.applyViewMode(mode);
        }
    },

    /**
     * Show the active view and hide the other (both are auto-height), then
     * reflow.
     *
     * @param {String} mode 'table' | 'cards'
     * @return {void}
     */
    applyViewMode: function (mode) {
        var showCards = (mode === 'cards');
        if (this.cardView) {
            this.cardView.setVisible(showCards);
        }
        if (this.gridPanel) {
            this.gridPanel.setVisible(!showCards);
        }
        this.reflow();
    },

    /**
     * How many catalogue rows currently have an update available (installed <
     * latest). Drives the "N updates" badge; computed from the loaded store, so
     * it reflects the data as of the last catalog load/refresh.
     *
     * @return {Number}
     */
    getUpdateCount: function () {
        var n = 0;
        this.store.each(function (rec) {
            if (rec.get('state') === 'update') {
                n++;
            }
        });
        return n;
    },

    /**
     * The modules that have an update available (state 'update'), for the
     * parent's "Update all".
     *
     * @return {Array} [{module, version}]
     */
    getUpdatable: function () {
        var out = [];
        this.store.each(function (rec) {
            if (rec.get('state') === 'update') {
                var release = rec.get('release') || {};
                out.push({module: rec.get('moduleName'), version: release.version || ''});
            }
        });
        return out;
    },

    initRowActions: function () {
        var me = this,
            actions = new Ext.ux.grid.RowActions({
                // A single kebab (⋮) per row opens the module action menu
                // (Download/Update/Buy + Changelog). Hidden when a row has no
                // actions (e.g. an owned collection).
                width: dp(50),
                menuDisabled: true,
                hideable: false,
                draggable: false,
                fixed: true,
                header: '',
                hideMode: 'display',
                keepSelection: true,
                actions: [{
                    iconCls: 'ic-more-vert',
                    tooltip: t("Actions", "marketplace", "community"),
                    hideIndex: 'hideActions'
                }]
            });

        actions.on({
            action: function (grid, record, action, row, col, e) {
                me.showModuleMenu(record, e ? e.getXY() : null);
            },
            scope: me
        });

        return actions;
    },

    /**
     * The available actions for a catalogue row, shared by the grid kebab and
     * the card kebab so both offer exactly the same menu.
     *
     * @param {Object} d {state, moduleName, release}
     * @return {Array} [{key, iconCls, text}]
     */
    moduleActionList: function (d) {
        var acts = [],
            installedInGo = !!(this.installed && this.installed[d.moduleName]),
            downloaded = d.state === 'update' || d.state === 'installed'; // files present locally

        // A collection has no module files of its own — its "download" fans out to
        // every member module. Offer that when the collection is free or owned;
        // otherwise it's a purchase.
        if (d.type === 'collection') {
            if (d.state === 'buy') {
                acts.push({key: 'buy', iconCls: 'ic-shopping-cart', text: t("Buy", "marketplace", "community")});
            } else if (d.modules && d.modules.length) {
                acts.push({key: 'downloadCollection', iconCls: 'ic-get-app', text: t("Download all", "marketplace", "community")});
            }
            return acts;
        }

        if (d.state === 'buy') {
            acts.push({key: 'buy', iconCls: 'ic-shopping-cart', text: t("Buy", "marketplace", "community")});
        } else if (d.moduleName && d.state === 'update') {
            acts.push({key: 'download', iconCls: 'ic-get-app', text: t("Update", "marketplace", "community")});
        } else if (d.moduleName && (d.state === 'ownedNotDownloaded' || d.state === 'free')) {
            acts.push({key: 'download', iconCls: 'ic-get-app', text: t("Download", "marketplace", "community")});
        } else if (d.moduleName && d.state === 'installed') {
            acts.push({key: 'download', iconCls: 'ic-get-app', text: t("Download", "marketplace", "community")});
        }

        // The marketplace only DOWNLOADS the files — installing (registering the
        // module in Group-Office) is a separate step. Offer it right here for a
        // downloaded module that isn't installed yet (same as System Settings →
        // Modules → Install).
        if (d.moduleName && downloaded && !installedInGo) {
            acts.push({key: 'install', iconCls: 'ic-extension', text: t("Install", "marketplace", "community")});
        }

        if (d.release && d.release.changelog) {
            acts.push({key: 'changelog', iconCls: 'ic-description', text: t("Changelog", "marketplace", "community")});
        }
        return acts;
    },

    /**
     * Build + show the per-module action menu at the given screen point.
     *
     * @param {Ext.data.Record} record
     * @param {Array} xy [x, y] or null
     * @return {void}
     */
    showModuleMenu: function (record, xy) {
        var me = this,
            acts = me.moduleActionList(record.data);
        if (!acts.length) {
            return;
        }
        var menu = new Ext.menu.Menu({
            items: acts.map(function (a) {
                return {
                    iconCls: a.iconCls,
                    text: a.text,
                    handler: function () { me.runModuleAction(a.key, record); }
                };
            })
        });
        menu.showAt(xy || Ext.EventObject.getXY());
    },

    /**
     * @param {String} key 'buy' | 'download' | 'changelog'
     * @param {Ext.data.Record} record
     * @return {void}
     */
    runModuleAction: function (key, record) {
        if (key === 'buy') {
            this.onBuy(record);
        } else if (key === 'download') {
            this.onDownload(record);
        } else if (key === 'downloadCollection') {
            this.onDownloadCollection(record);
        } else if (key === 'install') {
            this.onInstall(record);
        } else if (key === 'changelog') {
            this.showChangelog(record);
        }
    },

    /**
     * Install a downloaded module into this Group-Office — the same JMAP
     * Module/install the System Settings → Modules grid uses. On success the
     * catalogue reloads (so the Install action disappears) and the user is told
     * to reload the page so the module's UI loads.
     *
     * @param {Ext.data.Record} record
     * @return {void}
     */
    onInstall: function (record) {
        var me = this,
            moduleName = record.get('moduleName'),
            title = record.get('title') || moduleName;

        if (!me.package) {
            GO.errorDialog.show(t("Error"));
            return;
        }

        var maskEl = me.getEl();
        if (maskEl) {
            maskEl.mask(
                Ext.util.Format.htmlEncode(t("Installing {name}…", "marketplace", "community").replace("{name}", title)),
                'x-mask-loading'
            );
        }

        go.Jmap.request({
            method: "Module/install",
            params: {name: moduleName, package: me.package},
            callback: function (options, success, response) {
                if (maskEl) {
                    maskEl.unmask();
                }
                if (!success) {
                    GO.errorDialog.show((response && response.message) || t("Error"));
                    return;
                }
                go.Notifier.flyout({
                    description: t("The module {name} was installed. Reload the page to use it.", "marketplace", "community")
                        .replace("{name}", Ext.util.Format.htmlEncode(title)),
                    time: 8000
                });
                // Show the standard group-permissions dialog right after install,
                // like System Settings → Modules does.
                var mod = response && response.list && response.list[0];
                if (mod) {
                    me.showModulePermissions(mod.id, moduleName);
                }
                if (me.onReload) {
                    me.onReload();
                }
            },
            scope: me
        });
    },

    /**
     * Open the standard group-permissions dialog (go.modules.GroupRights) for a
     * just-installed module, fetching its declared right names from the server.
     *
     * @param {Number} moduleId the core_module id
     * @param {String} moduleName
     * @return {void}
     */
    showModulePermissions: function (moduleId, moduleName) {
        var me = this;
        go.Jmap.request({
            method: "MarketplaceRepository/moduleRights",
            params: {name: moduleName, package: me.package},
            callback: function (options, success, response) {
                if (!success || !response) {
                    return;
                }
                var dlg = new go.modules.GroupRights();
                go.Db.store('Module').single(moduleId).then(function (module) {
                    dlg.show(module, response.rights || []);
                });
            },
            scope: me
        });
    },

    /**
     * Show a module's release changelog in a read-only window.
     *
     * @param {Ext.data.Record} record
     * @return {void}
     */
    showChangelog: function (record) {
        var release = record.get('release') || {},
            win = new go.Window({
                title: t("Changelog", "marketplace", "community") + ' — ' +
                    Ext.util.Format.htmlEncode(record.get('title') || record.get('moduleName') || ''),
                width: dp(560),
                height: dp(480),
                modal: true,
                layout: 'fit',
                items: [{
                    xtype: 'panel',
                    border: false,
                    autoScroll: true,
                    bodyStyle: 'padding:12px; white-space:pre-wrap; color:var(--fg-text);',
                    html: Ext.util.Format.htmlEncode(release.changelog || '')
                }],
                buttons: [{text: t("Close"), handler: function () { win.close(); }}]
            });
        win.show();
    },

    /**
     * The Price cell shared by the grid renderer and the card template. Always
     * shows the actual price (a product costs what it costs regardless of
     * whether you own it): free -> "Free" (green), otherwise the price (or "-"
     * when a buyable product has no price set). Ownership is conveyed by the
     * "Owned" toolbar filter + the action button (Download when owned, Buy when
     * not), and the install/update state by the Installed cell.
     *
     * @param {Object} data record data (free/price/currency)
     * @return {String} html
     */
    priceHtml: function (data) {
        if (data.free) {
            return '<span style="color:var(--hue-green);">' +
                Ext.util.Format.htmlEncode(t("Free", "marketplace", "community")) + '</span>';
        }
        var v = data.price;
        if (v === null || v === undefined || v === '') {
            return '<span style="color:var(--fg-secondary-text);">-</span>';
        }
        return '<span style="color:var(--fg-text);">' +
            Ext.util.Format.htmlEncode(Number(v).toFixed(2) + ' ' + (data.currency || '')) + '</span>';
    },

    /**
     * License-expiry indicator, shared by the grid cell and the card. Empty when
     * the product carries no dated license (perpetual / free / not owned and never
     * had a grant). Red when expired, orange when expiring within 30 days.
     *
     * @param {Object} data record data (licenseExpired / licenseExpiresAt)
     * @return {String} html
     */
    licenseHtml: function (data) {
        if (data.licenseExpired) {
            var exp = data.licenseExpiresAt
                ? ' — ' + Ext.util.Format.date(new Date(data.licenseExpiresAt * 1000))
                : '';
            return '<span style="color:var(--hue-red);">' +
                Ext.util.Format.htmlEncode(t("License expired", "marketplace", "community")) + exp + '</span>';
        }
        if (data.licenseExpiresAt) {
            var ms = data.licenseExpiresAt * 1000,
                soon = (ms - new Date().getTime()) < (30 * 24 * 60 * 60 * 1000),
                color = soon ? 'var(--hue-orange)' : 'var(--fg-secondary-text)';
            return '<span style="color:' + color + ';">' +
                Ext.util.Format.htmlEncode(t("Expires", "marketplace", "community")) + ' ' +
                Ext.util.Format.date(new Date(ms)) + '</span>';
        }
        return '';
    },

    /**
     * True when a marketplace branch string is this instance's current GO
     * branch. Mirrors the server's dot-boundary match so it is robust for the
     * year-based scheme (goVersion "25.0" vs branch "25").
     *
     * @param {String} branch
     * @return {Boolean}
     */
    isCurrentBranch: function (branch) {
        var cur = this.goVersion || '';
        if (!cur || !branch) {
            return false;
        }
        return branch === cur ||
            cur.indexOf(branch + '.') === 0 ||
            branch.indexOf(cur + '.') === 0;
    },

    /**
     * Render one chip per published branch: green when the module has a build
     * for that branch (version in the tooltip), muted/struck-through when it
     * doesn't. Only module products carry releases; collections show "-".
     *
     * @param {Object} availability branch -> latest version
     * @param {String} moduleName
     * @return {String} html
     */
    availabilityChipsHtml: function (availability, moduleName) {
        var me = this,
            av = availability || {},
            branches = me.branches && me.branches.length ? me.branches : [];

        if (!branches.length) {
            for (var k in av) {
                if (av.hasOwnProperty(k)) {
                    branches.push(k);
                }
            }
        }

        if (!moduleName || !branches.length) {
            return '<span style="color:var(--fg-secondary-text);">-</span>';
        }

        var base = 'display:inline-block; margin:1px 4px 1px 0; padding:1px 6px; border-radius:3px; font-size:11px; line-height:1.5;';

        return branches.map(function (b) {
            var ver = av[b],
                current = me.isCurrentBranch(b) ? ' box-shadow:0 0 0 1px var(--c-primary);' : '';
            if (ver) {
                return '<span style="' + base + current + ' background:var(--hue-green); color:#fff;" ext:qtip="' +
                    Ext.util.Format.htmlEncode(b + ' – ' + ver) + '">' +
                    Ext.util.Format.htmlEncode(b) + '</span>';
            }
            return '<span style="' + base + current + ' background:var(--bg-box); color:var(--fg-secondary-text); border:1px solid var(--fg-border); text-decoration:line-through;" ext:qtip="' +
                Ext.util.Format.htmlEncode(t("No build for Group-Office {branch} yet", "marketplace", "community").replace("{branch}", b)) + '">' +
                Ext.util.Format.htmlEncode(b) + '</span>';
        }).join('');
    },

    /**
     * Filter the shared store to owned products (or clear the filter).
     *
     * @param {Boolean} ownedOnly
     * @return {void}
     */
    applyOwnedFilter: function (ownedOnly) {
        this.ownedOnly = ownedOnly;
        this.applyFilters();
    },

    /**
     * Filter the shared store by product type ('all' | 'module' | 'collection').
     *
     * @param {String} type
     * @return {void}
     */
    applyTypeFilter: function (type) {
        this.typeFilter = type || 'all';
        this.applyFilters();
    },

    /**
     * Apply the combined Owned + type filter to the shared store (both the grid
     * and the card view read from it). Kept as one filterBy so the two toolbar
     * filters compose instead of overwriting each other.
     *
     * @return {void}
     */
    applyFilters: function () {
        var ownedOnly = this.ownedOnly,
            type = this.typeFilter || 'all';

        if (!ownedOnly && type === 'all') {
            this.store.clearFilter();
        } else {
            this.store.filterBy(function (rec) {
                // "Mine" = modules you own (entitlement) OR already have installed/
                // downloaded. Free modules aren't "owned" but a downloaded one is
                // still yours, so filtering on `owned` alone wrongly hid it.
                if (ownedOnly && !rec.get('owned') && !rec.get('installedVersion')) {
                    return false;
                }
                if (type !== 'all' && rec.get('type') !== type) {
                    return false;
                }
                return true;
            });
        }
        this.reflow();
    },

    /**
     * Transform server product rows into the local store's flat shape,
     * computing the install state and whether the row has any actions (so the
     * kebab can hide when it doesn't).
     *
     * @param {Array} products
     * @param {Object} downloaded moduleName -> installed version
     * @return {Array}
     */
    buildRows: function (products, downloaded) {
        var me = this,
            rows = [];

        Ext.each(products, function (p) {
            var release = p.release || null,
                latestVersion = release ? release.version : null,
                installedVersion = downloaded[p.moduleName] || null,
                state;

            // Downloadable = the customer owns it (entitlement) OR it's free.
            var downloadable = p.owned || p.free;
            if (downloadable) {
                if (installedVersion) {
                    state = (latestVersion && me.compareVersions(latestVersion, installedVersion) > 0)
                        ? 'update' : 'installed';
                } else {
                    state = p.owned ? 'ownedNotDownloaded' : 'free';
                }
            } else {
                state = 'buy';
            }

            var hasActions = me.moduleActionList({
                state: state, moduleName: p.moduleName, release: release,
                type: p.type, modules: p.modules || []
            }).length > 0;

            rows.push({
                productId: p.id,
                moduleName: p.moduleName,
                title: p.title,
                type: p.type,
                description: p.description,
                logoUrl: p.logoUrl || null,
                latestVersion: latestVersion,
                installedVersion: installedVersion,
                goVersion: release ? release.goVersion : null,
                owned: !!p.owned,
                free: !!p.free,
                price: p.price,
                currency: p.currency,
                state: state,
                hasActions: hasActions,
                hideActions: !hasActions,
                release: release,
                availability: p.availability || {},
                modules: p.modules || [],
                licenseExpiresAt: p.licenseExpiresAt || null,
                licenseExpired: !!p.licenseExpired
            });
        });

        return rows;
    },

    /**
     * Minimal dotted-numeric version comparator.
     *
     * @param {String} a
     * @param {String} b
     * @return {Number} >0 if a>b, <0 if a<b, 0 if equal
     */
    compareVersions: function (a, b) {
        var pa = String(a).split('.'),
            pb = String(b).split('.'),
            len = Math.max(pa.length, pb.length),
            i, na, nb;

        for (i = 0; i < len; i++) {
            na = parseInt(pa[i], 10) || 0;
            nb = parseInt(pb[i], 10) || 0;
            if (na !== nb) {
                return na - nb;
            }
        }
        return 0;
    },

    onDownload: function (record) {
        var me = this,
            release = record.get('release'),
            module = record.get('moduleName'),
            title = record.get('title') || module,
            isUpdate = record.get('state') === 'update';

        // Download + extract + install can take a few seconds and gives no
        // interim feedback otherwise — mask the catalogue so the user sees
        // something is happening.
        var maskEl = me.getEl();
        if (maskEl) {
            maskEl.mask(
                Ext.util.Format.htmlEncode(
                    (isUpdate ? t("Updating {name}…", "marketplace", "community") : t("Downloading {name}…", "marketplace", "community"))
                        .replace("{name}", title)
                ),
                'x-mask-loading'
            );
        }

        go.Jmap.request({
            method: "MarketplaceRepository/download",
            params: {
                repositoryId: me.repositoryId,
                module: module,
                version: release ? release.version : ''
            },
            callback: function (options, success, response) {
                if (maskEl) {
                    maskEl.unmask();
                }
                if (!success) {
                    GO.errorDialog.show((response && response.message) || t("Error"));
                    return;
                }
                // go.Notifier.flyout injects `description` via innerHTML — html-encode
                // the dynamic module name.
                var name = Ext.util.Format.htmlEncode((response && response.module) || module);
                var msg = isUpdate
                    ? t("The module {name} was updated. Run the upgrade to apply it.", "marketplace", "community")
                    : t("The module {name} was downloaded. Install it in System Settings → Modules.", "marketplace", "community");
                go.Notifier.flyout({
                    description: msg.replace("{name}", name),
                    time: 6000
                });
                if (me.onReload) {
                    me.onReload();
                }
            },
            scope: me
        });
    },

    /**
     * Download every member module of a free/owned collection, one after
     * another. A collection ships no files of its own — acquiring all its
     * modules is what makes it "yours" (each free member is recorded server-side,
     * so the collection then reads as owned). The user decides which of the
     * downloaded modules to actually install.
     *
     * @param {Ext.data.Record} record
     * @return {void}
     */
    onDownloadCollection: function (record) {
        var me = this,
            title = record.get('title') || t("Collection", "marketplace", "community"),
            modules = (record.get('modules') || []).slice();

        if (!modules.length) {
            return;
        }

        var maskEl = me.getEl();
        if (maskEl) {
            maskEl.mask(
                Ext.util.Format.htmlEncode(
                    t("Downloading {name}…", "marketplace", "community").replace("{name}", title)),
                'x-mask-loading'
            );
        }

        var failed = [],
            i = 0;

        function next() {
            if (i >= modules.length) {
                if (maskEl) {
                    maskEl.unmask();
                }
                if (failed.length) {
                    // Show the SERVER's real reason per module (e.g. "No entitlement
                    // for this module", "Release not found") rather than assuming
                    // "no build". The download controller surfaces the server's JSON
                    // error message when the body isn't a ZIP.
                    var lines = failed.map(function (f) {
                        return Ext.util.Format.htmlEncode(f.module + " — " + f.message);
                    });
                    Ext.MessageBox.show({
                        title: t("Download all", "marketplace", "community"),
                        msg: t("Some modules from the collection {name} could not be downloaded:", "marketplace", "community")
                                .replace("{name}", Ext.util.Format.htmlEncode(title))
                            + "<br><br>" + lines.join("<br>"),
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.WARNING
                    });
                } else {
                    go.Notifier.flyout({
                        description: t("The collection {name} was downloaded. Install its modules in System Settings → Modules.", "marketplace", "community")
                            .replace("{name}", Ext.util.Format.htmlEncode(title)),
                        time: 7000
                    });
                }
                if (me.onReload) {
                    me.onReload();
                }
                return;
            }
            var moduleName = modules[i++];
            go.Jmap.request({
                method: "MarketplaceRepository/download",
                params: {repositoryId: me.repositoryId, module: moduleName, version: ''},
                callback: function (options, success, response) {
                    if (!success) {
                        failed.push({
                            module: moduleName,
                            message: (response && response.message) || t("Error")
                        });
                    }
                    next();
                },
                scope: me
            });
        }

        next();
    },

    /**
     * Start a hosted checkout for the product: ask the server for the gateway
     * redirect URL and open it in a new tab. When the payment completes the
     * server's webhook grants the entitlement; the user returns and presses
     * Refresh to download. A server without an active gateway answers 503, which
     * surfaces as the "contact the vendor" message.
     *
     * @param {Ext.data.Record} record
     * @return {void}
     */
    onBuy: function (record) {
        var me = this,
            productId = record.get('productId'),
            title = record.get('title') || record.get('moduleName') || '';

        if (!productId) {
            GO.errorDialog.show(t("Error"));
            return;
        }

        // Open the tab synchronously (before the async response) so the browser
        // does not treat it as a blocked popup, then point it at the gateway URL.
        var win = window.open('', '_blank');

        go.Jmap.request({
            method: "MarketplaceRepository/checkout",
            params: {repositoryId: me.repositoryId, productId: productId},
            callback: function (options, success, response) {
                if (!success || !response || !response.url) {
                    if (win) { win.close(); }
                    GO.errorDialog.show((response && response.message) ||
                        t("Purchasing isn't available. Please contact the vendor to buy this module.", "marketplace", "community"));
                    return;
                }
                if (win) {
                    win.location = response.url;
                } else {
                    window.open(response.url, '_blank');
                }
                go.Notifier.flyout({
                    description: t("Complete your purchase of {name} in the opened tab, then press Refresh to download it.", "marketplace", "community")
                        .replace("{name}", Ext.util.Format.htmlEncode(title)),
                    time: 8000
                });
            },
            scope: me
        });
    }
});
