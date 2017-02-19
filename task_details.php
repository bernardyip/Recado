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
		//If not logged in
		if (!isset($_SESSION['username'])) {
			header('Refresh: 0; URL=http://localhost/login.php');
			die();
		}
		
		if (!isset($_GET['task'])) {
			header('Refresh: 0; URL=http://localhost/task.php');
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
			$task_created_time = $row['craeted_time'];
			$task_updated_time = $row['updated_time'];
			$task_category_name = $row['category_name'];
			$task_creator_username = $row['username'];
			
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
			echo "<tr><td>creator: </td><td>" . $task_creator_username . " </td></tr>";
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
			if ($task_creator_username==$_SESSION['username']) { //is the owner of this task
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
		
		<h1>Comments</h1>
<?php 	$query  = "SELECT c.comment, c.created_time, u.username ";
		$query .= "FROM public.comment c ";
		$query .= "INNER JOIN public.user u ON c.user_id = u.id ";
		$query .= "WHERE c.task_id = $1;";
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'select_comments_query', $query);
		$result = pg_execute($dbcon, 'select_comments_query', array($_GET['task']));
		while ($row = pg_fetch_array($result)) { ?>
			<p><?=$row['username']?> : <?=$row['comment']?> @ <?=(new datetime($row['created_time']))->format('Y-m-d H:i:s')?></p>
<?php 	} ?>
	</body>
</html>
		