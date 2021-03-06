<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
//save data if we submitted the form
if (isset($_POST["saved"])) {
    $isValid = true;
    //check if our email changed
    $newEmail = get_email();
    if (get_email() != $_POST["email"]) {
        //TODO we'll need to check if the email is available
        $email = $_POST["email"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where email = :email");
        $stmt->execute([":email" => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Email already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newEmail = $email;
        }
    }
    $newUsername = get_username();
    if (get_username() != $_POST["username"]) {
        $username = $_POST["username"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
        $stmt->execute([":username" => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Username already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newUsername = $username;
        }
    }
    if ($isValid) {
        $userID = null;
        $currentPass = null;
        $newVisibility = $_POST["visibility"];
        $stmt = $db->prepare("UPDATE Users set email = :email, username = :username, visibility = :visibility where id = :id");
        $r = $stmt->execute([
            ":email"=>$newEmail,
            ":username"=>$newUsername,
            ":visibility"=>$newVisibility, 
            ":id" => get_user_id()
            
            ]);
        if ($r) {
            flash("Updated profile");
        }
        else {
            echo var_export($stmt->errorInfo(), true);
            flash("Error updating profile");
        }
        
       if (!empty($_POST["newPassword"]) && !empty($_POST["confirm"]) && empty($_POST["password"])) {
            flash("Please enter your current password"); }
        
        if (!empty($_POST["newPassword"]) && !empty($_POST["confirm"]) && !empty($_POST["password"])) {
            $currentPass = $_POST["password"];
            $stmt = $db->prepare("SELECT password from Users WHERE id = :id");
            $params = array(":id" => get_user_id());
            $r = $stmt->execute($params);
           $e = $stmt->errorInfo();
            if ($e[0] != "00000") {
                flash("Something went wrong, please try again");  }
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result["password"])) {
                
                $DBPassHash = $result["password"];
            if (password_verify($currentPass, $DBPassHash)) {
                if ($_POST["newPassword"] == $_POST["confirm"]) {
                    $newPassword = $_POST["newPassword"];
                    $newPassHash = password_hash($newPassword, PASSWORD_BCRYPT);
                    //this one we'll do separate
                    $stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
                    $r = $stmt->execute([":id" => get_user_id(), ":password" => $newPassHash]);
                
                    if ($r) {
                        flash("Reset Password"); }
                    else {
                        flash("Error resetting password"); }
            }
            }   
        }
    } 
//fetch/select fresh data in case anything changed
        $stmt = $db->prepare("SELECT email, username from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => get_user_id()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email = $result["email"];
            $username = $result["username"];
            //let's update our session too
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
        }
    }
    else {
        //else for $isValid, though don't need to put anything here since the specific failure will output the message
    }
}


?>

    <form method="POST">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
    </div>    
        
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
    </div>    
        <!-- DO NOT PRELOAD PASSWORD-->
    <div class="form-group"> 
        <label for="pw">Password</label>
        <input type="password" name="password"/>
    </div>
    <div class="form-group">
        <label for="npw">New Password</label>
        <input type="password" name="newPassword"/>
    </div>   
    <div class="form-group">
        <label for="cpw">Confirm Password</label>
        <input type="password" name="confirm"/>
    </div>
    <div class="form-group">
    <label for="visibility">Public Profile</label>
    <input type="hidden" name="visibility" value="0" />
    <input type="checkbox" name="visibility" value="1" /> 
    </div>        
        <input type="submit" name="saved" value="Save Profile"/>
    </form>
<?php require(__DIR__ . "/partials/flash.php");