<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$page = 1;
$countOnPage = 10;
$total = 0;
$query = "";
$dataQuery = "";
$pageQuery = "";
$category = "";
$search = "";
$sort = "";
$order = "";
$params = [];
$results = [];


$category = extractData("category");
$search = extractData("search");
$sort = extractData("sort");
$order = extractData("order");
if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}

if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $dataQuery = "SELECT name, id, price, quantity, description, user_id from Products WHERE quantity >1 AND visibility =1";
    $pageQuery = "SELECT COUNT(1) as total from Products WHERE 1=1";  
}
        if (isset ($_POST["category"]) && $_POST["category"] !=-1) {
        $dataQuery .= " AND category =:cat";
        $pageQuery .= " AND category =:cat";
        $params[":cat"] = $category;
    }
    if (isset ($_POST["query"]) && ($_POST["query"])!= "" ) {
        $dataQuery .= " AND name like :q";
        $pageQuery .= " AND name like :q";
        $params[":q"] = "%$query%";
    }

    if (isset ($sort) && isset($order)){
        if(in_array($sort,["price","category","name"])
        && in_array($order,["asc","desc"])) {
            $dataQuery .= " ORDER by $sort $order";
            $pageQuery .= " ORDER by $sort $order";
        }
    }
    $dataQuery .= " LIMIT :offset, :count";
    $total_pages = ceil($total / $countOnPage);
    $offset = ($page-1) * $countOnPage;
    

    $db = getDB();
    $stmt = $db->prepare("SELECT distinct category from Products;");
    $r = $stmt->execute();
    if ($r) {
        $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
   
   
   
    $db = getDB();
    $stmt = $db->prepare($pageQuery);
    $stmt->execute($params);
    $params[":offset"] = $offset;
    $params[":count"] = $countOnPage;
    $pageResults = $stmt->fetch(PDO::FETCH_ASSOC);
    $total=0;
    if($results){
        $total = (int) $pageResult["total"];
    }

    $e = $stmt->errorInfo();
    flash($e[2]);

    $total_pages = ceil($total / $countOnPage);
    $offset = ($page-1) * $countOnPage;
    
    //$db = getDB();
    $stmt = $db->prepare($dataQuery);
     foreach($params as $key=>$val) {
        if ($key == ":offset" || $key == ":count") {
            $stmt->bindValue($key,$val, PDO::PARAM_INT);
        }
        else{
            $stmt->bindValue($key,$val);
        }
    }
    /* $stmt->bindValue(":offset",$offset,PDO::PARAM_INT);
    $stmt->bindValue(":count",$countOnPage,PDO::PARAM_INT);
    $stmt->bindValue(":quantity",$_POST["quantityFilter"],PDO::PARAM_INT); */
    $r = $stmt->execute();
    //$r = $stmt->execute($params);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
        $e = $stmt->errorInfo();
        flash($e[2]);
    }

?>

<h3>List Products</h3>
<form method="POST">
    <div class="form-group">    
        <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
        <select name="category" value="<?php echo $result["category"];?>" >
            <option value="-1">All Categories</option>
            <?php foreach ($cats as $cat): ?>
                <option value="<?php safer_echo($cat["category"]); ?>"
                ><?php safer_echo($cat["category"]); ?></option>
            <?php endforeach; ?>
        </select>
        <input name ="order" label="Ascending" type=radio value="asc"/>
        <label for="ASC">Ascending</label>
        <input name ="order" label="Descending" type=radio value="desc"/>
        <label for="DESC">Descending</label>
        <input name ="sort"  type=radio value="price"/>
        <label for="price">Price</label>
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
                        <a type="button" href="user_view_products.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>