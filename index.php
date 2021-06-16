<?php 

session_start();
require_once "connection.php";
require_once "util.php";

?>


<!DOCTYPE html>
<html>
<head>
	<title>Adriana Fernández López</title>
	<?php require_once "bootstrap.php"; ?>
</head>
<body>

<div class="container mb-5">
	<h1>Resume Registry</h1>
	<?php 
		flashMessages();
		
		if ( !isset($_SESSION['name']) ) { 
			echo '<a href="login.php">Please log in</a>';
			$query = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM profile");

			if ($query->rowCount() > 0) {

				echo '<table border="1">';
				echo '<tr><th>Name</th><th>Headline</th></tr>';
				while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
					$txt = '<tr><td><a href="view.php?profile_id='.$row['profile_id'].'" >'.htmlentities($row['first_name']).' ';
					$txt .= htmlentities($row['last_name']).'</td>';
					$txt .= '<td>'.htmlentities($row['headline']).'</td></tr>';
					echo $txt;
				}
				echo '</table>';

			}
		} else {
			echo '<a href="logout.php">Logout</a> ';
			
			$query = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM profile");

			if ($query->rowCount() > 0) {

				echo '<table border="1">';
				echo '<tr><th>Name</th><th>Headline</th><th>Action</th></tr>';
				while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
					$txt = '<tr><td><a href="view.php?profile_id='.$row['profile_id'].'" >'.htmlentities($row['first_name']).' ';
					$txt .= htmlentities($row['last_name']).'</a></td>';
					$txt .= '<td>'.htmlentities($row['headline']).'</td>';
					$txt .= '<td><a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> <a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a></td></tr>';
					echo $txt;
				}
				echo '</table>';
			}

			echo '<a href="add.php">Add New Entry</a>';
		}
	?>
	
</div>

</body>
</html>