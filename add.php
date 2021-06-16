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

function validatePos() {
  	for($i=1; $i<=9; $i++) {
    	if ( ! isset($_POST['year'.$i]) ) continue;
    	if ( ! isset($_POST['desc'.$i]) ) continue;

    	$year = $_POST['year'.$i];
    	$desc = $_POST['desc'.$i];

    	if ( strlen($year) == 0 || strlen($desc) == 0 ) {
      		$_SESSION['error'] = "All fields are required";
			header("Location: add.php");
			return;
    	}

    	if ( ! is_numeric($year) ) {
      		$_SESSION['error'] = "Position year must be numeric";
			header("Location: add.php");
			return;
    	}
  	}
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

	validatePos();

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
	  ':pid' => $profile_id,
	  ':rank' => $rank,
	  ':year' => $year,
	  ':desc' => $desc)
	  );

	  $rank++;

	}

	$_SESSION['success'] = "Profile added";
	header("Location: index.php");
	return;
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
	let area = $('#positions');
	let temp = $('#temp').html();
	let num = 1;

	console.log(area);

	function addPosition() {
		if (num==9) {
			alert("You can't add more positions.");
			return;
		}

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