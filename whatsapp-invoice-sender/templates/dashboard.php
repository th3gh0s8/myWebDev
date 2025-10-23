<?php
include_once 'header.php';
?>

<h2>Dashboard</h2>

<?php
if (isset($_GET['success'])) {
    echo "<p style='color: green;'>Message sent successfully!</p>";
}

if (isset($_GET['error'])) {
    echo "<p style='color: red;'>Failed to send message.</p>";
}
?>

<p>Welcome, <?php echo $_SESSION['username']; ?>!</p>

<a href="/logout">Logout</a>

<h3>Send a message</h3>

<form action="/send" method="post">
    <div>
        <label for="to">To</label>
        <input type="text" name="to" id="to" placeholder="Enter phone number" required>
    </div>
    <div>
        <label for="message">Message</label>
        <textarea name="message" id="message" required></textarea>
    </div>
    <div>
        <label for="invoice">Invoice (optional)</label>
        <input type="file" name="invoice" id="invoice">
    </div>
    <div>
        <input type="submit" value="Send">
    </div>
</form>

<?php
include_once 'footer.php';
?>
