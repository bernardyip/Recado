<html>
	<head>
		<title>Recado</title>
	</head>
	<body>		
	<?php 
		include('banner.php');
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$searchTerm = pg_escape_string($_POST['searchTerm']);
			$method = pg_escape_string($_POST['method']);
			$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
			if ($method == 'search') { 
				pg_prepare($dbcon, 'search_task_query', "SELECT t.id, t.name FROM public.task t WHERE t.name LIKE $1;");
				$result = pg_execute($dbcon, 'search_task_query', array("%".$searchTerm."%"));
				if (pg_num_rows($result) >= 1) { ?>
					<h3>Here are the results:</h3>
					<?php while ($row = pg_fetch_array($result)) { ?>
						<a href="task_details.php?task=<?=$row['id']?>"><?=$row['name']?></a><br />
					<?php   }		
				}else { ?>
					<h3>Unable to find any tasks matching "<?=$searchTerm?>"</h3>
				<?php 	} 
			} 
		} else { ?>
			<h3>Please input query to search for matching tasks</h3>
	<?php   
		} ?>
	</body>
</html>