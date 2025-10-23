<?php
include_once 'header.php';
?>

<h2>Login</h2>

<form action="/login" method="post">
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <div>
        <input type="submit" value="Login">
    </div>
</form>

<?php
include_once 'footer.php';
?>
