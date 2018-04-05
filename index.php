<?php
require('resources/php/config.php');
$now = time();
$today = new DateTime();

/*******************This part is to set up the database and the tables****************/
$_connection = mysqli_connect(config::$db_host,config::$db_user,config::$db_password);
if($_connection){
	$_create_db = mysqli_query($_connection,"CREATE DATABASE IF NOT EXISTS ".config::$db_name." DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci");
	if($_create_db){
		
	mysqli_select_db($_connection,config::$db_name);
	
	$_create_users_table = mysqli_query($_connection,"CREATE TABLE `users` 
													(`user_id` int(255) NOT NULL,
												  `username` varchar(25) NOT NULL,
												  `password` varchar(255) NOT NULL,
												  `timestamp` int(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1");
						
	
	$_add_users_primary_key = mysqli_query($_connection,"ALTER TABLE `users` ADD PRIMARY KEY (`user_id`)");
		
	$_add_user_sample =mysqli_query($_connection,"INSERT INTO `users` (`user_id`, `username`, `password`, `timestamp`) 
												VALUES(1518974389, 'secretary', 'matt', 1518972470)");
	
	$_create_activities_table = mysqli_query($_connection,"CREATE TABLE `activities` 
														(`activity_id` varchar(255) NOT NULL,
													  `user_id` int(255) NOT NULL,
													  `subject` varchar(255) NOT NULL,
													  `detail` longtext NOT NULL,
													  `time_created` int(255) NOT NULL,
													  `event_date` date NOT NULL,
													  `event_time` time NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1");
						
	$_add_activities_indexes = mysqli_query($_connection,"ALTER TABLE `activities` ADD PRIMARY KEY (`activity_id`), ADD KEY `user_id` (`user_id`)");
	
	$_add_activities_sample =mysqli_query($_connection,"INSERT INTO `activities` (`activity_id`, `user_id`, `subject`, `detail`, `time_created`, `event_date`, `event_time`) 
														VALUES('Matthew1518972568', 1518974389, 'NYSC Registration List', 'Submit NYSC registration list to the senate as requested', 1518972568, '2018-03-05', '14:00:00')");

	$_add_foreign_key = mysqli_query($_connection,"ALTER TABLE `activities`
													ADD CONSTRAINT `user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE");
		}
		mysqli_close($_connection);
}
else{
	echo "<h2 class=\"text-center\">Something went wrong</h2>";
}
/*******************************************************************************************/

class database{
public $connection;
	function __construct(){
		$this->connection = new MySQLi(config::$db_host,config::$db_user,config::$db_password,config::$db_name);
		if ($this->connection->connect_error) {
			echo "There was an error connecting to the database";
			die();
		}
	}
	function query($q){
		$obj = $this->connection->query($q);
			if(!$this->connection->error){
				return $obj;
			}
			else{
?>
	<div style="text-align:center">
	<h2>Something isn't right</h2>
	<i><?php echo $this->connection->error ?></i>
	<h4>check your configuration in config.php <br/><br/> <a href="READ ME.txt">read this system guide </a></h4>
	</div>
	<?php	
	die();
				}
	}
}


$connection = new database();

if(isset($_POST['add_event']) && isset($_COOKIE['todo_user'])){
	$user = $_COOKIE['todo_user'];
$user_id = $_COOKIE['todo_user_id'];
$activity_id = $user.$now;
$event_label = $_POST['event_label'];
$event_detail = $_POST['event_detail'];
$event_date = $_POST['event_date'];
$event_time = $_POST['event_time'];
if(new DateTime($event_date.', '.$event_time) < $today){
	$addEventReport = "Event <b>$event_label</b> cannot be created<br/>Date set has passed";
	}
else{
$connection->query("INSERT INTO activities (activity_id,user_id,subject,detail,time_created,event_date,event_time) VALUES('$activity_id',$user_id,'$event_label','$event_detail',$now,'$event_date','$event_time')");
if($connection->connection->affected_rows == 1){
	$addEventReport = "New event <b>$event_label</b> created successfully";
}
else{
	$addEventReport = "Event <b>$event_label</b> could not be created due to some errors";
			}
		}
	}
//This handles login
else if(isset($_POST['login'])){
	$u = $_POST['login_username'];
	$p = $_POST['login_password'];
	$verifyUser = $connection->query("SELECT user_id,username,password FROM users WHERE (username='$u' AND password='$p')");
	if($verifyUser->num_rows == 1){
		setcookie('todo_user',$u,time()+(3600*24*30),"/");
		setcookie('todo_user_id',$verifyUser->fetch_array(MYSQLI_ASSOC)['user_id'],time()+(3600*24*30),"/");
		header("location: ../todo");
		exit();
	}
	else{
		$loginError = "Incorrect username or password";
	}
$defaultTab = 0;
}
//this handles signup
else if(isset($_POST['signup'])){
	if($_POST['new_password'] != $_POST['new_password2']){
		$signupError = "passwords do not match";
		$defaultTab = 1;
	}
	else{
			$u = $_POST['new_username'];
			$p = $_POST['new_password2'];
			$id = time()+rand(1000,9999);
		//check for existence of the username
			if($connection->query("SELECT username FROM users WHERE username = '$u'")->num_rows == 0){
			$connection->query("INSERT INTO users (user_id,username,password,timestamp) VALUES($id,'$u','$p',$now)");
			if($connection->connection->affected_rows == 1){
				$signupSuccess = "Sign up successful, login now";
			$defaultTab = 0;
				}	
			}
			else{
			$signupError = "username $u already exist, choose another";
		$defaultTab = 1;	
			}
			
			
	}

}
else if(isset($_POST['logout'])){
			setcookie('todo_user',$u,time()-3600,"/");
			setcookie('todo_user_id',$u,time()-3600,"/");
			header("location: ../todo");
			exit();
}

?>

<html>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<head>
<title>STLS</title>
<link href="resources/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
<link href="resources/css/mato.css" type="text/css" rel="stylesheet" />
<style>
body{
	background-color:white;
}
#header{
position:fixed;
z-index:100;
width:100%;
}
.container-fluid{
	padding-top:90px;
}
.sidebar{
	height:100vh;
	position:fixed;
}

.event-color-sample{
	border-radius:50%;
	padding:10%;
	margin:1%;
	box-shadow:0px 5px 5px rgba(0,0,0,0.2);
}
.login-signup-container{
	width:40%;
	margin-top:20px;
	padding:10px 20px;
}
.event-countdown-container{
	margin:20px 0px;
	box-shadow:0px 5px 5px rgba(0,0,0,0.05) inset;
}
.event-stats-container>span{
	height:70px;
}
span.stat-figure{
	background-color:white;
	color:black;
	font-weight:bold;
	font-size:20px;
	border:1px solid #e3e3e3;
	padding:5px;
	border-radius:10px;
}
.past-event-bg{
	background-color:rgba(100,0,0,0.7);
	border : 1px solid rgba(100,0,0,1);
}
.pending-event-bg{
	background-color:rgba(0,100,0,0.7);
	border : 1px solid rgba(0,100,0,1);
}
.total-event-bg{
	background-color:rgba(32, 67, 92,0.5);
	border : 1px solid rgba(32, 67, 92,1);
}
.past-event-bg,.pending-event-bg,.total-event-bg{
	color:white;
}
.LHS{
	height:80vh;
	overflow-y:hidden;
}
.LHS:hover{
	overflow-y:auto;
}
@media all and (max-width:992px){
	.sidebar{
			height:auto;
		position:relative;
	}
	.LHS{
	height:auto;
}
	.main{
		min-height:500px;
	}
.login-signup-container{
	margin-top:100px;
	width:90%;
	padding:10px;
	}
}
</style>
</head>
<body>
<div id="header" class="site-color-background white">
<div class="row">
<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
<h2 class="float-left">Secretarial Todo List System</h2>
<p class="float-right italic">...towards productivity</p></span>
</div>
<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
<div id="filter-holder"></div>
</div>
</div>
</div>
<div class="container-fluid">

<?php
//If no user is logged in
if(!isset($_COOKIE['todo_user']) && !isset($_COOKIE['todo_user_id'])){	
?>
<style>
.container-fluid{
	padding-top:50px;
}
#header{
	background-color:white;
	color:#20435C;
}
</style>
<div class="row site-color-background" style="height:90vh">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

<div class="login-signup-container white-background border-radius-3 margin-auto" data-action="switch-tab" data-default-tab="<?php echo (isset($defaultTab) ? $defaultTab : 0)?>" >

<div class="tabs-container">

<div class="login-form-container" data-tab-index="0">
<h1 class="text-center custom-color">Login</h1>
<h3 class="text-center red">
<?php
echo (isset($loginError) ? $loginError : "" );
?>
</h3>

<h3 class="text-center green">
<?php
echo (isset($signupSuccess) ? $signupSuccess : "" );
?>
</h3>

<p class="text-right blue" data-switch-action="change-tab"  data-switch-to="">Sign up instead</p>

<form action="<?php $_PHP_SELF ?>" method="post">
<div class="form-group">
<input type="text" class="form-control" name="login_username" placeholder="Username" value="<?php echo (isset($_POST['login_username']) ? $_POST['login_username']: "" ) ?>" required />
</div>
<div class="form-group">
<input type="password" class="form-control" name="login_password" placeholder="Password" required/>
</div>
<div class="form-group" style="padding:0px 20%">
<input type="submit" class="btn btn-primary btn-block" name="login" value="Login"/>
</div>
</form>
</div>


<div class="signup-form-container" data-tab-index="1">
<h1 class="text-center custom-color">Sign up</h1>
<p class="text-right blue" data-switch-action="change-tab" data-switch-to="">Login</p>

<h3 class="text-center red">
<?php
echo (isset($signupError) ? $signupError : "" );
?>
</h3>

<form action="<?php $_PHP_SELF ?>" method="post">

<div class="form-group">
<input type="text" class="form-control padding-2" name="new_username" placeholder="Choose a username" value="<?php echo (isset($_POST['new_username']) ? $_POST['new_username']: "" )?>" required/>
</div>

<div class="form-group">
<input type="password" class="form-control padding-2" name="new_password" placeholder="create password" required/>
</div>

<div class="form-group">
<input type="password" class="form-control padding-2" name="new_password2" placeholder="repeat password" required/>
</div>

<div class="form-group" style="padding:0px 20%">
<input type="submit" class="btn btn-primary btn-block" name="signup" value="Sign up"/>
</div>
</form>
</div>


</div>

</div>
</div>
</div>

<?php
}
else{	
$currentUser = $_COOKIE['todo_user'];
$currentUser_id = $_COOKIE['todo_user_id'];
$user_total_activities = $connection->query("SELECT activity_id FROM activities WHERE user_id = $currentUser_id")->num_rows;
$user_total_past_activities = $connection->query("SELECT activity_id FROM activities WHERE (user_id = $currentUser_id AND event_date < NOW())")->num_rows;
$user_total_pending_activities = $connection->query("SELECT activity_id FROM activities WHERE (user_id = $currentUser_id AND event_date > NOW())")->num_rows;
	
if(isset($_GET['filter']) && $_GET['filter']=="all"){
$getEvents = $connection->query("SELECT * FROM activities LEFT JOIN users USING(user_id) WHERE (users.user_id = $currentUser_id) ORDER BY activities.event_date DESC");
$noEvent = "There is no event created yet";
$eventHeading = "ALL ACTIVITIES";
}
else if(isset($_GET['filter']) && $_GET['filter']=="past"){
$getEvents = $connection->query("SELECT * FROM activities LEFT JOIN users USING(user_id) WHERE (users.user_id = $currentUser_id AND activities.event_date < NOW()) ORDER BY activities.event_date DESC");
$noEvent = "There is no past event";
$eventHeading = "PAST ACTIVITIES";

}
else if(isset($_GET['filter']) && $_GET['filter']=="pending"){
$getEvents = $connection->query("SELECT * FROM activities LEFT JOIN users USING(user_id) WHERE (users.user_id = $currentUser_id AND activities.event_date >= NOW()) ORDER BY activities.event_date DESC");
$noEvent = "There is no pending event";
$eventHeading = "PENDING ACTIVITIES";

}
else{
$getEvents = $connection->query("SELECT * FROM activities LEFT JOIN users USING(user_id) WHERE (users.user_id = $currentUser_id) ORDER BY activities.event_date DESC");
$noEvent = "There is no event created yet";
$eventHeading = "ALL ACTIVITIES";
		}		
?>
<div class="row" >
<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 sidebar opac-5-site-color-background site-color">
<div class="padding-5 LHS">
<div class="row">
<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
<span class="glyphicon glyphicon-user white-background site-color padding-10 round e3-border icon-size-25"></span>
<span class="bold font-20"><?php echo $_COOKIE['todo_user']?></span>
</div>
<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-right">
<form action="<?php $_PHP_SELF ?>" method="post">
<input type="submit" name="logout" class="btn btn-warning" value="Logout"/>
</form>
</div>
</div>

<div data-action="togglex">

<?php if(isset($addEventReport)){
	?>
<div class="site-color-background white padding-10 margin-3 text-center">
<?php echo $addEventReport; ?>
</div>
	<?php
}
?>

<div class="row text-center event-stats-container margin-10-0">
<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 ">
	<div class="margin-2 pending-event-bg">
		Pending<br/>Activities <br/> <span class="stat-figure"><?php echo $user_total_pending_activities ?></span>
	</div>
</div>
<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 ">
	<div class="margin-2 past-event-bg">
		Past<br/>Activities  <br/> <span class="stat-figure"><?php echo $user_total_past_activities ?></span>
	</div>
</div>
<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 ">
	<div class="margin-2 total-event-bg">
		Total<br/>Activities  <br/> <span class="stat-figure"><?php echo $user_total_activities ?></span>
	</div>
</div>

</div>
<div class="white-background text-center padding-10 font-20"><span class="glyphicon glyphicon-plus-sign"></span> New Activity</div>
<div class="border-radius-2 padding-5 full-width" data-action="animate-bgcolor">

<form action="<?php $_PHP_SELF ?>" method="POST">
<div class="form-group">
			<label class="white">Label</label>
			<input class="form-control" name="event_label" placeholder="Label the activity"  maxlength="50" type="text" value="<?php echo(isset($_POST['event_label']) ? $_POST['event_label'] : '') ?>"/>
			</div>
			
<div class="form-group">
			<label class="white">Details</label>
			<textarea class="form-control" name="event_detail" placeholder="More details about this activity" value="<?php echo(isset($_POST['event_detail']) ? $_POST['event_detail'] : '') ?>"></textarea>
			</div>
	<div class="form-group width-70p margin-auto">
			<label class="white text-center">Date</label>
			<input class="form-control" name="event_date" type="date" value="<?php echo(isset($_POST['event_date']) ? $_POST['event_date'] : '') ?>" required/>
	</div>
	<div class="form-group width-70p margin-auto">
			<label class="white text-center">Time</label>
			<input class="form-control" name="event_time" type="time" value="<?php echo(isset($_POST['event_time']) ? $_POST['event_time'] : '') ?>" required/>
			</div>
			
	
<div class="form-group margin-10-0">
			<input type="submit" class="btn btn-primary btn-lg btn-block" name="add_event" value="Add Activity"/>
			</div>
					
			</form>

</div>
</div>

</div>

</div>
<div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-8 col-sm-offset-4 col-xs-12 main" id="timeline" >

<div id="filter-temp-holder" style="display:none" >
<form action="../todo/#timeline" method="get">
<div class="row">
<div class="form-group col-lg-4 col-lg-offset-4 col-md-4 col-md-offset-4 col-sm-4 col-xs-6">
<select name="filter" class="form-control">
<option value="all">All Activities</option>
<option value="pending">Pending Activities</option>
<option value="past">Past Activities</option>
</select>
</div>
<div class="form-group col-lg-4 col-md-4  col-sm-4 col-xs-6" >
<button type="submit" class="btn btn-primary" value="Filter Events"><span class="glyphicon glyphicon-filter"></span> Filter Activities</button>
</div>
</div>
</form>
<h4 class="text-center"><?php echo $eventHeading ?></h4>
</div>

<div class="text-center padding-5 white-background">

<?php
if($getEvents->num_rows == 0){
echo $noEvent;	
}
else{
while($event = $getEvents->fetch_array(MYSQLI_ASSOC)){
	$label = $event['subject'];
	$detail = $event['detail'];
	$event_date = new DateTime($event['event_date'].' '.$event['event_time']);
	$date_difference = $today->diff($event_date);
	$days_remaining = $date_difference->days;
	$hours_remaining = $date_difference->h;
	$minutes_remaining = $date_difference->i;
	$seconds_remaining = $date_difference->s;
	?>
<div class="event-countdown-container padding-3 border-radius-3 <?php echo ($event_date < $today ? "past-event-bg" : "pending-event-bg")?>" >
<div class="row text-center">
<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
<h4><span class="glyphicon glyphicon-calendar"></span>  <?php echo $event['event_date']?></h4>
</div>
<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
<h4><span class="glyphicon glyphicon-time"></span>  <?php echo $event['event_time'] ?></h4>
</div>
</div>
<?php
if($event_date < $today){
	?>
<div data-action="countdown" data-seconds="0" data-label="<?php echo $label ?>" data-countdown-detail="<?php echo $detail ?>"></div>
<?php
}
else{
?>
<div data-action="countdown" data-days="<?php echo $days_remaining ?>" data-hours="<?php echo $hours_remaining ?>" data-minutes="<?php echo $minutes_remaining ?>" data-seconds="<?php echo $seconds_remaining ?>" data-label="<?php echo $label ?>" data-countdown-detail="<?php echo $detail ?>"></div>
<?php
}
?>
</div>	
<?php
	}
}
?>

</div>
</div>

</div>
<?php
}
?>
</div>
<script  type="text/javascript" language="javascript" src="resources/js/JMatt/countdown.js"></script>
<script  type="text/javascript" language="javascript" src="resources/js/JMatt/tabs.js"></script>
<script  type="text/javascript" language="javascript" src="resources/js/JMatt/coloranimation.js"></script>
<script>
document.querySelector("#filter-holder").innerHTML = document.querySelector("#filter-temp-holder").innerHTML;
</script>
</body>
</html>