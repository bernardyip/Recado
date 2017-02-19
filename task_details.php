<html>
	<head>
		<title>Recardo</title>
		<script type="text/javascript">
			function search() {
				var search = document.getElementById("search").value;
				console.log(search);
			}
		</script>
	</head>
	<body>
		<?php 
		include('banner.php');
		//If not logged in or no specified task
		if (!isset($_SESSION['username'])) {
			header('Refresh: 0; URL=http://localhost/login.php');
			die();
		} else if (!isset($_GET['task'])) {
			header('Refresh: 0; URL=http://localhost/task.php');
			die();
		}
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		    if ($_POST['method'] == 'submit_bid') {
		        $dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		        //Insert if not exists, update if exists query
		        pg_prepare($dbcon, 'update_bid_query', "UPDATE bid SET amount=$1, selected='f' WHERE user_id=$2 AND task_id=$3;");
		        $result = pg_execute($dbcon, 'update_bid_query', array($_POST['bid'], $_SESSION['id'], $_GET['task']));
		        pg_prepare($dbcon, 'insert_bid_query', "INSERT INTO bid (amount, bid_time, selected, user_id, task_id) SELECT $1, $2, $3, $4, $5 WHERE NOT EXISTS (SELECT * FROM bid WHERE user_id=$4 AND task_id=$5);");
		        $result = pg_execute($dbcon, 'insert_bid_query', array($_POST['bid'] ,(new DateTime(null, new DateTimeZone("Asia/Singapore")))->format('Y-m-d\TH:i:s\Z'), 'f', $_SESSION['id'], $_GET['task']));
                header('Refresh: 0; URL=http://localhost/task_details.php?task=' . $_GET['task'] . "&bid_message=" . urlencode('Bid Submitted!'));
		    } else if ($_POST['method'] == 'submit_comment') {
		        $dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		        pg_prepare($dbcon, 'my_query', "INSERT INTO public.comment (comment, created_time, user_id, task_id) VALUES ($1, $2, $3, $4);");
		        $result = pg_execute($dbcon, 'my_query', array($_POST['comment'] ,(new DateTime(null, new DateTimeZone("Asia/Singapore")))->format('Y-m-d\TH:i:s\Z'), $_SESSION['id'], $_GET['task']));
                header('Refresh: 0; URL=http://localhost/task_details.php?task=' . $_GET['task'] . "&comment_message=" . urlencode('Comment Submitted!'));
		    } 
		    die();
		}
		
		$query  = "SELECT t.id, t.name, t.description, t.postal_code, t.location, t.task_start_time, t.task_end_time, t.offer_amount, t.created_time, t.updated_time, c.name as category_name, u.username ";
		$query .= "FROM public.task t ";
		$query .= "INNER JOIN public.user u ON t.creator_id = u.id ";
		$query .= "INNER JOIN public.category c ON t.category_id = c.id ";
		$query .= "WHERE t.id = $1; ";
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'select_task_details_query', $query);
		$result = pg_execute($dbcon, 'select_task_details_query', array($_GET['task']));
		while ($row = pg_fetch_array($result)) { 
			$task_id = $row['id'];
			$task_name = $row['name'];
			$task_description = $row['description'];
			$task_postal_code = $row['postal_code'];
			$task_location = $row['location'];
			$task_start_time = $row['task_start_time'];
			$task_end_time = $row['task_end_time'];
			$task_offer_amount = $row['offer_amount'];
			$task_created_time = $row['created_time'];
			$task_updated_time = $row['updated_time'];
			$task_category_name = $row['category_name'];
			$task_creator_id = $row['username'];
			
			echo "<table>";
			echo "<tr><td>id: </td><td>" . $task_id . " </td></tr>";
			echo "<tr><td>name: </td><td>" . $task_name . " </td></tr>";
			echo "<tr><td>description: </td><td>" . $task_description . " </td></tr>";
			echo "<tr><td>postal code: </td><td>" . $task_postal_code . " </td></tr>";
			echo "<tr><td>location: </td><td>" . $task_location . " </td></tr>";
			echo "<tr><td>start time: </td><td>" . $task_start_time . " </td></tr>";
			echo "<tr><td>end time: </td><td>" . $task_end_time . " </td></tr>";
			echo "<tr><td>offer: </td><td>" . $task_offer_amount . " </td></tr>";
			echo "<tr><td>created time: </td><td>" . $task_created_time . " </td></tr>";
			echo "<tr><td>updated time: </td><td>" . $task_updated_time . " </td></tr>";
			echo "<tr><td>category: </td><td>" . $task_category_name . " </td></tr>";
			echo "<tr><td>creator: </td><td>" . $task_creator_id . " </td></tr>";
			echo "</table>"
?>
<?php 	} ?>

		<h1>Bids</h1>
<?php 	$query  = "SELECT b.id, b.amount, u.username, b.selected ";
		$query .= "FROM public.bid b ";
		$query .= "INNER JOIN public.user u ON b.user_id = u.id ";
		$query .= "WHERE b.task_id = $1 ";
		$query .= "ORDER BY b.amount DESC;";
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'select_bids_query', $query);
		$result = pg_execute($dbcon, 'select_bids_query', array($_GET['task']));
		while ($row = pg_fetch_array($result)) { 
			//Choose what option to show for diff kind of users
			$display = "";
			if ($task_creator_id==$_SESSION['username']) { //is the owner of this task
				if ($row['selected'] == 't') {
					$display = "<a href='#'>Unselect this user</a>";
				} else {
					$display = "<a href='#'>Select this user</a>";
				}
			} else if ($row['selected'] == 't') {
				$display = " (Selected)";
			}
			?>
			<p><?=$row['username']?> bids <?=$row['amount']?> <?=$display?></p>
<?php 	} ?>
		
<?php   //Only for non-owner
        if ($task_creator_id!=$_SESSION['username']) { ?>		
    		<h2>Submit a bid:</h2>
    		<form action="task_details.php?task=<?=$_GET['task'] ?>" method="POST">
    			Bid Amount: <input type="number" name="bid" /> <br /><br />
    			<input type="hidden" value="submit_bid" name="method"/>
    			<input type="submit" value="Submit a bid"/>
    		</form>
    		<p><?=$_GET['bid_message']?></p>
<?php   } ?>

		<h1>Comments</h1>
<?php 	$query  = "SELECT c.comment, c.created_time, u.username ";
		$query .= "FROM public.comment c ";
		$query .= "INNER JOIN public.user u ON c.user_id = u.id ";
		$query .= "WHERE c.task_id = $1 ";
		$query .= "ORDER BY c.created_time DESC;";
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'select_comments_query', $query);
		$result = pg_execute($dbcon, 'select_comments_query', array($_GET['task']));
		while ($row = pg_fetch_array($result)) { ?>
			<p><?=$row['username']?> : <?=$row['comment']?> @ <?=(new datetime($row['created_time']))->format('Y-m-d H:i:s')?></p>
<?php 	} ?>
	
		<h2>Submit a comment:</h2>
		<form action="task_details.php?task=<?=$_GET['task'] ?>" method="POST">
			Comment: <input type="textarea" name="comment" /> <br /><br />
			<input type="hidden" value="submit_comment" name="method"/>
			<input type="submit" value="Comment!"/>
		</form>
		<p><?=$_GET['comment_message']?></p>
	</body>
</html>
		