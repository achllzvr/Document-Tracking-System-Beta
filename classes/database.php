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

    // Fetch Institutions with optional region/type filters
    function fetchInstitutions($regionId = null, $typeId = null){
        $conn = $this->opencon();
        $query = "SELECT ip.inst_name as HEI_name, ip.inst_region as HEI_region, it.inst_type_desc as HEI_type FROM institutional_profile_data ip JOIN institution_type it ON it.inst_type_ID = ip.inst_type";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}