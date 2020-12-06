<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>

<?php

$userID = get_user_id();
$cartID = 0;
$productID = 0;
$results = [];
$quantity = 0;
$subtotal = 0;
$address = "";

$db = getDB();
    $stmt = $db->prepare("SELECT payment_method, total_price, user_id, id `address`, payment_method  From Orders  where user_id=:user_id LIMIT 10");
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
                    <h5 class="card-title"><?php safer_echo($r["name"]); ?></h5>
                    </div>
                    <div>Order Number: <?php safer_echo($r["id"]); ?></div>
                        <div>
                            <a type="button" href="view_order.php?id=<?php safer_echo($r['id']); ?>">View Order</a>
                        </div>
                <?php endforeach; ?>
               
            </div>
            <div class="card" style="width: 18rem;">
                    <div class="card-body">
                    <h5 class="card-title">Total Price:<?php safer_echo($r["total_price"]); ?></h5>
            </div> </div> </div>
            <form method="POST">
                <div class="form-group">
                <input type="submit" name="clearAll" value="Empty Cart"/>
                </form>

                <?php endif; ?>
                <?php require(__DIR__ . "/partials/flash.php");