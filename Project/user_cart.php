<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: ../../login.php"));
}
?>

<?php
$id=get_user_id();
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}

    $db = getDB();
    $stmt = $db->prepare("SELECT Cart.price, name, Cart.id, Cart.quantity From Cart JOIN Products on Cart.product_id = Products.id where Cart.user_id=:user_id LIMIT 10");
    $r = $stmt->execute([":user_id"=> $id,]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }

?>
<h3>Your Cart</h3>
<div class="results">
    <?php if (count($results) > 0): ?>
            <?php foreach ($results as $r): ?>
                <div class="card" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title"><?php safer_echo($r["name"]); ?></h5>
                <div>Price: <?php safer_echo($r["price"]); ?></div>
                <div><?php safer_echo($r["quantity"]); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Your cart is empty, but it doesn't have to be that way.</p>
    <?php endif; ?>

<?php require(__DIR__ . "/partials/flash.php");