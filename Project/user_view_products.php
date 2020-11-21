<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $productID = $_GET["id"];
    $userID = get_user_id();
}
?>
<?php
//fetching
$result = [];
if (isset($productID)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT name, price, quantity, description, user_id, Users.username FROM Products JOIN Users on Products.user_id = Users.id where Products.id = :id");
    $r = $stmt->execute([":id" => $productID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>

<?php
if(isset($_POST["quantity"])) {
$db = getDB();
$stmt = $db->prepare ("INSERT into Cart (`product_id`, `user_id`, `quantity`) VALUES (:productID, :userID, :quantity) on duplicate key update quantity = :quantity");
$r = $stmt->execute([
    ":productID" => $productID,
    ":userID" => $userID,
    ":quantity" => $_POST["quantity"]
    ]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$result) {
    $e = $stmt->errorInfo();
    flash($e[2]);
    }
}
?>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card" style="width: 18rem;">
        <div class="card-body">
        <h5 class="card-title"><?php safer_echo($result["name"]); ?></h5>
    
                <div>Price: <?php safer_echo($result["price"]); ?></div>
                <?php if ($result["quantity"] < 10): ?>   
                        <div><?php safer_echo("Only " . $result["quantity"] . " left in stock, order soon."); ?></div>
                   <?php endif;?>
                <div>Description: <?php safer_echo($result["description"]); ?></div></p>
                <form method="POST">
                    <div class="form-group">
                        <label>Quantity</label>
                            <input type="number" min="0" name="quantity" value="<?php echo $result["quantity"]; ?>"/>
                    </div>
                        <input type="submit" name="save" value="Add to Cart"/>
                </form>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");