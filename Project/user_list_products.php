<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$query = "";
$results = [];
$selectedCat = '';
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}

$db = getDB();
$stmt = $db->prepare("SELECT distinct category from Products;");
$r = $stmt->execute();
if ($r) {
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the results");
}

if (isset($_POST["search"]) && !empty($query)) {
    echo $_POST["search"];
    $selectedCat = $_POST['category'];
    $db = getDB();
    $stmt = $db->prepare("SELECT name, id, price, category, quantity, description, visibility, user_id from Products WHERE name like :q AND category = :cat AND visibility = 1 LIMIT 10");
    $r = $stmt->execute([":q" => "%$query%", ":cat" => $selectedCat]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}

if (empty($_POST["search"]) && isset($_POST["category"])) {
    echo $_POST["search"];
    $selectedCat = $_POST['category'];
    $db = getDB();
    $stmt = $db->prepare("SELECT name, id, price, category, quantity, description, visibility, user_id from Products WHERE category = :cat AND visibility = 1 LIMIT 10");
    $r = $stmt->execute([":cat" => $selectedCat]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}

?>
<h3>Search</h3>

<form method="POST">
    <div class="form-group">    
    <select name="category" value="<?php echo $result["category"];?>" >
            <option value="-1">None</option>
            <?php foreach ($cats as $cat): ?>
                <option value="<?php safer_echo($cat["category"]); ?>"
                ><?php safer_echo($cat["category"]); ?></option>
            <?php endforeach; ?>
        </select>
        <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
        <input type="submit" value="Search" name="search"/>
    </div>
</form>

<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
        <?php foreach ($results as $r): ?>
                    <div class="list-group-item">
                    <div>
                        <div><?php safer_echo($r["name"]); ?></div>
                    </div>
                    <div>
                        <div>Price:</div>
                        <div><?php safer_echo($r["price"]); ?></div>
                    </div>
                    <div>
                        <?php if ($r["quantity"] < 10): ?>
                        
                        <div><?php safer_echo("Only " . $r["quantity"] . " left in stock, order soon."); ?></div>
                   <?php endif;?>
                    </div>
                    <div>
                        <div>Description:</div>
                        <div><?php safer_echo($r["description"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="user_view_products.php?id=<?php safer_echo($r['id']); ?>">View Product</a>
                    </div>
                </div> 
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php");