/**
 * Guestify Access Gate — Admin Vue 3 App
 *
 * Standalone Vue 3 + Pinia application for the Access Gate admin page.
 * Mounts to #gfy-app-access-gate. Uses gfyAccessGateData for REST config.
 *
 * @since 1.2.0
 */

(function () {
    'use strict';

    const { createApp, ref, reactive, computed, onMounted, watch, nextTick } = Vue;
    const { createPinia, defineStore } = Pinia;

    // =========================================================================
    // HELPERS
    // =========================================================================

    function apiUrl(path) {
        return (gfyAccessGateData.apiUrl || '/wp-json/guestify/v1') + path;
    }

    function apiHeaders() {
        return {
            'X-WP-Nonce': gfyAccessGateData.nonce,
            'Content-Type': 'application/json',
        };
    }

    async function apiFetch(method, path, body = null) {
        const opts = { method, headers: apiHeaders() };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(apiUrl(path), opts);
        return res.json();
    }

    let toastTimer = null;

    // =========================================================================
    // PINIA STORE
    // =========================================================================

    const useAccessGateStore = defineStore('accessGate', {
        state: () => ({
            rules: [],
            tiers: {},
            defaults: {},
            loading: false,

            // Test tab
            testPath: '',
            testResult: null,
            testLoading: false,

            // Toast
            toast: { message: '', type: '', visible: false },
        }),

        getters: {
            customRules: (state) => state.rules.filter(r => r.source === 'custom'),
            defaultRules: (state) => state.rules.filter(r => r.source === 'default'),
            pluginRules: (state) => state.rules.filter(r => r.source === 'plugin'),

            tierOptions: (state) => {
                const opts = [{ value: '', label: '— None (login only) —' }];
                const entries = Object.entries(state.tiers);
                entries.sort((a, b) => (a[1].priority || 0) - (b[1].priority || 0));
                for (const [key, tier] of entries) {
                    opts.push({ value: key, label: tier.name + ' (priority ' + tier.priority + ')' });
                }
                return opts;
            },
        },

        actions: {
            showToast(message, type = 'success') {
                this.toast = { message, type, visible: true };
                if (toastTimer) clearTimeout(toastTimer);
                toastTimer = setTimeout(() => { this.toast.visible = false; }, 3500);
            },

            async fetchRules() {
                this.loading = true;
                try {
                    const data = await apiFetch('GET', '/access-gate/rules');
                    if (data.success) {
                        this.rules = data.rules || [];
                        this.tiers = data.tiers || {};
                        this.defaults = data.defaults || {};
                    } else {
                        this.showToast(data.message || 'Failed to load rules', 'error');
                    }
                } catch (e) {
                    console.error('fetchRules:', e);
                    this.showToast('Failed to load rules', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async saveRule(ruleData) {
                try {
                    const data = await apiFetch('POST', '/access-gate/rules', ruleData);
                    if (data.success) {
                        this.showToast(data.message || 'Rule saved');
                        await this.fetchRules();
                        return true;
                    } else {
                        this.showToast(data.message || 'Failed to save rule', 'error');
                        return false;
                    }
                } catch (e) {
                    console.error('saveRule:', e);
                    this.showToast('Failed to save rule', 'error');
                    return false;
                }
            },

            async deleteRule(path) {
                try {
                    const data = await apiFetch('DELETE', '/access-gate/rules', { path });
                    if (data.success) {
                        this.showToast(data.message || 'Rule deleted');
                        await this.fetchRules();
                    } else {
                        this.showToast(data.message || 'Failed to delete rule', 'error');
                    }
                } catch (e) {
                    console.error('deleteRule:', e);
                    this.showToast('Failed to delete rule', 'error');
                }
            },

            async resetRules() {
                try {
                    const data = await apiFetch('POST', '/access-gate/reset');
                    if (data.success) {
                        this.showToast(data.message || 'All custom rules removed');
                        await this.fetchRules();
                    } else {
                        this.showToast(data.message || 'Failed to reset', 'error');
                    }
                } catch (e) {
                    this.showToast('Failed to reset', 'error');
                }
            },

            async testUrl(path) {
                this.testLoading = true;
                this.testResult = null;
                try {
                    const data = await apiFetch('POST', '/access-gate/test', { test_path: path });
                    if (data.success) {
                        this.testResult = data;
                    } else {
                        this.showToast(data.message || 'Test failed', 'error');
                    }
                } catch (e) {
                    this.showToast('Test failed', 'error');
                } finally {
                    this.testLoading = false;
                }
            },
        },
    });

    // =========================================================================
    // TAB 1: RULES
    // =========================================================================

    const RulesTab = {
        setup() {
            const store = useAccessGateStore();

            // Modal state
            const modal = reactive({
                visible: false,
                mode: 'add', // 'add' or 'edit'
                path: '',
                match_type: 'prefix',
                access_type: 'auth', // 'public' or 'auth'
                required_tier: '',
                required_tags: '',
                capability: '',
                redirect_to: '',
                originalPath: '', // for editing — the path before changes
            });

            // Delete confirmation
            const deleteConfirm = ref('');

            // Reset confirmation
            const resetConfirm = ref(false);

            onMounted(() => store.fetchRules());

            function openAddModal() {
                modal.mode = 'add';
                modal.path = '/';
                modal.match_type = 'prefix';
                modal.access_type = 'auth';
                modal.required_tier = '';
                modal.required_tags = '';
                modal.capability = '';
                modal.redirect_to = '';
                modal.originalPath = '';
                modal.visible = true;
            }

            function openEditModal(rule) {
                modal.mode = 'edit';
                modal.path = rule.path;
                modal.match_type = rule.match_type || 'prefix';
                modal.access_type = rule.public ? 'public' : 'auth';
                modal.required_tier = rule.required_tier || '';
                modal.required_tags = Array.isArray(rule.required_tags) ? rule.required_tags.join(', ') : (rule.required_tags || '');
                modal.capability = rule.capability || '';
                modal.redirect_to = rule.redirect_to || '';
                modal.originalPath = rule.path;
                modal.visible = true;
            }

            function openOverrideModal(rule) {
                // Pre-fill with default rule's values but open as "add" to create custom override
                modal.mode = 'add';
                modal.path = rule.path;
                modal.match_type = rule.match_type || 'prefix';
                modal.access_type = rule.public ? 'public' : 'auth';
                modal.required_tier = rule.required_tier || '';
                modal.required_tags = Array.isArray(rule.required_tags) ? rule.required_tags.join(', ') : '';
                modal.capability = rule.capability || '';
                modal.redirect_to = rule.redirect_to || '';
                modal.originalPath = '';
                modal.visible = true;
            }

            function closeModal() {
                modal.visible = false;
            }

            async function submitModal() {
                const ruleData = {
                    path: modal.path,
                    match_type: modal.match_type,
                    public: modal.access_type === 'public',
                    auth_required: modal.access_type === 'auth',
                    required_tier: modal.access_type === 'auth' ? modal.required_tier : '',
                    required_tags: modal.access_type === 'auth' ? modal.required_tags : '',
                    capability: modal.access_type === 'auth' ? modal.capability : '',
                    redirect_to: modal.access_type === 'auth' ? modal.redirect_to : '',
                };
                const ok = await store.saveRule(ruleData);
                if (ok) closeModal();
            }

            function confirmDelete(path) {
                deleteConfirm.value = path;
            }

            function cancelDelete() {
                deleteConfirm.value = '';
            }

            async function doDelete(path) {
                await store.deleteRule(path);
                deleteConfirm.value = '';
            }

            function confirmReset() {
                resetConfirm.value = true;
            }

            function cancelReset() {
                resetConfirm.value = false;
            }

            async function doReset() {
                await store.resetRules();
                resetConfirm.value = false;
            }

            function accessLabel(rule) {
                if (rule.public) return 'Public';
                if (rule.auth_required) {
                    if (rule.required_tier) return 'Tier: ' + rule.required_tier;
                    if (rule.capability) return 'Cap: ' + rule.capability;
                    return 'Login Required';
                }
                return '—';
            }

            function accessBadgeClass(rule) {
                if (rule.public) return 'gfy-ag-badge-public';
                if (rule.required_tier) return 'gfy-ag-badge-tier';
                return 'gfy-ag-badge-auth';
            }

            return {
                store, modal, deleteConfirm, resetConfirm,
                openAddModal, openEditModal, openOverrideModal, closeModal, submitModal,
                confirmDelete, cancelDelete, doDelete,
                confirmReset, cancelReset, doReset,
                accessLabel, accessBadgeClass,
            };
        },

        template: `
            <div>
                <!-- Toolbar -->
                <div class="gfy-ag-toolbar">
                    <button class="button button-primary" @click="openAddModal">
                        + Add Rule
                    </button>
                    <span style="flex:1"></span>
                    <template v-if="!resetConfirm">
                        <button class="button" @click="confirmReset" :disabled="store.customRules.length === 0">
                            Reset All Custom Rules
                        </button>
                    </template>
                    <template v-else>
                        <span class="gfy-ag-confirm">
                            Remove all custom rules?
                            <button class="button button-link-delete" @click="doReset">Yes, Reset</button>
                            <button class="button button-link" @click="cancelReset">Cancel</button>
                        </span>
                    </template>
                </div>

                <!-- Loading -->
                <div v-if="store.loading" class="gfy-ag-loading">
                    <span class="gfy-ag-spinner"></span> Loading rules...
                </div>

                <template v-else>

                    <!-- Custom Rules Section -->
                    <div class="gfy-ag-section-header">
                        <h3>Custom Rules</h3>
                        <span class="gfy-ag-section-count">{{ store.customRules.length }}</span>
                    </div>

                    <div v-if="store.customRules.length === 0" class="gfy-ag-empty">
                        No custom rules defined. Click "Add Rule" to create one.
                    </div>

                    <div v-else class="gfy-ag-table-wrap">
                        <table class="gfy-ag-table">
                            <thead>
                                <tr>
                                    <th>Path</th>
                                    <th>Match</th>
                                    <th>Access</th>
                                    <th>Min Tier</th>
                                    <th>Redirect</th>
                                    <th>Source</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="rule in store.customRules" :key="rule.path">
                                    <td class="col-path">{{ rule.path }}</td>
                                    <td><span class="gfy-ag-badge" :class="'gfy-ag-badge-' + rule.match_type">{{ rule.match_type }}</span></td>
                                    <td><span class="gfy-ag-badge" :class="accessBadgeClass(rule)">{{ accessLabel(rule) }}</span></td>
                                    <td>{{ rule.required_tier || '—' }}</td>
                                    <td class="col-path">{{ rule.redirect_to || '—' }}</td>
                                    <td><span class="gfy-ag-badge gfy-ag-badge-custom">custom</span></td>
                                    <td class="col-actions">
                                        <template v-if="deleteConfirm === rule.path">
                                            <span class="gfy-ag-confirm">
                                                Delete?
                                                <button class="button button-link-delete button-small" @click="doDelete(rule.path)">Yes</button>
                                                <button class="button button-link button-small" @click="cancelDelete">No</button>
                                            </span>
                                        </template>
                                        <template v-else>
                                            <button class="button button-small" @click="openEditModal(rule)">Edit</button>
                                            <button class="button button-small button-link-delete" @click="confirmDelete(rule.path)">Delete</button>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Default Rules Section -->
                    <div class="gfy-ag-section-header">
                        <h3>Default Rules</h3>
                        <span class="gfy-ag-section-count">{{ store.defaultRules.length }}</span>
                    </div>

                    <div v-if="store.defaultRules.length === 0" class="gfy-ag-empty">
                        No default rules loaded.
                    </div>

                    <div v-else class="gfy-ag-table-wrap">
                        <table class="gfy-ag-table">
                            <thead>
                                <tr>
                                    <th>Path</th>
                                    <th>Match</th>
                                    <th>Access</th>
                                    <th>Min Tier</th>
                                    <th>Redirect</th>
                                    <th>Source</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="rule in store.defaultRules" :key="rule.path">
                                    <td class="col-path">{{ rule.path }}</td>
                                    <td><span class="gfy-ag-badge" :class="'gfy-ag-badge-' + rule.match_type">{{ rule.match_type }}</span></td>
                                    <td><span class="gfy-ag-badge" :class="accessBadgeClass(rule)">{{ accessLabel(rule) }}</span></td>
                                    <td>{{ rule.required_tier || '—' }}</td>
                                    <td class="col-path">{{ rule.redirect_to || '—' }}</td>
                                    <td><span class="gfy-ag-badge gfy-ag-badge-default">default</span></td>
                                    <td class="col-actions">
                                        <button class="button button-small" @click="openOverrideModal(rule)">Override</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Plugin Rules Section -->
                    <template v-if="store.pluginRules.length > 0">
                        <div class="gfy-ag-section-header">
                            <h3>Plugin Rules</h3>
                            <span class="gfy-ag-section-count">{{ store.pluginRules.length }}</span>
                        </div>

                        <div class="gfy-ag-table-wrap">
                            <table class="gfy-ag-table">
                                <thead>
                                    <tr>
                                        <th>Path</th>
                                        <th>Match</th>
                                        <th>Access</th>
                                        <th>Min Tier</th>
                                        <th>Redirect</th>
                                        <th>Source</th>
                                        <th class="col-actions"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="rule in store.pluginRules" :key="rule.path">
                                        <td class="col-path">{{ rule.path }}</td>
                                        <td><span class="gfy-ag-badge" :class="'gfy-ag-badge-' + rule.match_type">{{ rule.match_type }}</span></td>
                                        <td><span class="gfy-ag-badge" :class="accessBadgeClass(rule)">{{ accessLabel(rule) }}</span></td>
                                        <td>{{ rule.required_tier || '—' }}</td>
                                        <td class="col-path">{{ rule.redirect_to || '—' }}</td>
                                        <td><span class="gfy-ag-badge gfy-ag-badge-plugin">plugin</span></td>
                                        <td class="col-actions">
                                            <em style="font-size:11px;color:#999;">read-only</em>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                </template>

                <!-- ADD / EDIT MODAL -->
                <div v-if="modal.visible" class="gfy-ag-modal-overlay" @click.self="closeModal">
                    <div class="gfy-ag-modal">
                        <div class="gfy-ag-modal-header">
                            <h2>{{ modal.mode === 'edit' ? 'Edit Rule' : 'Add Rule' }}</h2>
                            <button class="gfy-ag-modal-close" @click="closeModal">&times;</button>
                        </div>
                        <div class="gfy-ag-modal-body">

                            <!-- Path -->
                            <div class="gfy-ag-field">
                                <label>URL Path</label>
                                <input type="text" v-model="modal.path" class="regular-text" placeholder="/app/outreach/" />
                                <p class="description">The path prefix or exact path to match, e.g. /app/outreach/</p>
                            </div>

                            <!-- Match Type -->
                            <div class="gfy-ag-field">
                                <label>Match Type</label>
                                <select v-model="modal.match_type">
                                    <option value="prefix">Prefix — matches path and all sub-paths</option>
                                    <option value="exact">Exact — matches this path only</option>
                                </select>
                            </div>

                            <!-- Access Type -->
                            <div class="gfy-ag-field">
                                <label>Access Type</label>
                                <div class="gfy-ag-radio-group">
                                    <label>
                                        <input type="radio" v-model="modal.access_type" value="auth" /> Auth Required
                                    </label>
                                    <label>
                                        <input type="radio" v-model="modal.access_type" value="public" /> Public
                                    </label>
                                </div>
                                <p class="description">Public paths allow anyone. Auth-required paths redirect non-logged-in users to /login/.</p>
                            </div>

                            <!-- Conditional: Auth-specific fields -->
                            <div v-if="modal.access_type === 'auth'" class="gfy-ag-conditional">

                                <div class="gfy-ag-field">
                                    <label>Minimum Tier</label>
                                    <select v-model="modal.required_tier">
                                        <option v-for="opt in store.tierOptions" :key="opt.value" :value="opt.value">
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                    <p class="description">Users below this tier will be redirected. Leave empty for login-only (any tier).</p>
                                </div>

                                <div class="gfy-ag-field-inline">
                                    <div class="gfy-ag-field">
                                        <label>WP Fusion Tags</label>
                                        <input type="text" v-model="modal.required_tags" class="regular-text" placeholder="tag1, tag2" />
                                        <p class="description">Comma-separated. User needs ANY of these tags.</p>
                                    </div>
                                    <div class="gfy-ag-field">
                                        <label>WordPress Capability</label>
                                        <input type="text" v-model="modal.capability" class="regular-text" placeholder="e.g. edit_posts" />
                                        <p class="description">WP capability the user must have.</p>
                                    </div>
                                </div>

                                <div class="gfy-ag-field">
                                    <label>Custom Redirect URL</label>
                                    <input type="text" v-model="modal.redirect_to" class="regular-text" placeholder="/features/outreach/" />
                                    <p class="description">Where to redirect denied users. Leave empty for default (/upgrade/).</p>
                                </div>

                            </div>

                        </div>
                        <div class="gfy-ag-modal-footer">
                            <button class="button" @click="closeModal">Cancel</button>
                            <button class="button button-primary" @click="submitModal">
                                {{ modal.mode === 'edit' ? 'Update Rule' : 'Save Rule' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `,
    };

    // =========================================================================
    // TAB 2: TEST URL
    // =========================================================================

    const TestUrlTab = {
        setup() {
            const store = useAccessGateStore();
            const testInput = ref('');

            async function runTest() {
                if (!testInput.value.trim()) {
                    store.showToast('Please enter a URL path to test', 'error');
                    return;
                }
                store.testPath = testInput.value.trim();
                await store.testUrl(store.testPath);
            }

            function outcomeLabel(outcome) {
                const labels = {
                    allow: 'Allowed',
                    login_required: 'Login Required',
                    tier_or_capability_required: 'Tier/Capability Required',
                    no_rule: 'No Rule Matches',
                };
                return labels[outcome] || outcome;
            }

            return { store, testInput, runTest, outcomeLabel };
        },

        template: `
            <div class="gfy-ag-test-wrap">
                <p class="description" style="margin-bottom:16px;">
                    Enter a URL path to see which access rule would match and what would happen.
                </p>

                <div class="gfy-ag-test-input-row">
                    <input type="text"
                           v-model="testInput"
                           class="regular-text"
                           placeholder="/app/interview/detail/"
                           @keyup.enter="runTest" />
                    <button class="button button-primary" @click="runTest" :disabled="store.testLoading">
                        <template v-if="store.testLoading">
                            <span class="gfy-ag-spinner" style="width:14px;height:14px;border-width:2px;margin-right:4px;"></span>
                            Testing...
                        </template>
                        <template v-else>Test Path</template>
                    </button>
                </div>

                <!-- Result -->
                <div v-if="store.testResult" class="gfy-ag-test-result">
                    <h4>Result</h4>

                    <div class="gfy-ag-test-result-row">
                        <span class="gfy-ag-test-result-label">Tested Path:</span>
                        <span class="gfy-ag-test-result-value">{{ store.testPath }}</span>
                    </div>

                    <div class="gfy-ag-test-result-row">
                        <span class="gfy-ag-test-result-label">Rule Matched:</span>
                        <span class="gfy-ag-test-result-value">
                            <template v-if="store.testResult.matched">
                                Yes — <code>{{ store.testResult.rule.path }}</code>
                                <span class="gfy-ag-badge" :class="'gfy-ag-badge-' + store.testResult.rule.source" style="margin-left:6px;">
                                    {{ store.testResult.rule.source }}
                                </span>
                            </template>
                            <template v-else>
                                <em>No matching rule</em>
                            </template>
                        </span>
                    </div>

                    <div class="gfy-ag-test-result-row">
                        <span class="gfy-ag-test-result-label">Outcome:</span>
                        <span class="gfy-ag-test-result-value">
                            <span class="gfy-ag-outcome" :class="'gfy-ag-outcome-' + store.testResult.outcome">
                                {{ outcomeLabel(store.testResult.outcome) }}
                            </span>
                        </span>
                    </div>

                    <template v-if="store.testResult.matched && store.testResult.rule">
                        <div class="gfy-ag-test-result-row">
                            <span class="gfy-ag-test-result-label">Match Type:</span>
                            <span class="gfy-ag-test-result-value">{{ store.testResult.rule.match_type || 'prefix' }}</span>
                        </div>

                        <div v-if="store.testResult.rule.required_tier" class="gfy-ag-test-result-row">
                            <span class="gfy-ag-test-result-label">Required Tier:</span>
                            <span class="gfy-ag-test-result-value">{{ store.testResult.rule.required_tier }}</span>
                        </div>

                        <div v-if="store.testResult.rule.required_tags && store.testResult.rule.required_tags.length" class="gfy-ag-test-result-row">
                            <span class="gfy-ag-test-result-label">Required Tags:</span>
                            <span class="gfy-ag-test-result-value">{{ store.testResult.rule.required_tags.join(', ') }}</span>
                        </div>

                        <div v-if="store.testResult.rule.capability" class="gfy-ag-test-result-row">
                            <span class="gfy-ag-test-result-label">Capability:</span>
                            <span class="gfy-ag-test-result-value">{{ store.testResult.rule.capability }}</span>
                        </div>

                        <div v-if="store.testResult.rule.redirect_to" class="gfy-ag-test-result-row">
                            <span class="gfy-ag-test-result-label">Redirect To:</span>
                            <span class="gfy-ag-test-result-value">{{ store.testResult.rule.redirect_to }}</span>
                        </div>
                    </template>
                </div>
            </div>
        `,
    };

    // =========================================================================
    // MOUNT APP
    // =========================================================================

    document.addEventListener('DOMContentLoaded', function () {
        const mountEl = document.getElementById('gfy-app-access-gate');
        if (!mountEl) return;

        const activeTab = mountEl.dataset.tab || 'rules';

        const tabComponents = {
            rules: RulesTab,
            test: TestUrlTab,
        };

        const tabComponent = tabComponents[activeTab] || RulesTab;

        const RootApp = {
            setup() {
                const store = useAccessGateStore();
                return { store };
            },
            components: { TabContent: tabComponent },
            template: `
                <div>
                    <div v-if="store.toast.visible" class="gfy-toast" :class="'gfy-toast-' + store.toast.type">
                        {{ store.toast.message }}
                    </div>
                    <TabContent />
                </div>
            `,
        };

        const app = createApp(RootApp);
        app.use(createPinia());
        app.mount('#gfy-app-access-gate');
    });

})();
