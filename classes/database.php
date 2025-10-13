<?php

class database{

    // Open database connection
    function opencon(){
        return new PDO(
            'mysql:host=127.0.0.1;
            dbname=CHED_document_repository',
            username: 'root',
            password:''
        );
    }

    // CHED Funcitons

    // Account Functions

    //Login
    function loginCHEDUser($id, $password){
        $conn = $this->opencon();
        $stmt = $conn->prepare("SELECT ched_id, ched_last_name, ched_first_name, ched_role, ched_password FROM ched_users WHERE ched_id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['ched_password'])) {
            return $user;
        } else {
            return false;
        }
    }

}