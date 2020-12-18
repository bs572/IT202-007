<?php require_once(__DIR__ . "/../../partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>
<?php
//saving
if(isset($_POST["save"])){
	$name = $_POST["name"];
	$cat = $_POST["category"];
	$pr = $_POST["price"];
	$quantity = $_POST["quantity"];
	$desc = $_POST["description"];
	$user = get_user_id();
	$db = getDB();
	if(isset($id)){
		$stmt = $db->prepare("UPDATE Products set name=:name, price=:pr, quantity=:quantity, description=:desc, category=:cat where id=:id");
		
		$r = $stmt->execute([
            ":name"=>$name,
            ":pr"=>$pr,
            ":quantity"=>$quantity,
            ":desc"=>$desc,
            ":id"=>$id,
            ":cat"=>$cat
		]);
		if($r){
			flash("Updated successfully with id: " . $id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
	}
	else{
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>
<?php
//fetching
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Products where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
<div class="form-group">
	<label>Name</label>
	<input name="name" placeholder="Name" value="<?php echo $result["name"];?>"/>
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
	<input type="submit" name="save" value="Update"/>
</form>


<?php require(__DIR__ . "/../../partials/flash.php");