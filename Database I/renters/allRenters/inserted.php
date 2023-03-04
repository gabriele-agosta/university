<head>
    <title>AccaHousingME - Inserted</title>
    <link rel="stylesheet" href="../../styles/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet">
</head>

<body>
    <div class="content">    
    <?php
        include '../../coreComponents/header3.php';
        include '../../db.php';


        $conn->begin_transaction();
        try {
            $renterCheckQuery = "SELECT * FROM renter WHERE renter_name = ? AND renter_type = ? ";
            $renterCheck = $conn->prepare($renterCheckQuery);

            $name = trim($_POST['renter_name']);
            $renterType = trim($_POST['renter_type']);
            
            if (isset($_POST['renter_surname'])) {
                $surname = trim($_POST['renter_surname']);
                $renterCheckQuery .= " AND renter_surname = ?";
            } else {
                $corporateStructure = trim($_POST['corporate_structure']);
                $renterCheckQuery .= " AND corporate_structure = ?";
            }

            $renterCheck->bind_param("ss", $name, $renterType);
            if (isset($surname)) {
                $renterCheck->bind_param("s", $surname);
            } else {
                $renterCheck->bind_param("s", $corporateStructure);
            }

            echo $renterType;
            echo $renterCheckQuery;

            
            if ($renterCheck->execute()) {
                $result = $renterCheck->get_result();
                $renterExists = $result->num_rows > 0;
                $renterCheck->close();
            } else {
                throw new Exception($renterCheck->error);
            }

            if($renterExists){
                throw new Exception("Error: referent already exists.");
            }

            if(isset($surname)){
                $rentersQuery = "INSERT INTO renter(renter_name, renter_surname, renter_type) VALUES(?, ?, ?)";
            }
            else {
                $rentersQuery = "INSERT INTO renter(renter_name, corporate_structure, renter_type) VALUES(?, ?, ?)";
            }
            
            $stmt = $conn->prepare($rentersQuery);

            if(isset($surname)){
                $stmt->bind_param("sss", $name, $surname, $renterType);
            }
            else {
                $stmt->bind_param("sss", $name, $corporateStructure, $renterType);
            }
            
    
            if (!($stmt->execute())) {
                throw new Exception("Error: " . $conn->error);
            } 
            
            $stmt->close();
    
            $renterId = $conn->insert_id;
            $contactsQuery = "INSERT INTO renter_contact (value, type, renter_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($contactsQuery);
    
            for($i = 0; isset($_POST['type'.$i]); $i++){
                $type = trim($_POST['type'.$i]);
                $contactValue = trim($_POST['value'.$i]);
        
                if ($type == 'Email') {
                    $pattern = "/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/";
                    if (preg_match($pattern, $contactValue)) {
                        $emailCheckQuery = "SELECT * FROM referent_contact WHERE value = ?";
                        $emailCheck = $conn->prepare($emailCheckQuery);
                        $emailCheck->bind_param("s", $contactValue);

                        if ($emailCheck->execute()) {
                            $result = $emailCheck->get_result();
                            $emailExists = $result->num_rows > 0;
                            $emailCheck->close();
                        } else {
                            throw new Exception($conn->error);
                        }
            
                        if ($emailExists) {
                            throw new Exception("Email already exists.");
                        }
                    } 
                    else {
                        throw new Exception("Email must be in the right format.");
                    }
                }
                else {
                    if(!(preg_match('/^[0-9]{10}+$/', $contactValue))) {
                        throw new Exception("Phone number must be in the right format.");
                    }

                    $phoneCheckQuery = "SELECT * FROM referent_contact WHERE value = ?";
                    $phoneCheck = $conn->prepare($phoneCheckQuery);
                    $phoneCheck->bind_param("s", $contactValue);

                    if ($phoneCheck->execute()) {
                        $result = $phoneCheck->get_result();
                        $phoneExists = $result->num_rows > 0;
                        $phoneCheck->close();
                    } else {
                        throw new Exception($conn->error);
                    }
        
                    if ($phoneExists) {
                        throw new Exception("Phone already exists.");
                    }
                }
        
                if(isset($type) && isset($contactValue)){
                    $stmt->bind_param("ssi", $contactValue, $type, $renterId);
                    if (!($stmt->execute())) {
                        throw new Exception($conn->error);
                    } 
                }
            }
    
            $conn->commit();
            $stmt->close();
            echo "<script>location.href = 'landing.php';</script>";
        } 
        catch (Exception $e) {
            $conn->rollback();
            echo "<div class='error'>
                        Exception: ".$e->getMessage().". <br> <a href='insert.php' class='black'>Go back to the form</a>
                  </div>";
        }
    ?>

    <?php
        include '../../coreComponents/footer.php'
    ?>
</body>


