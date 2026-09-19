<?php
/**
 * admin/settings/_form.php
 *
 * Shared form for create.php and edit.php. Expects these variables:
 *   $mode          'create' | 'edit'
 *   $form          array of field values (see settingFormFromRow/FromPost)
 *   $error         string|null  error message to show above the form
 *   $csrfToken     string
 *   $formAction    string       where the form posts to
 *   $existingImage string       stored filename of the current image (edit)
 *   $conn          mysqli       (used to list existing groups)
 */

$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};

$isEdit = ($mode === 'edit');

// Group suggestions: built-in list + whatever groups already exist.
$groupOptions = SETTING_GROUP_SUGGESTIONS();
try {
    $groupOptions = array_values(array_unique(array_merge($groupOptions, getSettingGroups($conn))));
} catch (Throwable $ex) {
    // fall back to the built-in suggestions
}
sort($groupOptions, SORT_NATURAL | SORT_FLAG_CASE);

$imageUrl = !empty($existingImage) ? getSettingImageUrl($existingImage) : null;
?>
<style>
    :root {
        --leaf: #2f9e6e;
        --leaf-dark: #22794f;
        --leaf-tint: #e7f6ee;
        --ink: #1c2b3a;
        --sky: #0f6fb0;
        --sky-tint: #eaf4fb;
        --paper: #ffffff;
        --mist: #f4f8fb;
        --line: #e1e9f0;
        --muted: #64798c;
        --danger: #c8412f;
        --danger-tint: #fbebe8;
    }

    body {
        font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--mist);
        margin: 0;
        color: var(--ink);
    }

    .admin-wrap {
        max-width: 820px;
        margin: 0 auto;
        padding: 24px 20px 60px;
    }

    h1 {
        font-size: 21px;
        font-weight: 800;
        letter-spacing: -0.01em;
        margin: 0 0 2px;
    }

    .page-subtitle {
        color: var(--muted);
        font-size: 13px;
        margin: 0 0 20px;
    }

    .card {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 20px;
        box-shadow: 0 1px 2px rgba(28, 43, 58, 0.04);
    }

    .flash {
        padding: 11px 15px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 14px;
        font-weight: 600;
        border-left: 3px solid transparent;
    }

    .flash-error {
        background: var(--danger-tint);
        color: var(--danger);
        border-left-color: var(--danger);
    }

    .btn {
        display: inline-block;
        padding: 9px 16px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .btn-primary {
        background: var(--leaf);
        color: #fff;
    }

    .btn-primary:hover {
        background: var(--leaf-dark);
    }

    .btn-secondary {
        background: #fff;
        color: var(--sky);
        border-color: var(--line);
    }

    .btn-secondary:hover {
        background: var(--sky-tint);
        border-color: var(--sky);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px 18px;
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .field.full {
        grid-column: 1 / -1;
    }

    .field label {
        font-size: 12.5px;
        font-weight: 700;
    }

    .field .req {
        color: var(--danger);
    }

    .field input[type=text],
    .field input[type=email],
    .field input[type=url],
    .field input[type=number],
    .field input[type=tel],
    .field input[type=file],
    .field select,
    .field textarea {
        padding: 9px 11px;
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 13.5px;
        font-family: inherit;
        color: var(--ink);
        background: #fff;
        width: 100%;
        box-sizing: border-box;
    }

    .field textarea {
        min-height: 110px;
        resize: vertical;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        outline: none;
        border-color: var(--sky);
        box-shadow: 0 0 0 3px var(--sky-tint);
    }

    .hint {
        color: var(--muted);
        font-size: 12px;
    }

    .hint.warn {
        color: var(--danger);
    }

    .toggle {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 13.5px;
        font-weight: 600;
        padding: 9px 0;
    }

    .toggle input {
        width: 18px;
        height: 18px;
        accent-color: var(--leaf);
    }

    .current-image {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .current-image img {
        max-height: 70px;
        max-width: 160px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--mist);
        padding: 4px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid var(--line);
    }

    [hidden] {
        display: none !important;
    }

    @media (max-width: 640px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="admin-wrap">
    <h1><?= $isEdit ? 'Edit Setting' : 'Add Setting' ?></h1>
    <p class="page-subtitle">
        <?= $isEdit ? 'Update this website-wide setting.' : 'Create a new website-wide setting.' ?>
    </p>

    <?php if (!empty($error)): ?>
        <div class="flash flash-error"><?= $e($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="post" action="<?= $e($formAction) ?>" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="label">Label <span class="req">*</span></label>
                    <input type="text" id="label" name="label" maxlength="150" required
                        value="<?= $e($form['label']) ?>" placeholder="e.g. Site Name">
                    <span class="hint">Shown in the admin list.</span>
                </div>

                <div class="field">
                    <label for="setting_key">Key <span class="req">*</span></label>
                    <input type="text" id="setting_key" name="setting_key" maxlength="100" required
                        pattern="[a-z0-9_\-]+" value="<?= $e($form['setting_key']) ?>" placeholder="e.g. site_name">
                    <?php if ($isEdit): ?>
                        <span class="hint warn">Changing the key breaks any code that reads the old key.</span>
                    <?php else: ?>
                        <span class="hint">Lowercase letters, numbers, _ and - only. Used in code: getSetting($conn, 'key').</span>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="setting_type">Type <span class="req">*</span></label>
                    <select id="setting_type" name="setting_type">
                        <?php foreach (SETTING_TYPES() as $value => $label): ?>
                            <option value="<?= $e($value) ?>" <?= $form['setting_type'] === $value ? 'selected' : '' ?>>
                                <?= $e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="setting_group">Group <span class="req">*</span></label>
                    <input type="text" id="setting_group" name="setting_group" maxlength="100" required
                        list="group-list" value="<?= $e($form['setting_group']) ?>">
                    <datalist id="group-list">
                        <?php foreach ($groupOptions as $g): ?>
                            <option value="<?= $e($g) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <span class="hint">Pick one or type a new group.</span>
                </div>

                <!-- Value: one input per kind, JS shows the one that matches the type -->
                <div class="field full" data-types="text email url number phone">
                    <label for="value_text">Value</label>
                    <input type="text" id="value_text" name="value_text" value="<?= $e($form['value_text']) ?>">
                </div>

                <div class="field full" data-types="textarea" hidden>
                    <label for="value_textarea">Value</label>
                    <textarea id="value_textarea" name="value_textarea"><?= $e($form['value_textarea']) ?></textarea>
                </div>

                <div class="field full" data-types="boolean" hidden>
                    <label>Value</label>
                    <label class="toggle">
                        <input type="checkbox" name="value_boolean" value="1" <?= !empty($form['value_boolean']) ? 'checked' : '' ?>>
                        Enabled (On)
                    </label>
                </div>

                <div class="field full" data-types="image" hidden>
                    <label for="value_image">Image</label>
                    <?php if ($imageUrl): ?>
                        <div class="current-image">
                            <img src="<?= $e($imageUrl) ?>" alt="Current image">
                            <span class="hint">Current: <?= $e($existingImage) ?><br>Upload a new file to replace it.</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="value_image" name="value_image" accept="image/jpeg,image/png,image/webp,image/gif">
                    <span class="hint">JPG, PNG, WEBP or GIF. Max 2MB.</span>
                </div>

                <div class="field full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" style="min-height:70px"
                        placeholder="What is this setting used for?"><?= $e($form['description']) ?></textarea>
                </div>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach (SETTING_STATUS_OPTIONS() as $value => $label): ?>
                            <option value="<?= $e($value) ?>" <?= $form['status'] === $value ? 'selected' : '' ?>>
                                <?= $e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="sort_order">Sort order</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= (int) $form['sort_order'] ?>">
                    <span class="hint">Lower numbers appear first within a group.</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Setting' ?></button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var typeSelect = document.getElementById('setting_type');
        var panels = document.querySelectorAll('[data-types]');
        var textInput = document.getElementById('value_text');
        var inputTypes = { text: 'text', email: 'email', url: 'url', number: 'number', phone: 'tel' };

        function sync() {
            var type = typeSelect.value;
            panels.forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-types').split(' ').indexOf(type) === -1;
            });
            if (inputTypes[type]) {
                textInput.type = inputTypes[type];
                if (type === 'number') { textInput.step = 'any'; } else { textInput.removeAttribute('step'); }
            }
        }

        typeSelect.addEventListener('change', sync);
        sync();
    })();
</script>