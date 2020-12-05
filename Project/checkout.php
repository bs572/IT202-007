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
$payments = [];
$address = "";



$db = getDB();
    $stmt = $db->prepare("SELECT * from PaymentMethods");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);



$db = getDB();
    $stmt = $db->prepare("SELECT Cart.price, name, product_id, Cart.id, Cart.quantity, Products.quantity as pquantity From Cart JOIN Products on Cart.product_id = Products.id where Cart.user_id=:user_id LIMIT 10");
    $r = $stmt->execute([":user_id"=> $userID,]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }


    $stmt = $db->prepare("INSERT into Orders (`product_id`, `user_id`, `quantity`, `payment_method`, id, `address`) VALUES (:productID, :userID, :quantity) on duplicate key update quantity = :quantity");
    $r = $stmt->execute([
        ":productID" => $productID,
        ":userID" => $userID,
        ":quantity" => $quantity
        ]);

?>


<h3>Checkout</h3>
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
                    <?php if ($r["pquantity"] < $r["quantity"]): ?>
                    <?php echo "Quantity too high" ?>
                <?php endif; ?>
                    <input type="submit" name="save" value="Update Quantity"/>
                    <input type="hidden" name="product_id" value="<?php echo $r["product_id"]; ?>"/>
                    <input type="hidden" name="id" value="<?php echo $r["id"]; ?>"/>
                </div>
                </form>
                <form method="POST">
                    <input type="hidden" name="quantity" value="0"/>
                    <input type="submit" value="Remove Item"/>
                    <input type="hidden" name="product_id" value="<?php echo $r["product_id"]; ?>"/>
                    <input type="hidden" name="id" value="<?php echo $r["id"]; ?>"/>        
                </form>
                    <div>
                        <a type="button" href="user_view_product_cart.php?id=<?php safer_echo($r['product_id']); ?>">View Product</a>
                    </div>
            
            <?php endforeach; ?>
           <div>    
            <a type="button" href="checkout.php">Checkout</a>
            </div>
        </div>
        <div class="card" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">Subtotal:<?php safer_echo($subtotal); ?></h5>
        </div> </div> </div>


<label>Payment Method</label>
            <select name="id" value="<?php echo $payment["payment_method"];?>" >
                <option value="-1">None</option>
                <?php foreach ($payments as $payment): ?>
                    <option value="<?php safer_echo($payment["payment_method"]); ?>" <?php echo ($result["product_id"] == $product["id"] ? 'selected="selected"' : ''); ?>
                    ><?php safer_echo($product["name"]); ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

<form method="POST">
        <input type="number" name="payment" value=""/>
        <input type="submit" name="save" value="Payment"/>
</form>
<form method="POST">
        <input type="text" name="address" value="Address"/>
        <input type="submit" name="save" value="Address"/>
</form>
