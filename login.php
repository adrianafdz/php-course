<?php  
session_start();
require_once "connection.php";
require_once "util.php";

if ( isset($_POST['cancel']) ) {
	header("Location: index.php");
	return;
}

if ( isset($_POST['login']) ) {
	if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
		$_SESSION['error'] = "Both fields must be filled out";
		header("Location: login.php");
		return;
	} else if ( strpos($_POST['email'], "@") === false ) {
		$_SESSION['error'] = "Invalid email address";
		header("Location: login.php");
		return;
	} else {
		// validate password
		$salt = 'XyZzy12*_';
		$check = hash('md5', $salt.$_POST['pass']);

		$query = $pdo->prepare("SELECT user_id, name FROM users WHERE email = :em AND password = :pw");
		$query->execute(array(
			':em' => $_POST['email'],
			':pw' => $check
		));

		$row = $query->fetch(PDO::FETCH_ASSOC);

		if ( $row === false ) {
			$_SESSION['error'] = "Incorrect password";
		} else {
			$_SESSION['name'] = $row['name'];
			$_SESSION['user_id'] = $row['user_id'];
			header("Location: index.php");
			return;
		}

	}
}


?>

<!DOCTYPE html>
<html>
<head>
	<title>Adriana Fernández López</title>
	<?php require_once "bootstrap.php"; ?>
</head>
<body>
	<div class="container">
		<h1>Please Log In</h1>

		<?php flashMessages(); ?>

		<form method="POST">
			<p>Email <input type="text" name="email" id="email"></p>
			<p>Password <input type="password" name="pass" id="pass"></p>
			<input type="submit" name="login" value="Log In" onclick="return doValidate();">
			<input type="submit" name="cancel" value="Cancel">
		</form>

	</div>
</body>

<script type="text/javascript">
	function doValidate() {
		console.log("Validating...");
		try {
			pw = document.getElementById("pass").value;
			em = document.getElementById("email").value;
			
			if (pw == null || pw == "" || em == null || em == "") {
	            alert("Both fields must be filled out");
	            return false;
	        }
	        if ( em.indexOf("@") == -1) {
	        	alert("Invalid email address");
	        	return false;
	        }
	        return true;
		} catch (e) {
			return false;
		}
		return false;
	}
</script>
</html>