<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $productID = $_GET["id"];
    $userID = get_user_id();
    $price = 0;
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
        flash(var_export($e, true));
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT user_id, rating, comment, Users.username FROM Ratings JOIN Users on Ratings.user_id = Users.id where Ratings.product_id = :id");
    $r = $stmt->execute([":id" => $productID]);
    $rating = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash(var_export($e, true));
    }


}
?>

<?php
if(isset($_POST["quantity"])) {
    $db = getDB();
    $price = $_POST["price"];
    echo $price;
    $stmt = $db->prepare ("INSERT into Cart (`product_id`, `user_id`, `quantity`, `price`) VALUES (:productID, :userID, :quantity, :price) on duplicate key update quantity = :quantity");
    $r = $stmt->execute([
        ":productID" => $productID,
        ":userID" => $userID,
        ":quantity" => $_POST["quantity"],
        ":price" => $price
        ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash(var_export($e, true));
    }
}


if(isset($_POST["comment"]) && isset($_POST["rating"]) && !empty ($_POST["comment"])) {
    $db = getDB();
    echo $price;
    $stmt = $db->prepare ("INSERT into Ratings (`product_id`, `user_id`, `rating`, `comment`) VALUES (:productID, :userID, :rating, :comment)");
    $r = $stmt->execute([
        ":productID" => $productID,
        ":userID" => $userID,
        ":quantity" => $_POST["comment"],
        ":price" => $price
        ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash(var_export($e, true));
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
                            <input type="submit" name="save" value="Add to Cart"/>
                        <input type="hidden" name="price" value="<?php echo $result["price"]; ?>"/>
                </form>
                    </div>  
                    <form>
                    <div class="form-group">
                        <label>Leave a Review</label>
                        <label for="rating">Rating:</label>
                        <input type="text" name="comment" placeholder="Leave a Review"/>
                        <input type="range" id="rating" name="rating" min="1" max="5" step="1">   
                        <input type="submit" name="save" value="Submit review"/> 
                    </div></form>

                        
        </div>
    </div>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");