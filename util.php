<?php 

function flashMessages() {
    if ( isset($_SESSION['error']) ) {
        echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
        unset($_SESSION['error']); 
    }
    if ( isset($_SESSION['success']) ) {
        echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
        unset($_SESSION['success']); 
    }
}

function insertPositions($pdo, $profile_id) {
    $rank = 1;
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;

        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];
        $stmt = $pdo->prepare('INSERT INTO Position
            (profile_id, rank, year, description)
            VALUES ( :pid, :rank, :year, :desc)');

        $stmt->execute(array(
        ':pid' => $row['profile_id'],
        ':rank' => $rank,
        ':year' => $year,
        ':desc' => $desc)
        );

        $rank++;
    }   
}

function insertEducation($pdo, $profile_id) {
    $rank = 1;
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['edu_year'.$i]) ) continue;
        if ( ! isset($_POST['edu_school'.$i]) ) continue;

        $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :school');
        $stmt->execute(array(
            ':school' => $_POST['edu_school'.$i]
        ));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $inst_id = "";

        if (!$row) {
            $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:school)');
            $stmt->execute(array(
                ':school' => $_POST['edu_school'.$i]
            ));
            $inst_id = $pdo->lastInsertId();
        } else {
            $inst_id = $row['institution_id'];
        }

        $year = $_POST['edu_year'.$i];
        $stmt = $pdo->prepare('INSERT INTO Education
            (profile_id, institution_id, rank, year)
            VALUES ( :pid, :iid, :rank, :year)');

        $stmt->execute(array(
        ':pid' => $profile_id,
        ':iid' => $inst_id,
        ':rank' => $rank,
        ':year' => $year)
        );

        $rank++;
	}
}

function validatePos() {
    for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['year'.$i]) ) continue;
      if ( ! isset($_POST['desc'.$i]) ) continue;

      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];

      if ( strlen($year) == 0 || strlen($desc) == 0 ) {
            $_SESSION['error'] = "All fields are required";
          return false;
      }

      if ( ! is_numeric($year) ) {
            $_SESSION['error'] = "Position year must be numeric";
          return false;
      }
    }
    return true;
}

function validateEdu() {
  for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['edu_year'.$i]) ) continue;
    if ( ! isset($_POST['edu_school'.$i]) ) continue;

    $year = $_POST['edu_year'.$i];
    $desc = $_POST['edu_school'.$i];

    if ( strlen($year) == 0 || strlen($desc) == 0 ) {
          $_SESSION['error'] = "All fields are required";
        return false;
    }

    if ( ! is_numeric($year) ) {
          $_SESSION['error'] = "Education year must be numeric";
        return false;
    }
  }
  return true;
}


?>