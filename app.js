/* ============================================================
   cask.ink — app.js
   Vanilla JS. No framework. No build step. No localStorage.
   Dependency: marked.js (loaded via CDN in index.php)
   ============================================================ */

(function () {
    'use strict';

    // ----------------------------------------------------------
    // State
    // ----------------------------------------------------------
    let allNodes  = [];
    let nodeTree  = [];
    let nodeMap   = {};
    let currentId = null;

    // ----------------------------------------------------------
    // DOM refs
    // ----------------------------------------------------------
    let elSidebar, elNavList, elSearchInput,
        elContent, elBreadcrumb, elMarkdown,
        elThumbnail, elTags, elSidebarToggle;

    // ----------------------------------------------------------
    // 1. Bootstrap
    // ----------------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        elSidebar       = document.getElementById('cask-sidebar');
        elNavList       = document.getElementById('cask-nav');
        elSearchInput   = document.getElementById('cask-search-input');
        elContent       = document.getElementById('cask-content-area');
        elBreadcrumb    = document.getElementById('cask-breadcrumb');
        elMarkdown      = document.getElementById('cask-markdown');
        elThumbnail     = document.getElementById('cask-thumbnail');
        elTags          = document.getElementById('cask-tags');
        elSidebarToggle = document.getElementById('cask-sidebar-toggle');

        if (elSidebarToggle) {
            elSidebarToggle.addEventListener('click', () => elSidebar.classList.toggle('open'));
        }

        if (elSearchInput) {
            elSearchInput.addEventListener('input', onSearch);
        }

        initWikiLinkHandler();
        initAdmin();
        loadNodes();
    });

    // ----------------------------------------------------------
    // 2. Load nodes: PHP first, JSON fallback
    // ----------------------------------------------------------
    async function loadNodes() {
        setLoading(true);
        let nodes = null;

        try {
            const res  = await fetch('content-list.php');
            const text = await res.text();
            const data = JSON.parse(text);
            if (Array.isArray(data)) nodes = data;
        } catch (_) {}

        if (!nodes) {
            try {
                const res  = await fetch('content/index.json');
                const text = await res.text();
                const stripped = text.split('\n')
                    .filter(l => !l.trim().startsWith('//'))
                    .join('\n');
                const data = JSON.parse(stripped);
                if (Array.isArray(data)) nodes = data;
            } catch (_) {}
        }

        setLoading(false);

        if (!nodes || nodes.length === 0) {
            showEmpty('No content found. Add .md files to your /content/ folder.');
            return;
        }

        allNodes = nodes;
        buildTree();
        renderSidebar();

        const hash = decodeURIComponent(window.location.hash.replace('#', ''));
        if (hash && nodeMap[hash]) {
            loadPage(hash);
        } else if (nodeTree.length > 0) {
            loadPage(findFirstLeaf(nodeTree[0]).identifier);
        }
    }

    // ----------------------------------------------------------
    // 3. Tree building (two-pass)
    // ----------------------------------------------------------
    function buildTree() {
        nodeMap = {};

        // Pass 1: index by declared identifier
        allNodes.forEach(node => {
            node.children = [];
            node._resolvedParent = null;
            nodeMap[node.identifier] = node;
        });

        // Pass 1b: add filename aliases only if not already claimed
        allNodes.forEach(node => {
            [node.file, node.file.replace(/\.(md|txt)$/i, '')].forEach(alias => {
                if (!nodeMap[alias]) nodeMap[alias] = node;
            });
        });

        // Pass 2: resolve parents
        const roots = [];
        allNodes.forEach(node => {
            const p = node.parent && nodeMap[node.parent];
            if (p && p !== node) {
                p.children.push(node);
                node._resolvedParent = p;
            } else {
                roots.push(node);
            }
        });

        // Sort every level
        function sortLevel(arr) {
            arr.sort((a, b) => {
                const ao = a.order != null ? a.order : Infinity;
                const bo = b.order != null ? b.order : Infinity;
                if (ao !== bo) return ao - bo;
                const t = a.title.localeCompare(b.title);
                return t !== 0 ? t : a.file.localeCompare(b.file);
            });
            arr.forEach(n => { if (n.children.length) sortLevel(n.children); });
        }
        sortLevel(roots);
        nodeTree = roots;
    }

    function findFirstLeaf(node) {
        return (!node.children || !node.children.length) ? node : findFirstLeaf(node.children[0]);
    }

    // ----------------------------------------------------------
    // 4. Sidebar
    // ----------------------------------------------------------
    const expandDepth = (window.CASK_CONFIG && window.CASK_CONFIG.expandDepth != null)
        ? parseInt(window.CASK_CONFIG.expandDepth, 10) : 1;

    function renderSidebar(nodes, container, depth) {
        nodes     = nodes     || nodeTree;
        container = container || elNavList;
        depth     = depth     != null ? depth : 0;
        container.innerHTML = '';

        nodes.forEach(node => {
            const li  = document.createElement('li');
            li.className = 'cask-nav__item';

            const hasChildren = node.children && node.children.length > 0;
            const link = document.createElement('span');
            link.className   = 'cask-nav__link';
            link.dataset.id  = node.identifier;
            link.title       = node.title;

            if (hasChildren) {
                const toggle = document.createElement('span');
                toggle.className = 'cask-nav__toggle';
                toggle.textContent = '▶';
                toggle.setAttribute('aria-hidden', 'true');
                toggle.addEventListener('click', e => {
                    e.stopPropagation();
                    const cl = li.querySelector('.cask-nav__children');
                    if (cl) {
                        cl.classList.toggle('open');
                        toggle.classList.toggle('open');
                    }
                });
                link.appendChild(toggle);
            }

            link.appendChild(document.createTextNode(node.title));
            link.addEventListener('click', e => {
                e.stopPropagation();
                loadPage(node.identifier);
                if (window.innerWidth <= 768) elSidebar.classList.remove('open');
            });

            li.appendChild(link);

            if (hasChildren) {
                const childList = document.createElement('ul');
                childList.className = 'cask-nav__children';
                if (depth < expandDepth) {
                    childList.classList.add('open');
                    const t = link.querySelector('.cask-nav__toggle');
                    if (t) t.classList.add('open');
                }
                renderSidebar(node.children, childList, depth + 1);
                li.appendChild(childList);
            }

            container.appendChild(li);
        });
    }

    function setActiveLink(identifier) {
        document.querySelectorAll('.cask-nav__link').forEach(el => {
            el.classList.toggle('active', el.dataset.id === identifier);
        });
        const activeEl = document.querySelector(`.cask-nav__link[data-id="${identifier}"]`);
        if (activeEl) {
            let p = activeEl.closest('.cask-nav__children');
            while (p) {
                p.classList.add('open');
                const t = p.previousSibling && p.previousSibling.querySelector && p.previousSibling.querySelector('.cask-nav__toggle');
                if (t) t.classList.add('open');
                p = p.parentElement ? p.parentElement.closest('.cask-nav__children') : null;
            }
        }
    }

    // ----------------------------------------------------------
    // 5. Page loading
    // ----------------------------------------------------------
    async function loadPage(identifier) {
        const node = nodeMap[identifier];
        if (!node) return;

        currentId = identifier;
        setActiveLink(identifier);
        window.location.hash = encodeURIComponent(identifier);
        renderBreadcrumb(node);

        if (elThumbnail) {
            elThumbnail.style.display = node.thumbnail ? 'block' : 'none';
            if (node.thumbnail) { elThumbnail.src = node.thumbnail; elThumbnail.alt = node.title; }
        }

        if (elTags) {
            elTags.innerHTML = '';
            if (node.tags && node.tags.length) {
                node.tags.forEach(tag => {
                    const s = document.createElement('span');
                    s.className   = 'cask-tag';
                    s.textContent = tag;
                    elTags.appendChild(s);
                });
                elTags.style.display = 'flex';
            } else {
                elTags.style.display = 'none';
            }
        }

        if (elMarkdown) {
            elMarkdown.innerHTML = '<span class="cask-loading">Loading…</span>';
            try {
                const res = await fetch('content/' + node.file);
                if (!res.ok) throw new Error('not found');
                const raw  = await res.text();
                elMarkdown.innerHTML = renderMarkdown(stripFrontmatter(raw));
                addCopyButtons(elMarkdown);
            } catch (_) {
                elMarkdown.innerHTML = '<p class="cask-empty">Could not load this page.</p>';
            }
        }

        if (elContent) elContent.scrollTop = 0;
        window.scrollTo(0, 0);
    }

    function renderBreadcrumb(node) {
        if (!elBreadcrumb) return;
        elBreadcrumb.innerHTML = '';
        const chain = [];
        let cur = node;
        while (cur) { chain.unshift(cur); cur = cur._resolvedParent; }
        chain.forEach((n, i) => {
            if (i > 0) {
                const sep = document.createElement('span');
                sep.className   = 'cask-breadcrumb__sep';
                sep.textContent = '/';
                elBreadcrumb.appendChild(sep);
            }
            if (i < chain.length - 1) {
                const a = document.createElement('a');
                a.href        = '#' + encodeURIComponent(n.identifier);
                a.textContent = n.title;
                a.addEventListener('click', e => { e.preventDefault(); loadPage(n.identifier); });
                elBreadcrumb.appendChild(a);
            } else {
                const s = document.createElement('span');
                s.textContent = n.title;
                elBreadcrumb.appendChild(s);
            }
        });
    }

    // ----------------------------------------------------------
    // 6. Markdown & wiki links
    // ----------------------------------------------------------
    function stripFrontmatter(raw) {
        const t = raw.trimStart();
        if (!t.startsWith('---')) return raw;
        const end = t.indexOf('---', 3);
        return end === -1 ? raw : t.slice(end + 3).trimStart();
    }

    function preprocessWikiLinks(md) {
        return md.replace(/\[\[([^\]|]+?)(?:\|([^\]]+?))?\]\]/g, (_, target, label) => {
            const text    = (label || target).trim();
            const trimmed = target.trim();
            const node    = nodeMap[trimmed] || nodeMap[trimmed + '.md'] || nodeMap[trimmed + '.txt'];
            return node
                ? `<a href="#" class="cask-wikilink" data-id="${node.identifier}">${text}</a>`
                : `<span class="cask-wikilink cask-wikilink--missing" title="Page not found: ${trimmed}">${text}</span>`;
        });
    }

    function initWikiLinkHandler() {
        if (!elMarkdown) return;
        elMarkdown.addEventListener('click', e => {
            const link = e.target.closest('.cask-wikilink[data-id]');
            if (!link) return;
            e.preventDefault();
            loadPage(link.dataset.id);
        });
    }

    function renderMarkdown(md) {
        const processed = preprocessWikiLinks(md);
        if (typeof marked !== 'undefined') return marked.parse(processed);
        return processed
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .split('\n\n').map(p => `<p>${p.replace(/\n/g, '<br>')}</p>`).join('');
    }

    function addCopyButtons(container) {
        container.querySelectorAll('pre').forEach(pre => {
            const code = pre.querySelector('code');
            if (!code || pre.querySelector('.cask-copy-btn')) return;
            const text = code.innerText || code.textContent || '';
            if (!text.includes('\n')) return;

            const btn = document.createElement('button');
            btn.className = 'cask-copy-btn';
            btn.setAttribute('aria-label', 'Copy code');
            // SVG copy icon — two overlapping rectangles, universally understood
            btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>`;

            btn.addEventListener('click', () => {
                const done = () => {
                    // Switch to a checkmark on success
                    btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>`;
                    btn.classList.add('cask-copy-btn--copied');
                    setTimeout(() => {
                        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>`;
                        btn.classList.remove('cask-copy-btn--copied');
                    }, 2000);
                };
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(done).catch(done);
                } else {
                    const ta = Object.assign(document.createElement('textarea'), { value: text });
                    Object.assign(ta.style, { position: 'fixed', opacity: '0' });
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    done();
                }
            });
            pre.appendChild(btn);
        });
    }

    // ----------------------------------------------------------
    // 7. Search
    // ----------------------------------------------------------
    let searchTimeout = null;

    function onSearch(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        if (!query) { renderSidebar(); if (currentId) setActiveLink(currentId); return; }
        searchTimeout = setTimeout(() => runSearch(query.toLowerCase()), 180);
    }

    function runSearch(query) {
        const results = allNodes.filter(node =>
            [node.title, node.excerpt || '', (node.tags || []).join(' ')]
                .join(' ').toLowerCase().includes(query)
        );

        elNavList.innerHTML = '';
        if (!results.length) {
            const li = document.createElement('li');
            li.className   = 'cask-nav__item';
            li.innerHTML   = '<span class="cask-empty">No results.</span>';
            elNavList.appendChild(li);
        } else {
            results.forEach(node => {
                const li   = document.createElement('li');
                li.className = 'cask-nav__item';
                const link = document.createElement('span');
                link.className   = 'cask-nav__link';
                link.dataset.id  = node.identifier;
                link.textContent = node.title;
                link.title       = node.excerpt || node.title;
                link.addEventListener('click', () => loadPage(node.identifier));
                li.appendChild(link);
                elNavList.appendChild(li);
            });
        }

        if (elMarkdown) {
            if (elBreadcrumb) elBreadcrumb.innerHTML = '';
            if (elThumbnail)  elThumbnail.style.display = 'none';
            if (elTags)       elTags.style.display = 'none';

            const ul = document.createElement('ul');
            ul.className = 'cask-search-results';
            results.forEach(node => {
                const li  = document.createElement('li');
                li.className = 'cask-search-result';
                const ttl = document.createElement('div');
                ttl.className   = 'cask-search-result__title';
                ttl.textContent = node.title;
                li.appendChild(ttl);
                if (node.excerpt) {
                    const ex = document.createElement('div');
                    ex.className   = 'cask-search-result__excerpt';
                    ex.textContent = node.excerpt;
                    li.appendChild(ex);
                }
                li.addEventListener('click', () => loadPage(node.identifier));
                ul.appendChild(li);
            });

            elMarkdown.innerHTML = '';
            const h = document.createElement('h2');
            h.textContent = `${results.length} result${results.length !== 1 ? 's' : ''} for "${query}"`;
            h.style.marginBottom = '1.5rem';
            elMarkdown.appendChild(h);
            elMarkdown.appendChild(ul);
        }
    }

    // ----------------------------------------------------------
    // 8. Admin panel
    // ----------------------------------------------------------
    // SESSION-ONLY design preview. No localStorage. No persistence.
    // Export CSS → upload to server → that is the only way to save changes.
    // The dynamic <style> tag is the ONLY mechanism used to apply preview styles.
    // root.style.setProperty() is NEVER called — it would override style.css.

    function initAdmin() {
        const trigger = document.getElementById('cask-admin-trigger');
        const overlay = document.getElementById('cask-modal-overlay');
        const closeBtn = document.getElementById('cask-modal-close');
        if (!trigger || !overlay) return;

        trigger.addEventListener('click', () => {
            populateAdminFromComputedStyles();
            overlay.classList.add('open');
            overlay.setAttribute('aria-hidden', 'false');
        });

        closeBtn && closeBtn.addEventListener('click', closeAdmin);
        overlay.addEventListener('click', e => { if (e.target === overlay) closeAdmin(); });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && overlay.classList.contains('open')) closeAdmin();
        });

        // Tabs
        document.querySelectorAll('.cask-modal__tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.cask-modal__tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const target = tab.dataset.tab;
                document.querySelectorAll('.cask-modal__body').forEach(body => {
                    body.classList.toggle('cask-modal__body--hidden', body.id !== `cask-tab-${target}`);
                });
            });
        });

        // Colour swatch ↔ hex text sync
        document.querySelectorAll('.cask-color-swatch').forEach(swatch => {
            const textEl = document.getElementById(swatch.id + '-text');
            if (!textEl) return;
            swatch.addEventListener('input', () => { textEl.value = swatch.value; });
            textEl.addEventListener('input', () => {
                if (/^#[0-9a-fA-F]{3,6}$/.test(textEl.value)) swatch.value = textEl.value;
            });
        });

        // Live preview on any field change — writes ONLY to the <style> tag
        document.querySelectorAll('#cask-tab-design input, #cask-tab-design textarea').forEach(el => {
            el.addEventListener('input',  writePreviewStyles);
            el.addEventListener('change', writePreviewStyles);
        });

        document.getElementById('admin-save') && document.getElementById('admin-save').addEventListener('click', () => {
            writePreviewStyles();
            closeAdmin();
        });

        document.getElementById('admin-reset') && document.getElementById('admin-reset').addEventListener('click', () => {
            const dynStyle = document.getElementById('cask-dynamic-styles');
            if (dynStyle) dynStyle.textContent = '';
            populateAdminFromComputedStyles();
        });

        document.getElementById('admin-export-css') && document.getElementById('admin-export-css').addEventListener('click', exportCSS);

        document.getElementById('admin-map-save') && document.getElementById('admin-map-save').addEventListener('click', () => {
            applyDataMapping();
            closeAdmin();
        });

        populateDataMappingFields();
    }

    function closeAdmin() {
        const overlay = document.getElementById('cask-modal-overlay');
        if (overlay) { overlay.classList.remove('open'); overlay.setAttribute('aria-hidden', 'true'); }
    }

    // Reads computed CSS variable values from the live page and populates the admin fields.
    // This means the panel always shows what style.css (or the current preview) has set.
    function populateAdminFromComputedStyles() {
        const cs = getComputedStyle(document.documentElement);
        const get = name => cs.getPropertyValue(name).trim();

        const setColor = (swatchId, varName) => {
            const raw    = get(varName);
            const hex    = rgbToHex(raw) || raw;
            const swatch = document.getElementById(swatchId);
            const text   = document.getElementById(swatchId + '-text');
            if (swatch && hex.startsWith('#')) swatch.value = hex;
            if (text)                          text.value   = hex;
        };

        setColor('admin-color-bg',         '--color-bg');
        setColor('admin-color-text',        '--color-text');
        setColor('admin-color-accent',      '--color-accent');
        setColor('admin-color-sidebar-bg',  '--color-sidebar-bg');
        setColor('admin-color-navbar-bg',   '--color-navbar-bg');
        setColor('admin-color-border',      '--color-border');
        setColor('admin-color-muted',       '--color-muted');
        setColor('admin-color-code-bg',     '--color-code-bg');
        setColor('admin-color-code-text',   '--color-code-text');
        setColor('admin-color-hover-bg',    '--color-hover-bg');
        setColor('admin-color-tag-bg',      '--color-tag-bg');
        setColor('admin-color-tag-text',    '--color-tag-text');
        setColor('admin-color-tag-border',  '--color-tag-border');

        const setVal = (id, varName, fallback) => {
            const el = document.getElementById(id);
            if (el) el.value = get(varName) || fallback || '';
        };

        setVal('admin-font-size-base',    '--font-size-base',    '17px');
        setVal('admin-line-height',       '--line-height-body',  '1.75');
        setVal('admin-sidebar-width',     '--sidebar-width',     '272px');
        setVal('admin-content-max-width', '--content-max-width', '72ch');
        setVal('admin-radius-sm',         '--radius-sm',         '3px');
        setVal('admin-radius-md',         '--radius-md',         '6px');
        setVal('admin-radius-tag',        '--radius-tag',        '3px');

        // Font fields — CSS vars return empty for font strings in some browsers, use fallbacks
        const setFont = (id, varName, fallback) => {
            const el = document.getElementById(id);
            if (!el) return;
            const v = get(varName);
            el.value = v || fallback;
        };
        setFont('admin-font-heading', '--font-heading', "'Libre Baskerville', Georgia, serif");
        setFont('admin-font-body',    '--font-body',    "'Inter', system-ui, sans-serif");
        setFont('admin-font-mono',    '--font-ui',      "'DM Mono', 'Courier New', monospace");

        // Heading sizes (not CSS vars, set via dynamic style tag)
        const setSizeWeight = (sizeId, weightId, defaultSize, defaultWeight) => {
            const se = document.getElementById(sizeId);
            const we = document.getElementById(weightId);
            if (se && !se.value) se.value = defaultSize;
            if (we && !we.value) we.value = defaultWeight;
        };
        setSizeWeight('admin-h1-size', 'admin-h1-weight', '2rem',    '600');
        setSizeWeight('admin-h2-size', 'admin-h2-weight', '1.45rem', '600');
        setSizeWeight('admin-h3-size', 'admin-h3-weight', '1.15rem', '600');

        const fontImports = document.getElementById('admin-font-imports');
        if (fontImports && !fontImports.value) {
            fontImports.value = 'https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Libre+Baskerville:ital,wght@0,400..700;1,400..700&display=swap';
        }

        const titleEl = document.getElementById('admin-site-title');
        if (titleEl) titleEl.value = document.title || '';

        // Checkboxes default to sensible on-state
        ['admin-show-tags', 'admin-show-breadcrumb'].forEach(id => {
            const el = document.getElementById(id);
            if (el && el.indeterminate === false && !el.dataset.set) { el.checked = true; el.dataset.set = '1'; }
        });
        ['admin-hide-title', 'admin-hide-frontmatter'].forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.dataset.set) { el.checked = false; el.dataset.set = '1'; }
        });
    }

    function rgbToHex(rgb) {
        const m = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        if (!m) return rgb;
        return '#' + [m[1], m[2], m[3]].map(n => parseInt(n).toString(16).padStart(2, '0')).join('');
    }

    function readFields() {
        const get      = id => { const el = document.getElementById(id); return el ? el.value.trim() : ''; };
        const getColor = id => get(id + '-text') || get(id);
        const getCheck = id => { const el = document.getElementById(id); return el ? el.checked : false; };
        return {
            'color-bg':          getColor('admin-color-bg'),
            'color-text':        getColor('admin-color-text'),
            'color-accent':      getColor('admin-color-accent'),
            'color-sidebar-bg':  getColor('admin-color-sidebar-bg'),
            'color-navbar-bg':   getColor('admin-color-navbar-bg'),
            'color-border':      getColor('admin-color-border'),
            'color-muted':       getColor('admin-color-muted'),
            'color-code-bg':     getColor('admin-color-code-bg'),
            'color-code-text':   getColor('admin-color-code-text'),
            'color-hover-bg':    getColor('admin-color-hover-bg'),
            'color-tag-bg':      getColor('admin-color-tag-bg'),
            'color-tag-text':    getColor('admin-color-tag-text'),
            'color-tag-border':  getColor('admin-color-tag-border'),
            'radius-tag':        get('admin-radius-tag'),
            'font-body':         get('admin-font-body'),
            'font-heading':      get('admin-font-heading'),
            'font-ui':           get('admin-font-mono'),
            'font-size-base':    get('admin-font-size-base'),
            'line-height-body':  get('admin-line-height'),
            'sidebar-width':     get('admin-sidebar-width'),
            'content-max-width': get('admin-content-max-width'),
            'radius-sm':         get('admin-radius-sm'),
            'radius-md':         get('admin-radius-md'),
            'h1-size':           get('admin-h1-size'),
            'h1-weight':         get('admin-h1-weight'),
            'h2-size':           get('admin-h2-size'),
            'h2-weight':         get('admin-h2-weight'),
            'h3-size':           get('admin-h3-size'),
            'h3-weight':         get('admin-h3-weight'),
            'font-imports':      get('admin-font-imports'),
            'logo-url':          get('admin-logo-url'),
            'hide-title':        getCheck('admin-hide-title'),
            'hide-frontmatter':  getCheck('admin-hide-frontmatter'),
            'show-tags':         getCheck('admin-show-tags'),
            'show-breadcrumb':   getCheck('admin-show-breadcrumb'),
        };
    }

    // Writes ALL preview styles exclusively through a <style> tag.
    // NEVER touches root.style or any inline style on <html>.
    function writePreviewStyles() {
        const v = readFields();

        // Handle logo
        const brand = document.querySelector('.cask-navbar__brand');
        if (brand) {
            let img = brand.querySelector('.cask-logo-img');
            if (v['logo-url']) {
                if (!img) {
                    img = document.createElement('img');
                    img.className = 'cask-logo-img';
                    img.alt       = document.title;
                    brand.insertBefore(img, brand.firstChild);
                }
                img.src           = v['logo-url'];
                img.style.display = '';
            } else if (img) {
                img.style.display = 'none';
            }
        }

        // Build @import block
        let imports = '';
        if (v['font-imports']) {
            v['font-imports'].split('\n').forEach(line => {
                const u = line.trim();
                if (!u) return;
                imports += u.startsWith('@import')
                    ? u + (u.endsWith(';') ? '' : ';') + '\n'
                    : `@import url('${u}');\n`;
            });
        }

        // Helper: only emit a CSS declaration if the value is non-empty and looks valid
        const decl = (prop, val) => val && val.length > 1 ? `    ${prop}: ${val};\n` : '';

        const hideTitle      = v['hide-title']      ? '.cask-markdown h1:first-child { display: none !important; }\n' : '';
        const hideBreadcrumb = !v['show-breadcrumb'] ? '.cask-breadcrumb { display: none !important; }\n' : '';
        const hideTags       = !v['show-tags']       ? '.cask-tags { display: none !important; }\n' : '';

        let dynStyle = document.getElementById('cask-dynamic-styles');
        if (!dynStyle) {
            dynStyle = document.createElement('style');
            dynStyle.id = 'cask-dynamic-styles';
            document.head.appendChild(dynStyle);
        }

        dynStyle.textContent = `${imports}
:root {
${decl('--color-bg',          v['color-bg'])}${decl('--color-text',        v['color-text'])}${decl('--color-accent',      v['color-accent'])}${decl('--color-sidebar-bg',  v['color-sidebar-bg'])}${decl('--color-navbar-bg',   v['color-navbar-bg'])}${decl('--color-border',      v['color-border'])}${decl('--color-muted',       v['color-muted'])}${decl('--color-code-bg',     v['color-code-bg'])}${decl('--color-code-text',   v['color-code-text'])}${decl('--color-hover-bg',    v['color-hover-bg'])}${decl('--color-tag-bg',      v['color-tag-bg'])}${decl('--color-tag-text',    v['color-tag-text'])}${decl('--color-tag-border',  v['color-tag-border'])}${decl('--radius-tag',        v['radius-tag'])}${decl('--font-body',         v['font-body'])}${decl('--font-heading',      v['font-heading'])}${decl('--font-ui',           v['font-ui'])}${decl('--font-size-base',    v['font-size-base'])}${decl('--line-height-body',  v['line-height-body'])}${decl('--sidebar-width',     v['sidebar-width'])}${decl('--content-max-width', v['content-max-width'])}${decl('--radius-sm',         v['radius-sm'])}${decl('--radius-md',         v['radius-md'])}}
.cask-markdown h1 { ${v['h1-size'] ? `font-size: ${v['h1-size']};` : ''} ${v['h1-weight'] ? `font-weight: ${v['h1-weight']};` : ''} }
.cask-markdown h2 { ${v['h2-size'] ? `font-size: ${v['h2-size']};` : ''} ${v['h2-weight'] ? `font-weight: ${v['h2-weight']};` : ''} }
.cask-markdown h3 { ${v['h3-size'] ? `font-size: ${v['h3-size']};` : ''} ${v['h3-weight'] ? `font-weight: ${v['h3-weight']};` : ''} }
.cask-markdown h1,.cask-markdown h2,.cask-markdown h3,
.cask-markdown h4,.cask-markdown h5,.cask-markdown h6 { font-family: var(--font-heading); }
.cask-markdown code,.cask-markdown pre code { color: var(--color-code-text); }
${hideTitle}${hideBreadcrumb}${hideTags}`.trim();
    }

    function populateDataMappingFields() {
        const fm = window.CASK_FIELD_MAP || {};
        const setVal = (id, key) => {
            const el = document.getElementById(id);
            if (el && fm[key]) el.value = fm[key].join(', ');
        };
        setVal('admin-map-identifier', 'identifier');
        setVal('admin-map-title',      'title');
        setVal('admin-map-excerpt',    'excerpt');
        setVal('admin-map-tags',       'tags');
        setVal('admin-map-thumbnail',  'thumbnail');
        setVal('admin-map-parent',     'parent');
        setVal('admin-map-order',      'order');
        const depthEl = document.getElementById('admin-expand-depth');
        if (depthEl && window.CASK_CONFIG) depthEl.value = window.CASK_CONFIG.expandDepth || 1;
    }

    function applyDataMapping() {
        const get = id => {
            const el = document.getElementById(id);
            return el ? el.value.split(',').map(s => s.trim()).filter(Boolean) : [];
        };
        window.CASK_FIELD_MAP = {
            identifier: get('admin-map-identifier'),
            title:      get('admin-map-title'),
            excerpt:    get('admin-map-excerpt'),
            tags:       get('admin-map-tags'),
            thumbnail:  get('admin-map-thumbnail'),
            parent:     get('admin-map-parent'),
            order:      get('admin-map-order'),
        };
        const depthEl = document.getElementById('admin-expand-depth');
        if (depthEl && window.CASK_CONFIG) {
            window.CASK_CONFIG.expandDepth = parseInt(depthEl.value, 10) || 0;
        }
        loadNodes();
    }

    function exportCSS() {
        const v = readFields();
        let imports = '';
        if (v['font-imports']) {
            v['font-imports'].split('\n').forEach(line => {
                const u = line.trim();
                if (!u) return;
                imports += u.startsWith('@import')
                    ? u + (u.endsWith(';') ? '' : ';') + '\n'
                    : `@import url('${u}');\n`;
            });
        }

        const css =
`/* cask.ink — exported stylesheet */
/* Generated by the cask.ink admin panel. Upload this to your server to make changes permanent. */

${imports}
:root {
    --color-bg:          ${v['color-bg']};
    --color-text:        ${v['color-text']};
    --color-accent:      ${v['color-accent']};
    --color-sidebar-bg:  ${v['color-sidebar-bg']};
    --color-navbar-bg:   ${v['color-navbar-bg']};
    --color-border:      ${v['color-border']};
    --color-muted:       ${v['color-muted']};
    --color-code-bg:     ${v['color-code-bg']};
    --color-code-text:   ${v['color-code-text']};
    --color-hover-bg:    ${v['color-hover-bg']};
    --color-tag-bg:      ${v['color-tag-bg']};
    --color-tag-text:    ${v['color-tag-text']};
    --color-tag-border:  ${v['color-tag-border']};
    --radius-tag:        ${v['radius-tag']};

    --font-body:         ${v['font-body']};
    --font-heading:      ${v['font-heading']};
    --font-ui:           ${v['font-ui']};
    --font-size-base:    ${v['font-size-base']};
    --line-height-body:  ${v['line-height-body']};

    --sidebar-width:     ${v['sidebar-width']};
    --content-max-width: ${v['content-max-width']};
    --radius-sm:         ${v['radius-sm']};
    --radius-md:         ${v['radius-md']};
}

.cask-markdown h1 { font-size: ${v['h1-size']}; font-weight: ${v['h1-weight']}; }
.cask-markdown h2 { font-size: ${v['h2-size']}; font-weight: ${v['h2-weight']}; }
.cask-markdown h3 { font-size: ${v['h3-size']}; font-weight: ${v['h3-weight']}; }
.cask-markdown h1, .cask-markdown h2, .cask-markdown h3,
.cask-markdown h4, .cask-markdown h5, .cask-markdown h6 {
    font-family: var(--font-heading);
}
`;
        const a = Object.assign(document.createElement('a'), {
            href:     URL.createObjectURL(new Blob([css], { type: 'text/css' })),
            download: 'style.css',
        });
        a.click();
        URL.revokeObjectURL(a.href);
    }

    // ----------------------------------------------------------
    // 9. Utility
    // ----------------------------------------------------------
    function setLoading(state) {
        if (elMarkdown) elMarkdown.innerHTML = state ? '<span class="cask-loading">Loading content…</span>' : '';
    }

    function showEmpty(msg) {
        if (elMarkdown) elMarkdown.innerHTML = `<p class="cask-empty">${msg}</p>`;
    }

})();
