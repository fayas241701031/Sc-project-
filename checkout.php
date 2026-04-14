<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    setFlash('Please log in to complete checkout.', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    setFlash('Your cart is empty.', 'info');
    header('Location: ' . BASE_URL . '/shop.php');
    exit;
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal = trim($_POST['postal'] ?? '');
    
    if (!$name || !$email || !$address || !$city || !$postal) {
        setFlash('Please fill all shipping fields.', 'danger');
    } else {
        // Here we ideally create a Razorpay Order server-side
        // To keep it simple and free of SDK dependencies, we'll pass parameters to JS
        $order_data = [
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'postal' => $postal,
            'amount' => $total * 100, // Razorpay works in paise
            'currency' => 'INR'
        ];
        $payment_init = true; 
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="container py-5 mt-4" id="checkoutSection">
    <div class="row g-5">
        <div class="col-lg-7">
            <div class="mb-4">
                <a href="<?= BASE_URL ?>/cart.php" class="text-decoration-none fw-bold" style="color: var(--text-muted);"><i class="fas fa-arrow-left me-2"></i>Back to Cart</a>
                <h2 class="fw-bold mt-4 mb-2">Checkout</h2>
                <p style="color: var(--text-muted);">Complete your shipping and payment details.</p>
            </div>

            <form id="checkoutForm" method="post" action="<?= BASE_URL ?>/checkout.php">
                <div class="card-apple p-4 mb-4" style="background: var(--surface-solid);">
                    <h5 class="fw-bold mb-4">Shipping Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Full Name</label>
                            <input type="text" class="form-control" name="name" value="<?= escape($_SESSION['user']['nama']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= escape($_SESSION['user']['email']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Address</label>
                            <input type="text" class="form-control" name="address" placeholder="123 Apple Park Way" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">City</label>
                            <input type="text" class="form-control" name="city" placeholder="Cupertino" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Postal Code</label>
                            <input type="text" class="form-control" name="postal" placeholder="95014" required>
                        </div>
                    </div>
                </div>

                <div class="card-apple p-4 mb-4" style="background: var(--surface-solid);">
                    <h5 class="fw-bold mb-4">Payment Method <span class="badge border ms-2" style="color: var(--text-muted);">Secure</span></h5>
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 border" style="background: var(--surface);">
                        <i class="fas fa-credit-card fs-4 text-primary"></i>
                        <div>
                            <div class="fw-bold">Razorpay Integrated Checkout</div>
                            <div class="small text-muted">Supports Cards, UPI, Netbanking, and Wallets</div>
                        </div>
                        <i class="fas fa-check-circle ms-auto text-success"></i>
                    </div>
                </div>

                <button type="submit" id="payBtn" class="btn btn-primary btn-apple btn-lg w-100 py-3 mb-2">Continue to Payment (<?= formatPrice($total) ?>)</button>
                <p class="text-center small mt-2" style="color: var(--text-muted);"><i class="fas fa-shield-alt me-1"></i>Secured by Razorpay 256-bit encryption.</p>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card-apple p-4 mb-4 position-sticky" style="top: 100px; background: var(--surface);">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                <div style="max-height: 300px; overflow-y: auto;" class="pe-2 mb-4">
                    <?php foreach ($cart as $item): ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-white rounded p-1 shadow-sm d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                <img src="<?= escape($item['image']) ?>" alt="<?= escape($item['name']) ?>" class="img-fluid rounded skeleton" style="max-height: 50px; object-fit: contain;" onload="this.classList.remove('skeleton')" onerror="this.classList.remove('skeleton')">
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold m-0" style="font-size: 0.9rem;"><?= escape($item['name']) ?></h6>
                                <div class="text-muted small">Qty <?= escape($item['quantity']) ?></div>
                            </div>
                            <span class="fw-bold"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr style="border-color: var(--border-color); opacity: 1;">
                
                <div class="d-flex justify-content-between mb-2 mt-3">
                    <span style="color: var(--text-muted);">Subtotal</span>
                    <strong><?= formatPrice($total) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span style="color: var(--text-muted);">Shipping</span>
                    <span class="badge" style="background: rgba(52, 199, 89, 0.1); color: #34c759; border-radius: 99px;">Free</span>
                </div>
                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3" style="background: rgba(120,120,128,0.1) !important;">
                    <span class="fs-5 fw-bold" style="color: var(--text-main);">Total</span>
                    <strong class="fs-4 fw-bold" style="color: var(--text-main);"><?= formatPrice($total) ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (isset($payment_init) && $payment_init): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    const options = {
        "key": "<?= RAZORPAY_KEY_ID ?>",
        "amount": "<?= $order_data['amount'] ?>",
        "currency": "<?= $order_data['currency'] ?>",
        "name": "Bazaar eCommerce",
        "description": "Payment for Order",
        "image": "<?= BASE_URL ?>/assets/images/logo.png",
        "handler": function (response) {
            // Success handler - send to server for verification
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASE_URL ?>/verify_payment.php';
            
            const params = {
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id || '',
                razorpay_signature: response.razorpay_signature || '',
                name: "<?= $order_data['name'] ?>",
                email: "<?= $order_data['email'] ?>",
                address: "<?= $order_data['address'] ?>",
                city: "<?= $order_data['city'] ?>",
                postal: "<?= $order_data['postal'] ?>",
                amount: "<?= $total ?>"
            };

            for (const key in params) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = params[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        },
        "prefill": {
            "name": "<?= $order_data['name'] ?>",
            "email": "<?= $order_data['email'] ?>"
        },
        "theme": {
            "color": "#007aff"
        }
    };
    const rzp = new Razorpay(options);
    rzp.open();
</script>
<?php endif; ?>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#checkoutSection", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6, easing: [0.16, 1, 0.3, 1] });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
