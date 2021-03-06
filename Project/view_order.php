<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $orderID = $_GET["id"];
    $userID = get_user_id();
}
?>

<?php
$results = [];

?>
<?php   
    $db = getDB();
    $stmt = $db->prepare("SELECT OrderItems.id, order_id, product_id, unit_price, OrderItems.quantity, Products.name From OrderItems JOIN Products ON Products.id = OrderItems.product_id Where order_id=:order_id LIMIT  10");
    $r = $stmt->execute([":order_id"=> $orderID,]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }


?>

<h3>Order Details</h3>
<div class="results">
    <?php if (count($results) > 0): ?>
            <?php foreach ($results as $r): ?>
                <div class="card" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">Item: <?php safer_echo($r["name"]); ?></h5>
                <div>Price: <?php safer_echo($r["unit_price"]); ?></div>
                <div>Quantity: <?php safer_echo($r["quantity"]); ?></div>
                </div>
            <?php endforeach; ?>
                </div>
         </div>

    <?php else: ?>
        <p>You don't have any orders matching the selected criteria</p>
    <?php endif; ?>

<?php require(__DIR__ . "/partials/flash.php");