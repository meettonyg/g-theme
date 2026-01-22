/**
 * Command Palette (Ctrl+K Search)
 * Guestify quick search and navigation
 */

(function() {
    'use strict';

    // ==========================================================================
    // CONFIGURATION
    // ==========================================================================

    const CONFIG = {
        debounceMs: 300,
        minSearchLength: 2,
        maxResults: 5,
        recentStorageKey: 'guestify_recent_searches',
        maxRecentItems: 5
    };

    // Navigation items for quick access
    const NAV_ITEMS = [
        { title: 'Prospector - Search', url: '/app/prospector/search/', icon: 'fa-magnifying-glass', section: 'Prospector' },
        { title: 'Episodes by Person', url: '/app/prospector/search/?tab=person', icon: 'fa-user', section: 'Prospector' },
        { title: 'Podcasts by Title', url: '/app/prospector/search/?tab=title', icon: 'fa-microphone', section: 'Prospector' },
        { title: 'Pipeline Dashboard', url: '/app/pipeline/', icon: 'fa-layer-group', section: 'Pipeline' },
        { title: 'My Podcasts', url: '/app/pipeline/podcasts/', icon: 'fa-podcast', section: 'Pipeline' },
        { title: 'Opportunities', url: '/app/pipeline/opportunities/', icon: 'fa-lightbulb', section: 'Pipeline' },
        { title: 'Calendar', url: '/app/pipeline/calendar/', icon: 'fa-calendar', section: 'Pipeline' },
        { title: 'Outreach Dashboard', url: '/app/outreach/', icon: 'fa-paper-plane', section: 'Outreach' },
        { title: 'Campaigns', url: '/app/outreach/campaigns/', icon: 'fa-bullhorn', section: 'Outreach' },
        { title: 'Templates', url: '/app/outreach/templates/', icon: 'fa-file-lines', section: 'Outreach' },
        { title: 'My Profile', url: '/app/media-kit/profile/', icon: 'fa-id-card', section: 'Media Kit' },
        { title: 'Media Kit Builder', url: '/tools/media-kit/', icon: 'fa-wand-magic-sparkles', section: 'Media Kit' },
        { title: 'AI Content Tools', url: '/app/media-kit/ai-tools/', icon: 'fa-robot', section: 'Media Kit' },
        { title: 'Platform Insights', url: '/app/insights/', icon: 'fa-chart-line', section: 'Insights' },
        { title: 'Cost Tracking', url: '/app/insights/costs/', icon: 'fa-sack-dollar', section: 'Insights' },
        { title: 'Reports', url: '/app/insights/reports/', icon: 'fa-file-arrow-down', section: 'Insights' }
    ];

    // ==========================================================================
    // STATE
    // ==========================================================================

    let state = {
        isOpen: false,
        query: '',
        selectedIndex: 0,
        results: [],
        searchTimeout: null
    };

    // ==========================================================================
    // DOM ELEMENTS
    // ==========================================================================

    let elements = {};

    function cacheElements() {
        elements = {
            palette: document.getElementById('commandPalette'),
            backdrop: document.getElementById('commandPaletteBackdrop'),
            input: document.getElementById('commandPaletteInput'),
            body: document.getElementById('commandPaletteBody'),
            toggle: document.getElementById('commandPaletteToggle'),
            // Sections
            sectionRecent: document.getElementById('cpSectionRecent'),
            sectionActions: document.getElementById('cpSectionActions'),
            sectionNav: document.getElementById('cpSectionNav'),
            sectionPodcasts: document.getElementById('cpSectionPodcasts'),
            sectionGuests: document.getElementById('cpSectionGuests'),
            sectionCampaigns: document.getElementById('cpSectionCampaigns'),
            // Results containers
            resultsRecent: document.getElementById('cpResultsRecent'),
            resultsNav: document.getElementById('cpResultsNav'),
            resultsPodcasts: document.getElementById('cpResultsPodcasts'),
            resultsGuests: document.getElementById('cpResultsGuests'),
            resultsCampaigns: document.getElementById('cpResultsCampaigns'),
            // States
            empty: document.getElementById('cpEmpty'),
            loading: document.getElementById('cpLoading')
        };
    }

    // ==========================================================================
    // OPEN / CLOSE
    // ==========================================================================

    function open() {
        if (state.isOpen) return;
        state.isOpen = true;
        elements.palette.classList.add('command-palette--open');
        elements.input.focus();
        document.body.style.overflow = 'hidden';

        // Show default view
        showDefaultView();
    }

    function close() {
        if (!state.isOpen) return;
        state.isOpen = false;
        elements.palette.classList.remove('command-palette--open');
        elements.input.value = '';
        state.query = '';
        state.selectedIndex = 0;
        document.body.style.overflow = '';
    }

    function toggle() {
        state.isOpen ? close() : open();
    }

    // ==========================================================================
    // DEFAULT VIEW
    // ==========================================================================

    function showDefaultView() {
        // Show default sections
        elements.sectionRecent.style.display = '';
        elements.sectionActions.style.display = '';
        elements.sectionNav.style.display = '';

        // Hide search result sections
        elements.sectionPodcasts.classList.add('command-palette__section--hidden');
        elements.sectionGuests.classList.add('command-palette__section--hidden');
        elements.sectionCampaigns.classList.add('command-palette__section--hidden');

        elements.empty.style.display = 'none';
        elements.loading.style.display = 'none';

        // Populate recent searches
        renderRecentSearches();

        // Populate navigation shortcuts
        renderNavigation();

        // Collect all items for keyboard navigation
        collectSelectableItems();
    }

    // ==========================================================================
    // RECENT SEARCHES
    // ==========================================================================

    function getRecentSearches() {
        try {
            const stored = localStorage.getItem(CONFIG.recentStorageKey);
            return stored ? JSON.parse(stored) : [];
        } catch (e) {
            return [];
        }
    }

    function saveRecentSearch(item) {
        const recent = getRecentSearches();

        // Remove duplicate if exists
        const filtered = recent.filter(r => r.url !== item.url);

        // Add to beginning
        filtered.unshift({
            title: item.title,
            url: item.url,
            type: item.type || 'page',
            icon: item.icon || 'fa-clock-rotate-left'
        });

        // Limit to max items
        const limited = filtered.slice(0, CONFIG.maxRecentItems);

        localStorage.setItem(CONFIG.recentStorageKey, JSON.stringify(limited));
    }

    function renderRecentSearches() {
        const recent = getRecentSearches();

        if (recent.length === 0) {
            elements.sectionRecent.style.display = 'none';
            return;
        }

        elements.sectionRecent.style.display = '';
        elements.resultsRecent.innerHTML = recent.map(item => createItemHTML(item)).join('');
    }

    // ==========================================================================
    // NAVIGATION
    // ==========================================================================

    function renderNavigation(filter = '') {
        let items = NAV_ITEMS;

        if (filter) {
            const lowerFilter = filter.toLowerCase();
            items = NAV_ITEMS.filter(item =>
                item.title.toLowerCase().includes(lowerFilter) ||
                item.section.toLowerCase().includes(lowerFilter)
            );
        }

        // Limit results
        items = items.slice(0, 8);

        if (items.length === 0) {
            elements.sectionNav.style.display = 'none';
            return;
        }

        elements.sectionNav.style.display = '';
        elements.resultsNav.innerHTML = items.map(item => createItemHTML({
            title: item.title,
            url: item.url,
            icon: item.icon,
            hint: item.section,
            type: 'page'
        })).join('');
    }

    // ==========================================================================
    // SEARCH
    // ==========================================================================

    function handleInput(e) {
        const query = e.target.value.trim();
        state.query = query;

        // Clear previous timeout
        if (state.searchTimeout) {
            clearTimeout(state.searchTimeout);
        }

        if (query.length < CONFIG.minSearchLength) {
            showDefaultView();
            return;
        }

        // Debounce the search
        state.searchTimeout = setTimeout(() => {
            performSearch(query);
        }, CONFIG.debounceMs);
    }

    async function performSearch(query) {
        // Show loading
        elements.loading.style.display = '';
        elements.empty.style.display = 'none';

        // Hide default sections
        elements.sectionRecent.style.display = 'none';
        elements.sectionActions.style.display = 'none';

        // Filter navigation
        renderNavigation(query);

        try {
            // Call API
            const nonce = window.guestifyCommandPalette?.nonce || '';
            const response = await fetch(`/wp-json/guestify/v1/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-WP-Nonce': nonce
                }
            });

            if (!response.ok) throw new Error('Search failed');

            const data = await response.json();

            elements.loading.style.display = 'none';

            renderSearchResults(data);

        } catch (error) {
            console.error('Search error:', error);
            elements.loading.style.display = 'none';

            // Still show filtered navigation even if API fails
            if (elements.resultsNav.children.length === 0) {
                elements.empty.style.display = '';
            }
        }

        collectSelectableItems();
    }

    function renderSearchResults(data) {
        let hasResults = false;

        // Podcasts
        if (data.podcasts && data.podcasts.length > 0) {
            elements.sectionPodcasts.classList.remove('command-palette__section--hidden');
            elements.resultsPodcasts.innerHTML = data.podcasts.slice(0, CONFIG.maxResults).map(item => createItemHTML({
                title: item.title,
                subtitle: item.host || item.publisher,
                url: item.url,
                icon: 'fa-podcast',
                type: 'podcast'
            })).join('');
            hasResults = true;
        } else {
            elements.sectionPodcasts.classList.add('command-palette__section--hidden');
        }

        // Guests
        if (data.guests && data.guests.length > 0) {
            elements.sectionGuests.classList.remove('command-palette__section--hidden');
            elements.resultsGuests.innerHTML = data.guests.slice(0, CONFIG.maxResults).map(item => createItemHTML({
                title: item.name,
                subtitle: item.company || item.title,
                url: item.url,
                icon: 'fa-user',
                type: 'guest'
            })).join('');
            hasResults = true;
        } else {
            elements.sectionGuests.classList.add('command-palette__section--hidden');
        }

        // Campaigns
        if (data.campaigns && data.campaigns.length > 0) {
            elements.sectionCampaigns.classList.remove('command-palette__section--hidden');
            elements.resultsCampaigns.innerHTML = data.campaigns.slice(0, CONFIG.maxResults).map(item => createItemHTML({
                title: item.title,
                subtitle: item.status,
                url: item.url,
                icon: 'fa-bullhorn',
                type: 'campaign'
            })).join('');
            hasResults = true;
        } else {
            elements.sectionCampaigns.classList.add('command-palette__section--hidden');
        }

        // Check if no results at all
        const hasNavResults = elements.resultsNav.children.length > 0;
        if (!hasResults && !hasNavResults) {
            elements.empty.style.display = '';
        } else {
            elements.empty.style.display = 'none';
        }
    }

    // ==========================================================================
    // ITEM RENDERING
    // ==========================================================================

    function createItemHTML(item) {
        const hasSubtitle = item.subtitle;

        return `
            <a href="${escapeHTML(item.url)}"
               class="command-palette__item"
               data-type="${item.type || 'page'}"
               data-title="${escapeHTML(item.title)}"
               data-url="${escapeHTML(item.url)}">
                <i class="fa-solid ${item.icon} command-palette__item-icon"></i>
                ${hasSubtitle ? `
                    <div class="command-palette__item-meta">
                        <span class="command-palette__item-text">${escapeHTML(item.title)}</span>
                        <span class="command-palette__item-subtitle">${escapeHTML(item.subtitle)}</span>
                    </div>
                ` : `
                    <span class="command-palette__item-text">${escapeHTML(item.title)}</span>
                `}
                ${item.hint ? `<span class="command-palette__item-hint">${escapeHTML(item.hint)}</span>` : ''}
            </a>
        `;
    }

    function escapeHTML(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ==========================================================================
    // KEYBOARD NAVIGATION
    // ==========================================================================

    function collectSelectableItems() {
        state.results = Array.from(elements.body.querySelectorAll('.command-palette__item:not([style*="display: none"])'));
        state.selectedIndex = 0;
        updateSelection();
    }

    function updateSelection() {
        // Remove previous selection
        state.results.forEach(item => item.classList.remove('command-palette__item--selected'));

        // Add selection to current
        if (state.results[state.selectedIndex]) {
            state.results[state.selectedIndex].classList.add('command-palette__item--selected');
            state.results[state.selectedIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    function selectNext() {
        if (state.results.length === 0) return;
        state.selectedIndex = (state.selectedIndex + 1) % state.results.length;
        updateSelection();
    }

    function selectPrevious() {
        if (state.results.length === 0) return;
        state.selectedIndex = (state.selectedIndex - 1 + state.results.length) % state.results.length;
        updateSelection();
    }

    function selectCurrent() {
        const selected = state.results[state.selectedIndex];
        if (selected) {
            // Save to recent
            saveRecentSearch({
                title: selected.dataset.title,
                url: selected.dataset.url,
                type: selected.dataset.type
            });

            // Navigate
            window.location.href = selected.href;
        }
    }

    // ==========================================================================
    // EVENT HANDLERS
    // ==========================================================================

    function handleKeyDown(e) {
        // Global Ctrl+K / Cmd+K to open
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            toggle();
            return;
        }

        // Only handle these when open
        if (!state.isOpen) return;

        switch (e.key) {
            case 'Escape':
                e.preventDefault();
                close();
                break;
            case 'ArrowDown':
                e.preventDefault();
                selectNext();
                break;
            case 'ArrowUp':
                e.preventDefault();
                selectPrevious();
                break;
            case 'Enter':
                e.preventDefault();
                selectCurrent();
                break;
        }
    }

    function handleClick(e) {
        // Close when clicking backdrop
        if (e.target === elements.backdrop) {
            close();
            return;
        }

        // Handle item click
        const item = e.target.closest('.command-palette__item');
        if (item) {
            // Save to recent
            saveRecentSearch({
                title: item.dataset.title,
                url: item.dataset.url,
                type: item.dataset.type
            });
        }
    }

    // ==========================================================================
    // INITIALIZATION
    // ==========================================================================

    function init() {
        // Wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setup);
        } else {
            setup();
        }
    }

    function setup() {
        cacheElements();

        if (!elements.palette) {
            console.warn('Command palette elements not found');
            return;
        }

        // Event listeners
        document.addEventListener('keydown', handleKeyDown);
        elements.palette.addEventListener('click', handleClick);
        elements.input.addEventListener('input', handleInput);

        // Toggle button
        if (elements.toggle) {
            elements.toggle.addEventListener('click', (e) => {
                e.preventDefault();
                open();
            });
        }

        // Populate initial nav items
        renderNavigation();
    }

    // Start
    init();

    // Expose for external use
    window.GuestifyCommandPalette = {
        open,
        close,
        toggle
    };

})();
