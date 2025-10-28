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

            <?php if ($editing):
              // prefill from $editData if available
              $vName = htmlspecialchars($editData['template_name'] ?? '', ENT_QUOTES);
              $vCat = htmlspecialchars($editData['template_category'] ?? 'Enrollment', ENT_QUOTES);
              $vVer = htmlspecialchars($editData['template_version'] ?? '', ENT_QUOTES);
              $vId = (int)($editData['template_ID'] ?? 0);
            ?>
            <div class="p-5 border-b">
              <form method="post" enctype="multipart/form-data" class="space-y-3">
                <input type="hidden" name="edit_id" value="<?php echo $vId; ?>" />
                <div>
                  <label class="block text-xs">Name</label>
                  <input name="template_name" value="<?php echo $vName; ?>" class="w-full px-2 py-1 rounded border" required />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs">Category</label>
                    <select name="template_category" class="w-full px-2 py-1 rounded border">
                      <option <?php echo $vCat === 'Enrollment' ? 'selected' : ''; ?>>Enrollment</option>
                      <option <?php echo $vCat === 'Faculty' ? 'selected' : ''; ?>>Faculty</option>
                      <option <?php echo $vCat === 'Graduates' ? 'selected' : ''; ?>>Graduates</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs">Version</label>
                    <input name="template_version" value="<?php echo $vVer; ?>" class="w-full px-2 py-1 rounded border" placeholder="e.g. 1.1" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs">Spreadsheet File (.xlsx)</label>
                  <input name="uFile" type="file" accept=".xlsx" class="w-full" />
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                  <a href="manage-data-templates.php" class="px-3 py-2 rounded border">Cancel</a>
                  <button type="submit" name="save_template" class="px-3 py-2 rounded bg-blue-600 text-white">Save</button>
                </div>
              </form>
            </div>
            <?php endif; ?>

            <div class="p-5 ched-overflow-x-auto">
              <table class="min-w-full ched-text-sm">
                <thead class="ched-text-slate-800" style="border-bottom: 1px solid #e5e7eb;">
                  <tr>
                    <th class="py-2 text-left">Name</th>
                    <th class="text-left">Category</th>
                    <th class="text-left">Version</th>
                    <th class="text-left">File</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $templates = $con->getTemplates();
                    if (empty($templates)) {
                      echo '<tr><td colspan="5" class="py-6 text-center text-slate-500">No templates yet.</td></tr>';
                    } else {
                      foreach ($templates as $t) {
                        $tid = (int)($t['template_ID'] ?? $t['id'] ?? 0);
                        $tname = htmlspecialchars($t['template_name'] ?? $t['name'] ?? '-');
                        $tcat = htmlspecialchars($t['template_category'] ?? $t['category'] ?? '-');
                        $tver = htmlspecialchars($t['template_version'] ?? '-');
                        $tfile = htmlspecialchars($t['template_file_rel_path'] ?? '#');
                        echo "<tr class='border-b'>
                          <td class='py-2'>{$tname}</td>
                          <td>{$tcat}</td>
                          <td>{$tver}</td>
                          <td>" . ($tfile && $tfile !== '#' ? "<a class='text-blue-600 underline' href='{$tfile}' target='_blank' rel='noopener'>Download</a>" : '-') . "</td>
                          <td class='text-right'>
                            <a href='?edit_id={$tid}' class='text-sm text-blue-600 hover:underline'>Replace</a>
                            <form method='post' style='display:inline-block;margin-left:12px;'>
                              <input type='hidden' name='template_id' value='{$tid}' />
                              <button name='deprecate_template' class='text-sm text-rose-600 hover:underline' type='submit'>Deprecate</button>
                            </form>
                            <a href='?confirm_delete={$tid}' class='text-sm text-rose-700 hover:underline ml-3'>Delete Permanently</a>
                          </td>
                        </tr>";
                      }
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </section>

        <!-- Column Mappings removed (managed elsewhere) -->
      </div>
    </main>
  </div>

  

  <?php if ($confirmDeleteId): ?>
    <section class="ched-card">
      <div class="p-5">
        <h3 class="font-semibold">Confirm Permanent Delete</h3>
        <p class="text-sm text-slate-500">You are about to permanently delete template ID <?php echo $confirmDeleteId; ?>. This action cannot be undone.</p>
        <form method="post" class="mt-4">
          <input type="hidden" name="template_id" value="<?php echo $confirmDeleteId; ?>" />
          <div>
            <label class="block text-xs">Enter your CHED password to confirm</label>
            <input type="password" name="confirm_password" required class="w-full px-2 py-1 rounded border" />
          </div>
          <div class="mt-3">
            <a href="manage-data-templates.php" class="px-3 py-2 rounded border">Cancel</a>
            <button type="submit" name="delete_template_permanent" class="px-3 py-2 rounded bg-rose-600 text-white">Delete Permanently</button>
          </div>
        </form>
      </div>
    </section>
  <?php endif; ?>

  <script>if (window.lucide) lucide.createIcons();</script>
    <?php echo $sweetAlertConfig; ?>
</body>
</html>
