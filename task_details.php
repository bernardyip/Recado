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
		} else {
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
				
				echo "<br />";
				echo "id: " . $task_id . " <br />";
				echo "name: " . $task_name . " <br />";
				echo "description: " . $task_description . " <br />";
				echo "postal code: " . $task_postal_code . " <br />";
				echo "location: " . $task_location . " <br />";
				echo "start time: " . $task_start_time . " <br />";
				echo "end time: " . $task_end_time . " <br />";
				echo "offer: " . $task_offer_amount . " <br />";
				echo "created time: " . $task_created_time . " <br />";
				echo "updated time: " . $task_updated_time . " <br />";
				echo "category: " . $task_category_name . " <br />";
				echo "creator: " . $task_creator_username . " <br />";
?>
				
<?php 		}
		}
		?>
	</body>
</html>
		