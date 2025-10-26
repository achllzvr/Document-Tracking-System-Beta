<?php

class database{

    // Open database connection
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

    // CHED Funcitons

    // Account Functions

    //Login
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

    // CHED Dashboard Page Functions

    // Get total number of HEIs
    function getTotalHEIs(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM institutional_profile_data");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0; 
    }

    // Get total number of pending tickets
    function getTotalPendingTickets(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM tickets WHERE ticket_status = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    // Recent Tickets Fetch
    function getRecentTickets(){
        $conn = $this->opencon();
        $stmt = $conn->prepare("SELECT t.ticket_ID, t.hei_ID, h.inst_name AS hei_name, t.ticket_title, t.ticket_category, t.ticket_priority, t.ticket_status, t.ticket_created_at 
                                FROM tickets t
                                JOIN institutional_profile_data h ON t.hei_ID = h.hei_ID
                                ORDER BY t.ticket_created_at DESC 
                                LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get total number of enrollment records updated in the current week
    function getTotalEnrollmentUpdates(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM update_history u 
                                JOIN enrollment_data e ON u.update_ID = e.enr_udd_id
                                WHERE e.enr_udd_id >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    // Get total number of faculty records updated in the current week
    function getTotalFacultyUpdates(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM update_history u 
                                JOIN faculty_data f ON u.update_ID = f.fac_udd_id
                                WHERE f.fac_udd_id >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    // Get total number of graduates records updated in the current week
    function getTotalGraduatesUpdates(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT COUNT(*) as total FROM update_history u 
                                JOIN graduates_data g ON u.update_ID = g.grad_udd_id
                                WHERE g.grad_udd_id >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    // CHED Institutions Page Functions

    // Fetch all Region Names
    function fetchAllRegions(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT DISTINCT region_ID, region_number, region_division FROM national_regions ORDER BY region_number");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch all institution types
    function fetchAllInstitutionTypes(){
        $conn = $this->opencon();
        $stmt = $conn->query("SELECT DISTINCT inst_type_ID, inst_type_code, inst_type_desc FROM institution_type ORDER BY inst_type_code");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch Institutions
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

    /*
    ======================================================================
    TODO: Missing backend functions (stubs / signatures) required by pages
    ======================================================================

    Follow the same pattern used above (use $this->opencon(), prepared
    statements, return associative arrays or booleans). Implement these
    functions below when ready. They are grouped by feature/page and
    include suggested signatures and short notes.

    Authentication & Account
    ------------------------
    // function loginHEIUser($email, $password)
    // function createCHEDUser($data)
    // function createHEIUser($heiId, $data)
    // function updateUser($userId, $fields)
    // function deleteUser($userId)
    // function resetPasswordCHED($userId, $newPassword)
    // function resetPasswordHEI($userId, $newPassword)

    Tickets (CHED & HEI)
    ---------------------
    // function getTickets($filters = [], $page = 1, $perPage = 25)
    // function getTicketById($ticketId)
    // function createTicket($data)
    // function updateTicket($ticketId, $data)
    // function changeTicketStatus($ticketId, $status, $updatedBy)
    // function assignTicket($ticketId, $assigneeId)

    Comments & Notifications
    ------------------------
    // function getCommentsForTicket($ticketId)
    // function addComment($ticketId, $userType, $userId, $content)
    // function getNotificationsForUser($userId, $userType)
    // function createNotification($userId, $type, $title, $message, $link)
    // function markNotificationRead($notifId)

    HEI / Institutions
    -------------------
    // function getHEIs($filters = [])
    // function getInstitutionProfile($heiId)
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

}