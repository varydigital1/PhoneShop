<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PhoneShop') }} – Products</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body { font-family: 'Instrument Sans', sans-serif; background: #f9fafb; color: #111827; }
    </style>
</head>
<body class="min-h-screen bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="text-xl font-semibold text-orange-600 hover:text-orange-700">
                📱 PhoneShop
            </a>
            <nav class="flex gap-4 text-sm font-medium text-gray-600">
                <a href="/" class="hover:text-orange-600">Home</a>
                <a href="/products" class="text-orange-600 font-semibold">Products</a>
                <a href="/categories" class="hover:text-orange-600">Categories</a>
            </nav>
        </div>
    </header>

    <!-- Main -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Products</h1>
            <span id="result-count" class="text-sm text-gray-500"></span>
        </div>

        <!-- Search Bar -->
        <div class="mb-6 flex gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </span>
                <input
                    id="search-input"
                    type="text"
                    placeholder="Search by product name or category…"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                />
            </div>
            <button
                id="search-btn"
                class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg shadow-sm transition"
            >
                Search
            </button>
            <button
                id="clear-btn"
                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg shadow-sm transition hidden"
            >
                Clear
            </button>
        </div>

        <!-- Per-page selector -->
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-600">
            <label for="per-page">Show</label>
            <select id="per-page" class="border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <span>per page</span>
        </div>

        <!-- Loading indicator -->
        <div id="loading" class="hidden flex justify-center items-center py-16">
            <svg class="animate-spin h-8 w-8 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        </div>

        <!-- Error message -->
        <div id="error-msg" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4"></div>

        <!-- Products Table -->
        <div id="table-wrapper" class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Image</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Product Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Qty</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Sale Price</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Currency</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody id="product-tbody" class="divide-y divide-gray-100 bg-white">
                    <!-- Rows injected by JS -->
                </tbody>
            </table>
        </div>

        <!-- Empty state -->
        <div id="empty-state" class="hidden text-center py-16 text-gray-400">
            <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-lg font-medium">No products found</p>
            <p class="text-sm mt-1">Try a different search term.</p>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="mt-6 flex items-center justify-between hidden">
            <p id="pagination-info" class="text-sm text-gray-500"></p>
            <div id="pagination-controls" class="flex items-center gap-1"></div>
        </div>
    </main>

    <script>
    (function () {
        'use strict';

        const searchInput  = document.getElementById('search-input');
        const searchBtn    = document.getElementById('search-btn');
        const clearBtn     = document.getElementById('clear-btn');
        const perPageSel   = document.getElementById('per-page');
        const loading      = document.getElementById('loading');
        const errorMsg     = document.getElementById('error-msg');
        const tableWrapper = document.getElementById('table-wrapper');
        const tbody        = document.getElementById('product-tbody');
        const emptyState   = document.getElementById('empty-state');
        const pagination   = document.getElementById('pagination');
        const paginationInfo = document.getElementById('pagination-info');
        const paginationControls = document.getElementById('pagination-controls');
        const resultCount  = document.getElementById('result-count');

        let currentPage   = 1;
        let currentSearch = '';
        let debounceTimer = null;

        // ── Fetch products from the API ────────────────────────────────────────
        function fetchProducts(page, search, perPage) {
            currentPage   = page;
            currentSearch = search;

            const params = new URLSearchParams({
                page,
                search: search || '',
                per_page: perPage,
            });

            showLoading(true);
            hideError();

            fetch(`/api/products?${params}`)
                .then(function (res) {
                    if (!res.ok) throw new Error('Server error: ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    renderTable(data.list || []);
                    renderPagination(data.pagination || null);
                })
                .catch(function (err) {
                    showError(err.message || 'Failed to load products. Please try again.');
                })
                .finally(function () {
                    showLoading(false);
                });
        }

        // ── Render table rows ─────────────────────────────────────────────────
        function renderTable(products) {
            tbody.innerHTML = '';

            if (!products.length) {
                tableWrapper.classList.add('hidden');
                emptyState.classList.remove('hidden');
                resultCount.textContent = 'No results';
                return;
            }

            tableWrapper.classList.remove('hidden');
            emptyState.classList.add('hidden');

            products.forEach(function (p, i) {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition';

                const rowNum = ((currentPage - 1) * parseInt(perPageSel.value)) + i + 1;
                const statusClass = p.status === 'active'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700';

                const imgHtml = p.image
                    ? `<img src="${escHtml(p.image)}" alt="${escHtml(p.product_name)}" class="h-10 w-10 rounded-md object-cover border border-gray-200">`
                    : `<span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-gray-100 text-gray-400 text-xs">N/A</span>`;

                const categoryName = p.category
                    ? escHtml(p.category.cate_name || p.category.item_name || '')
                    : '—';

                row.innerHTML = `
                    <td class="px-4 py-3 text-gray-500">${rowNum}</td>
                    <td class="px-4 py-3">${imgHtml}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">${escHtml(p.product_name)}</td>
                    <td class="px-4 py-3 text-gray-600">${categoryName}</td>
                    <td class="px-4 py-3 text-gray-700">${escHtml(String(p.quantity))}</td>
                    <td class="px-4 py-3 text-gray-700">${escHtml(String(p.sale_price))}</td>
                    <td class="px-4 py-3 text-gray-700">${escHtml(p.currency)}</td>
                    <td class="px-4 py-3">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold ${statusClass}">${escHtml(p.status)}</span>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // ── Render pagination controls ────────────────────────────────────────
        function renderPagination(meta) {
            if (!meta || meta.last_page <= 0) {
                pagination.classList.add('hidden');
                resultCount.textContent = '';
                return;
            }

            pagination.classList.remove('hidden');

            const { current_page, last_page, total, from, to } = meta;

            paginationInfo.textContent = `Showing ${from ?? 0}–${to ?? 0} of ${total} result${total !== 1 ? 's' : ''}`;
            resultCount.textContent = `${total} result${total !== 1 ? 's' : ''}`;

            paginationControls.innerHTML = '';

            // Previous button
            const prevBtn = makePageBtn('← Prev', current_page - 1, current_page === 1);
            paginationControls.appendChild(prevBtn);

            // Page number buttons (show at most 5 around current page)
            const range = getPageRange(current_page, last_page, 5);
            range.forEach(function (p) {
                if (p === '…') {
                    const dots = document.createElement('span');
                    dots.textContent = '…';
                    dots.className = 'px-2 py-1 text-gray-400 text-sm';
                    paginationControls.appendChild(dots);
                } else {
                    const btn = makePageBtn(String(p), p, false, p === current_page);
                    paginationControls.appendChild(btn);
                }
            });

            // Next button
            const nextBtn = makePageBtn('Next →', current_page + 1, current_page === last_page);
            paginationControls.appendChild(nextBtn);
        }

        function makePageBtn(label, page, disabled, active) {
            const btn = document.createElement('button');
            btn.textContent = label;
            btn.disabled = disabled;

            if (active) {
                btn.className = 'px-3 py-1 rounded-md text-sm font-semibold bg-orange-600 text-white cursor-default';
            } else if (disabled) {
                btn.className = 'px-3 py-1 rounded-md text-sm text-gray-300 cursor-not-allowed';
            } else {
                btn.className = 'px-3 py-1 rounded-md text-sm text-gray-700 hover:bg-gray-100 transition';
                btn.addEventListener('click', function () {
                    fetchProducts(page, currentSearch, parseInt(perPageSel.value));
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }
            return btn;
        }

        // Generate a page range with ellipsis, e.g. [1, '…', 4, 5, 6, '…', 20]
        function getPageRange(current, last, delta) {
            const range = [];
            const left  = Math.max(1, current - Math.floor(delta / 2));
            const right = Math.min(last, Math.max(left + delta - 1, current + Math.floor(delta / 2)));

            if (left > 1) {
                range.push(1);
                if (left > 2) range.push('…');
            }
            for (let p = left; p <= right; p++) range.push(p);
            if (right < last) {
                if (right < last - 1) range.push('…');
                range.push(last);
            }
            return range;
        }

        // ── Helpers ───────────────────────────────────────────────────────────
        function showLoading(show) {
            loading.classList.toggle('hidden', !show);
            if (show) {
                tableWrapper.classList.add('hidden');
                emptyState.classList.add('hidden');
                pagination.classList.add('hidden');
            }
        }

        function showError(msg) {
            errorMsg.textContent = msg;
            errorMsg.classList.remove('hidden');
        }

        function hideError() {
            errorMsg.classList.add('hidden');
        }

        function escHtml(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str == null ? '' : str));
            return div.innerHTML;
        }

        // ── Event listeners ───────────────────────────────────────────────────
        function triggerSearch() {
            const q = searchInput.value.trim();
            clearBtn.classList.toggle('hidden', !q);
            fetchProducts(1, q, parseInt(perPageSel.value));
        }

        searchBtn.addEventListener('click', triggerSearch);

        searchInput.addEventListener('keyup', function (e) {
            if (e.key === 'Enter') {
                triggerSearch();
            } else {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(triggerSearch, 400);
            }
        });

        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            clearBtn.classList.add('hidden');
            fetchProducts(1, '', parseInt(perPageSel.value));
        });

        perPageSel.addEventListener('change', function () {
            fetchProducts(1, currentSearch, parseInt(perPageSel.value));
        });

        // ── Initial load ──────────────────────────────────────────────────────
        fetchProducts(1, '', parseInt(perPageSel.value));
    })();
    </script>
</body>
</html>
