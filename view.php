<?php  
	session_start();
	require_once "connection.php";

	$query = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :pid");

	$query->execute(array(
		':pid' => $_GET['profile_id']
	));

	$row = $query->fetch(PDO::FETCH_ASSOC);

	if ($row === false) {
		$_SESSION['error'] = "Could not load profile";
		header("Location: index.php");
		return;
	}

	// Get current positions
	$posArr = $pdo->query("SELECT * FROM Position WHERE profile_id = ".$row['profile_id']);

?>

<!DOCTYPE html>
<html>
<head>
	<title>Adriana Fernández López</title>
	<?php require_once "bootstrap.php"; ?>
</head>
<body>

	<div class="container">
		
		<h1>Profile information</h1>

		<p>First Name: <?= htmlentities($row['first_name']); ?></p>
		<p>Last Name: <?= htmlentities($row['last_name']); ?></p>
		<p>Email: <?= htmlentities($row['email']); ?></p>
		<p>Headline:</p>
		<p><?= htmlentities($row['headline']); ?></p>
		<p>Summary:</p>
		<p><?= htmlentities($row['summary']); ?></p>
		<p>Position</p>
		<ul>

			<?php
			$i = 1;
			while ($p = $posArr->fetch(PDO::FETCH_ASSOC)) {
			?>
				<li>
					<?= htmlentities($p['year']) ?>: <?= htmlentities($p['description']) ?>
				</li>
			<?php
			}
			?>

		</ul>

		<a href="index.php">Done</a>

	</div>

</body>
</html>