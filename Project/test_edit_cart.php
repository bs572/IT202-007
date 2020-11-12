<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//saving
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $name = $_POST["name"];
    $pid = $_POST["product_id"];
    if ($pid <= 0) {
        $pid = null;
    }
    $pr = $_POST["price"];
    $quantity = $_POST["quantity"];
    $desc = $_POST["description"];
    $user = get_user_id();
    $db = getDB();
    if (isset($id)) {
        $stmt = $db->prepare("UPDATE Cart set name=:name, product_id=:pid, price=:pr, quantity=:quantity, description=:description where id=:id");
        $r = $stmt->execute([
            ":name" => $name,
            ":pr" => $pr,
            ":quantity"=>$quantity,
		    ":desc"=>$desc,
            ":id" => $id
        ]);
        if ($r) {
            flash("Updated successfully with id: " . $id);
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
    }
    else {
        flash("ID isn't set, we need an ID in order to update");
    }
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Cart where id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
//get pids for dropdown
$db = getDB();
$stmt = $db->prepare("SELECT id,name from Products LIMIT 10");
$r = $stmt->execute();
$pids = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <h3>Edit Cart</h3>
    <form method="POST">
        <label>Name</label>
        <input name="name" placeholder="Name" value="<?php echo $result["name"]; ?>"/>
        <label>pid</label>
        <select name="pid_id" value="<?php echo $result["pid_id"];?>" >
            <option value="-1">None</option>
            <?php foreach ($Products as $Product): ?>
                <option value="<?php safer_echo($pid["id"]); ?>" <?php echo ($result["product_id"] == $pid["id"] ? 'selected="selected"' : ''); ?>
                ><?php safer_echo($pid["name"]); ?></option>
            <?php endforeach; ?>
        </select>
        <label>Price</label>
        <input type="number" min="0" name="price" value="<?php echo $result["price"]; ?>"/>
        <label>Quantity</label>
        <input type="number" min="0" name="quantity" value="<?php echo $result["quantity"]; ?>"/>
        <label>Description</label>
        <input type="text" name="description" value="<?php echo $result["description"]; ?>"/>
        <input type="submit" name="save" value="Update"/>
    </form>


<?php require(__DIR__ . "/partials/flash.php");