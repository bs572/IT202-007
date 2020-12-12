<?php require_once(__DIR__ . "/../../partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$query = "";
$dbQuery = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $dbQuery = "SELECT name, id, price, quantity, description, user_id from Products WHERE name like :q";
    if (!empty ($_POST["quantityFilter"]))
    {
    $dbQuery .= " AND quantity <= ";
    $dbQuery .= $_POST["quantityFilter"];
    }
    $dbQuery .= " LIMIT 10";
    $db = getDB();
    $stmt = $db->prepare($dbQuery);
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
?>
<h3>List Products</h3>
<form method="POST">
    <div class="form-group">    
        <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
        <input name="quantityFilter" placeholder="Max Quantity in Stock"/>
        <input type="submit" value="Search" name="search"/>
    </div>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Name:</div>
                        <div><?php safer_echo($r["name"]); ?></div>
                    </div>
                    <div>
                        <div>Price:</div>
                        <div><?php safer_echo($r["price"]); ?></div>
                    </div>
                    <div>
                        <div>Quantity:</div>
                        <div><?php safer_echo($r["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Description:</div>
                        <div><?php safer_echo($r["description"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="edit_products.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" href="view_products.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/../../partials/flash.php");