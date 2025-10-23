<?php
include_once 'header.php';
require_once __DIR__ . '/../src/Customer.php';

$customer = new Customer($db);
$stmt = $customer->readAll($_SESSION['user_id']);
?>

<h2>Customers</h2>

<h3>Add Customer</h3>
<form action="/add_customer" method="post">
    <div>
        <label for="name">Name</label>
        <input type="text" name="name" id="name" required>
    </div>
    <div>
        <label for="phone_number">Phone Number</label>
        <input type="text" name="phone_number" id="phone_number" required>
    </div>
    <div>
        <input type="submit" value="Add Customer">
    </div>
</form>

<h3>Your Customers</h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Phone Number</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['phone_number']; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
include_once 'footer.php';
?>
