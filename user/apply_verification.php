<?php
require_once dirname(__DIR__) . '/includes/header.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name'] ?? '');
    $brand_proof = trim($_POST['brand_proof'] ?? '');
    $contact_details = trim($_POST['contact_details'] ?? '');
    
    // File upload handling for ID Proof
    $id_proof_path = '';
    if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = dirname(__DIR__) . '/assets/uploads/verifications/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['id_proof']['name'], PATHINFO_EXTENSION);
        $filename = 'id_' . $_SESSION['user']['id'] . '_' . time() . '.' . $file_ext;
        $id_proof_path = 'assets/uploads/verifications/' . $filename;
        
        move_uploaded_file($_FILES['id_proof']['tmp_name'], $upload_dir . $filename);
    }

    if ($business_name && $id_proof_path && $brand_proof && $contact_details) {
        // Check if a request already exists
        $stmt = $pdo->prepare('SELECT id FROM verification_requests WHERE user_id = ? AND status = "pending"');
        $stmt->execute([$_SESSION['user']['id']]);
        if ($stmt->fetch()) {
            setFlash('You already have a pending verification request.', 'warning');
        } else {
            $stmt = $pdo->prepare('INSERT INTO verification_requests (user_id, business_name, id_proof, brand_proof, contact_details) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $_SESSION['user']['id'],
                $business_name,
                $id_proof_path,
                $brand_proof,
                $contact_details
            ]);
            setFlash('Verification request submitted successfully. Admin will review it manually.', 'success');
        }
        header('Location: ' . BASE_URL . '/user/dashboard.php');
        exit;
    } else {
        setFlash('Please fill all fields and upload required documents.', 'danger');
    }
}
?>

<section class="container py-5 mt-4" id="verificationForm">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card-apple p-5 shadow-lg border-0 bg-glass" style="background: var(--surface-solid);">
                <div class="text-center mb-5">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 70px; height: 70px; background: rgba(0, 122, 255, 0.1); color: var(--primary-color); font-size: 1.8rem;">
                        <i class="fas fa-check-decagram"></i>
                    </div>
                    <h2 class="fw-bold m-0">Apply for Verification</h2>
                    <p style="color: var(--text-muted);" class="mt-2">Join trusted brands like Nike and Puma with a verified badge.</p>
                </div>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Legal Business/Brand Name</label>
                        <input type="text" class="form-control" name="business_name" required placeholder="e.g. Acme Corp" style="background: var(--surface);">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Government ID (Passport/ID Card)</label>
                        <input type="file" class="form-control" name="id_proof" required style="background: var(--surface);">
                        <div class="form-text small">Please upload a clear photo of your official ID.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Brand Proof / Official Website</label>
                        <textarea class="form-control" name="brand_proof" rows="3" required placeholder="Provide links to your official website or social media presence." style="background: var(--surface);"></textarea>
                    </div>
                    <div class="mb-5">
                        <label class="form-label small fw-bold text-muted">Contact Details (Official Email/Phone)</label>
                        <input type="text" class="form-control" name="contact_details" required placeholder="e.g. contact@yourbrand.com" style="background: var(--surface);">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-apple btn-lg w-100 py-3 mb-4">Submit Verification Request</button>
                    
                    <div class="mt-2 text-center text-muted small">
                         Admin will manually verify your documents before approval.
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#verificationForm", { opacity: [0, 1], y: [20, 0] }, { duration: 0.8 });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
