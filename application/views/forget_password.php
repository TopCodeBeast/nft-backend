<!DOCTYPE html> 
<html> 
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/css-style.css">

<body> 
	<h2>Reset Password</h2> 
	<!--Step 1 : Adding HTML-->
	<form action="<?php echo base_url()?>ResetPassword/resetpassword" method="post"> 
		<div class="imgcontainer"> 
        <img  alt="logo" src="<?php echo base_url(); ?>assets/images/logo.png" />
		</div> 
		<input type="hidden"  name="email" value="<?php if(!empty($email)){ echo $email; } ?>">
		<div class="container"> 
            <label><b>Password</b></label> 
			<input type="password" placeholder="Enter Password" name="password" required>

			<label><b>Confirm Password</b></label> 
			<input type="password" placeholder="ReEnter Password" name="psw" required> 

			<button type="submit">Submit</button> 
		</div> 
	</form> 

</body> 

</html> 
