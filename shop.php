<?php
require_once __DIR__ . '/includes/header.php';
// Initial page state doesn't need to fetch products, JS will do it perfectly
?>
<style>
    /* Styling specific to the shop page */
    .filter-sidebar {
        position: sticky;
        top: 100px;
        z-index: 10;
    }
    .custom-checkbox .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .pagination-apple .page-link {
        border: none;
        color: var(--text-main);
        background: transparent;
        font-weight: 500;
        margin: 0 4px;
        transition: all 0.3s;
    }
    .pagination-apple .page-link:hover {
        background: rgba(120, 120, 128, 0.1);
    }
    .pagination-apple .page-item.active .page-link {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3);
    }
    
    /* Search suggestions dropdown */
    .ai-suggestions {
        border: 1px solid var(--border-color);
        box-shadow: var(--card-hover-shadow);
        background: var(--surface-solid);
    }
    .suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;
    }
    .suggestion-item:hover {
        background: rgba(120, 120, 128, 0.05);
    }
    
    .rating-stars {
        color: #ffcc00;
        font-size: 0.9em;
    }
</style>

<section class="container py-5 mt-4">
    <div class="row mb-5 align-items-center" id="shopHeader">
        <div class="col-md-12 text-center text-md-start">
            <h1 class="display-4 fw-bold mb-3">Marketplace</h1>
            <p style="color: var(--text-muted); font-size: 1.15rem; max-width: 600px;">Discover, search, and find exactly what you need with our AI-powered directory.</p>
        </div>
    </div>
    
    <div class="row g-5">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card-apple p-4 filter-sidebar" id="shopSidebar">
                <h5 class="fw-bold mb-4">Filters</h5>
                
                <form id="advancedFilterForm" onsubmit="event.preventDefault();">
                     <!-- Search with AI suggestions -->
                     <div class="position-relative mb-4">
                         <label class="form-label small fw-bold text-muted">Search AI</label>
                         <input type="text" class="form-control" id="aiSearchInput" placeholder="Ask AI..." autocomplete="off">
                         <div id="aiSuggestions" class="ai-suggestions position-absolute w-100 rounded-3 mt-1 d-none" style="z-index: 100; max-height: 250px; overflow-y: auto;">
                             <!-- Suggestions populate here -->
                         </div>
                     </div>
                     
                     <!-- Categories -->
                     <div class="mb-4">
                         <label class="form-label small fw-bold text-muted mb-3">Categories</label>
                         <div id="categoriesList">
                             <?php foreach ($categories as $category): ?>
                             <div class="form-check custom-checkbox mb-2">
                                 <input class="form-check-input category-check" type="checkbox" value="<?= escape($category['id']) ?>" id="cat_<?= escape($category['id']) ?>">
                                 <label class="form-check-label" style="color: var(--text-main);" for="cat_<?= escape($category['id']) ?>">
                                     <?= escape($category['name']) ?>
                                 </label>
                             </div>
                             <?php endforeach; ?>
                         </div>
                     </div>
                     
                     <!-- Price Range Slider -->
                     <div class="mb-4">
                         <label class="form-label small fw-bold text-muted">Max Price: ₹<span id="priceVal">20000</span></label>
                         <input type="range" class="form-range" id="priceRange" min="1000" max="20000" step="500" value="20000">
                     </div>

                     <!-- Minimum Rating -->
                     <div class="mb-4">
                         <label class="form-label small fw-bold text-muted mb-3">Minimum Rating</label>
                         <select class="form-select" id="ratingSelect">
                             <option value="0">All Ratings</option>
                             <option value="4">4 Stars & Up</option>
                             <option value="3">3 Stars & Up</option>
                         </select>
                     </div>
                     
                     <!-- Sort -->
                     <div class="mb-4">
                         <label class="form-label small fw-bold text-muted mb-3">Sort By</label>
                         <select class="form-select" id="sortSelect">
                             <option value="">Recommended</option>
                             <option value="price_asc">Price: Low to High</option>
                             <option value="price_desc">Price: High to Low</option>
                             <option value="popularity">Most Popular</option>
                         </select>
                     </div>

                     <button type="button" class="btn btn-glass btn-apple w-100 mt-2" id="resetFiltersBtn">Reset Filters</button>
                </form>
            </div>
        </div>
        
        <!-- Product Grid & Pagination -->
        <div class="col-lg-9">
            <div class="row g-4" id="productsContainer">
                <!-- Skeletons Loader -->
                <?php for($i=0; $i<6; $i++): ?>
                <div class="col-md-4 product-skeleton">
                    <div class="card-apple p-3">
                        <div class="skeleton mb-3" style="height: 200px; width: 100%; border-radius: 12px;"></div>
                        <div class="skeleton mb-2" style="height: 20px; width: 80%;"></div>
                        <div class="skeleton mb-3" style="height: 20px; width: 60%;"></div>
                        <div class="skeleton" style="height: 40px; width: 100%; border-radius: 99px;"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            
            <div id="noResults" class="text-center py-5 d-none">
                <i class="fas fa-search fa-3x mb-3" style="color: var(--text-muted); opacity: 0.5;"></i>
                <h4 class="fw-bold">No exact matches</h4>
                <p style="color: var(--text-muted);">Try adjusting your filters or asking the AI differently.</p>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5 d-none" id="paginationWrapper">
                <nav aria-label="Page navigation">
                  <ul class="pagination pagination-apple gap-2" id="paginationContainer">
                    <!-- Pagination generated by JS -->
                  </ul>
                </nav>
            </div>
        </div>
    </div>
</section>

<script type="module">
    let currentPage = 1;

    const productsContainer = document.getElementById('productsContainer');
    const paginationContainer = document.getElementById('paginationContainer');
    const paginationWrapper = document.getElementById('paginationWrapper');
    const noResults = document.getElementById('noResults');
    const priceVal = document.getElementById('priceVal');
    
    // Inputs
    const aiSearchInput = document.getElementById('aiSearchInput');
    const aiSuggestions = document.getElementById('aiSuggestions');
    const priceRange = document.getElementById('priceRange');
    const sortSelect = document.getElementById('sortSelect');
    const ratingSelect = document.getElementById('ratingSelect');
    const checkboxes = document.querySelectorAll('.category-check');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');

    // UI Animations
    window.animate("#shopHeader > *", { opacity: [0, 1], y: [30, 0] }, { delay: window.stagger(0.1), duration: 0.8, easing: [0.16, 1, 0.3, 1] });
    window.animate("#shopSidebar", { opacity: [0, 1], x: [-30, 0] }, { delay: 0.3, duration: 0.8, easing: [0.16, 1, 0.3, 1] });

    function formatPrice(price) {
        return '₹' + parseFloat(price).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) stars += '<i class="fas fa-star"></i>';
            else if (i - 0.5 <= rating) stars += '<i class="fas fa-star-half-alt"></i>';
            else stars += '<i class="far fa-star"></i>';
        }
        return stars;
    }

    async function fetchProducts(page = 1) {
        currentPage = page;
        const query = aiSearchInput.value;
        const maxPrice = priceRange.value;
        const sort = sortSelect.value;
        const minRating = parseFloat(ratingSelect.value);
        
        const selectedCats = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value)
            .join(',');

        // Show skeletons
        productsContainer.innerHTML = '';
        for(let i=0; i<6; i++) {
            productsContainer.innerHTML += `
            <div class="col-md-4 product-item">
                <div class="card-apple p-3">
                    <div class="skeleton mb-3" style="height: 200px; width: 100%; border-radius: 12px;"></div>
                    <div class="skeleton mb-2" style="height: 20px; width: 80%;"></div>
                    <div class="skeleton mb-3" style="height: 20px; width: 60%;"></div>
                    <div class="skeleton" style="height: 40px; width: 100%; border-radius: 99px;"></div>
                </div>
            </div>`;
        }
        noResults.classList.add('d-none');
        paginationWrapper.classList.add('d-none');

        try {
            const url = `search_api.php?q=${encodeURIComponent(query)}&category=${selectedCats}&max_price=${maxPrice}&sort=${sort}&page=${page}`;
            const response = await fetch(url);
            const data = await response.json();
            
            let filteredProducts = data.products;
            
            // Client side filter for mocked ratings
            if (minRating > 0) {
                filteredProducts = filteredProducts.filter(p => p.rating >= minRating);
            }

            renderProducts(filteredProducts);
            renderPagination(data.pagination);
            
        } catch (error) {
            console.error('Error fetching products:', error);
            productsContainer.innerHTML = '<div class="alert alert-danger w-100">Failed to load marketplace data.</div>';
        }
    }

    function renderProducts(products) {
        productsContainer.innerHTML = '';
        
        if (products.length === 0) {
            noResults.classList.remove('d-none');
            return;
        }

        products.forEach(product => {
            const productHTML = `
                <div class="col-md-4 product-item">
                    <div class="card-apple d-flex flex-column position-relative">
                        <img src="${product.image}" class="card-img-top w-100 skeleton" alt="${product.name}" loading="lazy" onload="this.classList.remove('skeleton')" onerror="this.classList.remove('skeleton')">
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-apple" style="font-size: 0.7rem;">${product.category_name}</span>
                                    ${product.is_verified_seller == 1 ? '<i class="fas fa-check-circle text-primary ms-1" style="font-size: 0.8rem;" title="Verified Brand"></i>' : ''}
                                </div>
                                <div class="rating-stars">${generateStars(product.rating)} <span class="text-muted small ms-1">(${product.reviews_count})</span></div>
                            </div>
                            <h5 class="fw-bold mb-1 fs-6">${product.name}</h5>
                            <p class="small mb-3" style="color: var(--text-muted);">${product.description.substring(0, 50)}...</p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <strong class="fs-5">${formatPrice(product.price)}</strong>
                                <a href="product.php?id=${product.id}" class="btn btn-primary btn-apple btn-sm px-3 rounded-pill stretched-link">Buy</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            productsContainer.insertAdjacentHTML('beforeend', productHTML);
        });

        // Intro Animation
        window.animate(".product-item", { opacity: [0, 1], y: [20, 0], scale: [0.95, 1] }, { delay: window.stagger(0.08), duration: 0.5, easing: [0.16, 1, 0.3, 1] });
    }

    function renderPagination(pagination) {
        if (pagination.total_pages <= 1) {
            paginationWrapper.classList.add('d-none');
            return;
        }
        paginationWrapper.classList.remove('d-none');
        paginationContainer.innerHTML = '';

        // Prev Button
        const prevDisabled = pagination.current_page === 1 ? 'disabled' : '';
        paginationContainer.innerHTML += `<li class="page-item ${prevDisabled}"><a class="page-link rounded-circle px-3 py-2" href="#" data-page="${pagination.current_page - 1}">Prev</a></li>`;

        // Pages
        for (let i = 1; i <= pagination.total_pages; i++) {
            const activeClass = i === pagination.current_page ? 'active' : '';
            paginationContainer.innerHTML += `<li class="page-item ${activeClass}"><a class="page-link rounded-circle px-3 py-2" href="#" data-page="${i}">${i}</a></li>`;
        }

        // Next Button
        const nextDisabled = pagination.current_page === pagination.total_pages ? 'disabled' : '';
        paginationContainer.innerHTML += `<li class="page-item ${nextDisabled}"><a class="page-link rounded-circle px-3 py-2" href="#" data-page="${pagination.current_page + 1}">Next</a></li>`;

        // Click Logic
        paginationContainer.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(link.getAttribute('data-page'));
                if (!isNaN(page) && page > 0 && page <= pagination.total_pages) {
                    fetchProducts(page);
                    window.scrollTo({ top: document.getElementById('shopHeader').offsetTop - 100, behavior: 'smooth' });
                }
            });
        });
    }

    // AI Suggestions Logic
    let debounceTimer;
    aiSearchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const query = e.target.value;
        
        if (query.length < 2) {
            aiSuggestions.classList.add('d-none');
            fetchProducts(1);
            return;
        }

        debounceTimer = setTimeout(async () => {
            fetchProducts(1); // Real update of grid

            // Show suggestions dropdown
            try {
                const response = await fetch(`search_api.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                if (data.products && data.products.length > 0) {
                    aiSuggestions.innerHTML = '';
                    data.products.slice(0, 4).forEach(p => {
                        aiSuggestions.innerHTML += `<div class="suggestion-item d-flex align-items-center gap-3" data-name="${p.name}">
                            <i class="fas fa-sparkles" style="color: var(--primary-color);"></i>
                            <div>
                                <div class="fw-bold" style="color: var(--text-main); font-size: 0.9rem;">${p.name}</div>
                                <div class="small" style="color: var(--text-muted);">${p.category_name} &middot; ${formatPrice(p.price)}</div>
                            </div>
                        </div>`;
                    });
                    aiSuggestions.classList.remove('d-none');

                    document.querySelectorAll('.suggestion-item').forEach(item => {
                        item.addEventListener('click', function() {
                            aiSearchInput.value = this.getAttribute('data-name');
                            aiSuggestions.classList.add('d-none');
                            fetchProducts(1);
                        });
                    });
                } else {
                    aiSuggestions.classList.add('d-none');
                }
            } catch (err) {
                console.error(err);
            }
        }, 300);
    });

    // Close suggestions on outside click
    document.addEventListener('click', (e) => {
        if (!aiSearchInput.contains(e.target) && !aiSuggestions.contains(e.target)) {
            aiSuggestions.classList.add('d-none');
        }
    });

    // Event Listeners for Filters
    priceRange.addEventListener('input', (e) => {
        priceVal.textContent = e.target.value;
    });
    
    priceRange.addEventListener('change', () => fetchProducts(1));
    sortSelect.addEventListener('change', () => fetchProducts(1));
    ratingSelect.addEventListener('change', () => fetchProducts(1));
    checkboxes.forEach(cb => cb.addEventListener('change', () => fetchProducts(1)));

    resetFiltersBtn.addEventListener('click', () => {
        aiSearchInput.value = '';
        priceRange.value = 20000;
        priceVal.textContent = '20000';
        sortSelect.value = '';
        ratingSelect.value = '0';
        checkboxes.forEach(cb => cb.checked = false);
        fetchProducts(1);
    });

    // Initial Load
    fetchProducts(1);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
