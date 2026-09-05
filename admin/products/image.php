<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/product-image.php';

$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
if ($productId <= 0) {
    die('Invalid product id.');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $altText = trim($_POST['alt_text'] ?? '');
    $makePrimary = isset($_POST['is_primary']);

    try {
        $relativePath = uploadProductImage($_FILES['image'], $productId);

        addProductImage(
            $conn,
            $productId,
            $relativePath,
            $altText !== '' ? $altText : null,
            $makePrimary
        );

        $success = true;
    } catch (InvalidArgumentException $e) {
        $errors['image'] = $e->getMessage();
    } catch (Exception $e) {
        $errors['general'] = 'Something went wrong while uploading the image.';
    }
}

try {
    $images = getProductImages($conn, $productId);
} catch (Exception $e) {
    $images = [];
    $errors['general'] = 'Something went wrong while loading the images.';
}
?>

<style>
    .img-manager {
        font-family: sans-serif;
    }

    .upload-form {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .upload-form label {
        font-weight: 600;
        font-size: 0.85rem;
    }

    .btn {
        display: inline-block;
        padding: 8px 16px;
        font-size: 0.85rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-primary {
        background-color: #007bff;
    }

    .btn-star {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
    }

    .image-card {
        border: 1px solid #e2e2e2;
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
    }

    .image-card img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        display: block;
        background: #f1f1f1;
    }

    .image-card-body {
        padding: 8px 10px;
    }

    .image-card-actions {
        display: flex;
        gap: 6px;
        margin-top: 8px;
    }

    .image-card-actions form {
        display: inline;
    }

    .primary-badge {
        display: inline-block;
        background-color: #d4edda;
        color: #155724;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 10px;
        margin-bottom: 4px;
    }

    .btn-sm {
        padding: 5px 8px;
        font-size: 0.75rem;
    }
</style>

<div class="img-manager">
    <h2>Product Images</h2>

    <?php if ($success): ?>
        <p class="alert-success">Image uploaded successfully.</p>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <p class="alert-error"><?= htmlspecialchars($errors['general']) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors['image'])): ?>
        <p class="alert-error"><?= htmlspecialchars($errors['image']) ?></p>
    <?php endif; ?>

    <form class="upload-form" method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?= (int) $productId ?>">

        <div>
            <label for="image">Image file</label><br>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required>
        </div>

        <div>
            <label for="alt_text">Alt text</label><br>
            <input type="text" id="alt_text" name="alt_text" placeholder="Describe the image">
        </div>

        <div>
            <label>
                <input type="checkbox" name="is_primary" value="1"> Set as primary
            </label>
        </div>

        <input type="submit" class="btn btn-primary" value="Upload Image">
    </form>

    <?php if (empty($images)): ?>
        <p>No images uploaded for this product yet.</p>
    <?php else: ?>
        <div class="image-grid">
            <?php foreach ($images as $image): ?>
                <div class="image-card">
                    <img src="<?= htmlspecialchars(getProductImageUrl($image['image'])) ?>"
                        alt="<?= htmlspecialchars($image['alt_text'] ?? '') ?>">
                    <div class="image-card-body">
                        <?php if ((int) $image['is_primary'] === 1): ?>
                            <span class="primary-badge">Primary</span>
                        <?php endif; ?>
                        <div class="image-card-actions">
                            <?php if ((int) $image['is_primary'] !== 1): ?>
                                <form method="POST" action="image-set.php">
                                    <input type="hidden" name="product_id" value="<?= (int) $productId ?>">
                                    <input type="hidden" name="image_id" value="<?= (int) $image['id'] ?>">
                                    <button type="submit" class="btn btn-star btn-sm">Make Primary</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="image-delete.php" onsubmit="return confirm('Delete this image? This cannot be undone.');">
                                <input type="hidden" name="product_id" value="<?= (int) $productId ?>">
                                <input type="hidden" name="image_id" value="<?= (int) $image['id'] ?>">
                                <button type="submit" class="btn btn-delete btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>