<?php require_once(__DIR__ . "/../../partials/nav.php"); ?>

<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: ../../login.php"));
}
?>

<?php

$userID = get_user_id();
$cartID = 0;
$productID = 0;
$results = [];
$quantity = 0;
$subtotal = 0;
$cumulativeTotal = 0;
$address = "";

$db = getDB();
    $stmt = $db->prepare("SELECT payment_method, total_price, user_id, id, `address`, payment_method  From Orders LIMIT 10");
    $r = $stmt->execute([":user_id"=> $userID,]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }



?>
    <h3>Order History</h3>
    <div class="results">
        <?php if (count($results) > 0): ?>
                <?php foreach ($results as $r): ?>
                    <div class="card" style="width: 18rem;">
                    <div class="card-body">
                    <h5 class="card-title">Order Number : <?php safer_echo($r["id"]); ?></h5>
                    </div>
                        <div>
                            <a type="button" href="view_order.php?id=<?php safer_echo($r['id']); ?>">View Order</a>
                        </div>
                       <?php $cumulativeTotal += $r["total_price"]; ?>
                       <div class="card" style="width: 18rem;">
                    <div class="card-body">
                    <h5 class="card-title">Total Price:<?php safer_echo($r["total_price"]); ?></h5>
            </div> </div> 
               
                <?php endforeach; ?>
               
            </div>
            </div>
            <?php echo "Total of All Orders: " . $cumulativeTotal ?>
            <form method="POST">
                <div class="form-group">
                <input type="submit" name="clearAll" value="Empty Cart"/>
                </form>

                <?php endif; ?>
                <?php require(__DIR__ . "/../../partials/flash.php");