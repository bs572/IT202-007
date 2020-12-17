<?php require_once(__DIR__ . "/../../partials/nav.php"); ?>

<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: ../../login.php"));
}
?>

<?php

$page = 1;
$countOnPage = 10;
$dataQuery = "";
$pageQuery = "";
$category = "";
$search = "";
$sort = "";
$order = "";
$userID = get_user_id();
$cartID = 0;
$productID = 0;
$results = [];
$params = [];
$quantity = 0;
$subtotal = 0;
$cumulativeTotal = 0;
$address = "";

$dataQuery = "SELECT * From OrderItems join Products on Products.id WHERE 1=1";
$pageQuery = "SELECT COUNT (*) as total from Orders WHERE 1=1";

if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}


if (isset ($_POST["category"])) {
    $dataQuery .= " AND category :=cat";
    $pageQuery .= " AND category :=cat";
    $params["cat"] = $category;
}


if (isset ($_POST["minimumDate"])&& isset ($_POST["maximumDate"])) {
    $dataQuery .= " AND created BETWEEN :minDate AND maxDate ";
    $pageQuery .= " AND created BETWEEN :minDate AND maxDate ";
    $params["cat"] = $category;
}

$dataQuery .= " LIMIT :offset, :count";
    
$db = getDB();
$stmt = $db->prepare($pageQuery);
$stmt->execute($params);
$results = $stmt->fetch(PDO::FETCH_ASSOC);
$total=0;
if($results){
    $total = (int) $result["total"];
}

$total_pages = ceil($total / $countOnPage);
$offset = ($page-1) * $per_page;

//$db = getDB();
$stmt = $db->prepare($dataQuery);
$stmt->bindValue(":offset",$offset,PDO::PARAM_INT);
$stmt->bindValue(":count",$count,PDO::PARAM_INT);
$stmt->bindValue(":quantity",$_POST["quantityFilter"],PDO::PARAM_INT);
$r = $stmt->execute();
//$r = $stmt->execute($params);
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the results");
}

?>
    <h3>Order History</h3>
    
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
        <label>Sort by Ascending Price</label>
        <input type="radio" value ="sort" name = "sort"/>
    </div>
</form>    
    <div class="results">
        <?php if (count($results) > 0): ?>
                <?php foreach ($results as $r): ?>
                    <div class="card" style="width: 18rem;">
                    <div class="card-body">
                    <h5 class="card-title">Order Number : <?php safer_echo($r["id"]); ?></h5>
                    </div>
                        <div>
                            <a type="button" href="view_order.php?id=<?php safer_echo($r['id']); ?>">View Order</a>
                        </div>
                       <?php $cumulativeTotal += $r["total_price"]; ?>
                       <div class="card" style="width: 18rem;">
                    <div class="card-body">
                    <h5 class="card-title">Total Price:<?php safer_echo($r["total_price"]); ?></h5>
            </div> </div> 
               
                <?php endforeach; ?>
               
            </div>
            </div>
            <?php echo "Total of All Orders: " . $cumulativeTotal ?>
            <div class="form-group">    
        <input name="category" placeholder="Search" value="<?php safer_echo($query); ?>"/>
        <input type=date name="minimumDate"/>
        <input type=date name="maximumDate"/>
        <input type="submit" value="Search" name="search"/>
    </div>

                <?php endif; ?>
                <?php require(__DIR__ . "/../../partials/flash.php");