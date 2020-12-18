<?php require_once(__DIR__ . "/../../partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<h3>Create Product</h3>
<form method="POST">
	<div class="form-group">	
		<label>Name</label>
		<input name="name" placeholder="Name"/>
	</div>
	<div class="form-group">
		<label>Category</label>
		<input type="text" name="category"/>
	</div>
	<div class="form-group">
		<label>Price</label>
		<input type="number" min="0" name="price"/>
	</div>
	<div class="form-group">
		<label>Quantity</label>
		<input type="number" min="0" name="quantity"/>
	</div>
	<div class="form-group">
		<label>Description</label>
		<input type="text" name="description"/>
	</div>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){

	$name = $_POST["name"];
	$cat = $_POST["category"];
	$pr = $_POST["price"];
	$quantity = $_POST["quantity"];
	$desc = $_POST["description"];
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Products (name, price, quantity, description, user_id, category VALUES(:name, :pr, :quantity, :desc, :user, :cat)");
	$r = $stmt->execute([
		":name"=>$name,
		":pr"=>$pr,
		":quantity"=>$quantity,
		":desc"=>$desc,
		":cat"=>$cat,
		":user"=>$user
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/../../partials/flash.php");