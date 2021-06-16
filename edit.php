<?php  
	session_start();
	require_once "connection.php";
	require_once "util.php";
	require_once "head.php";

	if ( !isset($_SESSION['name']) ) {
		die('ACCESS DENIED');
	}

	if ( isset($_POST['cancel']) ) {
		header("Location: index.php");
	}

	$query = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :pid AND user_id = :uid");

	$query->execute(array(
		':pid' => $_GET['profile_id'],
		':uid' => $_SESSION['user_id']
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
	// Get current education
	$eduArr = $pdo->query("SELECT e.year, i.name FROM Education e JOIN Institution i ON e.institution_id = i.institution_id WHERE e.profile_id = ".$row['profile_id']);

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

		if (!validatePos() || !validateEdu()) {
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

		// Clear out the old education entries
		$stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
		$stmt->execute(array( ':pid' => $row['profile_id']));

		// Insert new positions
		insertPositions($pdo, $row['profile_id']);

		// Insert education
		insertEducation($pdo, $row['profile_id']);
		
		$_SESSION['success'] = "Profile edited";
		header("Location: index.php");
		
	}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Adriana Fernández López</title>
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
			<p style="margin-top: 15px">Education: <button onclick="addEducation(); return false;">+</button></p>

			<?php
			$i = 1;
			while ($e = $eduArr->fetch(PDO::FETCH_ASSOC)) {
				?>
				<div class="education" id="education<?= $i ?>">
					<p>Year: <input class="year" type="text" value="<?= htmlentities($e['year'])?>" name="edu_year<?= $i?>">
					<input type="button" value="-" 
				  onclick="$(this).closest('div').remove()"></p>
					School: <input type="text" size="80" class="school" value="<?= htmlentities($e['name'])?>" name="edu_school<?= $i ?>" />
				</div>

			<?php
			$i++;
			}
			?>

			<div id="educations">
				<template id="temp-edu">
					
					  <p>Year: <input class="year" type="text" value="">
					  <input class="btn" type="button" value="-"></p>
					  School: <input type="text" size="80" class="school" value="" />
					
				</template>			
			</div>
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
	let posArea = $('#positions');
	let posTemp = $('#temp').html();
	let posNum = $('.position').length;


	function addPosition() {
		if (posNum==9) {
			alert("You can't add more positions.");
			return;
		}

		let position = $('<div></div>');
		let id = `position${posNum}`;
		
		position.attr("id", id);
		position.append(posTemp);

		position.find(".year").attr("name", `year${posNum}`);
		position.find(".txta").attr("name", `desc${posNum}`);

		position.find(".btn").attr("onclick", `$('#${id}').remove(); return false;`);
		
		position.appendTo(posArea);
		posNum++;
	}

	let eduArea = $('#educations');
	let eduTemp = $('#temp-edu').html();
	let eduNum = $('.education').length;

	$('.school').autocomplete({
			source: "school.php"
		});

	function addEducation() {
		if (eduNum==9) {
			alert("You can't add more education.");
			return;
		}

		let edu = $('<div style="margin-bottom: 15px"></div>');
		let id = `education${eduNum}`;
		
		edu.attr("id", id);
		edu.append(eduTemp);

		edu.find(".year").attr("name", `edu_year${eduNum}`);
		edu.find(".school").attr("name", `edu_school${eduNum}`);

		edu.find(".btn").attr("onclick", `$('#${id}').remove(); return false;`);
		
		edu.appendTo(eduArea);

		$('.school').autocomplete({
			source: "school.php"
		});
		eduNum++;
	}
</script>

</html>