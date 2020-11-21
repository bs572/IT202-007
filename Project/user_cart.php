<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: ../../login.php"));
}
?>

<?php
$query = "";
$id=get_user_id();
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Cart.price, name, Cart.id, Cart.quantity From Cart JOIN Products on Cart.product_id = Products.id where Cart.user_id=:user_id LIMIT 10");
    $r = $stmt->execute([
        ":q" => "%$query%",
        ":user_id"=> $id,
        ]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }
}
?>
<h3>Your Cart</h3>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Name:</div>
                        <div><?php safer_echo($r["name"]); ?></div>
                    </div>
                    <div>
                        <div>Price:</div>
                        <div><?php safer_echo($r["price"]); ?></div>
                    </div>
                    <div>
                        <div>Quantity:</div>
                        <div><?php safer_echo($r["quantity"]); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Your cart is empty, but it doesn't have to be that way.</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php");