<?php  
	session_start();
	require_once "connection.php";
	require_once "util.php";

	if ( !isset($_SESSION['name']) ) {
		die('ACCESS DENIED');
	}

	if ( isset($_POST['cancel']) ) {
		header("Location: index.php");
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

	// Get current positions
	$posArr = $pdo->query("SELECT * FROM Position WHERE profile_id = ".$row['profile_id']);

	if ( isset($_POST['save'])) {

		if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 ||  strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
			$_SESSION['error'] = "All fields are required";
			header("Location: edit.php?profile_id=".$row['profile_id']);
			return;
		} 

		if ( strpos($_POST['email'], "@") === false ) {
			$_SESSION['error'] = "Email address must contain @";
			header("Location: edit.php?profile_id=".$row['profile_id']);
			return;
		} 

		$query = $pdo->prepare('UPDATE profile SET first_name = :fn , last_name = :ln , email = :em , headline = :he , summary = :su WHERE profile_id = :pid');
			
		$query->execute(array(
	        	':fn' => $_POST['first_name'],
	        	':ln' => $_POST['last_name'],
	        	':em' => $_POST['email'],
	        	':he' => $_POST['headline'],
	        	':su' => $_POST['summary'],
	        	':pid' => $row['profile_id']
		));

		// Clear out the old position entries
		$stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
		$stmt->execute(array( ':pid' => $row['profile_id']));

		// Insert new positions
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
		
		$_SESSION['success'] = "Profile edited";
		header("Location: index.php");
		
	}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Adriana Fernández López</title>
	<?php require_once "bootstrap.php"; ?>
</head>
<body>
	<div class="container" style="margin-bottom: 50px;">
		<h1>Editing Profile for UMSI</h1>

		<?php flashMessages(); ?>

		<form method="POST">
			<p>First Name: <input type="text" name="first_name" size="40" value="<?= htmlentities($row['first_name']) ?>"></p>
			<p>Last Name: <input type="text" name="last_name" size="40" value="<?= htmlentities($row['last_name']) ?>"></p>
			<p>Email: <input type="text" name="email" size="30" value="<?= htmlentities($row['email']) ?>"></p>
			<p>Headline</p>
			<input type="text" name="headline" size="50" value="<?= htmlentities($row['headline']) ?>">
			<p>Summary</p>
			<textarea name="summary" rows="10" cols="50"><?= htmlentities($row['summary']) ?></textarea><br>
			<p>Position: <button onclick="addPosition(); return false;">+</button></p>

			<?php
			$i = 1;
			while ($p = $posArr->fetch(PDO::FETCH_ASSOC)) {
				?>
				<div class="position" id="position<?= $i ?>">
				  <p>Year: <input type="text" name="year<?= $i ?>" value="<?= htmlentities($p['year']) ?>">
				  <input type="button" value="-" 
				  onclick="$(this).closest('div').remove()"></p>
				  <textarea name="desc<?= $i ?>" rows="8" cols="80"><?= htmlentities($p['description']) ?></textarea>
				</div>

			<?php
			$i++;
			}
			?>

			<div id="positions">
				<template id="temp">
					
					  <p>Year: <input class="year" type="text" value="">
					  <input class="btn" type="button" value="-"></p>
					  <textarea class="txta" rows="8" cols="80"></textarea>
					
				</template>			
			</div>

			<input type="submit" name="save" value="Save">
			<input type="submit" name="cancel" value="Cancel">
		</form>

	</div>
	
</body>
<script type="text/javascript">
	let area = $('#positions');
	let temp = $('#temp').html();
	let num = $('.position').length;

	function addPosition() {
		if (num==9) {
			alert("You can't add more positions.");
			return;
		}

		num += 1;

		let position = $('<div></div>');
		let id = `position${num}`;
		
		position.attr("id", id);
		position.append(temp);

		position.find(".year").attr("name", `year${num}`);
		position.find(".txta").attr("name", `desc${num}`);

		position.find(".btn").attr("onclick", `$('#${id}').remove(); return false;`);
		
		position.appendTo(area);
		num++;
	}
</script>

</html>