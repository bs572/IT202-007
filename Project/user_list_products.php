<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT name, price, quantity, description, visibility, user_id from Products WHERE name like :q LIMIT 10");
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
        <input type="submit" value="Search" name="search"/>
    </div>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
        <?php foreach ($results as $r): ?>
               <?php if ($r["visibility"] == 1): ?>
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
                        <?php if ($r["quantity"] < 10): ?>
                        
                        <div><?php safer_echo("Only " . $r["quantity"] . " left in stock, order soon."); ?></div>
                   <?php endif;?>
                    </div>
                    <div>
                        <div>Description:</div>
                        <div><?php safer_echo($r["description"]); ?></div>
                    </div>
                </div> 
            <?php endif; ?>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>