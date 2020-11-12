<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$db = getDB();
$stmt = $db->prepare("SELECT id,name from Products LIMIT 10");
$r = $stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <h3>Create Cart</h3>
    <form method="POST">
    <select name="product_id" value="<?php echo $result["product_id"];?>" >
            <option value="-1">None</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php safer_echo($product["id"]); ?>"
                ><?php safer_echo($product["id"]); ?></option>
            <?php endforeach; ?>
        </select>
        <label>Quantity</label>
        <input type="number" min="1" name="quantity"/>
        <label>Price</label>
        <input type="number" min="1" name="price"/>
        <input type="submit" name="save" value="Create"/>
    </form>

<?php
if (isset($_POST["save"])) {
    $id = $_POST["product_id"];
    //TODO add proper validation/checks
    $pr = $_POST["price"];
	$quantity = $_POST["quantity"];
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