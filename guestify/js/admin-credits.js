/**
 * Guestify Credit Management - Admin Vue 3 App
 *
 * Standalone Vue 3 + Pinia application for the Credit Management admin page.
 * Mounts to #pit-app-credits. Uses pitData.guestifyApiUrl for REST calls.
 *
 * @since 5.8.0
 */

(function () {
    'use strict';

    const { createApp, ref, reactive, computed, onMounted, watch, nextTick } = Vue;
    const { createPinia, defineStore } = Pinia;

    // =========================================================================
    // HELPERS
    // =========================================================================

    function gfyUrl(path) {
        return (pitData.guestifyApiUrl || '/wp-json/guestify/v1') + path;
    }

    function apiHeaders(json = true) {
        const h = { 'X-WP-Nonce': pitData.nonce };
        if (json) h['Content-Type'] = 'application/json';
        return h;
    }

    async function apiFetch(method, path, body = null) {
        const opts = { method, headers: apiHeaders() };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(gfyUrl(path), opts);
        return res.json();
    }

    function formatNumber(n) {
        if (n === -1) return 'Unlimited';
        return Number(n).toLocaleString();
    }

    function formatActionName(str) {
        return (str || '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, c => c.toUpperCase());
    }

    function tierClass(tier) {
        return 'pit-tier-' + (tier || 'free').toLowerCase();
    }

    let toastTimer = null;

    // =========================================================================
    // PINIA STORE
    // =========================================================================

    const useCreditAdminStore = defineStore('creditAdmin', {
        state: () => ({
            // Tab 1: User Credits
            allocations: [],
            allocPagination: { page: 1, perPage: 20, total: 0, pages: 0 },
            allocSearch: '',
            allocSort: { by: 'user_email', order: 'asc' },
            allocLoading: false,

            // Tab 2: Tier Configuration
            tiers: {},
            tiersLoading: false,

            // Tab 3: Plan-Tier Mapping
            planTierMap: {},
            availableTiers: {},
            planTierDefaults: {},
            planTierLoading: false,

            // Tab 4: Action Costs
            actionCosts: [],
            actionCostsLoading: false,

            // Tab 5: Credit Packs
            creditPacks: {},
            creditPacksLoading: false,

            // Shared
            toast: { message: '', type: '', visible: false },
        }),

        actions: {
            // --- Toast ---
            showToast(message, type = 'success') {
                this.toast = { message, type, visible: true };
                if (toastTimer) clearTimeout(toastTimer);
                toastTimer = setTimeout(() => { this.toast.visible = false; }, 3500);
            },

            // --- Tab 1: Allocations ---
            async fetchAllocations() {
                this.allocLoading = true;
                try {
                    const params = new URLSearchParams({
                        page: this.allocPagination.page,
                        per_page: this.allocPagination.perPage,
                        search: this.allocSearch,
                        sort_by: this.allocSort.by,
                        sort_order: this.allocSort.order,
                    });
                    const data = await apiFetch('GET', '/credits/allocations?' + params);
                    if (data.success) {
                        this.allocations = data.data.allocations;
                        this.allocPagination.total = data.data.total;
                        this.allocPagination.pages = data.data.pages;
                    }
                } catch (e) {
                    console.error('fetchAllocations:', e);
                    this.showToast('Failed to load allocations', 'error');
                } finally {
                    this.allocLoading = false;
                }
            },

            async adjustBalance(userId, amount, reason) {
                const data = await apiFetch('POST', '/credits/adjust', { user_id: userId, amount, reason });
                if (data.success) {
                    this.showToast(data.message || 'Balance adjusted');
                    await this.fetchAllocations();
                } else {
                    this.showToast(data.message || 'Adjustment failed', 'error');
                }
                return data;
            },

            async syncUser(userId) {
                const data = await apiFetch('POST', '/credit-sync/sync-user', { user_id: userId });
                if (data.success) {
                    this.showToast(data.message || 'User synced');
                    await this.fetchAllocations();
                } else {
                    this.showToast(data.message || 'Sync failed', 'error');
                }
                return data;
            },

            // --- Tab 2: Tiers ---
            async fetchTiers() {
                this.tiersLoading = true;
                try {
                    const data = await apiFetch('GET', '/tier-config');
                    if (data.success) {
                        this.tiers = data.tiers || {};
                    }
                } catch (e) {
                    this.showToast('Failed to load tiers', 'error');
                } finally {
                    this.tiersLoading = false;
                }
            },

            async updateTier(tierKey, tierData) {
                const data = await apiFetch('PUT', '/tier-config/' + tierKey, tierData);
                if (data.success) {
                    this.tiers[tierKey] = data.tier || tierData;
                    this.showToast('Tier "' + tierKey + '" updated');
                } else {
                    this.showToast(data.message || 'Update failed', 'error');
                }
                return data;
            },

            async resetTiers() {
                const data = await apiFetch('POST', '/tier-config/reset');
                if (data.success) {
                    this.tiers = data.tiers || {};
                    this.showToast('Tiers reset to defaults');
                } else {
                    this.showToast(data.message || 'Reset failed', 'error');
                }
            },

            async createTier(tierKey, tierData) {
                const data = await apiFetch('PUT', '/tier-config/' + tierKey, tierData);
                if (data.success) {
                    this.tiers[tierKey] = data.tier || tierData;
                    this.showToast('Tier "' + tierKey + '" created');
                } else {
                    this.showToast(data.message || 'Create failed', 'error');
                }
                return data;
            },

            async deleteTier(tierKey) {
                const data = await apiFetch('DELETE', '/tier-config/' + tierKey);
                if (data.success) {
                    this.tiers = data.tiers || {};
                    this.showToast('Tier "' + tierKey + '" deleted');
                } else {
                    this.showToast(data.message || 'Delete failed', 'error');
                }
                return data;
            },

            // --- Tab 3: Plan-Tier Mapping ---
            async fetchPlanTierMap() {
                this.planTierLoading = true;
                try {
                    const data = await apiFetch('GET', '/credit-sync/plan-tier-map');
                    if (data.success) {
                        this.planTierMap = data.data.map || {};
                        this.availableTiers = data.data.available_tiers || {};
                        this.planTierDefaults = data.data.defaults || {};
                    }
                } catch (e) {
                    this.showToast('Failed to load mapping', 'error');
                } finally {
                    this.planTierLoading = false;
                }
            },

            async savePlanTierMap(map) {
                const data = await apiFetch('PUT', '/credit-sync/plan-tier-map', map);
                if (data.success) {
                    this.planTierMap = data.data || map;
                    this.showToast('Plan-tier mapping saved');
                } else {
                    this.showToast(data.message || 'Save failed', 'error');
                }
                return data;
            },

            // --- Tab 4: Action Costs ---
            async fetchActionCosts() {
                this.actionCostsLoading = true;
                try {
                    const data = await apiFetch('GET', '/credits/action-costs');
                    if (data.success) {
                        this.actionCosts = data.data || [];
                    }
                } catch (e) {
                    this.showToast('Failed to load action costs', 'error');
                } finally {
                    this.actionCostsLoading = false;
                }
            },

            async updateActionCost(actionType, updates) {
                const data = await apiFetch('PUT', '/credits/action-costs/' + actionType, updates);
                if (data.success) {
                    const idx = this.actionCosts.findIndex(c => c.action_type === actionType);
                    if (idx >= 0 && data.data) {
                        this.actionCosts[idx] = { ...this.actionCosts[idx], ...data.data };
                    }
                    this.showToast('Action cost updated');
                } else {
                    this.showToast(data.message || 'Update failed', 'error');
                }
                return data;
            },

            // --- Tab 5: Credit Packs ---
            async fetchCreditPacks() {
                this.creditPacksLoading = true;
                try {
                    const data = await apiFetch('GET', '/credits/packs');
                    if (data.success) {
                        // Convert array response back to keyed object
                        const packs = {};
                        (data.data || []).forEach(p => { packs[p.key] = p; });
                        this.creditPacks = packs;
                    }
                } catch (e) {
                    this.showToast('Failed to load credit packs', 'error');
                } finally {
                    this.creditPacksLoading = false;
                }
            },

            async saveCreditPacks(packs) {
                const data = await apiFetch('PUT', '/credits/packs', packs);
                if (data.success) {
                    this.showToast('Credit packs saved');
                    await this.fetchCreditPacks();
                } else {
                    this.showToast(data.message || 'Save failed', 'error');
                }
                return data;
            },
        },
    });

    // =========================================================================
    // TAB 1: USER CREDITS
    // =========================================================================

    const UserCreditsTab = {
        setup() {
            const store = useCreditAdminStore();

            const adjustModal = reactive({
                visible: false,
                userId: 0,
                userName: '',
                currentBalance: 0,
                amount: 0,
                reason: '',
                loading: false,
            });

            const syncingUser = ref(null);
            let searchTimeout = null;

            onMounted(() => store.fetchAllocations());

            function onSearch(e) {
                store.allocSearch = e.target.value;
                store.allocPagination.page = 1;
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => store.fetchAllocations(), 400);
            }

            function onSort(col) {
                if (store.allocSort.by === col) {
                    store.allocSort.order = store.allocSort.order === 'asc' ? 'desc' : 'asc';
                } else {
                    store.allocSort.by = col;
                    store.allocSort.order = 'asc';
                }
                store.fetchAllocations();
            }

            function sortArrow(col) {
                if (store.allocSort.by !== col) return '';
                return store.allocSort.order === 'asc' ? '\u25B2' : '\u25BC';
            }

            function onPage(p) {
                if (p < 1 || p > store.allocPagination.pages) return;
                store.allocPagination.page = p;
                store.fetchAllocations();
            }

            function openAdjust(alloc) {
                adjustModal.userId = alloc.user_id;
                adjustModal.userName = alloc.display_name || alloc.user_email;
                adjustModal.currentBalance = alloc.total_balance;
                adjustModal.amount = 0;
                adjustModal.reason = '';
                adjustModal.loading = false;
                adjustModal.visible = true;
            }

            async function submitAdjust() {
                if (!adjustModal.amount) return;
                adjustModal.loading = true;
                await store.adjustBalance(adjustModal.userId, adjustModal.amount, adjustModal.reason);
                adjustModal.loading = false;
                adjustModal.visible = false;
            }

            async function doSync(userId) {
                syncingUser.value = userId;
                await store.syncUser(userId);
                syncingUser.value = null;
            }

            return { store, adjustModal, syncingUser, onSearch, onSort, sortArrow, onPage, openAdjust, submitAdjust, doSync, formatNumber, tierClass };
        },

        template: `
            <div>
                <div class="pit-section-header">
                    <h2>User Credit Allocations</h2>
                </div>
                <p class="pit-section-desc">View and manage credit balances for all users.</p>

                <div class="pit-toolbar">
                    <input type="text" class="pit-search-input" placeholder="Search by email or name..."
                           :value="store.allocSearch" @input="onSearch" />
                    <span class="pit-page-info" v-if="store.allocPagination.total > 0">
                        {{ store.allocPagination.total }} user(s)
                    </span>
                </div>

                <div v-if="store.allocLoading" class="pit-loading">
                    <span class="pit-spinner"></span> Loading...
                </div>

                <div v-else-if="store.allocations.length === 0" class="pit-empty-state">
                    <h3>No Allocations Found</h3>
                    <p>No users have credit allocations yet. Allocations are created when a user's tier is resolved via WP Fusion tags.</p>
                </div>

                <div v-else class="pit-credit-table-wrap">
                    <table class="pit-credit-table">
                        <thead>
                            <tr>
                                <th @click="onSort('user_email')" :class="{ sorted: store.allocSort.by === 'user_email' }">
                                    Email <span class="sort-arrow">{{ sortArrow('user_email') }}</span>
                                </th>
                                <th @click="onSort('display_name')" :class="{ sorted: store.allocSort.by === 'display_name' }">
                                    Name <span class="sort-arrow">{{ sortArrow('display_name') }}</span>
                                </th>
                                <th @click="onSort('tier')" :class="{ sorted: store.allocSort.by === 'tier' }">
                                    Tier <span class="sort-arrow">{{ sortArrow('tier') }}</span>
                                </th>
                                <th class="col-number">Allowance</th>
                                <th class="col-number" @click="onSort('current_balance')" :class="{ sorted: store.allocSort.by === 'current_balance' }">
                                    Balance <span class="sort-arrow">{{ sortArrow('current_balance') }}</span>
                                </th>
                                <th class="col-number">Rollover</th>
                                <th class="col-number">Overage</th>
                                <th class="col-number">Total</th>
                                <th @click="onSort('billing_cycle_end')" :class="{ sorted: store.allocSort.by === 'billing_cycle_end' }">
                                    Cycle End <span class="sort-arrow">{{ sortArrow('billing_cycle_end') }}</span>
                                </th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in store.allocations" :key="a.user_id">
                                <td>{{ a.user_email }}</td>
                                <td>{{ a.display_name }}</td>
                                <td><span class="pit-tier-badge" :class="tierClass(a.tier)">{{ a.tier }}</span></td>
                                <td class="col-number">{{ formatNumber(a.monthly_allowance) }}</td>
                                <td class="col-number">{{ formatNumber(a.current_balance) }}</td>
                                <td class="col-number">{{ formatNumber(a.rollover_balance) }}</td>
                                <td class="col-number">{{ formatNumber(a.overage_balance) }}</td>
                                <td class="col-number"><strong>{{ formatNumber(a.total_balance) }}</strong></td>
                                <td>{{ a.billing_cycle_end || '\u2014' }}</td>
                                <td class="col-actions">
                                    <button class="pit-btn pit-btn-secondary pit-btn-sm" @click="openAdjust(a)">Adjust</button>
                                    <button class="pit-btn pit-btn-secondary pit-btn-sm" @click="doSync(a.user_id)"
                                            :disabled="syncingUser === a.user_id">
                                        {{ syncingUser === a.user_id ? 'Syncing...' : 'Sync' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="store.allocPagination.pages > 1" class="pit-pagination">
                    <button class="pit-page-btn" :disabled="store.allocPagination.page <= 1" @click="onPage(store.allocPagination.page - 1)">&laquo; Prev</button>
                    <template v-for="p in store.allocPagination.pages" :key="p">
                        <button v-if="Math.abs(p - store.allocPagination.page) < 3 || p === 1 || p === store.allocPagination.pages"
                                class="pit-page-btn" :class="{ active: p === store.allocPagination.page }" @click="onPage(p)">{{ p }}</button>
                        <span v-else-if="Math.abs(p - store.allocPagination.page) === 3" class="pit-page-info">...</span>
                    </template>
                    <button class="pit-page-btn" :disabled="store.allocPagination.page >= store.allocPagination.pages" @click="onPage(store.allocPagination.page + 1)">Next &raquo;</button>
                </div>

                <!-- Adjust Modal -->
                <div v-if="adjustModal.visible" class="pit-modal-overlay" @click.self="adjustModal.visible = false">
                    <div class="pit-modal">
                        <h2>Adjust Credits</h2>
                        <p style="color: var(--pit-text-secondary); font-size: 13px; margin: 0 0 16px;">
                            {{ adjustModal.userName }} &mdash; Current total: <strong>{{ formatNumber(adjustModal.currentBalance) }}</strong>
                        </p>

                        <div class="pit-modal-field">
                            <label>Amount (positive to add, negative to subtract)</label>
                            <input type="number" v-model.number="adjustModal.amount" :disabled="adjustModal.loading" />
                        </div>

                        <div class="pit-modal-field">
                            <label>Reason</label>
                            <textarea v-model="adjustModal.reason" :disabled="adjustModal.loading" placeholder="e.g., Courtesy credit, billing correction..."></textarea>
                        </div>

                        <div class="pit-modal-actions">
                            <button class="pit-btn pit-btn-secondary" @click="adjustModal.visible = false" :disabled="adjustModal.loading">Cancel</button>
                            <button class="pit-btn pit-btn-primary" @click="submitAdjust" :disabled="!adjustModal.amount || adjustModal.loading">
                                {{ adjustModal.loading ? 'Saving...' : 'Adjust Balance' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `,
    };

    // =========================================================================
    // TAB 2: TIER CONFIGURATION
    // =========================================================================

    const PROTECTED_TIERS = ['free', 'unlimited'];

    const TierConfigTab = {
        setup() {
            const store = useCreditAdminStore();
            const editing = reactive({ tierKey: null, field: null });
            const editValue = ref('');

            // Add / Edit tier modal
            const tierModal = reactive({
                visible: false,
                mode: 'add',        // 'add' or 'edit'
                originalKey: '',     // for edit mode
                key: '',
                name: '',
                priority: 50,
                tags: '',            // comma-separated
                credits: 0,
                opportunities: 0,
                campaigns: 0,
                profiles: 1,
                daily_enrichments: 0,
                saving: false,
            });

            // Delete confirmation
            const deleteConfirm = ref('');

            onMounted(() => store.fetchTiers());

            const tierEntries = computed(() => {
                return Object.entries(store.tiers)
                    .sort((a, b) => (b[1].priority || 0) - (a[1].priority || 0));
            });

            const limitFields = [
                { key: 'credits', label: 'Credits / Month' },
                { key: 'opportunities', label: 'Active Opportunities' },
                { key: 'campaigns', label: 'Active Campaigns' },
                { key: 'profiles', label: 'Brand Profiles' },
                { key: 'daily_enrichments', label: 'Daily Enrichments' },
            ];

            function isProtected(key) {
                return PROTECTED_TIERS.includes(key);
            }

            // --- Inline limit editing (click-to-edit numbers) ---
            function startEdit(tierKey, field, currentValue) {
                editing.tierKey = tierKey;
                editing.field = field;
                editValue.value = currentValue;
                nextTick(() => {
                    const input = document.querySelector('.pit-tier-limit-input');
                    if (input) input.focus();
                });
            }

            async function saveEdit(tierKey) {
                const val = parseInt(editValue.value, 10);
                if (isNaN(val)) {
                    editing.tierKey = null;
                    editing.field = null;
                    return;
                }
                const tierData = { ...store.tiers[tierKey] };
                tierData[editing.field] = val;
                await store.updateTier(tierKey, tierData);
                editing.tierKey = null;
                editing.field = null;
            }

            function cancelEdit() {
                editing.tierKey = null;
                editing.field = null;
            }

            // --- Tier modal (add / edit) ---
            function openAddTierModal() {
                tierModal.mode = 'add';
                tierModal.originalKey = '';
                tierModal.key = '';
                tierModal.name = '';
                tierModal.priority = 50;
                tierModal.tags = '';
                tierModal.credits = 0;
                tierModal.opportunities = 0;
                tierModal.campaigns = 0;
                tierModal.profiles = 1;
                tierModal.daily_enrichments = 0;
                tierModal.saving = false;
                tierModal.visible = true;
            }

            function openEditTierModal(key, tier) {
                tierModal.mode = 'edit';
                tierModal.originalKey = key;
                tierModal.key = key;
                tierModal.name = tier.name || '';
                tierModal.priority = tier.priority ?? 0;
                tierModal.tags = (tier.tags || []).join(', ');
                tierModal.credits = tier.credits ?? 0;
                tierModal.opportunities = tier.opportunities ?? 0;
                tierModal.campaigns = tier.campaigns ?? 0;
                tierModal.profiles = tier.profiles ?? 1;
                tierModal.daily_enrichments = tier.daily_enrichments ?? 0;
                tierModal.saving = false;
                tierModal.visible = true;
            }

            function closeTierModal() {
                tierModal.visible = false;
            }

            async function submitTierModal() {
                const slug = tierModal.key.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
                if (!slug) {
                    store.showToast('Tier key/slug is required', 'error');
                    return;
                }
                if (!tierModal.name.trim()) {
                    store.showToast('Tier name is required', 'error');
                    return;
                }
                // Check if key already exists when adding
                if (tierModal.mode === 'add' && store.tiers[slug]) {
                    store.showToast('A tier with key "' + slug + '" already exists. Edit it instead.', 'error');
                    return;
                }

                const tagsArray = tierModal.tags
                    .split(',')
                    .map(t => t.trim())
                    .filter(t => t.length > 0);

                const tierData = {
                    name: tierModal.name.trim(),
                    priority: parseInt(tierModal.priority, 10) || 0,
                    tags: tagsArray,
                    credits: parseInt(tierModal.credits, 10) || 0,
                    opportunities: parseInt(tierModal.opportunities, 10) || 0,
                    campaigns: parseInt(tierModal.campaigns, 10) || 0,
                    profiles: parseInt(tierModal.profiles, 10) || 1,
                    daily_enrichments: parseInt(tierModal.daily_enrichments, 10) || 0,
                };

                tierModal.saving = true;

                let data;
                if (tierModal.mode === 'add') {
                    data = await store.createTier(slug, tierData);
                } else {
                    data = await store.updateTier(slug, tierData);
                }

                tierModal.saving = false;

                if (data && data.success) {
                    closeTierModal();
                }
            }

            // --- Delete ---
            function confirmDeleteTier(key) {
                deleteConfirm.value = key;
            }

            function cancelDeleteTier() {
                deleteConfirm.value = '';
            }

            async function doDeleteTier(key) {
                await store.deleteTier(key);
                deleteConfirm.value = '';
            }

            // --- Reset ---
            async function resetAll() {
                if (!confirm('Reset all tiers to their default values? This will remove any custom tiers and cannot be undone.')) return;
                await store.resetTiers();
            }

            return {
                store, tierEntries, limitFields, editing, editValue,
                startEdit, saveEdit, cancelEdit,
                tierModal, openAddTierModal, openEditTierModal, closeTierModal, submitTierModal,
                deleteConfirm, confirmDeleteTier, cancelDeleteTier, doDeleteTier,
                resetAll, isProtected, formatNumber, tierClass,
            };
        },

        template: `
            <div>
                <div class="pit-section-header">
                    <h2>Tier Configuration</h2>
                    <div style="display:flex;gap:8px;">
                        <button class="pit-btn pit-btn-primary" @click="openAddTierModal">+ Add Tier</button>
                        <button class="pit-btn pit-btn-secondary" @click="resetAll">Reset to Defaults</button>
                    </div>
                </div>
                <p class="pit-section-desc">Edit the credit limits and caps for each subscription tier. Click any value to edit it. Use the <strong>Edit</strong> button to change name, tags, and priority.</p>

                <div v-if="store.tiersLoading" class="pit-loading">
                    <span class="pit-spinner"></span> Loading...
                </div>

                <div v-else class="pit-tier-grid">
                    <div v-for="[key, tier] in tierEntries" :key="key" class="pit-tier-card">
                        <div class="pit-tier-card-header">
                            <h3>
                                <span class="pit-tier-badge" :class="tierClass(key)">{{ key }}</span>
                                &nbsp;{{ tier.name }}
                            </h3>
                            <span class="tier-priority">Priority {{ tier.priority }}</span>
                        </div>

                        <div v-for="f in limitFields" :key="f.key" class="pit-tier-limit-row">
                            <span class="pit-tier-limit-label">{{ f.label }}</span>

                            <input v-if="editing.tierKey === key && editing.field === f.key"
                                   class="pit-tier-limit-input"
                                   type="number"
                                   v-model="editValue"
                                   @keyup.enter="saveEdit(key)"
                                   @keyup.escape="cancelEdit"
                                   @blur="saveEdit(key)" />

                            <span v-else class="pit-tier-limit-value" @click="startEdit(key, f.key, tier[f.key])">
                                {{ formatNumber(tier[f.key]) }}
                            </span>
                        </div>

                        <div v-if="tier.tags && tier.tags.length" class="pit-tier-tags">
                            <span v-for="tag in tier.tags" :key="tag" class="pit-tier-tag">{{ tag }}</span>
                        </div>

                        <div class="pit-tier-card-actions">
                            <button class="pit-btn pit-btn-secondary pit-btn-sm" @click="openEditTierModal(key, tier)">Edit</button>
                            <template v-if="!isProtected(key)">
                                <template v-if="deleteConfirm === key">
                                    <span style="font-size:12px;margin-left:8px;">
                                        Delete?
                                        <button class="pit-btn pit-btn-sm" style="color:var(--pit-danger,#b32d2e);font-weight:600;" @click="doDeleteTier(key)">Yes</button>
                                        <button class="pit-btn pit-btn-sm" @click="cancelDeleteTier">No</button>
                                    </span>
                                </template>
                                <button v-else class="pit-btn pit-btn-secondary pit-btn-sm" style="color:var(--pit-danger,#b32d2e);" @click="confirmDeleteTier(key)">Delete</button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ADD / EDIT TIER MODAL -->
                <div v-if="tierModal.visible" class="pit-modal-overlay" @click.self="closeTierModal">
                    <div class="pit-modal" style="max-width:520px;">
                        <h2>{{ tierModal.mode === 'edit' ? 'Edit Tier' : 'Add New Tier' }}</h2>

                        <div class="pit-modal-field">
                            <label>Tier Key (slug)</label>
                            <input type="text" v-model="tierModal.key"
                                   :disabled="tierModal.mode === 'edit'"
                                   placeholder="e.g. pro, starter, enterprise"
                                   style="width:100%;" />
                            <small v-if="tierModal.mode === 'add'" style="color:#757575;">Lowercase letters, numbers, underscores only. Cannot be changed later.</small>
                        </div>

                        <div class="pit-modal-field">
                            <label>Display Name</label>
                            <input type="text" v-model="tierModal.name" placeholder="e.g. Pro" style="width:100%;" />
                        </div>

                        <div class="pit-modal-field">
                            <label>Priority</label>
                            <input type="number" v-model.number="tierModal.priority" min="0" max="200" style="width:120px;" />
                            <small style="color:#757575;">Higher = more access. Free=0, Accelerator=40, Velocity=60, Zenith=80, Unlimited=100.</small>
                        </div>

                        <div class="pit-modal-field">
                            <label>WP Fusion Tags (comma-separated)</label>
                            <input type="text" v-model="tierModal.tags" placeholder="mem: guestify pro, mem: guestify pro trial" style="width:100%;" />
                            <small style="color:#757575;">Tag labels from WP Fusion / GoHighLevel. User needs ANY of these tags to match this tier.</small>
                        </div>

                        <hr style="border:none;border-top:1px solid #ddd;margin:16px 0;" />
                        <p style="font-weight:600;font-size:13px;margin:0 0 12px;">Credit Limits</p>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;">
                            <div class="pit-modal-field">
                                <label>Credits / Month</label>
                                <input type="number" v-model.number="tierModal.credits" min="-1" style="width:100%;" />
                            </div>
                            <div class="pit-modal-field">
                                <label>Active Opportunities</label>
                                <input type="number" v-model.number="tierModal.opportunities" min="-1" style="width:100%;" />
                            </div>
                            <div class="pit-modal-field">
                                <label>Active Campaigns</label>
                                <input type="number" v-model.number="tierModal.campaigns" min="-1" style="width:100%;" />
                            </div>
                            <div class="pit-modal-field">
                                <label>Brand Profiles</label>
                                <input type="number" v-model.number="tierModal.profiles" min="-1" style="width:100%;" />
                            </div>
                            <div class="pit-modal-field">
                                <label>Daily Enrichments</label>
                                <input type="number" v-model.number="tierModal.daily_enrichments" min="-1" style="width:100%;" />
                            </div>
                        </div>

                        <small style="color:#757575;display:block;margin-top:4px;">Use -1 for unlimited.</small>

                        <div class="pit-modal-actions">
                            <button class="pit-btn pit-btn-secondary" @click="closeTierModal" :disabled="tierModal.saving">Cancel</button>
                            <button class="pit-btn pit-btn-primary" @click="submitTierModal" :disabled="tierModal.saving">
                                {{ tierModal.saving ? 'Saving...' : (tierModal.mode === 'edit' ? 'Update Tier' : 'Create Tier') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `,
    };

    // =========================================================================
    // TAB 3: PLAN-TIER MAPPING
    // =========================================================================

    const PlanTierMapTab = {
        setup() {
            const store = useCreditAdminStore();
            const localMap = ref({});
            const newPlanName = ref('');
            const saving = ref(false);

            onMounted(async () => {
                await store.fetchPlanTierMap();
                localMap.value = JSON.parse(JSON.stringify(store.planTierMap));
            });

            const tierOptions = computed(() => {
                return Object.entries(store.availableTiers).map(([key, t]) => ({
                    value: key,
                    label: key + ' (' + formatNumber(t.credits) + ' cr)',
                }));
            });

            const mapEntries = computed(() => Object.entries(localMap.value));

            function addPlan() {
                const name = newPlanName.value.trim().toLowerCase().replace(/\s+/g, '_');
                if (!name || localMap.value[name]) return;
                localMap.value[name] = { tier: 'free', billing_period: 'monthly' };
                newPlanName.value = '';
            }

            function removePlan(key) {
                if (!confirm('Remove plan "' + key + '" from the mapping?')) return;
                const copy = { ...localMap.value };
                delete copy[key];
                localMap.value = copy;
            }

            async function saveMap() {
                saving.value = true;
                await store.savePlanTierMap(localMap.value);
                saving.value = false;
            }

            function resetMap() {
                localMap.value = JSON.parse(JSON.stringify(store.planTierDefaults));
            }

            return { store, localMap, mapEntries, tierOptions, newPlanName, saving, addPlan, removePlan, saveMap, resetMap };
        },

        template: `
            <div>
                <div class="pit-section-header">
                    <h2>Plan-to-Tier Mapping</h2>
                </div>
                <p class="pit-section-desc">
                    Map GoHighLevel plan names (from WP Fusion) to credit tiers. When a user's plan changes,
                    this mapping determines which credit tier they receive.
                </p>

                <div v-if="store.planTierLoading" class="pit-loading">
                    <span class="pit-spinner"></span> Loading...
                </div>

                <template v-else>
                    <table class="pit-mapping-table">
                        <thead>
                            <tr>
                                <th>GHL Plan Name</th>
                                <th>Credit Tier</th>
                                <th>Billing Period</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="[plan, config] in mapEntries" :key="plan">
                                <td><strong>{{ plan }}</strong></td>
                                <td>
                                    <select v-model="localMap[plan].tier">
                                        <option v-for="t in tierOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                                    </select>
                                </td>
                                <td>
                                    <select v-model="localMap[plan].billing_period">
                                        <option value="monthly">Monthly</option>
                                        <option value="annual">Annual</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="pit-btn pit-btn-secondary pit-btn-sm" @click="removePlan(plan)">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="pit-mapping-actions" style="margin-top: 16px;">
                        <input type="text" v-model="newPlanName" placeholder="New plan name..." class="pit-search-input"
                               style="max-width: 200px;" @keyup.enter="addPlan" />
                        <button class="pit-btn pit-btn-secondary" @click="addPlan" :disabled="!newPlanName.trim()">Add Plan</button>
                        <div style="flex: 1;"></div>
                        <button class="pit-btn pit-btn-secondary" @click="resetMap">Reset to Defaults</button>
                        <button class="pit-btn pit-btn-primary" @click="saveMap" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save Mapping' }}
                        </button>
                    </div>
                </template>
            </div>
        `,
    };

    // =========================================================================
    // TAB 4: ACTION COSTS
    // =========================================================================

    const ActionCostsTab = {
        setup() {
            const store = useCreditAdminStore();
            const savingAction = ref(null);

            onMounted(() => store.fetchActionCosts());

            async function updateCost(action, field, value) {
                savingAction.value = action.action_type;
                const updates = {};
                updates[field] = value;
                await store.updateActionCost(action.action_type, updates);
                savingAction.value = null;
            }

            async function toggleActive(action) {
                await updateCost(action, 'is_active', action.is_active ? 0 : 1);
            }

            function categoryClass(cat) {
                return 'pit-category-' + (cat || 'other').toLowerCase();
            }

            return { store, savingAction, updateCost, toggleActive, formatActionName, categoryClass };
        },

        template: `
            <div>
                <div class="pit-section-header">
                    <h2>Action Credit Costs</h2>
                </div>
                <p class="pit-section-desc">Configure how many credits each action costs. Changes take effect immediately.</p>

                <div v-if="store.actionCostsLoading" class="pit-loading">
                    <span class="pit-spinner"></span> Loading...
                </div>

                <table v-else class="pit-costs-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th style="text-align: center;">Credits</th>
                            <th style="text-align: center;">Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="action in store.actionCosts" :key="action.action_type">
                            <td><strong>{{ formatActionName(action.action_type) }}</strong></td>
                            <td style="color: var(--pit-text-secondary); font-size: 12px;">{{ action.description }}</td>
                            <td>
                                <span class="pit-category-badge" :class="categoryClass(action.category)">
                                    {{ action.category || 'other' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <input type="number" class="pit-cost-input"
                                       :value="action.credits_per_unit"
                                       @change="updateCost(action, 'credits_per_unit', parseInt($event.target.value, 10))"
                                       :disabled="savingAction === action.action_type"
                                       min="0" />
                            </td>
                            <td style="text-align: center;">
                                <label class="pit-toggle">
                                    <input type="checkbox" :checked="action.is_active" @change="toggleActive(action)"
                                           :disabled="savingAction === action.action_type" />
                                    <span class="pit-toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `,
    };

    // =========================================================================
    // TAB 5: CREDIT PACKS
    // =========================================================================

    const CreditPacksTab = {
        setup() {
            const store = useCreditAdminStore();
            const localPacks = ref({});
            const saving = ref(false);
            const stripeConfigured = pitData.stripeConfigured || false;

            onMounted(async () => {
                await store.fetchCreditPacks();
                localPacks.value = JSON.parse(JSON.stringify(store.creditPacks));
            });

            const packEntries = computed(() => Object.entries(localPacks.value));

            function addPack() {
                const key = 'pack_' + Date.now();
                localPacks.value[key] = { credits: 100, price_cents: 999, label: '100 Credits' };
            }

            function removePack(key) {
                if (!confirm('Remove this credit pack?')) return;
                const copy = { ...localPacks.value };
                delete copy[key];
                localPacks.value = copy;
            }

            async function savePacks() {
                saving.value = true;
                // Clean up: use only credits, price_cents, label for storage
                const clean = {};
                for (const [key, pack] of Object.entries(localPacks.value)) {
                    clean[key] = {
                        credits: parseInt(pack.credits, 10) || 0,
                        price_cents: parseInt(pack.price_cents, 10) || 0,
                        label: pack.label || '',
                    };
                }
                await store.saveCreditPacks(clean);
                localPacks.value = JSON.parse(JSON.stringify(store.creditPacks));
                saving.value = false;
            }

            function priceDisplay(cents) {
                return '$' + (Number(cents) / 100).toFixed(2);
            }

            return { store, localPacks, packEntries, saving, stripeConfigured, addPack, removePack, savePacks, priceDisplay };
        },

        template: `
            <div>
                <div class="pit-section-header">
                    <h2>Credit Packs</h2>
                </div>
                <p class="pit-section-desc">Configure the credit packs available for purchase via Stripe Checkout.</p>

                <div v-if="!stripeConfigured" class="pit-info-notice">
                    <h3>Stripe Not Configured</h3>
                    <p>Credit pack purchasing requires Stripe. Set the <code>guestify_stripe_config</code> wp_option with your
                       <code>secret_key</code> and <code>webhook_secret</code> to enable this feature. You can still configure
                       pack pricing below &mdash; they will become active once Stripe is connected.</p>
                </div>

                <div v-if="store.creditPacksLoading" class="pit-loading">
                    <span class="pit-spinner"></span> Loading...
                </div>

                <template v-else>
                    <div class="pit-packs-grid">
                        <div v-for="[key, pack] in packEntries" :key="key" class="pit-pack-card">
                            <button class="pit-pack-remove" @click="removePack(key)" title="Remove pack">&times;</button>

                            <div class="pit-pack-field">
                                <label>Label</label>
                                <input type="text" v-model="localPacks[key].label" />
                            </div>
                            <div class="pit-pack-field">
                                <label>Credits</label>
                                <input type="number" v-model.number="localPacks[key].credits" min="1" />
                            </div>
                            <div class="pit-pack-field">
                                <label>Price (cents)</label>
                                <input type="number" v-model.number="localPacks[key].price_cents" min="0" />
                            </div>
                            <div style="font-size: 13px; color: var(--pit-text-muted); margin-top: 4px;">
                                Display: <strong>{{ priceDisplay(localPacks[key].price_cents) }}</strong>
                                &middot;
                                {{ (localPacks[key].price_cents / Math.max(localPacks[key].credits, 1)).toFixed(1) }}&cent;/credit
                            </div>
                        </div>
                    </div>

                    <div class="pit-mapping-actions" style="margin-top: 20px;">
                        <button class="pit-btn pit-btn-secondary" @click="addPack">+ Add Pack</button>
                        <div style="flex: 1;"></div>
                        <button class="pit-btn pit-btn-primary" @click="savePacks" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save Packs' }}
                        </button>
                    </div>
                </template>
            </div>
        `,
    };

    // =========================================================================
    // MOUNT LOGIC
    // =========================================================================

    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('pit-app-credits');
        if (!el) return;

        const activeTab = el.dataset.tab || 'users';

        const tabComponents = {
            users: UserCreditsTab,
            tiers: TierConfigTab,
            mapping: PlanTierMapTab,
            costs: ActionCostsTab,
            packs: CreditPacksTab,
        };

        const tabComponent = tabComponents[activeTab] || UserCreditsTab;

        // Root component wraps the active tab + toast
        const RootApp = {
            setup() {
                const store = useCreditAdminStore();
                return { store };
            },
            components: { TabContent: tabComponent },
            template: `
                <div>
                    <div v-if="store.toast.visible" class="pit-toast" :class="'pit-toast-' + store.toast.type">
                        {{ store.toast.message }}
                    </div>
                    <TabContent />
                </div>
            `,
        };

        const app = createApp(RootApp);
        app.use(createPinia());
        app.mount('#pit-app-credits');
    });

})();
