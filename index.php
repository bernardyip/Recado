<html>
	<head>
		<title>Recado</title>
	</head>
	<body>
		<?php include('banner.php') ?>
		<h1>Welcome to Recado</h1>
		<p>$$$$EZPZ MONEY$$$$ SIGN UP NOW ABOVE AND REAPS THE BENEFIT OF YOUR LIFETIME</p>
		<p> Here are some tasks for you to see: </p>
		<?php 
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'select_category_query', "SELECT c.id, c.name FROM public.category c");
		$result = pg_execute($dbcon, 'select_category_query', array());
		while ($row = pg_fetch_array($result)) { 
			pg_prepare($dbcon, 'count_category_query', "SELECT COUNT(*) as count FROM public.task t WHERE t.category_id=$1");
			$count_result = pg_execute($dbcon, 'count_category_query', array($row['id']));
			$count_result = pg_fetch_array($count_result); ?>
			<h3><?=$row['name']?> (<?=$count_result['count']?> tasks)</h3>
	<?php 	pg_prepare($dbcon, 'select_task_in_category_query', "SELECT t.id, t.name, t.description FROM public.task t WHERE t.category_id=$1 LIMIT 3");
			$cat_result = pg_execute($dbcon, 'select_task_in_category_query', array($row['id']));
			while ($task_row = pg_fetch_array($cat_result)) {
				echo $task_row['name'] . "<br />";
			}
			
			if ($count_result['count'] > 3) {
				echo "and more...<br />";
			}
		}
		?>
	</body>
</html>