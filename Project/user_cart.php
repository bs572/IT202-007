<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
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

if (isset($_POST["query"])) {
    $query = $_POST["query"];
}

?>
<?php   
    if(isset($_POST["quantity"])) {
        $quantity = (int)$_POST["quantity"];
        if($quantity == 0) {
            $cartID = $_POST["id"];
            $db = getDB();
            $stmt = $db->prepare("DELETE From Cart where id = :cartID");
            $r = $stmt->execute([":cartID"=> $cartID,]);
        }
        if ($quantity != 0 ) {
            $productID = $_POST["product_id"];
            $db = getDB();
            $stmt = $db->prepare("INSERT into Cart (`product_id`, `user_id`, `quantity`) VALUES (:productID, :userID, :quantity) on duplicate key update quantity = :quantity");
            $r = $stmt->execute([
                ":productID" => $productID,
                ":userID" => $userID,
                ":quantity" => $quantity
                ]);
    }
    }
    if(isset($_POST["clearAll"])) {
        $db = getDB();
        $stmt = $db->prepare("DELETE from Cart where user_id = :userID");
        $r = $stmt->execute([":userID"=> $userID,]);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT Cart.price, name, product_id, Cart.id, Cart.quantity From Cart JOIN Products on Cart.product_id = Products.id where Cart.user_id=:user_id LIMIT 10");
    $r = $stmt->execute([":user_id"=> $userID,]);
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
               <?php  $subtotal += ($r["price"]*$r["quantity"]); ?>
                <div class="card" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title"><?php safer_echo($r["name"]); ?></h5>
                <div>Price: <?php safer_echo(($r["price"]* $r["quantity"])); ?></div>
                <div><?php safer_echo($r["quantity"]); ?></div>
                </div>
                <form method="POST">
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" name="quantity" value="<?php echo $r["quantity"]; ?>"/>
                    <input type="submit" name="save" value="Update Quantity"/>
                    <input type="hidden" name="product_id" value="<?php echo $r["product_id"]; ?>"/>
                </div>
                </form>
                <form method="POST">
                    <input type="hidden" name="quantity" value="0"/>
                        <input type="submit" name="quantity" value="Remove Item"/>        
                </form>
                    <div>
                    <form method="POST">
                        <a type="button" href="user_view_product_cart.php?id=<?php safer_echo($r['product_id']); ?>">View Product</a>
                        <input type="hidden" name="id" value="<?php echo $r["id"]; ?>"/>
                        <input type="hidden" name="product_id" value="<?php echo $r["product_id"]; ?>"/>
                    </div>
                    </form>
            <?php endforeach; ?>
           
        </div>
        <div class="card" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">Subtotal:<?php safer_echo($subtotal); ?></h5>
        </div> </div> </div>
        <form method="POST">
            <div class="form-group">
            <input type="submit" name="clearAll" value="Empty Cart"/>
            </form>
    <?php else: ?>
        <p>Your cart is empty, but it doesn't have to be that way.</p>
    <?php endif; ?>

<?php require(__DIR__ . "/partials/flash.php");