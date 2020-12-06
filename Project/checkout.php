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
$paymentMethod ="";
$address = "";
$amountTendered = 0;
$noError = True;
$query = "";


$db = getDB();
    $stmt = $db->prepare("SELECT * from PaymentMethods");
    $r = $stmt->execute();
    if ($r) {
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



$db = getDB();
    $stmt = $db->prepare("SELECT Cart.price, name, product_id, Cart.id, Cart.quantity, Products.quantity as pquantity From Cart JOIN Products on Cart.product_id = Products.id where Cart.user_id=:user_id LIMIT 10");
    $r = $stmt->execute([":user_id"=> $userID,]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }


    if (isset($_POST["save"])):
        echo("Submit successful");
    endif;

    
    if ($noError && isset($_POST["streetLine1"]) && isset($_POST["streetLine2"]) && isset($_POST["city"]) && isset($_POST["zipCode"])) {
        $address = $_POST["streetLine1"] . $_POST["streetLine2"] . $_POST["city"] . $_POST["zipCode"] ;
        $paymentMethod = $_POST["payment_method"];
        $subtotal = $_POST["subtotal"];
        $amountTendered = $_POST["payment"];
        echo ("No Errors");
        echo ("Subtotal:" . $subtotal . "Amount Tendered:" . $amountTendered . "user ID:" .$userID . "PaymentMethod: " . $paymentMethod . "Address:" . $address);
        
        
        $stmt = $db->prepare("INSERT into Orders (`user_id`, `total_price`, `payment_method`, `address`) VALUES (:userID, :tprice, :pmethod, :addr");
        $r = $stmt->execute([
        ":tprice" => $subtotal,
        ":userID" => $userID,
        ":pmethod" => $paymentMethod,
        ":addr" => $address
        ]);


        $query = "INSERT into OrderItems (`user_id`, `unit_price`, `product_id`, `order_id`, `quantity`) VALUES ";
                $params = [];
                foreach ($results as $index => $result) {
                    if ($index > 0) {
                        $query .= ",";
                    }
                    $query .= "(:userID, :price$index, :pid$index, :oid, :quantity$index )";
                    $params[":pid$index"] = $result["product_id"];
                    $params[":quantity$index"] = $result["quantity"];
                    $params[":price$index"] = $result["price"];
                   }
           $params[":oid"] = $db -> lastInsertID();
           $params[":userID"] = $userID;

        $stmt = $db->prepare($query);
        $r = $stmt->execute($params);
                }
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
                    <?php echo "Quantity too high";
                    $noError = False; 
                    endif; ?>
                     <?php if ($r["pquantity"] > $r["quantity"]): ?>
                    <?php $noError = True; ?>
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
        <form method="POST"> 
            <select name="payment_method" value="<?php echo $payment["payment_method"];?>" >
                <option value="-1">None</option>
                <?php foreach ($payments as $payment): ?>
                    <option value="<?php safer_echo($payment["payment_method"]); ?>" 
                    ><?php safer_echo($payment["payment_method"]); ?></option> <?php echo ($payment["payment_method"] == $product["id"] ? 'selected="selected"' : ''); ?>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        


        <input type="number" name="payment" value=""/>
        <input type="submit" name="save" value="Payment"/>


        <input type="text" name="streetLine1" value="Street Line 1"/>
        <input type="text" name="streetLine2" value="Street Line 2"/>
        <input type="text" name="city" value="City"/>
        <input type="number" name="zipCode" value="Zip Code"/>
        <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>"/>
        <input type="submit" name="save" value="Place Order"/>
</form>
