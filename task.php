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
		<h1>My Bids:</h1>
		
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
	<?php 	pg_prepare($dbcon, 'select_task_in_category_query', "SELECT t.id, t.name, t.description FROM public.task t WHERE t.category_id=$1");
			$cat_result = pg_execute($dbcon, 'select_task_in_category_query', array($row['id']));
			while ($task_row = pg_fetch_array($cat_result)) {
				echo $task_row['name'] . "<br />";
			}
		}
		?>
	</body>
</html>