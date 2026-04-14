<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_GET['id'])) {
    header('Location: ' . BASE_URL . '/shop.php');
    exit;
}
$id = (int)$_GET['id'];

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!isLoggedIn()) {
        setFlash('Please login to submit a review.', 'danger');
        header('Location: ' . BASE_URL . '/product.php?id=' . $id);
        exit;
    }
    $rating = max(1, min(5, (int)$_POST['rating']));
    $comment = trim($_POST['comment']);
    $userId = $_SESSION['user']['id'];
    
    // Anti-spam
    $stmt = $pdo->prepare('SELECT id FROM reviews WHERE product_id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    if ($stmt->fetch()) {
        setFlash('You have already reviewed this product.', 'warning');
    } else {
        // Verify purchase
        $stmt = $pdo->prepare("SELECT 1 FROM orders o JOIN order_items oi ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status IN ('paid', 'shipped', 'delivered') LIMIT 1");
        $stmt->execute([$userId, $id]);
        $isVerified = $stmt->fetch() ? 1 : 0;
        
        $stmt = $pdo->prepare('INSERT INTO reviews (product_id, user_id, rating, comment, is_verified_purchase) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$id, $userId, $rating, $comment, $isVerified]);
        setFlash('Review submitted successfully!', 'success');
    }
    header('Location: ' . BASE_URL . '/product.php?id=' . $id . '#reviewsSection');
    exit;
}

// Fetch Product & Seller Data
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name, u.nama AS seller_name, u.is_verified_seller FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.seller_id = u.id WHERE p.id = :id');
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();
if (!$product) {
    setFlash('Product not found.', 'danger');
    header('Location: ' . BASE_URL . '/shop.php');
    exit;
}

// Related Products
$stmt = $pdo->prepare('SELECT p.* FROM products p WHERE category_id = :category_id AND p.id != :id ORDER BY popularity DESC LIMIT 4');
$stmt->execute([':category_id' => $product['category_id'], ':id' => $product['id']]);
$related = $stmt->fetchAll();

// Fetch Real Reviews
$stmt = $pdo->prepare('SELECT r.*, u.nama as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC');
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$totalRating = 0;
foreach($reviews as $r) $totalRating += $r['rating'];
$avgRating = count($reviews) > 0 ? $totalRating / count($reviews) : 0;

$displayRating = count($reviews) > 0 ? $avgRating : 0;
$reviewsCount = count($reviews);

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .seller-card { border-radius: var(--border-radius-lg); background: var(--surface-solid); padding: 1.5rem; border: 1px solid var(--border-color); }
    .review-card { border-radius: var(--border-radius-md); background: var(--surface); backdrop-filter: blur(10px); padding: 1.5rem; margin-bottom: 1rem; border: var(--glass-border); position: relative; }
    .nav-pills .nav-link.active { background-color: var(--primary-color) !important; color: #fff !important; }
    .nav-pills .nav-link { color: var(--text-muted); border-radius: 99px; padding: 0.5rem 1.5rem; transition: all 0.3s; }
    .hero-product-image { max-height: 500px; object-fit: contain; cursor: zoom-in; transition: transform 0.3s; }
    
    .lightbox { display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.8); backdrop-filter: blur(15px); justify-content: center; align-items: center; cursor: zoom-out; opacity: 0; transition: opacity 0.3s;}
    .lightbox.active { display: flex; opacity: 1; }
    .lightbox img { max-width: 90%; max-height: 90%; object-fit: contain; transform: scale(0.9); transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .lightbox.active img { transform: scale(1); }
    
    .star-rating-input { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
    .star-rating-input input { display: none; }
    .star-rating-input label { text-align: center; cursor: pointer; color: var(--border-color); font-size: 2rem; margin-right: 5px; transition: color 0.2s; }
    .star-rating-input input:checked ~ label { color: #ffcc00; }
    .star-rating-input label:hover, .star-rating-input label:hover ~ label { color: #ffcc00; }
</style>

<section class="container py-5 mt-4" id="productHeader">
    <div class="row g-5">
        <div class="col-lg-7" id="productImageCol">
            <div class="card-apple p-4 d-flex justify-content-center align-items-center bg-transparent position-relative" style="box-shadow: none; border: none; height: 100%;">
                <img src="<?= escape($product['image']) ?>" class="hero-product-image w-100 skeleton" id="mainProdImage" alt="<?= escape($product['name']) ?>" onload="this.classList.remove('skeleton')" onerror="this.classList.remove('skeleton')">
            </div>
        </div>
        
        <div class="col-lg-5" id="productDetailsCol">
            <div class="mb-4">
                <span class="badge badge-apple mb-3"><?= escape($product['category_name']) ?></span>
                <h1 class="display-5 fw-bold mb-3"><?= escape($product['name']) ?></h1>
                
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div style="color: #ffcc00;">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <?php if($i <= $displayRating) echo '<i class="fas fa-star"></i>'; else if($i - 0.5 <= $displayRating) echo '<i class="fas fa-star-half-alt"></i>'; else echo '<i class="far fa-star"></i>'; ?>
                        <?php endfor; ?>
                    </div>
                    <?php if($reviewsCount > 0): ?>
                        <span class="fw-bold" style="color: var(--text-main);"><?= number_format($displayRating, 1) ?></span>
                        <a href="#reviewsSection" class="small text-decoration-none" style="color: var(--primary-color);">SEE ALL <?= $reviewsCount ?> REVIEWS</a>
                    <?php else: ?>
                        <span class="small" style="color: var(--text-muted);">No reviews yet</span>
                    <?php endif; ?>
                </div>

                <p class="lead" style="color: var(--text-muted); font-size: 1.1rem; line-height: 1.6;">
                    <?= escape($product['description']) ?>
                </p>
            </div>
            
            <hr style="border-color: var(--border-color); opacity: 1;">
            
            <div class="d-flex justify-content-between align-items-end my-4">
                <div>
                    <h2 class="display-6 fw-bold m-0" style="color: var(--text-main); letter-spacing: -1px;"><?= formatPrice($product['price']) ?></h2>
                </div>
                <div>
                    <?php if($product['stock'] > 0): ?>
                        <span class="badge" style="background: rgba(52, 199, 89, 0.1); color: #34c759; padding: 0.5em 1em; border-radius: 99px;">
                            <?= escape($product['stock']) ?> in stock
                        </span>
                    <?php else: ?>
                        <span class="badge" style="background: rgba(255, 59, 48, 0.1); color: #ff3b30; padding: 0.5em 1em; border-radius: 99px;">
                            Out of stock
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex gap-2">
                <form action="<?= BASE_URL ?>/cart.php" method="post" id="addToCartForm" class="flex-grow-1">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= escape($product['id']) ?>">
                    
                    <div class="d-flex gap-3 align-items-center">
                        <div class="bg-transparent border rounded-pill d-flex align-items-center px-3" style="border-color: var(--border-color) !important;">
                            <button type="button" class="btn btn-link px-2 py-3 shadow-0 text-decoration-none" style="color: var(--text-main);" onclick="this.parentNode.querySelector('input[type=number]').stepDown()">-</button>
                            <input type="number" class="form-control text-center bg-transparent border-0 shadow-none fw-bold" name="quantity" value="1" min="1" max="<?= escape($product['stock']) ?>" style="width: 50px; color: var(--text-main); appearance: none;">
                            <button type="button" class="btn btn-link px-2 py-3 shadow-0 text-decoration-none" style="color: var(--text-main);" onclick="this.parentNode.querySelector('input[type=number]').stepUp()">+</button>
                        </div>
                        
                        <button type="submit" id="addToCartBtn" class="btn btn-primary btn-apple flex-grow-1 py-3 fs-5" <?= $product['stock'] == 0 ? 'disabled' : '' ?>>
                            Add to Bag
                        </button>
                    </div>
                </form>

                <!-- Add to wishlist -->
                <form action="<?= BASE_URL ?>/user/wishlist.php" method="post">
                    <input type="hidden" name="action" value="add_wishlist">
                    <input type="hidden" name="product_id" value="<?= escape($product['id']) ?>">
                    <button type="submit" class="btn btn-glass" style="border-radius: 50%; width: 56px; height: 56px; padding: 0;"><i class="fas fa-heart" style="color: var(--text-muted); font-size: 1.5rem; line-height: 56px;"></i></button>
                </form>
            </div>
            
            <!-- Seller Card -->
            <div class="seller-card d-flex align-items-center gap-3 mt-5">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color), #ec4899); color: white; font-weight: bold; font-size: 1.2rem;">
                    <?= strtoupper(substr($product['seller_name'] ?? 'S', 0, 1)) ?>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold m-0"><?= escape($product['seller_name'] ?? 'Bazaar Seller') ?></h6>
                        <?php if ($product['is_verified_seller'] == 1): ?>
                            <i class="fas fa-check-circle text-primary" title="Verified Brand"></i>
                        <?php endif; ?>
                    </div>
                    <div class="small" style="color: var(--text-muted);">
                        <i class="fas fa-box" style="color: #ffcc00;"></i> Official Vendor
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Infomation & Reviews -->
<section class="container py-5 border-top" id="reviewsSection" style="border-color: var(--border-color) !important;">
    <ul class="nav text-center w-100 justify-content-center gap-3 mb-5" id="pills-tab" role="tablist">
        <li class="nav-item d-inline-block">
            <button class="btn btn-glass btn-apple active shadow-sm" id="pills-desc-tab" data-mdb-toggle="pill" data-mdb-target="#pills-desc" type="button" role="tab" aria-selected="true">Features Overview</button>
        </li>
        <li class="nav-item d-inline-block">
            <button class="btn btn-glass btn-apple shadow-sm" id="pills-reviews-tab" data-mdb-toggle="pill" data-mdb-target="#pills-reviews" type="button" role="tab" aria-selected="false">Customer Reviews (<?= $reviewsCount ?>)</button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-desc" role="tabpanel" aria-labelledby="pills-desc-tab">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h3 class="fw-bold mb-4">Precision Engineering</h3>
                    <p class="lead" style="color: var(--text-muted);">Every detail of this product has been carefully considered to provide the ultimate customer experience. Minimalist design meets uncompromising power.</p>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pills-reviews" role="tabpanel" aria-labelledby="pills-reviews-tab">
            <div class="row g-5">
                <!-- Review List -->
                <div class="col-md-7">
                    <h4 class="fw-bold mb-4"><?= $reviewsCount ?> Reviews</h4>
                    <?php if(!$reviews): ?>
                        <p style="color: var(--text-muted);">Be the first to review this product!</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="review-card">
                                <?php if($rev['is_verified_purchase']): ?>
                                    <span class="badge bg-success position-absolute" style="top: 1.5rem; right: 1.5rem;"><i class="fas fa-check-circle me-1"></i>Verified Purchase</span>
                                <?php endif; ?>
                                
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(120,120,128,0.2); color: var(--text-main); font-weight: bold;">
                                        <?= strtoupper(substr($rev['user_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold m-0"><?= escape($rev['user_name']) ?></h6>
                                        <div style="color: #ffcc00; font-size: 0.8rem;">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <?php if($i <= $rev['rating']) echo '<i class="fas fa-star"></i>'; else echo '<i class="far fa-star"></i>'; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <p class="m-0" style="color: var(--text-muted);"><?= escape($rev['comment']) ?></p>
                                <div class="small mt-2" style="color: var(--text-muted); opacity: 0.7;"><?= date('M j, Y', strtotime($rev['created_at'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Write Review Form -->
                <div class="col-md-5">
                    <div class="card-apple p-4 position-sticky" style="top: 100px; background: var(--surface-solid);">
                        <h5 class="fw-bold mb-4">Write a Review</h5>
                        <?php if(!isLoggedIn()): ?>
                            <div class="alert alert-info">Please <a href="<?= BASE_URL ?>/login.php">log in</a> to write a review.</div>
                        <?php else: ?>
                            <form action="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>" method="post">
                                <input type="hidden" name="action" value="submit_review">
                                
                                <div class="mb-3 text-center">
                                    <div class="star-rating-input">
                                        <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                                        <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                                        <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                                        <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                                        <input type="radio" id="star1" name="rating" value="1" required /><label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <textarea class="form-control" name="comment" rows="4" placeholder="Share your experience..." required style="background: var(--surface); color: var(--text-main); border: var(--glass-border);"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-apple w-100">Submit Review</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox Element -->
<div class="lightbox" id="imageLightbox">
    <img src="<?= escape($product['image']) ?>" id="lightboxImg">
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        // Entrance Animations
        window.animate("#productImageCol", { opacity: [0, 1], scale: [0.95, 1], filter: ["blur(10px)", "blur(0px)"] }, { duration: 0.8 });
        window.animate("#productDetailsCol > *", { opacity: [0, 1], x: [30, 0] }, { delay: window.stagger(0.1), duration: 0.8 });
        
        window.inView("#reviewsSection", (info) => {
            window.animate(info.target, { opacity: [0, 1], y: [40, 0] }, { duration: 0.8 });
        });

        const addToCartBtn = document.getElementById('addToCartBtn');
        const form = document.getElementById('addToCartForm');
        if (addToCartBtn) {
            form.addEventListener('submit', (e) => {
                window.animate(addToCartBtn, { scale: [1, 0.95, 1.05, 1] }, { duration: 0.4 });
                addToCartBtn.innerHTML = '<i class="fas fa-check"></i> Added';
            });
        }

        const mainImg = document.getElementById('mainProdImage');
        const lightbox = document.getElementById('imageLightbox');
        mainImg.addEventListener('click', () => {
            lightbox.classList.add('active');
            window.animate(lightbox, { opacity: [0, 1] }, { duration: 0.3 });
        });
        lightbox.addEventListener('click', () => {
            window.animate(lightbox, { opacity: [1, 0] }, { duration: 0.3 }).then(() => {
                lightbox.classList.remove('active');
            });
        });

        // Pill nav logic
        const tabs = document.querySelectorAll('#pills-tab .btn');
        const panes = document.querySelectorAll('.tab-pane');
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active', 'btn-primary'));
                tabs.forEach(t => t.classList.add('btn-glass'));
                tab.classList.remove('btn-glass');
                tab.classList.add('active', 'btn-primary');
                
                const targetId = tab.getAttribute('data-mdb-target').replace('#', '');
                panes.forEach(p => {
                    p.classList.remove('show', 'active');
                    if(p.id === targetId) {
                        p.classList.add('show', 'active');
                        window.animate(p, { opacity: [0, 1], y: [10, 0] }, { duration: 0.4 });
                    }
                });
            });
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
