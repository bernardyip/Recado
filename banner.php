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
                            <li><a href="/profile.php"><?php echo $_SESSION['username'] ?></a></li>
                            <li><a href="/tasks.php">Tasks</a></li>
                            <li><a href="/mytasks.php">My Tasks</a></li>
                            <li><a href="/mybids.php">My Bids</a></li>
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