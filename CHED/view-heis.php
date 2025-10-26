<?php

// import dev error output
require_once __DIR__ . '/../includes/dev_logs.php';

// CHED protector
require_once __DIR__ . '/../includes/ched_protect.php';

// Database connection
require_once('../classes/database.php');

// Instance of the database class
$con = new database();

// Alert Initialization
$sweetAlertConfig = "";

// Apply filter form submission function
if(isset($_POST['applyFilters'])) {

  // Selected filter values
  $region = isset($_POST['region']) ? $_POST['region'] : 'all';           // expects region_number or 'all'
  $instType = isset($_POST['instType']) ? $_POST['instType'] : 'all';     // expects inst_type_ID or 'all'

  // Normalize as strings to compare reliably
  $region = ($region === '' ? 'all' : (string)$region);
  $instType = ($instType === '' ? 'all' : (string)$instType);

  // Decide fetch strategy based on selections
  if ($region === 'all' && $instType === 'all') {
    // No filters -> full list
    $inst_list = $con->fetchInstitutions();
    $error = '';
  } elseif ($region !== 'all' && $instType !== 'all') {
    // Both filters provided -> use filtered query
    $inst_list = $con->fetchInstitutionsFiltered($region, $instType);
    $error = '';
  } elseif ($region !== 'all' && $instType === 'all') {
    // Region only -> fetch all then filter by region number (HEI_region)
    $all = $con->fetchInstitutions();
    $inst_list = array_values(array_filter($all, function($row) use ($region) {
      return isset($row['HEI_region']) && (string)$row['HEI_region'] === (string)$region;
    }));
    $error = '';
  } else {
    // Type only -> map inst_type_ID to description then filter by HEI_type
    $types = $con->fetchAllInstitutionTypes();
    $typeDesc = '';
    foreach ($types as $t) {
      if ((string)$t['inst_type_ID'] === (string)$instType) {
        $typeDesc = (string)$t['inst_type_desc'];
        break;
      }
    }
    $all = $con->fetchInstitutions();
    if ($typeDesc !== '') {
      $inst_list = array_values(array_filter($all, function($row) use ($typeDesc) {
        return isset($row['HEI_type']) && (string)$row['HEI_type'] === (string)$typeDesc;
      }));
    } else {
      $inst_list = $all;
    }
    $error = '';
  }
} else {
  $error = '';
}

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CHED PRISM – HEIs</title>
    <link rel="icon" type="image/png" href="/PRISM/assets/CHED_logo.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  </head>
  <body class="min-h-screen flex flex-col bg-gray-50 text-slate-800">
  <?php $showHEI = false; require_once __DIR__ . '/../includes/header.php'; ?>

  <div class="flex-1 flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto space-y-6">
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b">
            <h2 class="font-semibold">Search & Filter</h2>
            <p class="text-sm text-slate-500">Find institutions by name, region, type, ownership</p>
          </header>
          <form method="POST" class="">
          <div class="p-5 grid md:grid-cols-4 gap-4">
              <div class="relative md:col-span-1">
                <i data-lucide="search" class="absolute left-3 top-3 h-4 w-4 text-slate-400"></i>
                <input id="search" class="w-full pl-9 px-3 py-2 rounded border" placeholder="Search by name..." />
              </div>
              <select name="region" id="region" class="px-3 py-2 rounded border">
                <?php

                  // Fetch all regions from the database
                  $regions = $con->fetchAllRegions();
                  echo '<option value="all">All Regions</option>';
                  foreach ($regions as $region) {
                    // Use region_number as value because fetchInstitutionsFiltered expects region_number
                    $regionNumber = htmlspecialchars($region['region_number']);
                    $regionDivision = htmlspecialchars($region['region_division']);
                    echo "<option value=\"$regionNumber\">Region $regionNumber - $regionDivision</option>";
                  }

                ?>
              </select>
              <select name="instType" id="instType" class="px-3 py-2 rounded border">
                <?php
                  // Fetch all institution types from the database
                  $types = $con->fetchAllInstitutionTypes();
                  echo '<option value="all">All Types</option>';
                  foreach ($types as $type) {
                    // fetchAllInstitutionTypes returns inst_type_ID and inst_type_desc
                    $typeID = htmlspecialchars($type['inst_type_ID']);
                    $typeName = htmlspecialchars($type['inst_type_desc']);
                    echo "<option value=\"$typeID\">$typeName</option>";
                  }
                ?>
              </select>
              <!-- Button to apply filters -->
              <div class="flex items-end">
                <button type="submit" name="applyFilters" id="applyFilters" class="px-3 py-2 rounded bg-blue-600 text-white">Apply</button>
                <button type="button" name="resetFilters" id="resetFilters" class="ml-2 px-3 py-2 rounded border">Reset</button>
              </div>
            </div>
          </form>
        </section>

        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <?php
            // Compute dataset and count once for header and rows
            $heis = isset($inst_list) ? $inst_list : $con->fetchInstitutions();
            $heisCount = is_array($heis) ? count($heis) : 0;
          ?>
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <div>
              <h2 class="font-semibold">Institutions (<span id="institutionsCount"><?php echo $heisCount; ?></span>)</h2>
              <p class="text-sm text-slate-500">Browse and manage institutional profiles</p>
            </div>
            <div id="pager" class="text-sm text-slate-600"></div>
          </header>
          <div class="p-5 overflow-x-auto">
            <div id="heisLoading" class="hidden items-center gap-2 text-sm text-slate-500 mb-3">
              <svg class="animate-spin h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
              Loading institutions...
            </div>
            <table id="heisTable" class="min-w-full text-left text-sm">
              <thead class="text-slate-600 border-b">
                <tr>
                  <th class="py-2">Name</th>
                  <th>Region</th>
                  <th>Type</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="heisTbody">
                <?php
                // Render rows
                foreach ($heis as $hei) {
                    $heiId = htmlspecialchars($hei['HEI_id'] ?? '');
                    $heiName = htmlspecialchars($hei['HEI_name'] ?? '');
                    $heiRegion = htmlspecialchars($hei['HEI_region'] ?? '');
                    $heiTypeRaw = $hei['HEI_type'] ?? '';
                    $heiType = htmlspecialchars($heiTypeRaw);
                    // Try to show an acronym/short code if present
                    $heiAcronym = htmlspecialchars($hei['HEI_code'] ?? $hei['inst_code'] ?? $hei['acronym'] ?? $hei['inst_acronym'] ?? '');
                    echo '<tr class="border-b last:border-0">';
                    // Name stacked with acronym
                    echo '<td class="py-4">';
                    echo '<div class="flex flex-col">';
                    echo '<div class="font-medium text-slate-900">'.$heiName.'</div>';
                    if (!empty($heiAcronym)) {
                      echo '<div class="text-xs text-slate-500">'.$heiAcronym.'</div>';
                    }
                    echo '</div>';
                    echo '</td>';

                    // Region
                    echo '<td class="py-4 text-slate-700">'.$heiRegion.'</td>';

                    // Type
                    echo '<td class="py-4 text-slate-700">'.$heiType.'</td>';

                    // Actions
                    $profileHref = 'institution-profile.php?hei_id='.$heiId;
                    echo '<td class="py-4">';
                    echo '<a href="'.$profileHref.'" class="inline-flex items-center gap-2 text-slate-700 hover:text-slate-900">';
                    echo '<i data-lucide="eye" class="w-4 h-4"></i><span>View Details</span>';
                    echo '</a>';
                    echo '</td>';

                    echo '</tr>';
                }

                ?>

                <!-- AJAX filter submission script -->
                <script>
                (function(){
                  const form = document.querySelector('form'); // the filter form
                  const loading = document.getElementById('heisLoading');
                  async function submitAjax(e){
                    e.preventDefault();
                    try {
                      loading.classList.remove('hidden');
                      const fd = new FormData(form);
                      // include the applyFilters value if server checks this name
                      fd.set('applyFilters', '1');
                
                      const resp = await fetch(window.location.href, {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                      });
                      const html = await resp.text();
                      const parser = new DOMParser();
                      const doc = parser.parseFromString(html, 'text/html');
                      const newTbody = doc.getElementById('heisTbody');
                      const newPager = doc.getElementById('pager');
                      const newCount = doc.getElementById('institutionsCount');
                      if (newTbody) {
                        document.getElementById('heisTbody').innerHTML = newTbody.innerHTML;
                        if (newPager) document.getElementById('pager').innerHTML = newPager.innerHTML || '';
                        if (newCount) {
                          const countEl = document.getElementById('institutionsCount');
                          if (countEl) countEl.textContent = newCount.textContent;
                        }
                        // Re-render lucide icons in newly-inserted content
                        if (window.lucide && typeof lucide.createIcons === 'function') {
                          lucide.createIcons();
                        }
                      } else {
                        // fallback to full page navigation if response lacks expected elements
                        window.location.reload();
                      }
                    } catch (err) {
                      console.error('Filter fetch failed', err);
                      window.location.reload();
                    } finally {
                      loading.classList.add('hidden');
                    }
                  }
                  form.addEventListener('submit', submitAjax);

                  // Reset button: set selects to 'all' and resubmit via AJAX
                  const resetBtn = document.getElementById('resetFilters');
                  if (resetBtn) {
                    resetBtn.addEventListener('click', () => {
                      const regionSel = document.getElementById('region');
                      const typeSel = document.getElementById('instType');
                      if (regionSel) regionSel.value = 'all';
                      if (typeSel) typeSel.value = 'all';
                      form.requestSubmit();
                    });
                  }
                })();
                </script>

              </tbody>
            </table>
          </div>
          <footer class="px-5 pb-5">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <button id="prevPage" class="px-3 py-2 rounded border">Prev</button>
                <button id="nextPage" class="px-3 py-2 rounded border">Next</button>
              </div>
            </div>
          </footer>
        </section>
      </div>
    </main>
  </div>

  <?php echo $sweetAlertConfig; ?>
  <script>
    // Initialize lucide icons on initial load
    if (window.lucide && typeof lucide.createIcons === 'function') {
      lucide.createIcons();
    }
  </script>
  </body>
</html>