<?php
require_once("./BE/db.php");
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
$error = ''; // define error at the top, globally
$username='';
$password= '';
$emailUser= '';
$name='';
$phone='';
$dob='';


if (isset($_POST['name']) && isset($_POST['phone'])&& isset($_POST['dateOfBirth'] )&& isset($_POST['username'])&&isset( $_POST['password']) && isset($_POST['email'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $dob = $_POST['dateOfBirth'];
    $emailUser = $_POST['email'];
    $username= $_POST['username'];
    $password= $_POST['password'];
    $confirm= $_POST['confirmpass'];
    // Get user input from form
    $conn=create_connection();
    // check if email and username are used for register
    $stmt = $conn->prepare("SELECT * FROM customer WHERE email = ? or username=?");
    $stmt->bind_param("ss", $emailUser,$username);
    $stmt->execute();
    $result = $stmt->get_result();
    if(empty($username)||empty($emailUser)||empty($password)||empty($confirm)||empty($name)||empty($phone)||empty($dob)){
        $error = "please enter username password and email";
    }
    else if ($result->num_rows > 0) {
        $error = 'This email or username already used!';
    } else if($password != $confirm) {
        $error = 'incorrect confirm password';
    }
    else {
        echo'here';
        // Insert user into database safely
        $stmt = $conn->prepare("INSERT INTO customer (name,email,phone,dateOfBirth,username, password) VALUES (?, ?, ?, ?,? ,?)");
        $stmt->bind_param("ssidss", $name,$emailUser,$phone,$dob,$username, $password);

        if ($stmt->execute()) {
            header("Location: account.php");
            echo "<script>alert('Registration successful!');</script>";

            // Send confirmation email
            $subject = "Welcome to Our Website!";
            $body = "<h1>Hello, $name!</h1><p>Thank you for registering with us. Your registration is successful.</p>";

            
            
        } else {
            $error = "Error: " . $stmt->error;
        }
        
    }

    $stmt->close();
    $conn->close();
}

// PHPMailer send_mail function
function send_mail($to, $subject, $body) {
    $emailSender = "ngominhtam26112005@gmail.com";  
    $password = "nrekywgaozbbxaii";  
    $first_name = "Tam";    
    $last_name = "Ngo";      

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $emailSender;
        $mail->Password = $password;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom($emailSender, $first_name . ' ' . $last_name);
        $mail->addAddress($to); 
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
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

	<div class="container  ">
        <div class="row justify-content-center align-items-center  " style="height:100vh">
            <div class="col-lg-5 border p-3">
                <h2  class="text-center mx-5">Create an account</h2>
		        <form method="post" action="">
                    <div class="form-group loginform" >
                        <label for="email">EMAIL</label>
                        <input id="email" class="form-control" value="<?= $emailUser ?>" type="email" name="email" >
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input id="name" class="form-control" type="text" name="name">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input id="phone" class="form-control" type="text" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="dob">Date Of Birth</label>
                        <input id="dob" class="form-control" type="date" name="dateOfBirth">
                    </div>
                    <div class="form-group">
                        <label for="username">USERNAME</label>
                        <input id="username" class="form-control" value="<?= $username?>" type="text" name="username">
                    </div>
                    <div class="form-group loginform ">
                        <label for="password">PASSWORD</label>
                        <input id="password" class="form-control" type="password" name="password">
                    </div>
                    <div class="form-group loginform ">
                        <label for="confirmPassword">CONFIRM PASSWORD</label>
                        <input id="confirmPassword" class="form-control" type="password" name="confirmpass">
                    </div>
                    
                    <div class="form-group loginform">
                        <?php
                            if(!empty($error)){
                                echo "<div class='alert alert-danger mb-0'>$error</div>";
                            };
                        ?>
                    </div>
                    <button type="submit" name="register" class="btn btn-success px-5" style="width: 100%;">Submit</button>
                </form>
            </div> 

        </div>

	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>


</html>