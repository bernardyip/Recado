<html>
	<head>
		<title>Recado</title>
		<script type="text/javascript">
			function search() {
				var search = document.getElementById("search").value;
				console.log(search);
			}
		</script>
	</head>
	<body>
<?php   include('banner.php');
		//If not logged in
		if (!isset($_SESSION['username'])) {
			header('Refresh: 0; URL=http://localhost/login.php?next=' . urlencode("/task.php"));
			die();
		}
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		    $task_name = $_POST['name'];
		    $task_description = $_POST['description'];
		    $task_postal_code = $_POST['postal_code'];
		    $task_location = $_POST['location'];
		    $task_start_time = (new DateTime($_POST['task_start_time'], new DateTimeZone('Asia/Singapore')))->format('Y-m-d\TH:i:s');
		    $task_end_time = (new Datetime($_POST['task_end_time'], new DateTimeZone('Asia/Singapore')))->format('Y-m-d\TH:i:s');
		    $task_listing_price = $_POST['listing_price'];
		    $task_created_time = (new DateTime(null, new DateTimeZone("Asia/Singapore")))->format('Y-m-d\TH:i:s\Z');
		    $task_category_id = $_POST['category'];
		    $task_creator_username = $_SESSION['id'];
		    
		    $query  = "INSERT INTO public.task ";
		    $query .= "(name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, status, category_id, creator_id, bid_picked) ";
		    $query .= "VALUES";
		    $query .= "($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12); ";
		    
		    $parameters = array($task_name, $task_description, $task_postal_code, $task_location, $task_start_time, $task_end_time, $task_listing_price, $task_created_time, 'pending', $task_category_id, $task_creator_username, 'f');
		    
		    $dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		    pg_prepare($dbcon, 'create_task_query', $query);
		    $result = pg_execute($dbcon, 'create_task_query', $parameters);
		    
		    header('Refresh: 0; URL=http://localhost/task.php?message=' . urlencode('Task created!'));
		    die();
		}
		
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'select_category_query', "SELECT c.id, c.name FROM public.category c");
		$result = pg_execute($dbcon, 'select_category_query', array()); ?>
		
		<h1>Create a task:</h1>
		<form action="task.php" method="POST">
			<table>
    			<tr><td>name : 					</td><td><input type="text" name="name" 						value="test" /></td></tr>
    			<tr><td>description : 			</td><td><input type="text" name="description" 					value="test desc" /></td></tr>
    			<tr><td>postal_code :			</td><td><input type="text" pattern="[0-9]{6}" 					value="123123" title="Six digit zip code" name="postal_code" /></td></tr>
    			<tr><td>location : 				</td><td><input type="text" name="location" 					value="Singapore" /></td></tr>
    			<tr><td>task start date/time : 	</td><td><input type="datetime-local" name="task_start_time"	value="2017-01-02T11:00" /></td></tr>
    			<tr><td>task end date/time :	</td><td><input type="datetime-local" name="task_end_time"		value="2017-01-02T13:00" /></td></tr>
    			<tr><td>listing price :			</td><td><input type="number" name="listing_price"				value="12" /></td></tr>
    			<tr><td>category : 				</td><td>
    			<select name="category">
<?php               while ($row = pg_fetch_array($result)) { ?>
    				    <option value="<?=$row['id'] ?>"><?=$row['name'] ?></option>
<?php               } ?>
    			</select></td></tr>
			</table>
			<input type="submit" value="Create Task!" />
		</form>
		
		<h1>My Tasks:</h1>
		<?php 
    		$query  = "SELECT t.id as task_id, temp.user_id, temp.username, temp.max_bid, t.name, t.listing_price ";
    		$query .= "FROM public.task t ";
    		$query .= "LEFT OUTER JOIN  ";
    		$query .= 	"(SELECT b1.task_id, b1.amount as max_bid, b1.user_id, u.username, t.name, t.listing_price ";
    		$query .= 	"FROM public.task t ";
    		$query .= 	"INNER JOIN public.bid b1 ON t.id = b1.task_id ";
    		$query .= 	"INNER JOIN ( ";
    		$query .= 	"SELECT b2.task_id, MAX(b2.amount) ";
    		$query .= 	"FROM public.bid b2 ";
    		$query .= 	"GROUP BY b2.task_id ";
    		$query .= ") max_bid ON b1.task_id = max_bid.task_id AND b1.amount=max_bid.max ";
    		$query .= "INNER JOIN public.user u ON b1.user_id = u.id) temp ON temp.task_id = t.id AND temp.name = t.name AND temp.listing_price = t.listing_price ";
    		$query .= "WHERE t.creator_id = $1 ";
    		$query .= "ORDER BY t.id; ";
			$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
			pg_prepare($dbcon, 'my_query', $query);
			$result = pg_execute($dbcon, 'my_query', array($_SESSION['id']));
			$task_id = -1;
			while ($row = pg_fetch_array($result)) { 
				if ($task_id != intval($row['task_id'])) { //new task
					echo $task_id!=-1? "<br />" : ""; //break after the 2nd row onwards
					$task_id = intval($row['task_id']); 
					$max_bid = "";
					if ($row['max_bid'] != '') {
					    $max_bid = ", Current max bid: " . $row['max_bid'] . " by " . $row['username'];
					}
					?>
					<a href="task_details.php?task=<?=$row['task_id']?>"><?=$row['name']?></a> --> Offering <?=$row['listing_price']?> <?=$max_bid ?>
<?php 			} else {
					echo ", " . $row['username'];
				}
			} ?>
		<h1>My Bids:</h1>
		<?php 
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'bid_details_query', "SELECT b.id, b.bid_time, b.amount, b.selected, t.name as task_name, t.id as task_id FROM bid b INNER JOIN task t ON b.task_id = t.id WHERE b.user_id=$1");
		$result = pg_execute($dbcon, 'bid_details_query', array($_SESSION['id']));
		while ($row = pg_fetch_array($result)) { ?>
			<a href="task_details.php?task=<?=$row['task_id']?>"><?=$row['task_name']?></a> --> bidded $<?=$row['amount']?> @ <?=(new datetime($row['bid_time']))->format('Y-m-d H:i:s')?> <?=$row['selected']=='t'? '(Won)' : '(Pending)';?> <br />
<?php   } ?>
		
		<h2>Search</h2>
		<input id="search" type=text onchange="search()"/>
		
		<?php 
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'task_select_category_query', "SELECT c.id, c.name FROM public.category c");
		$result = pg_execute($dbcon, 'task_select_category_query', array());
		while ($row = pg_fetch_array($result)) { 
			pg_prepare($dbcon, 'task_count_category_query', "SELECT COUNT(*) as count FROM public.task t WHERE t.category_id=$1");
			$count_result = pg_execute($dbcon, 'task_count_category_query', array($row['id']));
			$count_result = pg_fetch_array($count_result); ?>
			<h3><?=$row['name']?> (<?=$count_result['count']?> tasks)</h3>
<?php    	pg_prepare($dbcon, 'task_select_all_task_in_category_query', "SELECT t.id, t.name, t.description FROM public.task t WHERE t.category_id=$1");
			$cat_result = pg_execute($dbcon, 'task_select_all_task_in_category_query', array($row['id']));
			while ($task_row = pg_fetch_array($cat_result)) { ?>
				<a href="task_details.php?task=<?=$task_row['id']?>"><?=$task_row['name']?></a><br />
<?php 		}
		}
		?>
	</body>
</html>