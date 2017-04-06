  	<div class="tm-header">
  		<div class="container">
  			<div class="row">
  				<div class="col-lg-6 col-md-4 col-sm-3 tm-site-name-container">
  					<a href="/" class="tm-site-name">Recado</a>	
  				</div>
	  			<div class="col-lg-6 col-md-8 col-sm-9">
	  				<div class="mobile-menu-icon">
		              <i class="fa fa-bars"></i>
		            </div>
	  				<nav class="tm-nav" style="float: right;">
						<ul>
                        <?php if (isset($_SESSION['username'])) { ?>
                            <?php $page = $_SERVER['SCRIPT_NAME']; ?>
                            <?php if (strpos($page, "/profile.php") !== false) { ?>
                                <li><a href="#" class="active"><?php echo $_SESSION['username'] ?></a></li>
                            <?php } else { ?>
                                <li><a href="/profile.php"><?php echo $_SESSION['username'] ?></a></li>
                            <?php } ?>
                            <?php if (strpos($page, "/tasks.php") !== false) { ?>
                                <li><a href="#" class="active">Tasks</a></li>
                            <?php } else { ?>
                                <li><a href="/tasks.php">Tasks</a></li>
                            <?php } ?>
                            <?php if (strpos($page, "/mytasks.php") !== false) { ?>
                                <li><a href="#" class="active">My Tasks</a></li>
                            <?php } else { ?>
                                <li><a href="/mytasks.php">My Tasks</a></li>
                            <?php } ?>
                            <?php if (strpos($page, "/mybids.php") !== false) { ?>
                                <li><a href="#" class="active">My Bids</a></li>
                            <?php } else { ?>
                                <li><a href="/mybids.php">My Bids</a></li>
                            <?php } ?>
                            <?php if (strpos($page, "/stats.php") !== false) { ?>
                                <li><a href="#" class="active">Statistics</a></li>
                            <?php } else { ?>
                                <li><a href="/stats.php">Statistics</a></li>
                            <?php } ?>
                            <li><a href="/login.php?action=logout">Log Out</a></li>
                        <?php } else { ?>
                            <li><a href="/login.php">Login</a></li>
                            <li><a href="/register.php">Register</a></li>
                        <?php } ?>
						</ul>
					</nav>		
	  			</div>				
  			</div>
  		</div>	  	
  	</div>