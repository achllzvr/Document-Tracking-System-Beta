<?php

// Development: show errors so we can see what causes HTTP 500 locally
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Start the session
session_start();

// Check for existing session
if (!isset($_SESSION['chedID']) || isset($_SESSION['heiID'])) {
  
  if (!isset($_SESSION['chedID'])) {
    // If a CHED user is logged in, redirect to CHED dashboard
    header("Location: ../php/ched_login.php");
    exit();
  } elseif (isset($_SESSION['heiID'])) {
    // If an HEI user is logged in, redirect to HEI dashboard
    header("Location: ../php/hei_login.php");
    exit();
  }

}

// Database connection
require_once('../classes/database.php');

// Instance of the database class
$con = new database();

// Alert Initialization
$sweetAlertConfig = "";

// Set User Name from Session
$userName = isset($_SESSION['chedName']) ? $_SESSION['chedName'] : 'Unknown User';



?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CHED PRISM – HEIs</title>
    <link rel="icon" type="image/png" href="../assets/media/ched_logo.png" />
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
          <div class="p-5 grid md:grid-cols-4 gap-4">
            <div class="relative md:col-span-1">
              <i data-lucide="search" class="absolute left-3 top-3 h-4 w-4 text-slate-400"></i>
              <input id="search" class="w-full pl-9 px-3 py-2 rounded border" placeholder="Search by name..." />
            </div>
            <select id="region" class="px-3 py-2 rounded border">
              <?php

                // Fetch all regions from the database
                $regions = $con->fetchAllRegions();
                echo '<option value="all">All Regions</option>';
                foreach ($regions as $region) {
                  $regionID = htmlspecialchars($region['region_ID']);
                  $regionNumber = htmlspecialchars($region['region_number']);
                  $regionDivision = htmlspecialchars($region['region_division']);
                  echo "<option value=\"$regionID\">Region $regionNumber - $regionDivision</option>";
                }

              ?>
            </select>
            <select id="instType" class="px-3 py-2 rounded border">
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
          </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
          <header class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
            <div>
              <h2 class="font-semibold">Institutions</h2>
              <p class="text-sm text-slate-500">Browse and manage institutional profiles</p>
            </div>
            <div id="pager" class="text-sm text-slate-600"></div>
          </header>
          <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="text-slate-600 border-b">
                <tr>
                  <th class="py-2">Name</th>
                  <th>Region</th>
                  <th>Type</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php

                // Read filter values from query string if provided (prevent undefined variable warnings)
                $regionId = isset($_GET['region']) && $_GET['region'] !== 'all' ? (int)$_GET['region'] : null;
                $typeId = isset($_GET['instType']) && $_GET['instType'] !== 'all' ? (int)$_GET['instType'] : null;

                $heis = $con->fetchInstitutions($regionId, $typeId);

                foreach ($heis as $hei) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($hei['HEI_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($hei['HEI_region']) . "</td>";
                    echo "<td>" . htmlspecialchars($hei['HEI_type']) . "</td>";
                    echo "<td><button class='text-blue-600'>Edit</button></td>";
                    echo "</tr>";
                }

                ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

</body>
</html>