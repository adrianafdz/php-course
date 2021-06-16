<?php 
	session_start();
	require_once "connection.php";

	if ( !isset($_SESSION['name']) ) {
		die('ACCESS DENIED');
	}

	$query = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :pid");

	$query->execute(array(
		':pid' => $_GET['profile_id']
	));

	$row = $query->fetch(PDO::FETCH_ASSOC);

	if ($row === false) {
		$_SESSION['error'] = "Could not load profile";
		header("Location: index.php");
		return;
	} else if ( $row['user_id'] != $_SESSION['user_id']) {
		$_SESSION['error'] = "You cannot edit this profile";
		header("Location: index.php");
		return;
	}

	if ( isset($_POST['cancel']) ) {
		header("Location: index.php");
	}

	if ( isset($_POST['delete']) ) {

		$stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
		$stmt->execute(array( ':pid' => $row['profile_id']));

		$query = $pdo->prepare("DELETE FROM profile WHERE profile_id = :pid");
		$query->execute(array(
			':pid' => $row['profile_id']
		));

		$_SESSION['success'] = "Record deleted";
		header("Location: index.php");
	}

?>


<!DOCTYPE html>
<html>
<head>
	<title>Adriana Fernández López 0ea326a4</title>
	<?php require_once "bootstrap.php"; ?>
</head>
<body>
	<div class="container">
		<h1>Deleting Profile</h1>


		<p>First Name: <?= htmlentities($row['first_name']); ?></p>
		<p>Last Name: <?= htmlentities($row['last_name']); ?></p>

		<form method="POST">
			<input type="hidden" name="pid" value="<?= $row['profile_id'] ?>">
			<input type="submit" name="delete" value="Delete">
			<input type="submit" name="cancel" value="Cancel">
		</form>

	</div>

</body>
</html>