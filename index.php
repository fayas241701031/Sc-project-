<?php
require_once __DIR__ . '/includes/header.php';
$stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.popularity DESC LIMIT 8');
$trending = $stmt->fetchAll();
?>

<!-- Full-screen immersive hero -->
<section class="hero-100vh">
    <div class="hero-mesh-bg"></div>
    <div class="container hero-content">
        <div class="row align-items-center justify-content-center text-center">
            <div class="col-lg-8" id="heroTextGroup">
                <h1 class="display-3 fw-bold mb-4">Shop smart, feel <span style="color: var(--primary-color);">modern.</span></h1>
                <p class="lead mb-5" style="color: var(--text-muted); font-size: 1.25rem;">
                    Discover premium gadgets, fashion, and lifestyle essentials with an AI-powered experience.
                </p>
                
                <!-- Floating AI Search Bar -->
                <div class="ai-search-wrapper mx-auto mb-5" id="heroSearchBar">
                    <form action="<?= BASE_URL ?>/shop.php" method="get" class="position-relative">
                        <i class="fas fa-search position-absolute" style="top: 50%; left: 1.5rem; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" class="ai-search-input pe-5 ps-5" name="search" id="typewriterSearch" placeholder="Ask AI: Find the best noise-cancelling headphones..." autocomplete="off">
                        <button type="submit" class="btn btn-primary btn-apple position-absolute" style="top: 50%; right: 0.5rem; transform: translateY(-50%); padding: 0.6rem 1.5rem;">Search</button>
                    </form>
                </div>

                <!-- CTA Buttons -->
                <div class="d-flex gap-3 justify-content-center" id="heroCtas">
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary btn-apple">Explore Marketplace</a>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-glass btn-apple">Start Selling</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="container py-5 mb-5" id="categoriesSection">
    <div class="text-center mb-5 section-header">
        <h2 class="fw-bold">Featured Categories</h2>
        <p style="color: var(--text-muted);">Browse our curated collection of top categories.</p>
    </div>
    <div class="row g-4 categories-grid">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-3 category-item">
                <div class="card-apple p-4 text-center d-flex flex-column justify-content-center" style="min-height: 200px;">
                    <div class="mb-3">
                        <?php
                            $icon = 'fa-box';
                            if(stripos($category['name'], 'electronic') !== false) $icon = 'fa-laptop';
                            if(stripos($category['name'], 'fashion') !== false) $icon = 'fa-tshirt';
                            if(stripos($category['name'], 'home') !== false) $icon = 'fa-couch';
                            if(stripos($category['name'], 'sport') !== false) $icon = 'fa-dumbbell';
                        ?>
                        <i class="fas <?= $icon ?> fa-3x" style="color: var(--primary-color);"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= escape($category['name']) ?></h5>
                    <a href="<?= BASE_URL ?>/shop.php?category=<?= escape($category['id']) ?>" class="stretched-link text-decoration-none mt-auto" style="color: var(--text-muted); font-weight: 500;">View products <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Trending Products -->
<section class="container pb-5 mb-5" id="trendingSection">
    <div class="d-flex justify-content-between align-items-end mb-5 section-header">
        <div>
            <h2 class="fw-bold m-0">Trending Products</h2>
            <p style="color: var(--text-muted);" class="m-0 mt-2">Best sellers selected for you.</p>
        </div>
        <a href="<?= BASE_URL ?>/shop.php" class="btn btn-glass btn-apple">See all</a>
    </div>
    <div class="row g-4 products-grid">
        <?php foreach ($trending as $product): ?>
            <div class="col-md-3 col-sm-6 product-item">
                <div class="card-apple d-flex flex-column position-relative">
                    <img src="<?= escape($product['image']) ?>" class="card-img-top w-100 skeleton" alt="<?= escape($product['name']) ?>" loading="lazy" onload="this.classList.remove('skeleton')" onerror="this.classList.remove('skeleton')">
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <div class="mb-2">
                            <span class="badge badge-apple" style="font-size: 0.7rem;"><?= escape($product['category_name']) ?></span>
                        </div>
                        <h5 class="fw-bold mb-1 fs-6"><?= escape($product['name']) ?></h5>
                        <p class="small mb-3" style="color: var(--text-muted);"><?= substr(escape($product['description']), 0, 50) ?>...</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <strong class="fs-5"><?= formatPrice($product['price']) ?></strong>
                            <a href="<?= BASE_URL ?>/product.php?id=<?= escape($product['id']) ?>" class="btn btn-primary btn-apple btn-sm px-3 rounded-pill stretched-link">View</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Motion Animations Script -->
<script type="module">
    document.addEventListener("DOMContentLoaded", () => {
        // Hero Entrance
        animate("#heroTextGroup > *", { 
            opacity: [0, 1], 
            y: [30, 0] 
        }, { 
            delay: stagger(0.15), 
            duration: 0.8, 
            easing: [0.16, 1, 0.3, 1] 
        });

        // CTA Spring Hover
        const ctas = document.querySelectorAll('#heroCtas a');
        ctas.forEach(cta => {
            cta.addEventListener('mouseenter', () => {
                animate(cta, { scale: 1.05 }, { type: "spring", stiffness: 300, damping: 15 });
            });
            cta.addEventListener('mouseleave', () => {
                animate(cta, { scale: 1 }, { type: "spring", stiffness: 300, damping: 15 });
            });
        });

        // Parallax Effect on Hero Mesh
        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            const mesh = document.querySelector('.hero-mesh-bg');
            if (mesh && scrollY < window.innerHeight) {
                mesh.style.transform = `translateY(${scrollY * 0.4}px)`;
            }
        });

        // Typewriter Effect for Search
        const searchInput = document.getElementById('typewriterSearch');
        const placeholders = [
            "Ask AI: Find the best noise-cancelling headphones...",
            "Ask AI: Show me premium minimalist lamps...",
            "Ask AI: What are the top trending sneakers?",
            "Ask AI: I need a comfortable yoga mat..."
        ];
        let pIdx = 0;
        let charIdx = 0;
        let isDeleting = false;
        
        function typeWriter() {
            const currentStr = placeholders[pIdx];
            if (isDeleting) {
                searchInput.setAttribute('placeholder', currentStr.substring(0, charIdx - 1));
                charIdx--;
            } else {
                searchInput.setAttribute('placeholder', currentStr.substring(0, charIdx + 1));
                charIdx++;
            }

            let typeSpeed = isDeleting ? 30 : 70;
            if (!isDeleting && charIdx === currentStr.length) {
                typeSpeed = 2000; // Pause at end
                isDeleting = true;
            } else if (isDeleting && charIdx === 0) {
                isDeleting = false;
                pIdx = (pIdx + 1) % placeholders.length;
                typeSpeed = 500; // Pause before new word
            }
            setTimeout(typeWriter, typeSpeed);
        }
        setTimeout(typeWriter, 1500);

        // Scroll Reveals via InView
        inView("#categoriesSection .section-header", (info) => {
            animate(info.target, { opacity: [0, 1], y: [40, 0] }, { duration: 0.8, easing: [0.16, 1, 0.3, 1] });
        });
        
        inView(".category-item", (info) => {
            animate(info.target, { opacity: [0, 1], scale: [0.95, 1] }, { delay: stagger(0.1), duration: 0.6, easing: [0.16, 1, 0.3, 1] });
        });

        inView("#trendingSection .section-header", (info) => {
            animate(info.target, { opacity: [0, 1], y: [40, 0] }, { duration: 0.8, easing: [0.16, 1, 0.3, 1] });
        });

        const productItems = document.querySelectorAll(".product-item");
        inView(productItems[0], () => {
            animate(".product-item", { opacity: [0, 1], y: [30, 0] }, { delay: stagger(0.1), duration: 0.6, easing: [0.16, 1, 0.3, 1] });
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
