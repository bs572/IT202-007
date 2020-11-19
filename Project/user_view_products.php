<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT name, price, quantity, description, user_id, Users.username FROM Products JOIN Users on Products.user_id = Users.id where Products.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card" style="width: 18rem;">
  <img src="https://www.precisionturbo.net/images/products/small/583-1.jpg" class="card-img-top" alt="CEA 7285">
  <div class="card-body">
    <h5 class="card-title">Precision 7285</h5>
    <p class="card-text">Gen2 Precision 7285 class legal turbo for SportFWD, and World Cup Finals Super Street</p>
    <form method="POST">
    <input type="hidden" name="productID" value="<?php echo $result["product_id"];?>"/>
    <input type="submit" value="Add to Cart"/>
    </form>
  </div>

<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php 
if (isset($_POST["save"])) {
    $id = $_POST["product_id"];
    //TODO add proper validation/checks
    $pr = $_POST["price"];
	$quantity = 1;
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT FROM Products(price) VALUES(:pr)");
    $stmt = $db->prepare("INSERT INTO Cart (product_id, price, quantity,user_id) VALUES(:id, :pr, :quantity, :user)");
    $r = $stmt->execute([
        ":id"=>$id,
        ":pr"=>$pr,
		":quantity"=>$quantity,
		":user"=>$user
    ]);
    if ($r) {
        flash("Created successfully with id: " . $db->lastInsertId());
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>

<?php require(__DIR__ . "/partials/flash.php");