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
		?>
		
		<h1>Create a task:</h1>
		
		<h1>My Tasks:</h1>
		<?php 
			$query  = 'SELECT b1.task_id, b1.amount as max_bid, b1.user_id, u.username, t.name, t.offer_amount ';
			$query .= 'FROM public.bid b1 ';
			$query .= 'INNER JOIN ( ';
			$query .=     'SELECT b2.task_id, MAX(b2.amount) ';
			$query .=     'FROM public.bid b2 ';
			$query .=     'GROUP BY b2.task_id ';
			$query .= ') max_bid ON b1.task_id = max_bid.task_id AND b1.amount=max_bid.max ';
			$query .= 'INNER JOIN public.task t ON t.id = b1.task_id ';
			$query .= 'INNER JOIN public.user u ON b1.user_id = u.id ';
			$query .= 'WHERE t.creator_id = $1 ';
			$query .= 'ORDER BY t.id;';
			$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
			pg_prepare($dbcon, 'my_query', $query);
			$result = pg_execute($dbcon, 'my_query', array($_SESSION['id']));
			$task_id = -1;
			while ($row = pg_fetch_array($result)) { 
				if ($task_id != intval($row['task_id'])) { 
					echo $task_id==-1? "<br />" : "";
					$task_id = intval($row['task_id']); ?>
					<a href="task_details.php?task=<?=$row['task_id']?>"><?=$row['name']?></a> --> Offering $<?=$row['offer_amount']?>, Current max bid: <?=$row['max_bid']?> by <?=$row['username']?>
<?php 			} else {
					echo ", " . $row['username'];
				}
			}
		?>
		<h1>My Bids:</h1>
		<?php 
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'bid_details_query', "SELECT b.id, b.bid_time, b.amount, b.selected, t.name as task_name, t.id as task_id FROM bid b INNER JOIN task t ON b.task_id = t.id WHERE b.user_id=$1");
		$result = pg_execute($dbcon, 'bid_details_query', array($_SESSION['id']));
		while ($row = pg_fetch_array($result)) { ?>
			<a href="task_details.php?task=<?=$row['task_id']?>"><?=$row['task_name']?></a> --> bidded $<?=$row['amount']?> @ <?=(new datetime($row['bid_time']))->format('Y-m-d H:i:s')?> <?=$row['selected']==t? '(Won)' : '(Pending)';?>
  <?php }
		?>
		
		<h2>Search</h2>
		<input id="search" type=text onchange="search()"/>
		
		<?php 
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'select_category_query', "SELECT c.id, c.name FROM public.category c");
		$result = pg_execute($dbcon, 'select_category_query', array());
		while ($row = pg_fetch_array($result)) { 
			pg_prepare($dbcon, 'count_category_query', "SELECT COUNT(*) as count FROM public.task t WHERE t.category_id=$1");
			$count_result = pg_execute($dbcon, 'count_category_query', array($row['id']));
			$count_result = pg_fetch_array($count_result); ?>
			<h3><?=$row['name']?> (<?=$count_result['count']?> tasks)</h3>
	<?php 	pg_prepare($dbcon, 'select_all_task_in_category_query', "SELECT t.id, t.name, t.description FROM public.task t WHERE t.category_id=$1");
			$cat_result = pg_execute($dbcon, 'select_all_task_in_category_query', array($row['id']));
			while ($task_row = pg_fetch_array($cat_result)) { ?>
				<a href="task_details.php?task=<?=$task_row['id']?>"><?=$task_row['name']?></a><br />
<?php 		}
		}
		?>
	</body>
</html>