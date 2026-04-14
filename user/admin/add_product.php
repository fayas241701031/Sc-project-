<?php
require_once __DIR__ . '/auth.php';
$stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
$categories = $stmt->fetchAll();
$product = ['name' => '', 'description' => '', 'category_id' => '', 'price' => '', 'stock' => '', 'image' => '', 'popularity' => 0];
$isEdit = false;
if (!empty($_GET['edit_id'])) {
    $isEdit = true;
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute([':id' => (int)$_GET['edit_id']]);
    $product = $stmt->fetch();
    if (!$product) {
        setFlash('Product not found.', 'danger');
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $popularity = (int)$_POST['popularity'];
    $imagePath = $product['image'] ?? 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=800&q=80';
    
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../assets/img/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = uniqid('prod_', true) . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
            $imagePath = BASE_URL . '/assets/img/' . $fileName;
        }
    }
    
    if (!$name || !$description || !$categoryId || !$price || !$stock) {
        setFlash('Please fill all required fields.', 'danger');
    } else {
        if ($isEdit) {
            $stmt = $pdo->prepare('UPDATE products SET name = :name, description = :description, category_id = :category_id, price = :price, stock = :stock, image = :image, popularity = :popularity WHERE id = :id');
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':category_id' => $categoryId,
                ':price' => $price,
                ':stock' => $stock,
                ':image' => $imagePath,
                ':popularity' => $popularity,
                ':id' => $product['id'],
            ]);
            setFlash('Product updated successfully.', 'success');
        } else {
            $sellerId = $_SESSION['user']['id'];
            $stmt = $pdo->prepare('INSERT INTO products (name, description, category_id, price, stock, image, popularity, seller_id) VALUES (:name, :description, :category_id, :price, :stock, :image, :popularity, :seller_id)');
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':category_id' => $categoryId,
                ':price' => $price,
                ':stock' => $stock,
                ':image' => $imagePath,
                ':popularity' => $popularity,
                ':seller_id' => $sellerId,
            ]);
            setFlash('Product added successfully.', 'success');
        }
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="container py-5 mt-4" id="addProductSection">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-5">
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-decoration-none fw-bold" style="color: var(--text-muted);"><i class="fas fa-arrow-left me-2"></i>Back to Command Center</a>
                <h2 class="fw-bold mt-4 mb-2"><?= $isEdit ? 'Edit Global Listing' : 'Create New Listing' ?></h2>
                <p style="color: var(--text-muted);">Configure top-tier products for the Bazaar marketplace.</p>
            </div>

            <form method="post" enctype="multipart/form-data" class="card-apple p-5 shadow-lg border-0" style="background: var(--surface-solid);">
                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Product Name</label>
                            <input type="text" class="form-control" name="name" value="<?= escape($product['name']) ?>" required placeholder="e.g. iPhone 15 Pro">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Category</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= escape($category['id']) ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>><?= escape($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Global Description</label>
                            <textarea class="form-control" name="description" rows="5" required placeholder="Describe the premium features..."><?= escape($product['description']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Price (₹)</label>
                            <input type="number" step="0.01" class="form-control fw-bold" name="price" value="<?= escape($product['price']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Initial Stock</label>
                            <input type="number" class="form-control" name="stock" value="<?= escape($product['stock']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Popularity Score</label>
                            <input type="number" class="form-control" name="popularity" value="<?= escape($product['popularity']) ?>" required>
                            <div class="form-text small">Global ranking on the marketplace.</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted">Visual Identity</label>
                            <input type="file" class="form-control" name="image" accept="image/*" <?= $isEdit ? '' : 'required' ?>>
                            <?php if (!empty($product['image'])): ?>
                                <div class="mt-3 text-center bg-white p-2 rounded-4 shadow-sm">
                                    <img src="<?= escape($product['image']) ?>" style="max-height: 120px; object-fit: contain;" class="img-fluid rounded">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12 border-top pt-4 mt-5">
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary btn-apple btn-lg flex-grow-1 py-3"><?= $isEdit ? 'Update listing' : 'Publish to Marketplace' ?></button>
                            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-glass btn-apple px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#addProductSection", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6 });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
