<?php

/**
 * =============================================================
 * DATABASE HELPER CLASS
 *
 * Function Organization Guide:
 * - [CHED] ... : Used by CHED users/pages
 * - [HEI]  ... : Used by HEI users/pages
 * - [SHARED] ... : Used by both user types or system-wide
 *
 * Each section is grouped by feature/page for easier navigation.
 * =============================================================
 */
class database{

    // =============================================================
    // [SHARED] Core: Database Connection
    // =============================================================
    /**
     * Open database connection
     * (SHARED) Used by all pages
     */
    function opencon(){
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=CHED_document_repository;charset=utf8mb4', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            // Re-throw so calling code fails loudly (HTTP 500) and we have an error log
            throw $e;
        }
    }

    /**
     * Fetch enrollment_data rows associated with a ticket_ID
     * Returns array of associative rows.
     */
    function getEnrollmentRowsByTicket($ticketId, $limit = null, $offset = 0){
        $conn = $this->opencon();
        try{
            $sql = "SELECT enroll_ID, hei_ID, enr_acad_year, enr_term, enr_program, enr_program_major, enr_year_level, enr_sex, enr_total_count, enr_udd_ID, ticket_ID, enr_created_at FROM enrollment_data WHERE ticket_ID = ? ORDER BY enr_program, enr_year_level, enr_sex";
            
            if ($limit !== null) {
                // Cast to integers and append to SQL (cannot bind LIMIT/OFFSET as parameters in PDO)
                $limitInt = (int)$limit;
                $offsetInt = (int)$offset;
                $sql .= " LIMIT {$limitInt} OFFSET {$offsetInt}";
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$ticketId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getEnrollmentRowsByTicket error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of enrollment rows for a ticket (for pagination)
     */
    function getEnrollmentRowsCountByTicket($ticketId){
        $conn = $this->opencon();
        try{
            $sql = "SELECT COUNT(*) as total FROM enrollment_data WHERE ticket_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$ticketId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        }catch(PDOException $e){
            error_log('getEnrollmentRowsCountByTicket error: ' . $e->getMessage());
            return 0;
        }
    }

    // =============================================================
    // [CHED] Account Functions (Login, User Management)
    // Pages: CHED Login, User Management
    // =============================================================

    //Login
    /**
     * Login CHED user
     * (CHED) ched_login.php
     */
    function loginCHEDUser($id, $password){
        $conn = $this->opencon();
        $stmt = $conn->prepare("SELECT ched_ID, ched_last_name, ched_first_name, ched_role, ched_password FROM ched_users WHERE ched_ID = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['ched_password'])) {
            return $user;
        } else {
            return false;
        }
    }

    // TODOS: Forgot Password, Change Password, Create User, Update User, Delete User

    // =============================================================
    // [CHED] Dashboard Page Functions
    // Pages: CHED Dashboard (ched-dashboard.php)
    // =============================================================

    // Get total number of HEIs
    /**
     * Get total number of HEIs
     * (CHED) ched-dashboard.php
     */
    function getTotalHEIs(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM institutional_profile_data");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0; 
    }

    // Get total number of pending tickets
    /**
     * Get total number of pending tickets
     * (CHED) ched-dashboard.php
     */
    function getTotalPendingTickets(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM tickets WHERE ticket_status = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    // Recent Tickets Fetch
    /**
     * Get recent tickets for dashboard
     * (CHED) ched-dashboard.php
     */
    function getRecentTickets(){
        $conn = $this->opencon();
        $stmt = $conn->prepare("SELECT t.ticket_ID AS id, t.hei_ID, h.inst_name AS hei_name, t.ticket_title, t.ticket_category, t.ticket_priority, t.ticket_status, t.ticket_created_at 
                                FROM tickets t
                                JOIN institutional_profile_data h ON t.hei_ID = h.hei_ID
                                ORDER BY t.ticket_created_at DESC 
                                LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get total number of enrollment records updated in the current week
    /**
     * Get total number of enrollment records updated in the current week
     * (CHED) ched-dashboard.php
     */
    function getTotalEnrollmentUpdates(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM update_history u 
                                JOIN enrollment_data e ON u.update_ID = e.enr_udd_ID
                                WHERE u.updated_at >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    // Get total number of faculty records updated in the current week
    /**
     * Get total number of faculty records updated in the current week
     * (CHED) ched-dashboard.php
     */
    function getTotalFacultyUpdates(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM update_history u 
                                JOIN faculty_data f ON u.update_ID = f.fac_udd_ID
                                WHERE u.updated_at >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    // Get total number of graduates records updated in the current week
    /**
     * Get total number of graduates records updated in the current week
     * (CHED) ched-dashboard.php
     */
    function getTotalGraduatesUpdates(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM update_history u 
                                JOIN graduates_data g ON u.update_ID = g.grad_udd_ID
                                WHERE u.updated_at >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    // =============================================================
    // [CHED] Institutions Page Functions
    // Pages: CHED Institutions (view-heis.php, manage-data-templates.php)
    // =============================================================

    // Fetch all Region Names
    /**
     * Fetch all Region Names
     * (CHED) view-heis.php, manage-data-templates.php, hei-analytics.php
     */
    function fetchAllRegions(){
        $conn = $this->opencon();
        try{
            $stmt = $conn->query("SELECT DISTINCT region_ID, region_number, region_division FROM national_regions ORDER BY region_number");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('fetchAllRegions error: ' . $e->getMessage());
            return [];
        }
    }

    // Fetch all institution types
    /**
     * Fetch all institution types
     * (CHED) view-heis.php, manage-data-templates.php
     */
    function fetchAllInstitutionTypes(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT DISTINCT inst_type_ID, inst_type_code, inst_type_desc FROM institution_type ORDER BY inst_type_code");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch Institutions
    /**
     * Fetch Institutions
     * (CHED) view-heis.php
     */
    function fetchInstitutions(){
        $conn = $this->opencon();
        $query = "SELECT ip.hei_ID as HEI_id, ip.inst_name as HEI_name, nr.region_number as HEI_region, it.inst_type_desc as HEI_type
                FROM institutional_profile_data ip
                JOIN institution_type it ON it.inst_type_ID = ip.inst_type
                JOIN national_regions nr ON nr.region_ID = ip.inst_region";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch Institutions with filter
    /**
     * Fetch Institutions with filter
     * (CHED) view-heis.php
     */
    function fetchInstitutionsFiltered($regionId = null, $typeId = null){
        $conn = $this->opencon();
        $query = "SELECT ip.hei_ID as HEI_id, ip.inst_name as HEI_name, nr.region_number as HEI_region, it.inst_type_desc as HEI_type
                FROM institutional_profile_data ip
                JOIN institution_type it ON it.inst_type_ID = ip.inst_type
                JOIN national_regions nr ON nr.region_ID = ip.inst_region
                WHERE nr.region_number = ? AND it.inst_type_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$regionId, $typeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =============================================================
    // [CHED] Institution Profile Page Functions
    // Pages: CHED institution-profile.php
    // =============================================================

    // Fetch Institution Profile by HEI ID
    /**
     * Fetch Institution Profile by HEI ID
     * (CHED) institution-profile.php
     */
    function getInstitutionProfile($heiId){
        $conn = $this->opencon();
        $sql = "SELECT i.*,
                CONCAT(region_number, ' - ', r.region_division) AS region_name,
                it.inst_type_desc,
                iht.title_name AS inst_head_title
        FROM institutional_profile_data i
        JOIN national_regions r ON r.region_ID = i.inst_region
        JOIN institution_type it ON it.inst_type_ID = i.inst_type
        JOIN institution_head_title iht ON iht.title_ID = i.inst_head_title
        WHERE i.hei_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$heiId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    // =============================================================
    // [SHARED] Ticket Pages Functions
    // Pages: Ticket listing/details (CHED & HEI), Create Ticket
    // =============================================================

    // Fetch Tickets
    /**
     * Fetch Tickets
     * (SHARED) view-tickets.php, ched-dashboard.php, hei-dashboard.php
     */
    function getTickets($filters = []){
        $conn = $this->opencon();

        $sql = "SELECT
                    t.ticket_ID AS id,
                    t.hei_ID,
                    t.ticket_title,
                    t.ticket_category,
                    t.ticket_priority,
                    t.ticket_status,
                    t.ticket_due_date,
                    ip.inst_name AS hei_name,
                    t.ticket_created_at
                FROM tickets t
                LEFT JOIN institutional_profile_data ip ON ip.hei_ID = t.hei_ID
                WHERE 1=1";

        $params = [];
        if (!empty($filters['hei_ID'])) {
            $sql .= " AND t.hei_ID = ?";
            $params[] = (int)$filters['hei_ID'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND t.ticket_category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND t.ticket_priority = ?";
            $params[] = $filters['priority'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.ticket_status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['due'])) {
            // match date portion only
            $sql .= " AND DATE(t.ticket_due_date) = ?";
            $params[] = $filters['due'];
        }

        $sql .= " ORDER BY t.ticket_created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =============================================================
    // [SHARED] HEI Dropdown/Selection Helpers
    // Pages: Ticket creation, filters, etc.
    // =============================================================
    /**
     * Fetch HEIs for dropdown
     * (SHARED) create-ticket.php, filters, hei-analytics.php, etc.
     */
    function getHEIs($filters = []){
        $conn = $this->opencon();
        try{
            $query = "SELECT hei_ID as id, inst_name as name
                      FROM institutional_profile_data";
            $params = [];
            $conditions = [];
            if (isset($filters['region'])) {
                $conditions[] = "inst_region = ?";
                $params[] = $filters['region'];
            }
            if (isset($filters['type'])) {
                $conditions[] = "inst_type = ?";
                $params[] = $filters['type'];
            }
            if ($conditions) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            $query .= " ORDER BY inst_name";
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getHEIs error: ' . $e->getMessage());
            return [];
        }
    }

    // =============================================================
    // [CHED] Create Ticket (CHED creates for HEI)
    // Pages: CHED create-ticket.php
    // =============================================================
    /**
     * Create Ticket
     * (CHED) create-ticket.php
     */
    function createTicket($heiID, $chedUserID, $title, $category, $priority, $dueDate, $description){
        $conn = $this->opencon();

        try{
            $conn->beginTransaction();

            $stmt = $conn->prepare("INSERT INTO tickets (hei_ID, ched_user_ID, ticket_title, ticket_category, ticket_priority, ticket_due_date, ticket_description) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$heiID, $chedUserID, $title, $category, $priority, $dueDate, $description]);

            $ticketID = $conn->lastInsertId();
            $conn->commit();

            return $ticketID;
        }catch (PDOException $e){
            $conn ->rollBack();
            return false;
        }
    }

    // =============================================================
    // [CHED] Template Management Functions
    // Pages: manage-data-templates.php
    // =============================================================

    /**
     * Fetch all templates (optionally filter by status/category)
     * (CHED) manage-data-templates.php
     */
    function getTemplates($filters = []){
        $conn = $this->opencon();
        $sql = "SELECT * FROM templates WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND template_category = ?";
            $params[] = $filters['category'];
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Upload a new template (insert row and file path)
     * (CHED) manage-data-templates.php
     */
    function uploadTemplate($meta){
        $conn = $this->opencon();
        // include template_version if provided
        $sql = "INSERT INTO templates (template_name, template_category, template_version, template_file_rel_path, status, template_udd_ID) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $ok = $stmt->execute([
            $meta['template_name'],
            $meta['template_category'] ?? null,
            $meta['template_version'] ?? null,
            $meta['template_file_rel_path'] ?? null,
            $meta['status'] ?? 'active',
            $meta['template_udd_ID'] ?? null
        ]);
        return $ok ? $conn->lastInsertId() : false;
    }

    /**
     * Soft delete a template by setting status to 'deprecated'.
     * (CHED) manage-data-templates.php
     */
    function deleteTemplate($templateId){
        $conn = $this->opencon();
        try {
            // record an update history row and attach it to the template
            $stmt = $conn->prepare("INSERT INTO update_history (updated_at) VALUES (NOW())");
            $stmt->execute();
            $udd = (int)$conn->lastInsertId();

            $sql = "UPDATE templates SET status = 'deprecated', template_udd_ID = ? WHERE template_ID = ?";
            $stmt = $conn->prepare($sql);
            return (bool)$stmt->execute([$udd, $templateId]);
        } catch (PDOException $e) {
            error_log('deleteTemplate error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Record a generic update in update_history and return the new update_ID
     * (SHARED) used by other features to attach *_udd_ID foreign keys
     */
    function recordUpdate(){
        $conn = $this->opencon();
        try {
            $stmt = $conn->prepare("INSERT INTO update_history (updated_at) VALUES (NOW())");
            $stmt->execute();
            return (int)$conn->lastInsertId();
        } catch (PDOException $e) {
            error_log('recordUpdate error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Permanently delete a template (requires password confirmation for CHED user)
     * (CHED) manage-data-templates.php
     */
    function deleteTemplatePermanently($templateId, $chedUserId, $password){
        $conn = $this->opencon();
        // Verify password for CHED user
        $stmt = $conn->prepare("SELECT ched_password FROM ched_users WHERE ched_ID = ? LIMIT 1");
        $stmt->execute([$chedUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !password_verify($password, $row['ched_password'])) {
            return false;
        }
        // Delete template
        $sql = "DELETE FROM templates WHERE template_ID = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$templateId]);
    }

    /**
     * Reactivate a deprecated template (requires CHED password confirmation)
     * Returns true on success, false on failure (invalid password or DB error)
     */
    function reactivateTemplate($templateId, $chedUserId, $password){
        $conn = $this->opencon();
        // Verify password for CHED user
        $stmt = $conn->prepare("SELECT ched_password FROM ched_users WHERE ched_ID = ? LIMIT 1");
        $stmt->execute([$chedUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !password_verify($password, $row['ched_password'])) {
            return false;
        }

        try {
            $conn->beginTransaction();
            // record update history row
            $ustmt = $conn->prepare("INSERT INTO update_history (updated_at) VALUES (NOW())");
            $ustmt->execute();
            $udd = (int)$conn->lastInsertId();

            $sql = "UPDATE templates SET status = 'active', template_udd_ID = ? WHERE template_ID = ?";
            $stmt = $conn->prepare($sql);
            $ok = (bool)$stmt->execute([$udd, $templateId]);
            $conn->commit();
            return $ok;
        } catch (PDOException $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            error_log('reactivateTemplate error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch templates attached to a ticket
     * Returns array of template rows
     */
    function getTemplatesForTicket($ticketId){
        $conn = $this->opencon();
        try{
            $sql = "SELECT t.* FROM ticket_templates tt JOIN templates t ON tt.template_ID = t.template_ID WHERE tt.ticket_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$ticketId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getTemplatesForTicket error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Replace the templates attached to a ticket (delete existing and insert provided list)
     * $templateIds should be an array of numeric template_IDs.
     * Returns true on success.
     */
    function setTemplatesForTicket($ticketId, $templateIds = [], $updatedBy = null){
        $conn = $this->opencon();
        try{
            $conn->beginTransaction();

            // delete existing
            $dstmt = $conn->prepare("DELETE FROM ticket_templates WHERE ticket_ID = ?");
            $dstmt->execute([$ticketId]);

            // insert new ones
            if (!empty($templateIds)){
                $istmt = $conn->prepare("INSERT INTO ticket_templates (ticket_ID, template_ID) VALUES (?, ?)");
                foreach ($templateIds as $tid){
                    $istmt->execute([$ticketId, (int)$tid]);
                }
            }

            // record update history and attach to tickets.ticket_udd_ID if present
            $ustmt = $conn->prepare("INSERT INTO update_history (updated_at) VALUES (NOW())");
            $ustmt->execute();
            $udd = (int)$conn->lastInsertId();

            // if tickets has ticket_udd_ID column, set it
            $colCheck = $conn->query("SHOW COLUMNS FROM tickets LIKE 'ticket_udd_ID'")->fetchAll(PDO::FETCH_ASSOC);
            if ($colCheck) {
                $tstmt = $conn->prepare("UPDATE tickets SET ticket_udd_ID = ? WHERE ticket_ID = ?");
                $tstmt->execute([$udd, $ticketId]);
            }

            $conn->commit();
            return true;
        }catch(PDOException $e){
            if ($conn->inTransaction()) $conn->rollBack();
            error_log('setTemplatesForTicket error: ' . $e->getMessage());
            return false;
        }
    }

    // =============================================================
    // [HEI] Account Functions (Login, User Management)
    // Pages: HEI Login, User Management
    // =============================================================

    // HEI Login
    /**
     * HEI Login
     * (HEI) hei_login.php
     */
    function loginHEIUser($email, $password){
        $conn = $this->opencon();
        $stmt = $conn->prepare("SELECT hei_user_ID, hei_ID, hei_first_name, hei_last_name, hei_role, hei_password FROM HEI_user WHERE hei_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['hei_password'])) {
            return $user;
        } else {
            return false;
        }
    }


    // =============================================================
    // [HEI] Dashboard & Ticket Helpers
    // Pages: HEI Dashboard, HEI ticket listing/details
    // =============================================================

    // --- Dashboard stats, ticket lists, ticket counts ---
    /**
     * Get aggregated dashboard stats for a specific HEI.
     * (HEI) hei-dashboard.php
     */
    function getHEIDashboardStats($heiId){
        $conn = $this->opencon();
        try{
            // Map provided heiId (could be institutional_profile_data.hei_ID) to tickets.hei_ID (which references HEI_user.hei_user_ID)
            $ticketHeiWhere = '';
            $ticketParams = [];
            // If the provided id exists as a HEI user id, use it directly
            $checkUser = $conn->prepare("SELECT 1 FROM HEI_user WHERE hei_user_ID = ? LIMIT 1");
            $checkUser->execute([$heiId]);
            if ($checkUser->fetch()) {
                $ticketHeiWhere = ' WHERE hei_ID = ?';
                $ticketParams = [$heiId];
            } else {
                // treat provided id as institutional_profile_data.hei_ID -> find HEI_user ids
                $uStmt = $conn->prepare("SELECT hei_user_ID FROM HEI_user WHERE hei_ID = ?");
                $uStmt->execute([$heiId]);
                $uids = $uStmt->fetchAll(PDO::FETCH_COLUMN);
                if (!$uids) {
                    // no users -> no tickets
                    return [
                        'totalTickets' => 0,
                        'open' => 0,
                        'pending' => 0,
                        'resolved' => 0,
                        'enrollmentUpdates' => 0,
                        'facultyUpdates' => 0,
                        'graduatesUpdates' => 0
                    ];
                }
                $placeholders = implode(',', array_fill(0, count($uids), '?'));
                $ticketHeiWhere = " WHERE hei_ID IN ($placeholders)";
                $ticketParams = $uids;
            }

            // ticket totals by status
            $stmt = $conn->prepare("SELECT
                                        COUNT(*) AS total,
                                        SUM(ticket_status = 0) AS open,
                                        SUM(ticket_status = 1) AS pending,
                                        SUM(ticket_status = 2) AS resolved
                                     FROM tickets" . $ticketHeiWhere);
            $stmt->execute($ticketParams);
            $t = $stmt->fetch(PDO::FETCH_ASSOC);

            // recent update counts for the HEI (week-to-date)
            // We assume enrollment_data, faculty_data, graduates_data have a column named hei_ID
            // and that update_history.update_ID links to *_udd_ID in those tables as in other helpers.
            $weekStart = "CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY"; // SQL expression

            $stmtEnr = $conn->prepare("SELECT COUNT(*) AS total FROM update_history u
                                        JOIN enrollment_data e ON u.update_ID = e.enr_udd_ID
                                        WHERE e.hei_ID = ? AND u.updated_at >= $weekStart");
            $stmtEnr->execute([$heiId]);
            $enr = $stmtEnr->fetch(PDO::FETCH_ASSOC);

            $stmtFac = $conn->prepare("SELECT COUNT(*) AS total FROM update_history u
                                        JOIN faculty_data f ON u.update_ID = f.fac_udd_ID
                                        WHERE f.hei_ID = ? AND u.updated_at >= $weekStart");
            $stmtFac->execute([$heiId]);
            $fac = $stmtFac->fetch(PDO::FETCH_ASSOC);

            $stmtGrad = $conn->prepare("SELECT COUNT(*) AS total FROM update_history u
                                        JOIN graduates_data g ON u.update_ID = g.grad_udd_ID
                                        WHERE g.hei_ID = ? AND u.updated_at >= $weekStart");
            $stmtGrad->execute([$heiId]);
            $grad = $stmtGrad->fetch(PDO::FETCH_ASSOC);

            return [
                'totalTickets' => (int)($t['total'] ?? 0),
                'open' => (int)($t['open'] ?? 0),
                'pending' => (int)($t['pending'] ?? 0),
                'resolved' => (int)($t['resolved'] ?? 0),
                'enrollmentUpdates' => (int)($enr['total'] ?? 0),
                'facultyUpdates' => (int)($fac['total'] ?? 0),
                'graduatesUpdates' => (int)($grad['total'] ?? 0)
            ];
        }catch(PDOException $e){
            error_log('getHEIDashboardStats error: ' . $e->getMessage());
            return [
                'totalTickets' => 0,
                'open' => 0,
                'pending' => 0,
                'resolved' => 0,
                'enrollmentUpdates' => 0,
                'facultyUpdates' => 0,
                'graduatesUpdates' => 0
            ];
        }
    }

    // =============================================================
    // [HEI] Enrollment data CRUD
    // =============================================================
    /**
     * Insert a single enrollment_data row and attach an update_history id.
     * Returns inserted ID or false on failure.
     */
    function createEnrollmentRow($heiId, $acadYear, $term, $program, $programMajor, $yearLevel, $sex, $totalCount, $ticketId = null){
        $conn = $this->opencon();
        try{
            $conn->beginTransaction();
            // record update history
            $stmt = $conn->prepare("INSERT INTO update_history (updated_at) VALUES (NOW())");
            $stmt->execute();
            $udd = (int)$conn->lastInsertId();

            // include ticket_ID if provided to link imported rows to a ticket
            if ($ticketId !== null) {
                $sql = "INSERT INTO enrollment_data (hei_ID, enr_acad_year, enr_term, enr_program, enr_program_major, enr_year_level, enr_sex, enr_total_count, enr_udd_ID, ticket_ID) VALUES (?,?,?,?,?,?,?,?,?,?)";
                $ist = $conn->prepare($sql);
                $ok = $ist->execute([
                    $heiId,
                    $acadYear,
                    $term,
                    $program,
                    $programMajor,
                    $yearLevel,
                    $sex,
                    $totalCount,
                    $udd,
                    $ticketId
                ]);
            } else {
                $sql = "INSERT INTO enrollment_data (hei_ID, enr_acad_year, enr_term, enr_program, enr_program_major, enr_year_level, enr_sex, enr_total_count, enr_udd_ID) VALUES (?,?,?,?,?,?,?,?,?)";
                $ist = $conn->prepare($sql);
                $ok = $ist->execute([
                    $heiId,
                    $acadYear,
                    $term,
                    $program,
                    $programMajor,
                    $yearLevel,
                    $sex,
                    $totalCount,
                    $udd
                ]);
            }
            $insertId = $ok ? (int)$conn->lastInsertId() : false;
            $conn->commit();
            return $insertId;
        }catch(PDOException $e){
            if ($conn->inTransaction()) $conn->rollBack();
            error_log('createEnrollmentRow error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert multiple enrollment_data rows in one transaction and attach a single update_history id.
     * $rows: array of associative arrays with keys: acad_year, term, program, program_major, year_level, sex, total_count
     * Returns number of inserted rows on success, or false on failure.
     */
    function createEnrollmentRowsBatch($heiId, $rows, $ticketId = null){
        if (empty($rows) || !is_array($rows)) return 0;
        $conn = $this->opencon();
        try{
            $conn->beginTransaction();
            // one update history for the whole batch
            $ustmt = $conn->prepare("INSERT INTO update_history (updated_at) VALUES (NOW())");
            $ustmt->execute();
            $udd = (int)$conn->lastInsertId();

            // choose SQL depending on whether we should store ticket_ID
            if ($ticketId !== null) {
                $sql = "INSERT INTO enrollment_data (hei_ID, enr_acad_year, enr_term, enr_program, enr_program_major, enr_year_level, enr_sex, enr_total_count, enr_udd_ID, ticket_ID) VALUES (?,?,?,?,?,?,?,?,?,?)";
            } else {
                $sql = "INSERT INTO enrollment_data (hei_ID, enr_acad_year, enr_term, enr_program, enr_program_major, enr_year_level, enr_sex, enr_total_count, enr_udd_ID) VALUES (?,?,?,?,?,?,?,?,?)";
            }
            $ist = $conn->prepare($sql);
            $inserted = 0;
            foreach ($rows as $r){
                if ($ticketId !== null) {
                    $params = [
                        $heiId,
                        $r['acad_year'] ?? null,
                        $r['term'] ?? null,
                        $r['program'] ?? null,
                        $r['program_major'] ?? null,
                        $r['year_level'] ?? null,
                        $r['sex'] ?? null,
                        $r['total_count'] ?? null,
                        $udd,
                        $ticketId
                    ];
                } else {
                    $params = [
                        $heiId,
                        $r['acad_year'] ?? null,
                        $r['term'] ?? null,
                        $r['program'] ?? null,
                        $r['program_major'] ?? null,
                        $r['year_level'] ?? null,
                        $r['sex'] ?? null,
                        $r['total_count'] ?? null,
                        $udd
                    ];
                }
                $ok = $ist->execute($params);
                if ($ok) $inserted++;
            }
            $conn->commit();
            return $inserted;
        }catch(PDOException $e){
            if ($conn->inTransaction()) $conn->rollBack();
            error_log('createEnrollmentRowsBatch error: ' . $e->getMessage());
            return false;
        }
    }

    // --- Ticket list for HEI ---
    /**
     * Fetch tickets for a specific HEI with optional filters and pagination.
     * (HEI) hei-dashboard.php, view-tickets.php
     */
    function getTicketsForHEI($heiId, $filters = [], $page = 1, $perPage = 25){
        $conn = $this->opencon();
        $offset = max(0, ($page - 1) * $perPage);

        // Determine whether $heiId is a HEI user id (HEI_user.hei_user_ID) or an institution id (institutional_profile_data.hei_ID)
        $params = [];
        $baseWhere = '';
        if (!$heiId) {
            return ['rows'=>[], 'total'=>0];
        }

        // If heiId exists as a HEI_user.hei_user_ID, use directly; otherwise gather HEI_user ids for the institution
        $checkUser = $conn->prepare("SELECT 1 FROM HEI_user WHERE hei_user_ID = ? LIMIT 1");
        $checkUser->execute([$heiId]);
        if ($checkUser->fetch()) {
            $baseWhere = " WHERE t.hei_ID = ?";
            $params = [$heiId];
        } else {
            $uStmt = $conn->prepare("SELECT hei_user_ID FROM HEI_user WHERE hei_ID = ?");
            $uStmt->execute([$heiId]);
            $uids = $uStmt->fetchAll(PDO::FETCH_COLUMN);
            if (empty($uids)) {
                return ['rows'=>[], 'total'=>0];
            }
            $placeholders = implode(',', array_fill(0, count($uids), '?'));
            $baseWhere = " WHERE t.hei_ID IN ($placeholders)";
            $params = $uids;
        }

        if (!empty($filters['category'])){
            $baseWhere .= " AND t.ticket_category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['priority'])){
            $baseWhere .= " AND t.ticket_priority = ?";
            $params[] = $filters['priority'];
        }
        if (isset($filters['status']) && $filters['status'] !== ''){
            $baseWhere .= " AND t.ticket_status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['due'])){
            $baseWhere .= " AND DATE(t.ticket_due_date) = ?";
            $params[] = $filters['due'];
        }

        try{
            // total count for pagination
            $countSql = "SELECT COUNT(*) as total FROM tickets t" . $baseWhere;
            $countStmt = $conn->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // data rows
            $dataSql = "SELECT
                            t.ticket_ID AS id,
                            t.hei_ID,
                            t.ticket_title,
                            t.ticket_category,
                            t.ticket_priority,
                            t.ticket_status,
                            t.ticket_due_date,
                            ip.inst_name AS hei_name,
                            t.ticket_created_at
                        FROM tickets t
                        LEFT JOIN institutional_profile_data ip ON ip.hei_ID = t.hei_ID"
                        . $baseWhere . " ORDER BY t.ticket_created_at DESC LIMIT ? OFFSET ?";

            // bind pagination params separately
            $dataParams = $params;
            $dataParams[] = (int)$perPage;
            $dataParams[] = (int)$offset;

            $dataStmt = $conn->prepare($dataSql);
            $dataStmt->execute($dataParams);
            $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'rows' => $rows,
                'total' => $total
            ];
        }catch(PDOException $e){
            error_log('getTicketsForHEI error: ' . $e->getMessage());
            return ['rows' => [], 'total' => 0];
        }
    }

    // --- Fetch single ticket (used by both CHED and HEI) ---
    /**
     * Fetch a single ticket by its ID. Return associative row or null.
     * (SHARED) ticket-details.php (CHED & HEI)
     */
    function getTicketById($ticketId){
        $conn = $this->opencon();
        try{
            $sql = "SELECT t.*, ip.inst_name AS hei_name
                    FROM tickets t
                    LEFT JOIN institutional_profile_data ip ON ip.hei_ID = t.hei_ID
                    WHERE t.ticket_ID = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            return $ticket ?: null;
        }catch(PDOException $e){
            error_log('getTicketById error: ' . $e->getMessage());
            return null;
        }
    }

    // --- HEI creates ticket (HEI user) ---
    /**
     * Create a ticket on behalf of an HEI user. Return inserted ticket ID (int) or false on failure.
     * (HEI) create-ticket.php
     */
    function createTicketForHEI($heiId, $heiUserId, $title, $category, $priority, $dueDate, $description){
        $conn = $this->opencon();
        try{
            $conn->beginTransaction();

            // Build insert dynamically depending on available columns (support hei_user_ID or ched_user_ID nullable)
            $cols = ['hei_ID','ticket_title','ticket_category','ticket_priority','ticket_due_date','ticket_description'];
            $placeholders = array_fill(0, count($cols), '?');
            $values = [$heiId, $title, $category, $priority, $dueDate, $description];

            // If hei_user_ID column exists, include it
            $colCheck = $conn->query("SHOW COLUMNS FROM tickets LIKE 'hei_user_ID'")->fetchAll(PDO::FETCH_ASSOC);
            if ($colCheck) {
                array_unshift($cols, 'hei_user_ID');
                array_unshift($placeholders, '?');
                array_unshift($values, $heiUserId);
            } else {
                // keep compatibility: if ched_user_ID exists and allows NULL, we skip it (HEI created)
            }

            $sql = "INSERT INTO tickets (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            $stmt->execute($values);

            $ticketID = $conn->lastInsertId();

            // Optionally create an initial comment record if ticket_comments exists
            $commentsCheck = $conn->query("SHOW TABLES LIKE 'ticket_comments'")->fetchAll(PDO::FETCH_ASSOC);
            if ($commentsCheck) {
                // Use the centralized comment inserter to match schema
                $this->addCommentToTicket($ticketID, 'hei', $heiUserId, $description);
            }

            $conn->commit();
            return $ticketID;
        }catch(PDOException $e){
            if ($conn->inTransaction()) $conn->rollBack();
            error_log('createTicketForHEI error: ' . $e->getMessage());
            return false;
        }
    }

    // --- Update ticket fields (shared) ---
    /**
     * Update ticket fields. $fields is an associative array of column => value.
     * (SHARED) edit-ticket.php (CHED & HEI)
     */
    function updateTicket($ticketId, $fields){
        if (empty($fields) || !is_array($fields)) return false;
        $conn = $this->opencon();
        try{
            $sets = [];
            $params = [];
            foreach ($fields as $col => $val){
                $sets[] = "$col = ?";
                $params[] = $val;
            }
            $params[] = $ticketId;
            $sql = "UPDATE tickets SET " . implode(', ', $sets) . " WHERE ticket_ID = ?";
            $stmt = $conn->prepare($sql);
            return $stmt->execute($params);
        }catch(PDOException $e){
            error_log('updateTicket error: ' . $e->getMessage());
            return false;
        }
    }

    // --- Change ticket status (CHED only, enforced in UI) ---
    /**
     * Change ticket status (e.g., open -> resolved). Return boolean success.
     * (CHED) ticket-details.php
     */
    function changeTicketStatus($ticketId, $status, $updatedBy){
        $conn = $this->opencon();
        try{
            $conn->beginTransaction();
            $stmt = $conn->prepare("UPDATE tickets SET ticket_status = ? WHERE ticket_ID = ?");
            $ok = $stmt->execute([$status, $ticketId]);

            // If there is a ticket_status_history table, insert a history row
            $histCheck = $conn->query("SHOW TABLES LIKE 'ticket_status_history'")->fetchAll(PDO::FETCH_ASSOC);
            if ($histCheck) {
                $hstmt = $conn->prepare("INSERT INTO ticket_status_history (ticket_ID, status, changed_by, changed_at) VALUES (?,?,?,NOW())");
                $hstmt->execute([$ticketId, $status, $updatedBy]);
            }

            $conn->commit();
            return (bool)$ok;
        }catch(PDOException $e){
            if ($conn->inTransaction()) $conn->rollBack();
            error_log('changeTicketStatus error: ' . $e->getMessage());
            return false;
        }
    }

    // --- Add comment to ticket (shared) ---
    /**
     * Add a comment to a ticket. userType = 'hei'|'ched' etc. Return inserted comment ID or false.
     * (SHARED) ticket-details.php (CHED & HEI)
     */
    function addCommentToTicket($ticketId, $userType, $userId, $content){
        $conn = $this->opencon();
        try{
            $check = $conn->query("SHOW TABLES LIKE 'ticket_comments'")->fetchAll(PDO::FETCH_ASSOC);
            if (!$check) return false; // comments table not available

            // The actual schema uses columns: comment_ID, ticket_ID, ched_user_ID, hei_user_ID, comment_desc, comment_timestamp
            if ($userType === 'hei') {
                $stmt = $conn->prepare("INSERT INTO ticket_comments (ticket_ID, hei_user_ID, comment_desc, comment_timestamp) VALUES (?,?,?,NOW())");
                $stmt->execute([$ticketId, $userId, $content]);
            } else if ($userType === 'ched') {
                $stmt = $conn->prepare("INSERT INTO ticket_comments (ticket_ID, ched_user_ID, comment_desc, comment_timestamp) VALUES (?,?,?,NOW())");
                $stmt->execute([$ticketID = $ticketId, $userId, $content]);
            } else {
                // fallback: record as system comment without user id
                $stmt = $conn->prepare("INSERT INTO ticket_comments (ticket_ID, comment_desc, comment_timestamp) VALUES (?,?,NOW())");
                $stmt->execute([$ticketId, $content]);
            }
            return $conn->lastInsertId();
        }catch(PDOException $e){
            error_log('addCommentToTicket error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a ticket has saved enrollment records
     * Returns true if ticket has associated enrollment records, false otherwise
     */
    function checkTicketHasRecords($ticketId){
        $conn = $this->opencon();
        try{
            $sql = "SELECT COUNT(*) as count FROM enrollment_data WHERE ticket_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$ticketId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['count'] ?? 0) > 0;
        }catch(PDOException $e){
            error_log('checkTicketHasRecords error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete all enrollment records associated with a ticket
     * Returns number of deleted records or false on error
     */
    function deleteTicketRecords($ticketId){
        $conn = $this->opencon();
        try{
            $sql = "DELETE FROM enrollment_data WHERE ticket_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$ticketId]);
            return $stmt->rowCount();
        }catch(PDOException $e){
            error_log('deleteTicketRecords error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update ticket status to 'For Review' after HEI uploads records
     * Returns boolean success
     */
    function updateTicketStatusToForReview($ticketId){
        $conn = $this->opencon();
        try{
            $sql = "UPDATE tickets SET ticket_status = 'For Review' WHERE ticket_ID = ?";
            $stmt = $conn->prepare($sql);
            return $stmt->execute([$ticketId]);
        }catch(PDOException $e){
            error_log('updateTicketStatusToForReview error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reopen ticket as 'In Progress' and delete associated records
     * Returns array with ['success' => bool, 'deleted' => int]
     */
    function reopenTicketAndDeleteRecords($ticketId){
        $conn = $this->opencon();
        try{
            $conn->beginTransaction();
            
            // Delete associated records
            $deleteSql = "DELETE FROM enrollment_data WHERE ticket_ID = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->execute([$ticketId]);
            $deletedCount = $deleteStmt->rowCount();
            
            // Update status to In Progress
            $updateSql = "UPDATE tickets SET ticket_status = 'In Progress' WHERE ticket_ID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([$ticketId]);
            
            $conn->commit();
            return ['success' => true, 'deleted' => $deletedCount];
        }catch(PDOException $e){
            if ($conn->inTransaction()) $conn->rollBack();
            error_log('reopenTicketAndDeleteRecords error: ' . $e->getMessage());
            return ['success' => false, 'deleted' => 0];
        }
    }

    /**
     * Check if ticket due date has been missed and update status if no records exist
     * Returns array with ['is_missed' => bool, 'has_records' => bool, 'status_updated' => bool]
     */
    function checkAndUpdateMissedDueDate($ticketId){
        $conn = $this->opencon();
        try{
            // Get ticket details
            $ticketSql = "SELECT ticket_due_date, ticket_status FROM tickets WHERE ticket_ID = ? LIMIT 1";
            $ticketStmt = $conn->prepare($ticketSql);
            $ticketStmt->execute([$ticketId]);
            $ticket = $ticketStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ticket || empty($ticket['ticket_due_date'])) {
                return ['is_missed' => false, 'has_records' => false, 'status_updated' => false];
            }
            
            $dueDate = $ticket['ticket_due_date'];
            $currentDate = date('Y-m-d');
            $isMissed = $currentDate > $dueDate;
            
            if (!$isMissed) {
                return ['is_missed' => false, 'has_records' => false, 'status_updated' => false];
            }
            
            // Check if ticket has records
            $hasRecords = $this->checkTicketHasRecords($ticketId);
            
            // Only update status to 'Missed' if no records exist and not already in terminal states
            $statusUpdated = false;
            if (!$hasRecords && !in_array($ticket['ticket_status'], ['For Review', 'Closed', 'Missed'])) {
                $updateSql = "UPDATE tickets SET ticket_status = 'Missed' WHERE ticket_ID = ?";
                $updateStmt = $conn->prepare($updateSql);
                $statusUpdated = $updateStmt->execute([$ticketId]);
            }
            
            return ['is_missed' => true, 'has_records' => $hasRecords, 'status_updated' => $statusUpdated];
        }catch(PDOException $e){
            error_log('checkAndUpdateMissedDueDate error: ' . $e->getMessage());
            return ['is_missed' => false, 'has_records' => false, 'status_updated' => false];
        }
    }

    // --- Get comments for ticket (shared) ---
    /**
     * Get comments for a ticket. Return array of associative rows.
     * (SHARED) ticket-details.php (CHED & HEI)
     */
    function getCommentsForTicket($ticketId){
        $conn = $this->opencon();
        try{
            // Map actual ticket_comments schema to a normalized shape used by views:
            // comment_ID -> id
            // comment_desc -> comment
            // comment_timestamp -> created_at
            // hei_user_ID / ched_user_ID -> user_type + user_ID
            // Also join to HEI_user and ched_users to fetch display name when available.
            $sql = "SELECT
                        tc.comment_ID AS id,
                        tc.ticket_ID,
                        tc.ched_user_ID,
                        tc.hei_user_ID,
                        tc.comment_desc AS comment,
                        tc.comment_timestamp AS created_at,
                        CASE WHEN tc.hei_user_ID IS NOT NULL THEN 'hei' WHEN tc.ched_user_ID IS NOT NULL THEN 'ched' ELSE 'system' END AS user_type,
                        COALESCE(CONCAT(h.hei_first_name, ' ', h.hei_last_name), CONCAT(cu.ched_first_name, ' ', cu.ched_last_name), 'System') AS user_name,
                        COALESCE(tc.hei_user_ID, tc.ched_user_ID) AS user_ID
                    FROM ticket_comments tc
                    LEFT JOIN HEI_user h ON tc.hei_user_ID = h.hei_user_ID
                    LEFT JOIN ched_users cu ON tc.ched_user_ID = cu.ched_ID
                    WHERE tc.ticket_ID = ?
                    ORDER BY tc.comment_timestamp ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$ticketId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getCommentsForTicket error: ' . $e->getMessage());
            return [];
        }
    }

    // --- Recent tickets for HEI dashboard ---
    /**
     * Get recent tickets for an HEI (small list for dashboard).
     * (HEI) hei-dashboard.php
     */
    function getRecentTicketsForHEI($heiId, $limit = 5){
        $conn = $this->opencon();
        try{
            // Map institution id -> hei_user IDs if needed (tickets.hei_ID references HEI_user.hei_user_ID)
            $checkUser = $conn->prepare("SELECT 1 FROM HEI_user WHERE hei_user_ID = ? LIMIT 1");
            $checkUser->execute([$heiId]);
            if ($checkUser->fetch()) {
                $sql = "SELECT ticket_ID AS id, ticket_title, ticket_status, ticket_priority, ticket_created_at
                        FROM tickets
                        WHERE hei_ID = ?
                        ORDER BY ticket_created_at DESC
                        LIMIT ?";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(1, $heiId, PDO::PARAM_INT);
                $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $uStmt = $conn->prepare("SELECT hei_user_ID FROM HEI_user WHERE hei_ID = ?");
                $uStmt->execute([$heiId]);
                $uids = $uStmt->fetchAll(PDO::FETCH_COLUMN);
                if (empty($uids)) return [];
                $placeholders = implode(',', array_fill(0, count($uids), '?'));
                $sql = "SELECT ticket_ID AS id, ticket_title, ticket_status, ticket_priority, ticket_created_at
                        FROM tickets
                        WHERE hei_ID IN ($placeholders)
                        ORDER BY ticket_created_at DESC
                        LIMIT ?";
                $stmt = $conn->prepare($sql);
                $i = 1;
                foreach ($uids as $uid) $stmt->bindValue($i++, (int)$uid, PDO::PARAM_INT);
                $stmt->bindValue($i, (int)$limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }catch(PDOException $e){
            error_log('getRecentTicketsForHEI error: ' . $e->getMessage());
            return [];
        }
    }

    // --- Ticket status counts for HEI ---
    /**
     * Return counts grouped by status for an HEI (useful for quick badges).
     * (HEI) hei-dashboard.php
     */
    function getTicketCountsForHEI($heiId){
        $conn = $this->opencon();
        try{
            // Map institution id to hei_user ids if necessary
            $checkUser = $conn->prepare("SELECT 1 FROM HEI_user WHERE hei_user_ID = ? LIMIT 1");
            $checkUser->execute([$heiId]);
            if ($checkUser->fetch()) {
                $sql = "SELECT ticket_status, COUNT(*) AS cnt FROM tickets WHERE hei_ID = ? GROUP BY ticket_status";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$heiId]);
            } else {
                $uStmt = $conn->prepare("SELECT hei_user_ID FROM HEI_user WHERE hei_ID = ?");
                $uStmt->execute([$heiId]);
                $uids = $uStmt->fetchAll(PDO::FETCH_COLUMN);
                if (empty($uids)) return ['open'=>0,'pending'=>0,'resolved'=>0,'other'=>0,'total'=>0];
                $placeholders = implode(',', array_fill(0, count($uids), '?'));
                $sql = "SELECT ticket_status, COUNT(*) AS cnt FROM tickets WHERE hei_ID IN ($placeholders) GROUP BY ticket_status";
                $stmt = $conn->prepare($sql);
                $stmt->execute($uids);
            }
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $out = ['open'=>0,'pending'=>0,'resolved'=>0,'other'=>0];
            foreach($rows as $r){
                $s = (int)$r['ticket_status'];
                $c = (int)$r['cnt'];
                if ($s === 0) $out['open'] = $c;
                else if ($s === 1) $out['pending'] = $c;
                else if ($s === 2) $out['resolved'] = $c;
                else $out['other'] += $c;
            }
            $out['total'] = array_sum(array_values($out));
            return $out;
        }catch(PDOException $e){
            error_log('getTicketCountsForHEI error: ' . $e->getMessage());
            return ['open'=>0,'pending'=>0,'resolved'=>0,'other'=>0,'total'=>0];
        }
    }

    /*
    ======================================================================
    TODO: Missing backend functions (stubs / signatures) required by pages
    ======================================================================

    Authentication & Account
    ------------------------
    // function createCHEDUser($data)
    // function createHEIUser($heiId, $data)
    // function updateUser($userId, $fields)
    // function deleteUser($userId)
    // function resetPasswordCHED($userId, $newPassword)
    // function resetPasswordHEI($userId, $newPassword)

    Tickets (CHED & HEI)
    ---------------------
    // DONE function getTickets($filters = [], $page = 1, $perPage = 25)
    // DONE function getTicketById($ticketId)
    // DONE function updateTicket($ticketId, $data)
    // DONE function changeTicketStatus($ticketId, $status, $updatedBy)
    // function assignTicket($ticketId, $assigneeId)

    Comments & Notifications
    ------------------------
    // DONE function getCommentsForTicket($ticketId)
    // DONE function addComment($ticketId, $userType, $userId, $content)
    // function getNotificationsForUser($userId, $userType)
    // function createNotification($userId, $type, $title, $message, $link)
    // function markNotificationRead($notifId)

    HEI / Institutions
    -------------------
    // DONE function getHEIs($filters = [])
    // DONE function getInstitutionProfile($heiId)
    // function updateInstitutionProfile($heiId, $data)

    Enrollment / Faculty / Graduates (CRUD)
    ---------------------------------------
    // Enrollment
    // function listEnrollmentData($filters = [], $page = 1, $perPage = 25)
    // function getEnrollmentRow($enrollId)
    // function createEnrollmentRow($heiId, $rowData)
    // function updateEnrollmentRow($enrollId, $data)
    // function deleteEnrollmentRow($enrollId)

    // Faculty
    // function listFacultyData($filters = [], $page = 1, $perPage = 25)
    // function getFacultyRow($facultyId)
    // function createFacultyRow($heiId, $data)
    // function updateFacultyRow($facultyId, $data)
    // function deleteFacultyRow($facultyId)

    // Graduates
    // function listGraduatesData($filters = [], $page = 1, $perPage = 25)
    // function getGraduatesRow($graduatesId)
    // function createGraduatesRow($heiId, $data)
    // function updateGraduatesRow($graduatesId, $data)
    // function deleteGraduatesRow($graduatesId)

    Templates & File uploads
    ------------------------
    // function getTemplates()
    // function uploadTemplate($meta, $fileTmpPath)
    // function deleteTemplate($templateId)
    // function handleFileUpload($heiId, $ticketId, $uploadedFile)

    Code tables (for CHED CodeTables page)
    --------------------------------------
    // function getEmploymentCodes()
    // function createEmploymentCode($desc)
    // function updateEmploymentCode($id, $desc)
    // function deleteEmploymentCode($id)
    // function getDegreeCodes(), getDisciplineCodes(), getInstitutionTypes(), getOwnershipForms(), getRegions()
    // function getCodeTables()  // returns all code tables in one call

    HEI users & Sub-users
    ---------------------
    // function getUsersForHEI($heiId)
    // function createHEIUser($heiId, $data)
    // function updateHEIUser($userId, $data)
    // function deleteHEIUser($userId)
    // function setHEIUserStatus($userId, $active)

    Calendar events
    ---------------
    // function getCalendarEvents($heiId)
    // function createCalendarEvent($heiId, $title, $date, $type, $desc)
    // function updateCalendarEvent($calendarId, $data)
    // function deleteCalendarEvent($calendarId)

    Update history & helpers
    ------------------------
    // function recordUpdate()  // insert into update_history and return update_ID
    // function paginateQuery($baseSql, $params, $page, $perPage)
    // function safeQuery($sql, $params)

    Analytics helpers
    ------------------
    // function getEnrollmentAggregates($groupBy, $filters = [])
    // function getFacultyAggregates($groupBy, $filters = [])
    // function getGraduatesAggregates($groupBy, $filters = [])

    Attachments & storage
    ---------------------
    // function saveUploadRecord($heiId, $ticketId, $filename, $storedPath, $uploadedBy)
    // function getUploadsForTicket($ticketId)

    Misc / Utilities
    ----------------
    // function searchHEIs($query, $page, $perPage)
    // function searchTickets($query, $filters)
    // function getRecentActivity($limit = 10)

    ======================================================================
    Implementation notes:
    - Use transactions for operations that touch multiple tables (create ticket + comments + notifications).
    - Call recordUpdate() when modifying domain tables to store *_udd_ID consistently.
    - Return consistent shapes: arrays for lists, assoc arrays for single rows, int for created IDs, boolean for success/failure.
    - Always use prepared statements and parameter binding (PDO) as in existing functions.
    ======================================================================

    */

    // =============================================================
    // [HEI] Analytics Functions
    // Pages: HEI Analytics (hei-analytics.php)
    // =============================================================

    /**
     * Get enrollment analytics data for an HEI
     * Returns aggregated enrollment data by various dimensions
     * (HEI) hei-analytics.php
     */
    function getEnrollmentAnalytics($heiId, $filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        enr_acad_year,
                        enr_term,
                        enr_program,
                        enr_program_major,
                        enr_year_level,
                        enr_sex,
                        SUM(enr_total_count) as total_count
                    FROM enrollment_data
                    WHERE hei_ID = ?";
            
            $params = [$heiId];
            
            if (!empty($filters['acad_year'])){
                $sql .= " AND enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            if (!empty($filters['term'])){
                $sql .= " AND enr_term = ?";
                $params[] = $filters['term'];
            }
            if (!empty($filters['program'])){
                $sql .= " AND enr_program = ?";
                $params[] = $filters['program'];
            }
            
            $sql .= " GROUP BY enr_acad_year, enr_term, enr_program, enr_program_major, enr_year_level, enr_sex
                      ORDER BY enr_acad_year DESC, enr_program, enr_year_level";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getEnrollmentAnalytics error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get enrollment summary statistics for an HEI
     * (HEI) hei-analytics.php
     */
    function getEnrollmentSummary($heiId, $filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        COUNT(DISTINCT enr_program) as total_programs,
                        COUNT(DISTINCT enr_acad_year) as total_years,
                        SUM(enr_total_count) as total_students
                    FROM enrollment_data
                    WHERE hei_ID = ?";
            
            $params = [$heiId];
            
            if (!empty($filters['acad_year'])){
                $sql .= " AND enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getEnrollmentSummary error: ' . $e->getMessage());
            return ['total_programs' => 0, 'total_years' => 0, 'total_students' => 0];
        }
    }

    /**
     * Get available academic years for an HEI
     * (HEI) hei-analytics.php
     */
    function getAvailableAcademicYears($heiId){
        $conn = $this->opencon();
        try{
            $sql = "SELECT DISTINCT enr_acad_year
                    FROM enrollment_data
                    WHERE hei_ID = ?
                    ORDER BY enr_acad_year DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$heiId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }catch(PDOException $e){
            error_log('getAvailableAcademicYears error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available terms for an HEI
     * (HEI) hei-analytics.php
     */
    function getAvailableTerms($heiId){
        $conn = $this->opencon();
        try{
            $sql = "SELECT DISTINCT enr_term
                    FROM enrollment_data
                    WHERE hei_ID = ? AND enr_term IS NOT NULL
                    ORDER BY enr_term";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$heiId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }catch(PDOException $e){
            error_log('getAvailableTerms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available programs for an HEI
     * (HEI) hei-analytics.php
     */
    function getAvailablePrograms($heiId){
        $conn = $this->opencon();
        try{
            $sql = "SELECT DISTINCT enr_program
                    FROM enrollment_data
                    WHERE hei_ID = ? AND enr_program IS NOT NULL
                    ORDER BY enr_program";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$heiId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }catch(PDOException $e){
            error_log('getAvailablePrograms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get enrollment trend data by academic year
     * (HEI) hei-analytics.php
     */
    function getEnrollmentTrend($heiId){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        enr_acad_year,
                        SUM(enr_total_count) as total
                    FROM enrollment_data
                    WHERE hei_ID = ?
                    GROUP BY enr_acad_year
                    ORDER BY enr_acad_year ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$heiId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getEnrollmentTrend error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get enrollment distribution by program
     * (HEI) hei-analytics.php
     */
    function getEnrollmentByProgram($heiId, $filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        enr_program,
                        SUM(enr_total_count) as total
                    FROM enrollment_data
                    WHERE hei_ID = ?";
            
            $params = [$heiId];
            
            if (!empty($filters['acad_year'])){
                $sql .= " AND enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $sql .= " GROUP BY enr_program
                      ORDER BY total DESC
                      LIMIT 10";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getEnrollmentByProgram error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get enrollment distribution by sex
     * (HEI) hei-analytics.php
     */
    function getEnrollmentBySex($heiId, $filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        enr_sex,
                        SUM(enr_total_count) as total
                    FROM enrollment_data
                    WHERE hei_ID = ?";
            
            $params = [$heiId];
            
            if (!empty($filters['acad_year'])){
                $sql .= " AND enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $sql .= " GROUP BY enr_sex
                      ORDER BY enr_sex";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getEnrollmentBySex error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get enrollment distribution by year level
     * (HEI) hei-analytics.php
     */
    function getEnrollmentByYearLevel($heiId, $filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        enr_year_level,
                        SUM(enr_total_count) as total
                    FROM enrollment_data
                    WHERE hei_ID = ?";
            
            $params = [$heiId];
            
            if (!empty($filters['acad_year'])){
                $sql .= " AND enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $sql .= " GROUP BY enr_year_level
                      ORDER BY enr_year_level";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getEnrollmentByYearLevel error: ' . $e->getMessage());
            return [];
        }
    }

    // =============================================================
    // [CHED] Analytics Functions (System-wide)
    // Pages: CHED hei-analytics.php
    // =============================================================

    /**
     * Get system-wide enrollment analytics across all HEIs or filtered
     * (CHED) hei-analytics.php
     */
    function getCHEDEnrollmentAnalytics($filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        e.hei_ID,
                        i.inst_name,
                        i.inst_region,
                        e.enr_acad_year,
                        e.enr_term,
                        e.enr_program,
                        e.enr_year_level,
                        e.enr_sex,
                        SUM(e.enr_total_count) as total_count
                    FROM enrollment_data e
                    JOIN institutional_profile_data i ON i.hei_ID = e.hei_ID
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['hei_id'])){
                $sql .= " AND e.hei_ID = ?";
                $params[] = $filters['hei_id'];
            }
            if (!empty($filters['region'])){
                $sql .= " AND i.inst_region = ?";
                $params[] = $filters['region'];
            }
            if (!empty($filters['acad_year'])){
                $sql .= " AND e.enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            if (!empty($filters['term'])){
                $sql .= " AND e.enr_term = ?";
                $params[] = $filters['term'];
            }
            if (!empty($filters['program'])){
                $sql .= " AND e.enr_program = ?";
                $params[] = $filters['program'];
            }
            
            $sql .= " GROUP BY e.hei_ID, i.inst_name, i.inst_region, e.enr_acad_year, e.enr_term, e.enr_program, e.enr_year_level, e.enr_sex
                      ORDER BY e.enr_acad_year DESC, i.inst_name";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getCHEDEnrollmentAnalytics error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system-wide enrollment summary statistics
     * (CHED) hei-analytics.php
     */
    function getCHEDEnrollmentSummary($filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        COALESCE(COUNT(DISTINCT e.hei_ID), 0) as hei_count,
                        COALESCE(COUNT(DISTINCT e.enr_program), 0) as program_count,
                        COALESCE(SUM(e.enr_total_count), 0) as total_students
                    FROM enrollment_data e
                    JOIN institutional_profile_data i ON i.hei_ID = e.hei_ID
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['hei_id'])){
                $sql .= " AND e.hei_ID = ?";
                $params[] = $filters['hei_id'];
            }
            if (!empty($filters['region'])){
                $sql .= " AND i.inst_region = ?";
                $params[] = $filters['region'];
            }
            if (!empty($filters['acad_year'])){
                $sql .= " AND e.enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Ensure all values are integers, not null
            return [
                'hei_count' => (int)($result['hei_count'] ?? 0),
                'program_count' => (int)($result['program_count'] ?? 0),
                'total_students' => (int)($result['total_students'] ?? 0)
            ];
        }catch(PDOException $e){
            error_log('getCHEDEnrollmentSummary error: ' . $e->getMessage());
            return ['hei_count' => 0, 'program_count' => 0, 'total_students' => 0];
        }
    }

    /**
     * Get enrollment trend across all HEIs by academic year
     * (CHED) hei-analytics.php
     */
    function getCHEDEnrollmentTrend($filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        e.enr_acad_year as acad_year,
                        COALESCE(SUM(e.enr_total_count), 0) as student_count
                    FROM enrollment_data e
                    JOIN institutional_profile_data i ON i.hei_ID = e.hei_ID
                    WHERE e.enr_acad_year IS NOT NULL";
            
            $params = [];
            
            if (!empty($filters['hei_id'])){
                $sql .= " AND e.hei_ID = ?";
                $params[] = $filters['hei_id'];
            }
            if (!empty($filters['region'])){
                $sql .= " AND i.inst_region = ?";
                $params[] = $filters['region'];
            }
            
            $sql .= " GROUP BY e.enr_acad_year
                      ORDER BY e.enr_acad_year ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getCHEDEnrollmentTrend error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get enrollment by region
     * (CHED) hei-analytics.php
     */
    function getCHEDEnrollmentByRegion($filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        nr.region_number,
                        nr.region_division,
                        COALESCE(SUM(e.enr_total_count), 0) as student_count
                    FROM enrollment_data e
                    JOIN institutional_profile_data i ON i.hei_ID = e.hei_ID
                    JOIN national_regions nr ON nr.region_ID = i.inst_region
                    WHERE nr.region_number IS NOT NULL";
            
            $params = [];
            
            if (!empty($filters['hei_id'])){
                $sql .= " AND e.hei_ID = ?";
                $params[] = $filters['hei_id'];
            }
            if (!empty($filters['acad_year'])){
                $sql .= " AND e.enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $sql .= " GROUP BY nr.region_number, nr.region_division
                      ORDER BY student_count DESC
                      LIMIT 10";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getCHEDEnrollmentByRegion error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top HEIs by enrollment
     * (CHED) hei-analytics.php
     */
    function getCHEDTopHEIsByEnrollment($filters = [], $limit = 10){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        i.hei_ID,
                        i.inst_name as hei_name,
                        COALESCE(SUM(e.enr_total_count), 0) as student_count
                    FROM enrollment_data e
                    JOIN institutional_profile_data i ON i.hei_ID = e.hei_ID
                    WHERE i.inst_name IS NOT NULL";
            
            $params = [];
            
            if (!empty($filters['region'])){
                $sql .= " AND i.inst_region = ?";
                $params[] = $filters['region'];
            }
            if (!empty($filters['acad_year'])){
                $sql .= " AND e.enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $sql .= " GROUP BY i.hei_ID, i.inst_name
                      ORDER BY student_count DESC
                      LIMIT ?";
            
            $stmt = $conn->prepare($sql);
            foreach ($params as $i => $val) {
                $stmt->bindValue($i + 1, $val);
            }
            $stmt->bindValue(count($params) + 1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getCHEDTopHEIsByEnrollment error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get enrollment by program (top programs)
     * (CHED) hei-analytics.php
     */
    function getCHEDEnrollmentByProgram($filters = [], $limit = 10){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        e.enr_program as program,
                        COALESCE(SUM(e.enr_total_count), 0) as student_count
                    FROM enrollment_data e
                    JOIN institutional_profile_data i ON i.hei_ID = e.hei_ID
                    WHERE e.enr_program IS NOT NULL AND e.enr_program != ''";
            
            $params = [];
            
            if (!empty($filters['hei_id'])){
                $sql .= " AND e.hei_ID = ?";
                $params[] = $filters['hei_id'];
            }
            if (!empty($filters['region'])){
                $sql .= " AND i.inst_region = ?";
                $params[] = $filters['region'];
            }
            if (!empty($filters['acad_year'])){
                $sql .= " AND e.enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $sql .= " GROUP BY e.enr_program
                      ORDER BY student_count DESC
                      LIMIT ?";
            
            $stmt = $conn->prepare($sql);
            foreach ($params as $i => $val) {
                $stmt->bindValue($i + 1, $val);
            }
            $stmt->bindValue(count($params) + 1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getCHEDEnrollmentByProgram error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system-wide enrollment by sex
     * (CHED) hei-analytics.php
     */
    function getCHEDEnrollmentBySex($filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        e.enr_sex as sex,
                        SUM(e.enr_total_count) as student_count
                    FROM enrollment_data e
                    JOIN institutional_profile_data i ON i.hei_ID = e.hei_ID
                    WHERE e.enr_sex IS NOT NULL AND e.enr_sex != ''";
            
            $params = [];
            
            if (!empty($filters['hei_id'])){
                $sql .= " AND e.hei_ID = ?";
                $params[] = $filters['hei_id'];
            }
            if (!empty($filters['region'])){
                $sql .= " AND i.inst_region = ?";
                $params[] = $filters['region'];
            }
            if (!empty($filters['acad_year'])){
                $sql .= " AND e.enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $sql .= " GROUP BY e.enr_sex
                      ORDER BY e.enr_sex";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getCHEDEnrollmentBySex error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available academic years (system-wide)
     * (CHED) hei-analytics.php
     */
    function getCHEDAvailableAcademicYears(){
        $conn = $this->opencon();
        try{
            $sql = "SELECT DISTINCT enr_acad_year
                    FROM enrollment_data
                    ORDER BY enr_acad_year DESC";
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }catch(PDOException $e){
            error_log('getCHEDAvailableAcademicYears error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available terms (system-wide)
     * (CHED) hei-analytics.php
     */
    function getCHEDAvailableTerms(){
        $conn = $this->opencon();
        try{
            $sql = "SELECT DISTINCT enr_term
                    FROM enrollment_data
                    WHERE enr_term IS NOT NULL AND enr_term != ''
                    ORDER BY enr_term";
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }catch(PDOException $e){
            error_log('getCHEDAvailableTerms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available programs (system-wide)
     * (CHED) hei-analytics.php
     */
    function getCHEDAvailablePrograms(){
        $conn = $this->opencon();
        try{
            $sql = "SELECT DISTINCT enr_program
                    FROM enrollment_data
                    WHERE enr_program IS NOT NULL AND enr_program != ''
                    ORDER BY enr_program";
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }catch(PDOException $e){
            error_log('getCHEDAvailablePrograms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total pending tickets count (system-wide)
     * (CHED) hei-analytics.php
     */
    function getCHEDTotalPendingTicketsCount(){
        $conn = $this->opencon();
        try{
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE ticket_status IN (0, 1)";
            $stmt = $conn->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        }catch(PDOException $e){
            error_log('getCHEDTotalPendingTicketsCount error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get system-wide enrollment by year level
     * (CHED) hei-analytics.php
     */
    function getCHEDEnrollmentByYearLevel($filters = []){
        $conn = $this->opencon();
        try{
            $sql = "SELECT 
                        e.enr_year_level as year_level,
                        COALESCE(SUM(e.enr_total_count), 0) as student_count
                    FROM enrollment_data e
                    JOIN institutional_profile_data i ON i.hei_ID = e.hei_ID
                    WHERE e.enr_year_level IS NOT NULL AND e.enr_year_level != ''";
            
            $params = [];
            
            if (!empty($filters['hei_id'])){
                $sql .= " AND e.hei_ID = ?";
                $params[] = $filters['hei_id'];
            }
            if (!empty($filters['region'])){
                $sql .= " AND i.inst_region = ?";
                $params[] = $filters['region'];
            }
            if (!empty($filters['acad_year'])){
                $sql .= " AND e.enr_acad_year = ?";
                $params[] = $filters['acad_year'];
            }
            
            $sql .= " GROUP BY e.enr_year_level
                      ORDER BY e.enr_year_level";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            error_log('getCHEDEnrollmentByYearLevel error: ' . $e->getMessage());
            return [];
        }
    }

}

