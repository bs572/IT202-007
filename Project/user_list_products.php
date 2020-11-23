<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$query = "";
$results = [];
$selectedCat = '';
$dbQuery = "SELECT name, id, price, category, quantity, description, visibility, user_id from Products WHERE 1 = 1 AND visibility = 1";
$sort = "default";
$params = [];
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

if (isset($_POST["Search"])) {
    echo $_POST["category"];
    echo $query;
    $selectedCat = $_POST['category'];
    if ($selectedCat != -1){
        $dbQuery .= " AND category = :cat ";
        $params[":cat"] = $selectedCat; 
    }
    if ($query != "") {
        $dbQuery .= " AND name like :q ";
        $params[":q"] = $query;
    }
    if(isset($_POST["sort"])) {
        $sort = "price";
        echo $sort;
        $dbQuery .= " ORDER BY $sort ASC";
    }
    
    $db = getDB();
    $stmt = $db->prepare($dbQuery);
    $r = $stmt->execute($params);
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
        <input type="submit" value="search" name="Search"/>
        <input type="radio" value ="sort" name = "sort"/>
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