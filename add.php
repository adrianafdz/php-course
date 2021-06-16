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

if ( isset($_POST['add'])) {

	if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 ||  strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1 ) {
		$_SESSION['error'] = "All fields are required";
		header("Location: add.php");
		return;
	} 

	if ( strpos($_POST['email'], "@") === false ) {
		$_SESSION['error'] = "Email address must contain @";
		header("Location: add.php");
		return;
	} 

	if (!validatePos() || !validateEdu()) {
		header("Location: add.php");
		return;
	}

	$query = $pdo->prepare('INSERT INTO profile (user_id, first_name, last_name, email, headline, summary)
			VALUES ( :uid, :fn, :ln, :em, :he, :su)');
		
	$query->execute(array(
			':uid' => $_SESSION['user_id'],
        	':fn' => $_POST['first_name'],
        	':ln' => $_POST['last_name'],
        	':em' => $_POST['email'],
        	':he' => $_POST['headline'],
        	':su' => $_POST['summary']
	));

	$profile_id = $pdo->lastInsertId();

	// Insert new positions
	insertPositions($pdo, $row['profile_id']);

	// Insert education
	insertEducation($pdo, $row['profile_id']);

	$_SESSION['success'] = "Profile added";
	header("Location: index.php");
	return;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Adriana Fernández López</title>
</head>
<body>
	<div class="container" style="margin-bottom: 50px;">
		<h1>Adding Profile for <?= htmlentities($_SESSION['name']) ?></h1>

		<?php flashMessages(); ?>

		<form method="POST">
			<p>First Name: <input type="text" name="first_name" size="40"></p>
			<p>Last Name: <input type="text" name="last_name" size="40"></p>
			<p>Email: <input type="text" name="email" size="30"></p>
			<p>Headline</p>
			<input type="text" name="headline" size="50">
			<p>Summary</p>
			<textarea name="summary" rows="10" cols="50"></textarea><br>
			<p style="margin-top: 15px">Education: <button onclick="addEducation(); return false;">+</button></p>
			<div id="educations">
				<template id="temp-edu">
					
					  <p>Year: <input class="year" type="text" value="">
					  <input class="btn" type="button" value="-"></p>
					  School: <input type="text" size="80" class="school" value="" />
					
				</template>			
			</div>
			<p>Position: <button onclick="addPosition(); return false;">+</button></p>
			<div id="positions">
				<template id="temp">
					
					  <p>Year: <input class="year" type="text" value="">
					  <input class="btn" type="button" value="-"></p>
					  <textarea class="txta" rows="8" cols="80"></textarea>
					
				</template>			
			</div>
			<input type="submit" name="add" value="Add">
			<input type="submit" name="cancel" value="Cancel">
		</form>

	</div>

</body>

<script type="text/javascript">
	let posArea = $('#positions');
	let posTemp = $('#temp').html();
	let posNum = 1;

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
	let eduNum = 1;

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