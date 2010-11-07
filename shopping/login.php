<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>

<h1>Membership Login</h1>
<p>Please login before doing transaction

<p>Default: c1/c1

<form action="login.func.php" method="POST">
<input type=hidden name=go value="<?=$_REQUEST['go']?>">
<table>
<tr><td>User:</td><td><input type="text" name="username" value="<?=$_REQUEST['username']?>"></td>
<tr><td>Pass:</td><td><input type=password name=password value="<?=$_REQUEST['password']?>"></td>
<tr><td>&nbsp</td><td><input type=submit></td>
</table>
<b><a href="register.php?go=<?=$_REQUEST['go']?>">Not member yet? Register here</a></b>
</form>

</body></html>