<?php
    require_once("./BE/db.php");
session_start();
$user = '';
$pass = '';
$error = '';

$conn=create_connection();


if (isset($_POST['username']) && isset($_POST['password'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    if (empty($user) && empty($pass)) {
        $error = 'Please enter Username and Password';
    } elseif (empty($user)) {
        $error = 'Please enter your username';
    } elseif (empty($pass)) {
        $error = 'Please enter your password';
    } else {
        // Prepare and bind AFTER getting the values
        $stmt = $conn->prepare("SELECT username FROM customer WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $user, $pass);

        $stmt->execute();
        $result = $stmt->get_result();      

        // Check if a user is found
        if ($result->num_rows >0) {
            if($user==='admin' and $pass=== 'admin123') {
                header("Location: admin.php");
                exit();    
            }else if($user==='supplier' and $pass=== 'supplier123'){
                header('Location: supplier.php');
                exit();
            }
            $_SESSION['username'] = $user;
            header("Location: index.php");
            exit(); 
        } else {
            $error = 'Incorrect username or password';
        }

        $stmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>   
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<title>Home Page</title>
</head>

<body>

        <div class="container ">
            <div class="row justify-content-center align-items-center  " style="height:100vh">
                <div class="col-lg-5 border p-3">
                    <h2  class="text-center mx-5">LOG IN</h2>
                    <form method="post" action="">
                        <div class="form-group loginform" >
                            <label for="username">Username</label>
                            <input id="username" class="form-control" type="text" name="username" value="<?= $user?>">
                        </div>
                        <div class="form-group loginform mb-0">
                            <label for="password">Password</label>
                            <input id="password" class="form-control" type="password" name="password" value="<?= $pass?>">
                        </div>
                        <div class="form-group ">
                            <a href="#"><small>Fogot Password?</small></a>
                        </div>
                        <div class="form-group loginform">
                            <?php
                                if(!empty($error)){
                                    echo "<div class='alert alert-danger'>$error</div>";
                                };
                            ?>
                        </div>
                        <button class="btn btn-success px-5" style="width: 100%;">Login</button>
                        <div class="form-group ">
                            <small>Need an account? <a href="register.php">Register</a></small>
                        </div>
                        
                    </form>
                </div>

            </div>

        </div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>


</html>