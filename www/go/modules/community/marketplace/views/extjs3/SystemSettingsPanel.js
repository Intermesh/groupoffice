/* global go, Ext, dp, t, GO */

/**
 * Marketplace system settings — a single scrollable stack of collapsible
 * repository sections (GO-Modules style) instead of a west selector + center
 * split. This panel owns the GLOBAL toolbar (table/cards toggle, Owned filter,
 * Update all, Upgrade readiness, Refresh, Add repository) and broadcasts to
 * every RepositorySection; each section renders one repository's catalogue.
 *
 * The repository list is held in ONE entity-bound go.data.Store, loaded once and
 * kept in sync by the entity store (add/edit/delete via dialogs and the section
 * gear reflect automatically). A debounced reconcile() maps the store's current
 * records to sections: it adds a section for a new repository (whose catalogue
 * loads progressively and appears when it settles), removes a section for a
 * deleted one, and updates a renamed one in place. Nothing is torn down and
 * rebuilt wholesale, so there is no empty-state flicker.
 */
go.modules.community.marketplace.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {

    iconCls: 'ic-shopping-cart',
    itemId: "marketplace",
    layout: "fit",
    title: t('Marketplace', 'marketplace', 'community'),

    // Persist the table/cards choice across sessions.
    stateful: true,
    stateId: 'community-marketplace-catalog-panel',
    stateEvents: ['viewmodechange'],

    // 'table' | 'cards' — overwritten by applyState() when a saved value exists.
    viewMode: 'table',
    ownedOnly: false,
    // 'all' | 'module' | 'collection' — filters the catalogue by product type.
    typeFilter: 'all',

    // The client's current GO branch, captured from the first settled catalogue
    // (identical across repositories — it is this instance's own version).
    goVersion: null,

    initComponent: function () {
        var me = this;

        me.addEvents('viewmodechange');

        // Sections still loading their catalogue, keyed by repository id, so a
        // reconcile that runs before a section settles doesn't create a duplicate.
        me.pendingSections = {};

        me.repoStore = new go.data.Store({
            fields: ['id', 'name', 'url', {name: 'lastSyncAt', type: 'date'}, 'lastError', 'keyMismatch', 'permissionLevel'],
            entityStore: "MarketplaceRepository",
            sortInfo: {field: 'name', direction: 'ASC'}
        });

        // Coalesce the many events a single change fires into one reconcile.
        me.reconcileTask = new Ext.util.DelayedTask(me.reconcile, me);
        me.repoStore.on({
            load: function () { me.reconcileTask.delay(30); },
            add: function () { me.reconcileTask.delay(30); },
            remove: function () { me.reconcileTask.delay(30); },
            update: function () { me.reconcileTask.delay(30); },
            datachanged: function () { me.reconcileTask.delay(30); },
            scope: me
        });

        me.tableBtn = new Ext.Button({
            iconCls: 'ic-view-list',
            tooltip: t("Table view", "marketplace", "community"),
            enableToggle: true,
            toggleGroup: 'mp-catalog-view',
            allowDepress: false,
            pressed: true,
            toggleHandler: function (btn, pressed) {
                if (pressed) { me.setViewMode('table'); }
            }
        });

        me.cardBtn = new Ext.Button({
            iconCls: 'ic-view-module',
            tooltip: t("Card view", "marketplace", "community"),
            enableToggle: true,
            toggleGroup: 'mp-catalog-view',
            allowDepress: false,
            toggleHandler: function (btn, pressed) {
                if (pressed) { me.setViewMode('cards'); }
            }
        });

        me.ownedToggle = new Ext.Button({
            iconCls: 'ic-verified-user',
            text: t("My modules", "marketplace", "community"),
            tooltip: t("Show only modules you own or have installed", "marketplace", "community"),
            enableToggle: true,
            toggleHandler: function (btn, pressed) {
                me.setOwnedOnly(pressed);
            }
        });

        // Product-type filter: All / Modules / Collections (one active at a time).
        me.allBtn = new Ext.Button({
            text: t("All"),
            enableToggle: true,
            toggleGroup: 'mp-catalog-type',
            allowDepress: false,
            pressed: true,
            toggleHandler: function (btn, pressed) {
                if (pressed) { me.setTypeFilter('all'); }
            }
        });
        me.modulesBtn = new Ext.Button({
            text: t("Modules", "marketplace", "community"),
            enableToggle: true,
            toggleGroup: 'mp-catalog-type',
            allowDepress: false,
            toggleHandler: function (btn, pressed) {
                if (pressed) { me.setTypeFilter('module'); }
            }
        });
        me.collectionsBtn = new Ext.Button({
            text: t("Collections", "marketplace", "community"),
            enableToggle: true,
            toggleGroup: 'mp-catalog-type',
            allowDepress: false,
            toggleHandler: function (btn, pressed) {
                if (pressed) { me.setTypeFilter('collection'); }
            }
        });

        me.tbar = {
            // Items that don't fit collapse into a ">>" overflow menu — this
            // toolbar is long (Add/Refresh/view/type filters/My modules/Update
            // all/Upgrade readiness) and would otherwise clip on narrow screens.
            enableOverflow: true,
            items: [
                {
                    text: t("Add repository", "marketplace", "community"),
                    iconCls: 'ic-add',
                    handler: me.onAddRepository,
                    scope: me
                },
                '-',
                {
                    iconCls: 'ic-refresh',
                    text: t("Refresh", "marketplace", "community"),
                    handler: me.onRefreshAll,
                    scope: me
                },
                me.tableBtn,
                me.cardBtn,
                '-',
                me.allBtn,
                me.modulesBtn,
                me.collectionsBtn,
                '-',
                me.ownedToggle,
                {
                    iconCls: 'ic-get-app',
                    text: t("Update all", "marketplace", "community"),
                    tooltip: t("Download every available update across all repositories.", "marketplace", "community"),
                    handler: me.onUpdateAll,
                    scope: me
                },
                {
                    iconCls: 'ic-system-update-alt',
                    text: t("Upgrade readiness", "marketplace", "community"),
                    tooltip: t("Check whether the modules you use have a build for a newer Group-Office version before you upgrade.", "marketplace", "community"),
                    handler: me.showUpgradeReadiness,
                    scope: me
                },
                '->',
                me.updatesItem = new Ext.Toolbar.TextItem({text: ''}),
                me.loadingItem = new Ext.Toolbar.TextItem({text: ''})
            ]
        };

        me.sectionsContainer = new Ext.Panel({
            autoScroll: true,
            layout: 'anchor',
            border: false,
            bodyStyle: 'padding:8px;',
            defaults: {anchor: '100%'}
        });

        me.items = [me.sectionsContainer];

        go.modules.community.marketplace.SystemSettingsPanel.superclass.initComponent.call(me);

        me.on('afterrender', function () {
            me.tableBtn.toggle(me.viewMode !== 'cards', true);
            me.cardBtn.toggle(me.viewMode === 'cards', true);
            me.repoStore.load();
        }, me, {single: true});
    },

    /**
     * Persist only the view mode.
     * @return {Object}
     */
    getState: function () {
        return {viewMode: this.viewMode};
    },

    /**
     * Map the repository store's current records onto sections: add new ones
     * (loaded progressively), drop removed ones, update renamed ones in place.
     * Idempotent — safe to run on any store event.
     *
     * @return {void}
     */
    reconcile: function () {
        var me = this;
        if (!me.rendered) {
            return;
        }

        var records = me.repoStore.getRange().slice().sort(function (a, b) {
            return String(a.get('name') || '').localeCompare(String(b.get('name') || ''));
        });

        me.desiredOrder = records.map(function (r) { return r.get('id'); });

        var wanted = {};
        Ext.each(records, function (r) { wanted[r.get('id')] = r; });

        // Drop sections (inserted or still-pending) whose repository is gone.
        me.eachSection(function (s) {
            if (!wanted[s.repo.id]) {
                me.sectionsContainer.remove(s);
            }
        });
        Ext.iterate(me.pendingSections, function (id, section) {
            if (!wanted[id]) {
                section.destroy();
                delete me.pendingSections[id];
            }
        });

        me.hideEmptyState();

        if (!records.length) {
            me.showEmptyState();
            me.updateLoadingItem();
            me.refreshUpdatesBadge();
            return;
        }
        me.refreshUpdatesBadge();

        Ext.each(records, function (r) {
            var id = r.get('id'),
                data = me.repoData(r),
                existing = me.findSection(id) || me.pendingSections[id];
            if (existing) {
                existing.updateRepo(data);
                return;
            }
            var section = new go.modules.community.marketplace.RepositorySection({
                repo: data,
                viewMode: me.viewMode,
                ownedOnly: me.ownedOnly,
                typeFilter: me.typeFilter,
                onUpdateCount: function () { me.refreshUpdatesBadge(); }
            });
            me.pendingSections[id] = section;
            section.loadCatalog(function (sec) {
                me.insertSectionSorted(sec);
            });
        });

        me.updateLoadingItem();
    },

    /**
     * @param {Ext.data.Record} r
     * @return {Object} plain repo data for a section
     */
    repoData: function (r) {
        return {
            id: r.get('id'),
            name: r.get('name'),
            url: r.get('url'),
            lastSyncAt: r.get('lastSyncAt'),
            keyMismatch: r.get('keyMismatch'),
            lastError: r.get('lastError'),
            permissionLevel: r.get('permissionLevel')
        };
    },

    /**
     * Insert a settled (freshly loaded) section at its name-sorted position.
     * Guards against a reconcile that removed the repository while its catalogue
     * was still loading.
     *
     * @param {go.modules.community.marketplace.RepositorySection} section
     * @return {void}
     */
    insertSectionSorted: function (section) {
        var me = this,
            id = section.repo.id;

        if (me.pendingSections[id] !== section) {
            return; // superseded or already handled
        }
        delete me.pendingSections[id];

        var order = me.desiredOrder || [],
            pos = order.indexOf(id);
        if (pos === -1) {
            section.destroy(); // no longer wanted
            me.updateLoadingItem();
            return;
        }

        var idx = 0;
        me.sectionsContainer.items.each(function (item) {
            if (!(item instanceof go.modules.community.marketplace.RepositorySection)) {
                return;
            }
            if (order.indexOf(item.repo.id) < pos) {
                idx++;
            } else {
                return false;
            }
        });

        me.hideEmptyState();
        me.sectionsContainer.insert(idx, section);
        me.sectionsContainer.doLayout();

        var relayout = function () { me.sectionsContainer.doLayout(); };
        section.on('collapse', relayout);
        section.on('expand', relayout);

        if (!me.goVersion && section.catalogGrid) {
            me.goVersion = section.catalogGrid.goVersion;
        }

        me.updateLoadingItem();
    },

    /**
     * @param {Number} id
     * @return {go.modules.community.marketplace.RepositorySection|null}
     */
    findSection: function (id) {
        var found = null;
        this.eachSection(function (s) {
            if (s.repo.id === id) { found = s; }
        });
        return found;
    },

    /**
     * Run a function against every inserted RepositorySection.
     *
     * @param {Function} fn called with (section)
     * @return {void}
     */
    eachSection: function (fn) {
        this.sectionsContainer.items.each(function (item) {
            if (item instanceof go.modules.community.marketplace.RepositorySection) {
                fn(item);
            }
        });
    },

    showEmptyState: function () {
        if (this.emptyBox) {
            return;
        }
        this.emptyBox = this.sectionsContainer.add({
            xtype: 'box',
            style: 'padding:24px;',
            html: '<p class="info"><i class="icon">info</i> ' +
                Ext.util.Format.htmlEncode(t("Add a repository to browse its module catalog.", "marketplace", "community")) +
                '</p>'
        });
        this.sectionsContainer.doLayout();
    },

    hideEmptyState: function () {
        if (this.emptyBox) {
            this.sectionsContainer.remove(this.emptyBox);
            this.emptyBox = null;
        }
    },

    updateLoadingItem: function () {
        if (!this.loadingItem) {
            return;
        }
        var n = 0, k;
        for (k in this.pendingSections) {
            if (this.pendingSections.hasOwnProperty(k)) { n++; }
        }
        if (n > 0) {
            this.loadingItem.setText(Ext.util.Format.htmlEncode(
                t("Loading {n} repositories…", "marketplace", "community").replace("{n}", n)
            ));
        } else {
            this.loadingItem.setText('');
        }
    },

    /**
     * Recompute the global "N updates available" toolbar badge from the update
     * counts every section reports after its catalog (re)loads. Cleared when
     * nothing is out of date.
     *
     * @return {void}
     */
    refreshUpdatesBadge: function () {
        if (!this.updatesItem) {
            return;
        }
        var total = 0;
        this.eachSection(function (s) { total += s.getUpdateCount(); });
        if (total > 0) {
            this.updatesItem.setText(
                '<span style="display:inline-block; padding:0 8px; border-radius:9px; background:var(--hue-orange); ' +
                'color:#fff; font-size:11px; line-height:18px;" ext:qtip="' +
                Ext.util.Format.htmlEncode(t("Updates available across all repositories", "marketplace", "community")) + '">' +
                total + ' ' + Ext.util.Format.htmlEncode(t("updates", "marketplace", "community")) + '</span>'
            );
        } else {
            this.updatesItem.setText('');
        }
    },

    setViewMode: function (mode) {
        this.viewMode = mode;
        this.eachSection(function (s) { s.setViewMode(mode); });
        this.fireEvent('viewmodechange', this, mode);
    },

    setTypeFilter: function (type) {
        this.typeFilter = type;
        this.eachSection(function (s) { s.applyTypeFilter(type); });
    },

    setOwnedOnly: function (ownedOnly) {
        this.ownedOnly = ownedOnly;
        this.eachSection(function (s) { s.applyOwnedFilter(ownedOnly); });
    },

    /**
     * Toolbar Refresh: re-fetch every section's catalogue (does NOT reload the
     * repository list — the entity store keeps that in sync on its own).
     *
     * @return {void}
     */
    onRefreshAll: function () {
        this.eachSection(function (s) { s.loadCatalog(); });
    },

    onAddRepository: function () {
        // On save the bound entity store fires add -> reconcile inserts the new
        // section. No explicit reload needed.
        new go.modules.community.marketplace.RepositoryDialog().show();
    },

    /**
     * Collect every module with an update available across all repositories and
     * download them sequentially after a confirmation.
     *
     * @return {void}
     */
    onUpdateAll: function () {
        var me = this,
            jobs = [];

        me.eachSection(function (s) {
            Ext.each(s.catalogGrid.getUpdatable(), function (u) {
                jobs.push({section: s, repositoryId: s.repo.id, module: u.module, version: u.version});
            });
        });

        if (!jobs.length) {
            Ext.MessageBox.alert(
                t("Update all", "marketplace", "community"),
                t("All installed modules are up to date.", "marketplace", "community")
            );
            return;
        }

        Ext.MessageBox.confirm(
            t("Update all", "marketplace", "community"),
            t("Download {n} available updates now?", "marketplace", "community").replace("{n}", jobs.length),
            function (btn) {
                if (btn === 'yes') {
                    me.runUpdateJobs(jobs);
                }
            }
        );
    },

    /**
     * Download a list of update jobs one after another, then reload every
     * affected section once.
     *
     * @param {Array} jobs [{section, repositoryId, module, version}]
     * @return {void}
     */
    runUpdateJobs: function (jobs) {
        var me = this,
            i = 0,
            failed = 0,
            affected = {};

        function finish() {
            Ext.iterate(affected, function (id, section) { section.loadCatalog(); });
            me.loadingItem.setText('');
            go.Notifier.flyout({
                description: failed
                    ? t("Updates finished with {n} error(s).", "marketplace", "community").replace("{n}", failed)
                    : t("All updates downloaded. Run the upgrade to apply them.", "marketplace", "community"),
                time: 6000
            });
        }

        function next() {
            if (i >= jobs.length) {
                finish();
                return;
            }
            var j = jobs[i++];
            me.loadingItem.setText(Ext.util.Format.htmlEncode(
                t("Updating {n}…", "marketplace", "community").replace("{n}", i + '/' + jobs.length)
            ));
            go.Jmap.request({
                method: "MarketplaceRepository/download",
                params: {repositoryId: j.repositoryId, module: j.module, version: j.version},
                callback: function (o, success) {
                    if (success) {
                        affected[j.repositoryId] = j.section;
                    } else {
                        failed++;
                    }
                    next();
                }
            });
        }

        next();
    },

    /**
     * True when a branch string is this instance's current GO branch (mirrors
     * the server's dot-boundary match).
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
     * Aggregate installed modules + published branches across ALL repositories
     * and open the upgrade-readiness picker.
     *
     * @return {void}
     */
    showUpgradeReadiness: function () {
        var me = this,
            mods = [],
            branchSet = {};

        me.eachSection(function (s) {
            Ext.each(s.getInstalledModules(), function (m) { mods.push(m); });
            Ext.each(s.getBranches(), function (b) { branchSet[b] = true; });
        });

        var branches = [];
        for (var b in branchSet) {
            if (branchSet.hasOwnProperty(b)) { branches.push(b); }
        }
        branches.sort();

        if (!branches.length) {
            Ext.MessageBox.alert(
                t("Upgrade readiness", "marketplace", "community"),
                t("The marketplace server has not published any Group-Office versions yet.", "marketplace", "community")
            );
            return;
        }

        var body = new Ext.Panel({
            flex: 1,
            autoScroll: true,
            border: false,
            bodyStyle: 'padding:0 12px 12px;'
        });

        // Default target: the first branch that isn't the current one.
        var defaultBranch = branches[0];
        Ext.each(branches, function (br) {
            if (!me.isCurrentBranch(br)) {
                defaultBranch = br;
                return false;
            }
        });

        var combo = new Ext.form.ComboBox({
            fieldLabel: t("Target Group-Office version", "marketplace", "community"),
            store: new Ext.data.ArrayStore({
                fields: ['branch'],
                data: branches.map(function (br) { return [br]; })
            }),
            valueField: 'branch',
            displayField: 'branch',
            mode: 'local',
            triggerAction: 'all',
            editable: false,
            forceSelection: true,
            value: defaultBranch,
            width: dp(160),
            listeners: {
                select: function (c) {
                    body.update(me.buildReadinessHtml(mods, c.getValue()));
                }
            }
        });

        var win = new go.Window({
            title: t("Upgrade readiness", "marketplace", "community"),
            modal: true,
            width: dp(560),
            height: dp(520),
            layout: {type: 'vbox', align: 'stretch'},
            items: [
                new Ext.form.FormPanel({
                    border: false,
                    autoHeight: true,
                    labelWidth: dp(200),
                    bodyStyle: 'padding:12px;',
                    items: [
                        {
                            xtype: 'box',
                            style: 'padding-bottom:8px;',
                            html: '<small>' + Ext.util.Format.htmlEncode(
                                t("Check whether the modules you use have a build for a newer Group-Office version before you upgrade.", "marketplace", "community")
                            ) + '</small>'
                        },
                        combo
                    ]
                }),
                body
            ],
            buttons: [
                '->',
                {text: t("Close"), handler: function () { win.close(); }}
            ]
        });

        win.show();
        body.update(me.buildReadinessHtml(mods, defaultBranch));
    },

    /**
     * Build the readiness list HTML for a target branch: a summary banner plus
     * one row per installed module (green check + target version when a build
     * exists, red cross + "No build yet" when it doesn't). Missing first.
     *
     * @param {Array} mods [{title, installed, availability}]
     * @param {String} branch target GO branch
     * @return {String} html
     */
    buildReadinessHtml: function (mods, branch) {
        if (!mods.length) {
            return '<div style="padding-top:12px; color:var(--fg-secondary-text);">' +
                Ext.util.Format.htmlEncode(t("You don't use any modules from this repository yet.", "marketplace", "community")) +
                '</div>';
        }

        var sorted = mods.slice().sort(function (a, b) {
            var aOk = !!a.availability[branch],
                bOk = !!b.availability[branch];
            if (aOk !== bOk) {
                return aOk ? 1 : -1;
            }
            return String(a.title).localeCompare(String(b.title));
        });

        var ready = 0,
            rows = [];

        Ext.each(sorted, function (m) {
            var ver = m.availability[branch],
                ok = !!ver,
                color = ok ? 'var(--hue-green)' : 'var(--hue-red)',
                icon = ok ? 'ic-check-circle' : 'ic-cancel',
                right = ok ? Ext.util.Format.htmlEncode(ver) : Ext.util.Format.htmlEncode(t("No build yet", "marketplace", "community"));
            if (ok) {
                ready++;
            }
            rows.push(
                '<div style="display:flex; align-items:center; padding:7px 0; border-bottom:1px solid var(--fg-border);">' +
                    '<i class="icon ' + icon + '" style="color:' + color + '; margin-right:8px;"></i>' +
                    '<span style="flex:1; min-width:0; color:var(--fg-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' +
                        Ext.util.Format.htmlEncode(m.title) +
                        (m.installed ? ' <small style="color:var(--fg-secondary-text);">(' +
                            Ext.util.Format.htmlEncode(t("Installed", "marketplace", "community") + ' ' + m.installed) + ')</small>' : '') +
                    '</span>' +
                    '<span style="color:' + color + '; white-space:nowrap;">' + right + '</span>' +
                '</div>'
            );
        });

        var total = sorted.length,
            allReady = ready === total,
            summary = t("{ready} of {total} modules ready for Group-Office {branch}", "marketplace", "community")
                .replace("{ready}", ready).replace("{total}", total).replace("{branch}", branch);

        var banner = '<div style="margin:12px 0 6px; padding:8px 10px; border-radius:6px; font-weight:bold; color:#fff; background:' +
            (allReady ? 'var(--hue-green)' : 'var(--hue-orange)') + ';">' +
            '<i class="icon ' + (allReady ? 'ic-check' : 'ic-warning') + '" style="color:#fff; vertical-align:middle; margin-right:6px;"></i>' +
            Ext.util.Format.htmlEncode(summary) + '</div>';

        return banner + rows.join('');
    }
});
