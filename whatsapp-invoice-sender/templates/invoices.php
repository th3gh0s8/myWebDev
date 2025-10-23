<?php
include_once 'header.php';
require_once __DIR__ . '/../src/Invoice.php';
require_once __DIR__ . '/../src/Customer.php';

$invoice = new Invoice($db);
$customer = new Customer($db);

$invoices = $invoice->readAll($_SESSION['user_id']);
$customers = $customer->readAll($_SESSION['user_id']);
?>

<h2>Invoices</h2>

<h3>Create Invoice</h3>
<form action="/create_invoice" method="post">
    <div>
        <label for="customer_id">Customer</label>
        <select name="customer_id" id="customer_id" required>
            <option value="">Select a customer</option>
            <?php while ($row = $customers->fetch(PDO::FETCH_ASSOC)): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div>
        <label for="invoice_data">Invoice Details (JSON format)</label>
        <textarea name="invoice_data" id="invoice_data" required></textarea>
    </div>
    <div>
        <input type="submit" value="Create and Send Invoice">
    </div>
</form>

<h3>Your Invoices</h3>
<table>
    <thead>
        <tr>
            <th>Customer</th>
            <th>Status</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $invoices->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['customer_name']; ?></td>
                <td><?php echo $row['status']; ?></td>
                <td><?php echo $row['invoice_data']; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
include_once 'footer.php';
?>
