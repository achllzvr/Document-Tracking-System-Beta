<?php

// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// CHED protector
require_once __DIR__ . '/../includes/ched_protect.php';

// Database connection
require_once __DIR__ . '/../includes/database_conn.php';

$sweetAlertConfig = "";

// If a flash message was set during POST redirect, convert to SweetAlert config
if (!empty($_SESSION['flash']) && is_array($_SESSION['flash'])) {
  $f = $_SESSION['flash'];
  unset($_SESSION['flash']);
  $opts = [
    'icon' => $f['icon'] ?? 'info',
    'title' => $f['title'] ?? '',
    'text' => $f['text'] ?? '',
    // keep short auto-close for success/info
    'timer' => ($f['icon'] === 'success') ? 1200 : null,
    'showConfirmButton' => ($f['icon'] === 'success') ? false : true
  ];
  // remove nulls so json is tidy
  foreach ($opts as $k => $v) if ($v === null) unset($opts[$k]);
  $json = json_encode($opts, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  $sweetAlertConfig = "<script>Swal.fire($json);</script>";
}

// Handle form submissions: upload/replace, deprecate (soft delete), permanent delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Add/Update template
  if (isset($_POST['save_template'])) {
    $name = trim($_POST['template_name'] ?? '');
    $category = trim($_POST['template_category'] ?? '');
    $version = trim($_POST['template_version'] ?? '');
    $editId = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    // handle file upload if present; save into category subfolder under uploads/templates/<category-slug>/
    $fileRel = null;
    if (!empty($_FILES['uFile']) && $_FILES['uFile']['error'] === UPLOAD_ERR_OK) {
      // decide category slug (fallback to 'uncategorized')
  $catRaw = $category ?? '';
  $catRaw = trim($catRaw) !== '' ? $catRaw : 'uncategorized';
  // create a safe folder name (lowercase, alnum, dash/underscore)
  $safeCat = strtolower(preg_replace('/[^A-Za-z0-9_-]+/', '_', $catRaw));
  // repository currently stores category folders with a `_category` suffix
  // (e.g. enrollment_category). Use that naming convention so we write
  // into the existing folders instead of creating different ones.
  $safeCatFolder = $safeCat . '_category';

  $uploadsBase = __DIR__ . '/../uploads/templates';
  $uploadsDir = $uploadsBase . '/' . $safeCatFolder;
      // ensure base and category dirs exist and are writable
      if (!is_dir($uploadsBase)) {
        @mkdir($uploadsBase, 0755, true);
      }
      if (!is_dir($uploadsDir)) {
        @mkdir($uploadsDir, 0755, true);
      }
      if (!is_writable($uploadsDir)) {
        // try to relax permissions for local/dev, but don't fail silently
        @chmod($uploadsDir, 0755);
        if (!is_writable($uploadsDir)) {
          @chmod($uploadsDir, 0777);
        }
      }

      $orig = basename($_FILES['uFile']['name']);
      $ext = pathinfo($orig, PATHINFO_EXTENSION);
      $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
      $filename = time() . '_' . $safe . ($ext ? '.' . $ext : '');
      $dest = $uploadsDir . '/' . $filename;
      $moved = false;
      if (is_uploaded_file($_FILES['uFile']['tmp_name'])) {
        $moved = @move_uploaded_file($_FILES['uFile']['tmp_name'], $dest);
      } else {
        // fallback: try copying (some environments may not flag is_uploaded_file)
        $moved = @copy($_FILES['uFile']['tmp_name'], $dest);
      }
      if (! $moved) {
        // collect diagnostics for debugging
        $errCode = $_FILES['uFile']['error'] ?? null;
        $tmpName = $_FILES['uFile']['tmp_name'] ?? '(none)';
        $tmpExists = is_file($tmpName) ? 'yes' : 'no';
        $isUploaded = is_uploaded_file($tmpName) ? 'yes' : 'no';
        $uploadsBaseWritable = is_writable($uploadsBase) ? 'yes' : 'no';
        $uploadsDirWritable = is_writable($uploadsDir) ? 'yes' : 'no';
        $uploadMax = ini_get('upload_max_filesize') ?: 'unknown';
        $postMax = ini_get('post_max_size') ?: 'unknown';
        $destPath = $dest;
        $errMsg = "Failed to store uploaded file. upload_error_code={$errCode}. tmp_name={$tmpName} (exists={$tmpExists}, is_uploaded_file={$isUploaded}). uploads_base_writable={$uploadsBaseWritable}, uploads_dir_writable={$uploadsDirWritable}. dest={$destPath}. upload_max_filesize={$uploadMax}, post_max_size={$postMax}";
        // set flash and redirect back — do not insert/update record when upload failed
        $_SESSION['flash'] = [ 'icon' => 'error', 'title' => 'Upload failed', 'text' => $errMsg ];
        header('Location: manage-data-templates.php'); exit;
      }
  // store web-rel path including category folder (match on-disk folder names)
  $fileRel = '/PRISM/uploads/templates/' . $safeCatFolder . '/' . $filename;
    }

    // If editId -> update existing template, else insert
  if ($editId) {
      // build update
      $conn = $con->opencon();
      $sets = [];
      $params = [];
      if ($name !== '') { $sets[] = 'template_name = ?'; $params[] = $name; }
      if ($category !== '') { $sets[] = 'template_category = ?'; $params[] = $category; }
      if ($fileRel !== null) { $sets[] = 'template_file_rel_path = ?'; $params[] = $fileRel; }
      if ($version !== '') { $sets[] = 'template_version = ?'; $params[] = $version; }
      if ($sets) {
        // record update history and attach to this template
        $udd = $con->recordUpdate();
        if ($udd) { $sets[] = 'template_udd_ID = ?'; $params[] = $udd; }
        $params[] = $editId;
        $sql = "UPDATE templates SET " . implode(', ', $sets) . " WHERE template_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
      }
      // set flash
      $_SESSION['flash'] = [
        'icon' => 'success',
        'title' => 'Template updated',
        'text' => 'Template was updated successfully.'
      ];
      header('Location: manage-data-templates.php'); exit;
    } else {
      // insert
      // record update history for the new template row
      $udd = $con->recordUpdate();
      $meta = [
        'template_name' => $name,
        'template_category' => $category ?: null,
        'template_version' => $version ?: null,
        'template_file_rel_path' => $fileRel,
        'status' => 'active',
        'template_udd_ID' => $udd
      ];
      $ok = $con->uploadTemplate($meta);
      $_SESSION['flash'] = [
        'icon' => $ok ? 'success' : 'error',
        'title' => $ok ? 'Template uploaded' : 'Upload failed',
        'text' => $ok ? 'Template was uploaded successfully.' : 'Could not save the template.'
      ];
      header('Location: manage-data-templates.php'); exit;
    }
  }

  // Soft delete (deprecate)
  if (isset($_POST['deprecate_template'])) {
    $tid = (int)($_POST['template_id'] ?? 0);
    if ($tid) {
      $con->deleteTemplate($tid);
      $_SESSION['flash'] = [ 'icon'=>'success','title'=>'Template deprecated','text'=>'Template marked as deprecated.' ];
      header('Location: manage-data-templates.php'); exit;
    }
  }

  // Permanent delete (requires password confirmation)
  if (isset($_POST['delete_template_permanent'])) {
    $tid = (int)($_POST['template_id'] ?? 0);
    $pwd = $_POST['confirm_password'] ?? '';
    $chedUserId = $_SESSION['chedID'] ?? null;
    if ($tid && $chedUserId) {
      $ok = $con->deleteTemplatePermanently($tid, $chedUserId, $pwd);
      if ($ok) {
        $_SESSION['flash'] = [ 'icon'=>'success','title'=>'Deleted','text'=>'Template deleted permanently.' ];
      } else {
        $_SESSION['flash'] = [ 'icon'=>'error','title'=>'Delete failed','text'=>'Password incorrect or delete failed.' ];
      }
    }
    header('Location: manage-data-templates.php'); exit;
  }

  // Reactivate a deprecated template (requires password confirmation)
  if (isset($_POST['reactivate_template'])) {
    $tid = (int)($_POST['template_id'] ?? 0);
    $pwd = $_POST['confirm_password'] ?? '';
    $chedUserId = $_SESSION['chedID'] ?? null;
    if ($tid && $chedUserId) {
      $ok = $con->reactivateTemplate($tid, $chedUserId, $pwd);
      if ($ok) {
        $_SESSION['flash'] = [ 'icon'=>'success','title'=>'Re-activated','text'=>'Template re-activated successfully.' ];
      } else {
        $_SESSION['flash'] = [ 'icon'=>'error','title'=>'Re-activate failed','text'=>'Password incorrect or re-activate failed.' ];
      }
    }
    header('Location: manage-data-templates.php'); exit;
  }

  // Change template category (requires CHED password confirmation)
  if (isset($_POST['change_template_category'])) {
    $tid = (int)($_POST['template_id'] ?? 0);
    $newCat = trim($_POST['template_category'] ?? '');
    $pwd = $_POST['confirm_password'] ?? '';
    $chedUserId = $_SESSION['chedID'] ?? null;
    if ($tid && $chedUserId && $newCat !== '') {
      // verify password
      $stmt = $con->opencon()->prepare("SELECT ched_password FROM ched_users WHERE ched_ID = ? LIMIT 1");
      $stmt->execute([$chedUserId]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row && password_verify($pwd, $row['ched_password'])) {
        // perform update and record update_history
        $udd = $con->recordUpdate();
        $conn = $con->opencon();
        $uStmt = $conn->prepare("UPDATE templates SET template_category = ?, template_udd_ID = ? WHERE template_ID = ?");
        $ok = $uStmt->execute([$newCat, $udd ?: null, $tid]);
        $_SESSION['flash'] = $ok ? ['icon'=>'success','title'=>'Category updated','text'=>'Template category updated.'] : ['icon'=>'error','title'=>'Update failed','text'=>'Could not update template category.'];
      } else {
        $_SESSION['flash'] = ['icon'=>'error','title'=>'Invalid password','text'=>'The provided CHED password is incorrect.'];
      }
    }
    header('Location: manage-data-templates.php'); exit;
  }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Templates & Mappings — CHED</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="ched-min-h-screen">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>
  <div class="ched-flex-1">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="ched-main">
      <div class="ched-max-w-7xl ched-space-y-6">

        <?php
        // Determine if we're showing the add/edit form or a confirm-delete panel
        $editing = false;
        $editData = null;
        if (isset($_GET['action']) && $_GET['action'] === 'add') {
          $editing = true;
        }
        if (!empty($_GET['edit_id'])) {
          $eid = (int)$_GET['edit_id'];
          $templatesAll = $con->getTemplates();
          foreach ($templatesAll as $tt) if ((int)($tt['template_ID'] ?? 0) === $eid) { $editData = $tt; $editing = true; break; }
        }

        $confirmDeleteId = !empty($_GET['confirm_delete']) ? (int)$_GET['confirm_delete'] : null;
        ?>

        
          <section class="ched-card">
            <header class="ched-card-header ched-items-center ched-justify-between" style="display: flex;">
              <div>
                <h2 class="ched-font-semibold">Domain Templates</h2>
                <p class="ched-text-sm">Upload or replace the templates used by HEIs</p>
              </div>
              <a href="?action=add" class="ched-btn ched-btn-primary">Add Template</a>
            </header>

            <!-- Add/Edit modal (hidden by default) -->
            <div id="templateModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
              <div class="absolute inset-0 bg-black/40"></div>
              <div class="relative max-w-2xl w-full ched-card overflow-hidden">
                <div class="p-4 border-b flex items-center justify-between">
                  <h3 id="templateModalTitle" class="ched-font-semibold">Add Template</h3>
                  <button id="closeTemplateModal" class="text-slate-600">✕</button>
                </div>
                <div class="p-5">
                  <form id="templateForm" method="post" enctype="multipart/form-data" class="space-y-3">
                    <input type="hidden" name="edit_id" id="edit_id" value="" />
                    <div>
                      <label class="block text-xs">Name</label>
                      <input id="template_name" name="template_name" value="" class="w-full px-2 py-1 rounded border" required />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                      <div>
                        <label class="block text-xs">Category</label>
                        <select id="template_category" name="template_category" class="w-full px-2 py-1 rounded border">
                          <option>Enrollment</option>
                          <option>Faculty</option>
                          <option>Graduates</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-xs">Version</label>
                        <input id="template_version" name="template_version" value="" class="w-full px-2 py-1 rounded border" placeholder="e.g. 1.1" />
                      </div>
                    </div>
                    <div>
                      <label class="block text-xs">Spreadsheet File (.xlsx)</label>
                      <input id="uFile" name="uFile" type="file" accept=".xlsx" class="w-full" />
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                      <button type="button" id="cancelTemplate" class="ched-btn">Cancel</button>
                      <button type="submit" name="save_template" class="ched-btn ched-btn-primary">Save</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

                <!-- Change category confirmation modal -->
                <div id="changeCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
                  <div class="absolute inset-0 bg-black/40"></div>
                  <div class="relative max-w-md w-full ched-card overflow-hidden">
                    <div class="p-4 border-b flex items-center justify-between">
                      <h3 class="ched-font-semibold">Confirm Category Change</h3>
                      <button id="closeChangeCategory" class="text-slate-600">✕</button>
                    </div>
                    <div class="p-5">
                      <p class="text-sm text-slate-500">Changing the template category requires CHED password confirmation. Enter your CHED password to confirm.</p>
                      <form id="changeCategoryForm" method="post" class="mt-4">
                        <input type="hidden" name="template_id" id="change_template_id" value="" />
                        <div>
                          <label class="block text-xs">New Category</label>
                          <input id="change_template_category_display" type="text" disabled class="w-full px-2 py-1 rounded border bg-slate-100" />
                          <input type="hidden" name="template_category" id="change_template_category" value="" />
                        </div>
                        <div class="mt-3">
                          <label class="block text-xs">CHED password</label>
                          <input id="change_confirm_password" type="password" name="confirm_password" required class="w-full px-2 py-1 rounded border" />
                        </div>
                        <div class="mt-3 flex justify-end gap-2">
                          <button type="button" id="cancelChangeCategory" class="ched-btn">Cancel</button>
                          <button type="submit" name="change_template_category" class="ched-btn ched-btn-primary">Confirm</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

              <div class="ched-overflow-x-auto">
              <table class="w-full text-left">
                <thead class="ched-text-sm" style="color: #64748b; background: #f8fafc;">
                  <tr>
                    <th class="px-6 py-4 ched-font-semibold">Name</th>
                    <th class="px-6 py-4 ched-font-semibold">Category</th>
                    <th class="px-6 py-4 ched-font-semibold">Version</th>
                    <th class="px-6 py-4 ched-font-semibold">Status</th>
                    <th class="px-6 py-4 ched-font-semibold">File</th>
                    <th class="px-6 py-4 ched-font-semibold">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <?php
                    $templates = $con->getTemplates();
                    $active = [];
                    $deprecated = [];
                    foreach (($templates ?: []) as $t) {
                      if (($t['status'] ?? 'active') === 'deprecated') $deprecated[] = $t; else $active[] = $t;
                    }

                    $renderRows = function($rows) {
                      if (empty($rows)) {
                        echo '<tr><td colspan="6" class="py-6 text-center text-slate-500">No templates.</td></tr>';
                        return;
                      }
                      foreach ($rows as $t) {
                        $tid = (int)($t['template_ID'] ?? $t['id'] ?? 0);
                        $tname = htmlspecialchars($t['template_name'] ?? $t['name'] ?? '-');
                        $tcat = htmlspecialchars($t['template_category'] ?? $t['category'] ?? '-');
                        $tver = htmlspecialchars($t['template_version'] ?? '-');
                        $tfile = htmlspecialchars($t['template_file_rel_path'] ?? '#');
                        $tstatus = htmlspecialchars($t['status'] ?? 'active');
                        // map template status to badge class
                        $statusLower = strtolower($tstatus);
                        $statusClass = 'prism-badge-status-default';
                        if ($statusLower === 'active') $statusClass = 'prism-badge-status-open';
                        elseif ($statusLower === 'deprecated') $statusClass = 'prism-badge-status-closed';
                        // build category select (dropdown) — only field editable inline
                        $categories = ['Enrollment','Faculty','Graduates'];
                        $opts = '';
                        foreach ($categories as $c) {
                          $sel = ($c === ($t['template_category'] ?? '')) ? 'selected' : '';
                          $esc = htmlspecialchars($c);
                          $opts .= "<option value=\"{$esc}\" {$sel}>{$esc}</option>";
                        }
                        $categorySelect = "<select class='category-select px-2 py-1 rounded border' data-id='{$tid}'>" . $opts . "</select>";

                        echo "<tr class='hover:bg-slate-50'>
                          <td class='px-6 py-4 align-middle'>
                            <div class='ched-font-semibold ched-text-slate-800'>{$tname}</div>
                            <div class='ched-text-xs' style='margin-top:0.25rem;color:#94a3b8;'>ID: {$tid}</div>
                          </td>
                          <td class='px-6 py-4 align-middle'>
                            {$categorySelect}
                          </td>
                          <td class='px-6 py-4 align-middle'>
                            <div class='ched-text-sm ched-text-slate-800'>{$tver}</div>
                          </td>
                          <td class='px-6 py-4 align-middle'>
                            <span class='prism-badge {$statusClass}'>{$tstatus}</span>
                          </td>
                          <td class='px-6 py-4 align-middle'>" . ($tfile && $tfile !== '#' ? "<a class='text-blue-600 underline' href='{$tfile}' target='_blank' rel='noopener'>Download</a>" : '-') . "</td>
                          <td class='px-6 py-4 align-middle'>
                            <div class='flex items-center gap-2'>
                              " . (
                                ($statusLower === 'deprecated')
                                ? "<button class='open-reactivate-modal ched-btn ched-btn-outline ched-text-emerald-600 ched-items-center ched-gap-2 px-3 py-1' data-id='{$tid}' style='display:inline-flex;align-items:center;'><i data-lucide='check-circle' class='h-4 w-4'></i><span class='ched-text-sm'>Re-activate</span></button>"
                                : "<form method='post' style='display:inline-block;margin:0;'><input type='hidden' name='template_id' value='{$tid}' /><button name='deprecate_template' class='ched-btn ched-btn-outline ched-text-amber-600 ched-items-center ched-gap-2 px-3 py-1' type='submit' style='display:inline-flex;align-items:center;'><i data-lucide='trash' class='h-4 w-4'></i><span class='ched-text-sm'>Deprecate</span></button></form>"
                              ) . "
                              <button class='open-delete-modal ched-btn ched-btn-outline ched-text-rose-600 ched-items-center ched-gap-2 px-3 py-1' data-id='{$tid}' style='display:inline-flex;align-items:center;'>
                                <i data-lucide='x-circle' class='h-4 w-4'></i>
                                <span class='ched-text-sm'>Delete</span>
                              </button>
                            </div>
                          </td>
                        </tr>";
                      }
                    };

                    // render active templates first
                    $renderRows($active);
                  ?>
                </tbody>
              </table>
            </div>
            <!-- Deprecated templates section -->
            <div class="mt-8 ched-card">
              <header class="ched-card-header ched-items-center ched-justify-between" style="display:flex;">
                <div>
                  <h3 class="ched-font-semibold">Deprecated Templates</h3>
                  <p class="ched-text-sm">Previously used templates (deprecated)</p>
                </div>
              </header>
              <div class="ched-overflow-x-auto">
                <table class="w-full text-left">
                  <thead class="ched-text-sm" style="color: #64748b; background: #f8fafc;">
                    <tr>
                      <th class="px-6 py-4 ched-font-semibold">Name</th>
                      <th class="px-6 py-4 ched-font-semibold">Category</th>
                      <th class="px-6 py-4 ched-font-semibold">Version</th>
                      <th class="px-6 py-4 ched-font-semibold">Status</th>
                      <th class="px-6 py-4 ched-font-semibold">File</th>
                      <th class="px-6 py-4 ched-font-semibold">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y">
                    <?php $renderRows($deprecated); ?>
                  </tbody>
                </table>
              </div>
            </div>
          </section>

        <!-- Column Mappings removed (managed elsewhere) -->
      </div>
    </main>
  </div>

  

  <!-- Delete permanently modal -->
  <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="relative max-w-md w-full ched-card overflow-hidden">
      <div class="p-4 border-b flex items-center justify-between">
        <h3 class="ched-font-semibold">Confirm Permanent Delete</h3>
        <button id="closeDeleteModal" class="text-slate-600">✕</button>
      </div>
      <div class="p-5">
        <p class="text-sm text-slate-500">This action will permanently delete the selected template. Enter your CHED password to confirm.</p>
        <form id="deleteForm" method="post" class="mt-4">
          <input type="hidden" name="template_id" id="delete_template_id" value="" />
          <div>
            <label class="block text-xs">CHED password</label>
            <input id="confirm_password" type="password" name="confirm_password" required class="w-full px-2 py-1 rounded border" />
          </div>
          <div class="mt-3 flex justify-end gap-2">
            <button type="button" id="cancelDelete" class="ched-btn">Cancel</button>
            <button type="submit" name="delete_template_permanent" class="ched-btn ched-btn-outline text-rose-700" style="background:#fee2e2;border-color:#fecaca;">Delete Permanently</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Reactivate modal -->
  <div id="reactivateModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="relative max-w-md w-full ched-card overflow-hidden">
      <div class="p-4 border-b flex items-center justify-between">
        <h3 class="ched-font-semibold">Confirm Reactivation</h3>
        <button id="closeReactivateModal" class="text-slate-600">✕</button>
      </div>
      <div class="p-5">
        <p class="text-sm text-slate-500">Re-activating this template will make it available again. Enter your CHED password to confirm.</p>
        <form id="reactivateForm" method="post" class="mt-4">
          <input type="hidden" name="template_id" id="reactivate_template_id" value="" />
          <div>
            <label class="block text-xs">CHED password</label>
            <input id="reactivate_confirm_password" type="password" name="confirm_password" required class="w-full px-2 py-1 rounded border" />
          </div>
          <div class="mt-3 flex justify-end gap-2">
            <button type="button" id="cancelReactivate" class="ched-btn">Cancel</button>
            <button type="submit" name="reactivate_template" class="ched-btn ched-btn-outline text-emerald-700" style="background:#ecfdf5;border-color:#bbf7d0;">Re-activate</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>if (window.lucide) lucide.createIcons();</script>
    <?php echo $sweetAlertConfig; ?>
  <script>
    // Modal helpers
    const templateModal = document.getElementById('templateModal');
    const deleteModal = document.getElementById('deleteModal');
    const openModal = (modal) => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
    const closeModal = (modal) => { modal.classList.remove('flex'); modal.classList.add('hidden'); };

    // Open Add modal
    document.querySelectorAll('a[href="?action=add"]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        // clear form
        document.getElementById('edit_id').value = '';
        document.getElementById('template_name').value = '';
        document.getElementById('template_category').value = 'Enrollment';
        document.getElementById('template_version').value = '';
        openModal(templateModal);
      });
    });

      // If server indicated we're editing an existing template, prefill and open modal
      <?php if (!empty($editing) && !empty($editData)): ?>
        (function(){
          var data = <?php echo json_encode($editData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
          // populate fields
          try{
            document.getElementById('edit_id').value = data.template_ID || data.id || '';
            document.getElementById('template_name').value = data.template_name || data.name || '';
            // if category value exists in select, set it; else append option
            var sel = document.getElementById('template_category');
            if (sel) {
              var found = false;
              for (var i=0;i<sel.options.length;i++){ if (sel.options[i].value === (data.template_category || '')) { found=true; break; } }
              if (!found && (data.template_category || '') !== '') {
                var opt = document.createElement('option'); opt.value = data.template_category; opt.text = data.template_category; sel.add(opt);
              }
              sel.value = data.template_category || '';
            }
            document.getElementById('template_version').value = data.template_version || '';
            document.getElementById('templateModalTitle').innerText = 'Edit Template';
            openModal(templateModal);
          }catch(e){ console.error('prefill edit modal', e); }
        })();
      <?php endif; ?>

  // Close buttons
  document.getElementById('closeTemplateModal').addEventListener('click', () => closeModal(templateModal));
  document.getElementById('cancelTemplate').addEventListener('click', () => closeModal(templateModal));

    // NOTE: Replace functionality removed — no client-side replace handlers

    // Delete modal openers
    document.querySelectorAll('.open-delete-modal').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = btn.getAttribute('data-id');
        document.getElementById('delete_template_id').value = id;
        document.getElementById('confirm_password').value = '';
        openModal(deleteModal);
      });
    });
    document.getElementById('closeDeleteModal').addEventListener('click', () => closeModal(deleteModal));
    document.getElementById('cancelDelete').addEventListener('click', () => closeModal(deleteModal));

    // Reactivate modal openers (for deprecated templates)
    const reactivateModal = document.getElementById('reactivateModal');
    document.querySelectorAll('.open-reactivate-modal').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = btn.getAttribute('data-id');
        document.getElementById('reactivate_template_id').value = id;
        document.getElementById('reactivate_confirm_password').value = '';
        openModal(reactivateModal);
      });
    });
    document.getElementById('closeReactivateModal').addEventListener('click', () => closeModal(reactivateModal));
    document.getElementById('cancelReactivate').addEventListener('click', () => closeModal(reactivateModal));

    // Server-side edit auto-open removed (Replace removed from UI)

    // Category change flow: intercept select change, open password modal to confirm
    const changeCategoryModal = document.getElementById('changeCategoryModal');
    const changeForm = document.getElementById('changeCategoryForm');
    const changeTemplateId = document.getElementById('change_template_id');
    const changeTemplateCategory = document.getElementById('change_template_category');
    const changeTemplateCategoryDisplay = document.getElementById('change_template_category_display');
    const closeChangeCategory = document.getElementById('closeChangeCategory');
    const cancelChangeCategory = document.getElementById('cancelChangeCategory');

    // store previous value so we can revert on cancel
    const _prevCategory = new Map();
    document.querySelectorAll('.category-select').forEach(sel => {
      const id = sel.getAttribute('data-id');
      // save on focus or mousedown (covers keyboard and mouse interactions)
      sel.addEventListener('focus', () => _prevCategory.set(id, sel.value));
      sel.addEventListener('mousedown', () => _prevCategory.set(id, sel.value));

      sel.addEventListener('change', (e) => {
        const newCat = sel.value;
        // ensure we have the previous value recorded
        if (!_prevCategory.has(id)) _prevCategory.set(id, sel.getAttribute('data-prev') || sel.value);
        // populate modal
        changeTemplateId.value = id;
        changeTemplateCategory.value = newCat;
        changeTemplateCategoryDisplay.value = newCat;
        document.getElementById('change_confirm_password').value = '';
        openModal(changeCategoryModal);
      });
    });

    // when the change form is submitted, clear the saved previous value for that id
    if (changeForm) {
      changeForm.addEventListener('submit', function(){
        try{ _prevCategory.delete(changeTemplateId.value); }catch(e){}
      });
    }

    if (closeChangeCategory) closeChangeCategory.addEventListener('click', () => {
      // revert selection
      const id = changeTemplateId.value;
      const prev = _prevCategory.get(id);
      if (prev !== undefined) {
        const sel = document.querySelector('.category-select[data-id="' + id + '"]');
        if (sel) sel.value = prev;
        _prevCategory.delete(id);
      }
      closeModal(changeCategoryModal);
    });
    if (cancelChangeCategory) cancelChangeCategory.addEventListener('click', () => {
      // revert selection
      const id = changeTemplateId.value;
      const prev = _prevCategory.get(id);
      if (prev !== undefined) {
        const sel = document.querySelector('.category-select[data-id="' + id + '"]');
        if (sel) sel.value = prev;
        _prevCategory.delete(id);
      }
      closeModal(changeCategoryModal);
    });
  </script>
</body>
</html>
