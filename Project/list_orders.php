<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
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
$total =0;
$address = "";

$dataQuery = "SELECT OrderItems.quantity, OrderItems.unit_price, OrderItems.order_id, Products.name, Products.description From OrderItems join Products on Products.id WHERE 1=1";
$pageQuery = "SELECT COUNT(1) as total from Orders WHERE 1=1";

if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

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
$r = $stmt->execute();
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the results");
    $e = $stmt->errorInfo();
        flash(var_export($e, true));
}

?>
    <h3>Order History</h3>
    <div class="results">
        <?php if (count($results) > 0): ?>
                <?php foreach ($results as $r): ?>
                    <?php  $cumulativeTotal += ($r["unit_price"]*$r["quantity"]); ?>
                    <div class="card" style="width: 18rem;">
                    <div class="card-body">
                    <h5 class="card-title">Order Number : <?php safer_echo($r["order_id"]); ?></h5>
                    <div class="list-group-item">
                    <div>
                        <div>Name:</div>
                        <div><?php safer_echo($r["name"]); ?></div>
                    </div>
                    <div>
                        <div>Price:</div>
                        <div><?php safer_echo($r["unit_price"]); ?></div>
                    </div>
                    <div>
                        <div>Quantity:</div>
                        <div><?php safer_echo($r["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Description:</div>
                        <div><?php safer_echo($r["description"]); ?></div>
                    </div>
                    </div>
                        <div>
                            <a type="button" href="view_order.php?id=<?php safer_echo($r['order_id']); ?>">View Order</a>
                        </div>
                       <div class="card" style="width: 18rem;">
                    </div> 
               
                <?php endforeach; ?>
               
            </div>
            </div>
            <?php echo "Total of All Orders: " . $cumulativeTotal ?>
           

                <?php endif; ?>
                <?php require(__DIR__ . "/partials/flash.php");