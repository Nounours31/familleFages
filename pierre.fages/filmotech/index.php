<!DOCTYPE html>
<?php

/* FILMOTECH Website 
	INDEX page
	
	(c) 2013-2015 by Pascal PLUCHON
	http://www.filmotech.fr
*/



// Site parameters
require_once("include/params.inc.php");
require_once("include/config.inc.php");



// Get configuration
$cfg = new CONFIG();

// Connection to database
$debugDB = $cfg->DB_TYPE;
try
{
	if ( $cfg->DB_TYPE == 'sqlite' ) {
	    $db = new PDO('sqlite:'.$cfg->DB_NAME.'.sqlite3');
	} else {
	    $db = new PDO('mysql:host='.$cfg->DB_SERVER.';dbname='.$cfg->DB_NAME, $cfg->DB_USER, $cfg->DB_PASSWORD);
		$db->query("SET NAMES UTF8"); 
	}
}
catch (Exception $e)
{
	die('Erreur db: ['.$debugDB.'] [DB_SERVER : '.$cfg->DB_SERVER.'][DB_NAME:'.$cfg->DB_NAME.'][DB_USER:'.$cfg->DB_USER.'][DB_PASSWORD:'.$cfg->DB_PASSWORD.']' . $e->getMessage());
}

// Query parameters
$search_fields = array( "TitreVF" , "TitreVO" , "Genre" , "Acteurs" , "Realisateurs" , "Commentaires" , "Bonus" , "Reference" );

$search_field = "TitreVF";
$search_label = "Titre";
$search_query = "";

// Last update
$lastUpdate = '?';
$filename = 'update.txt';
if (file_exists($filename)) {
	$handle = fopen($filename, "r");
	$lastUpdate = fread($handle, filesize($filename));
	fclose($handle);
}
$last_update_label = sprintf($last_update,$lastUpdate);

// Preparing database request
// Count number of records
$query = "SELECT count(*) from " . $cfg->DB_TABLE; 
$result = $db->query($query); 

if (is_bool($result))
{
	$total_record = 0;
}
else
{	
	$result_fetch = $result->fetch();
	$total_record = $result_fetch[0];
	$result->closeCursor();
}

$page = 1;
$offset = 0;
$pagination = $paginate;

if ( (isset($_POST['search_query'])) && ($_POST['search_query']!="") ) { 	
	$search_query = stripslashes($_POST['search_query']);
	$search_field = $_POST['search_field'];
	$search_label = $field_labels[$search_field];
	$select = sprintf( "SELECT ID, TitreVF, %s , FilmVu, PretEnCours, Reference FROM %s WHERE %s LIKE '%s' ORDER BY TitreVF", 		$second_column, $cfg->DB_TABLE , $_POST['search_field'] , '%' . addslashes($search_query) . '%' ); 
	$select_count = sprintf( "SELECT COUNT(*) FROM %s WHERE %s LIKE '%s' ORDER BY TitreVF", 
	$cfg->DB_TABLE , $_POST['search_field'] , '%' . addslashes($search_query) . '%' ); 
	$pagination = false;
	// Count number of rows returned
	$result = $db->query($select_count); 
	$result_fetch = $result->fetch();
	$count = $result_fetch[0];
	$result->closeCursor();
} 
else {
	if (isset($_GET['Page'])) $page = $_GET['Page'];
	$offset = ($page-1) * $nb_record_per_page;
	if ($paginate) {
		$select = "SELECT ID, TitreVF, " . $second_column . ", FilmVu, PretEnCours, Reference FROM " . $cfg->DB_TABLE . " ORDER BY TitreVF LIMIT " . $nb_record_per_page . " OFFSET " . $offset;
		$select_count = "SELECT COUNT(*) FROM " . $cfg->DB_TABLE . " ORDER BY TitreVF LIMIT " . $nb_record_per_page . " OFFSET " . $offset;
		$count = 1;
	}
	else  {
		$select = "SELECT ID, TitreVF, " . $second_column . ", FilmVu, PretEnCours, Reference FROM " . $cfg->DB_TABLE . " ORDER BY TitreVF";
		$count = $total_record;
		// $select_count = "SELECT COUNT(*) FROM " . $cfg->DB_TABLE;
	}
}
// echo $select;
$response = $db->query($select);

if ($pagination) $label_movie_count = sprintf( $movie_count_paginate , $offset+1 , ($offset+$nb_record_per_page)>$total_record ? $total_record : $offset+$nb_record_per_page , $total_record ); 
else $label_movie_count = sprintf( $movie_count , $count );

function column_format( $field, $value ) {
	if (($field=='Acteurs')||($field=='Realisateurs'))
		return strlen( $value ) <= 80 ? str_replace("\r",", ",$value) : mb_substr(str_replace("\r",", ",$value), 0 , 120 , "UTF-8" ) . '...' ;	
	if ($field=='Duree') return $value . ' mn';
	return $value;
}

?>

<html>
<head>
	<title><?php echo($window_title); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>


<!-- Header -->
<div align="center">
<?php if ($show_title) {
		echo( '<div class="jumbotron">' );
		echo( '<h1><a href="'.$_SERVER['PHP_SELF'].'"><span class="text-info">'. $title_label .'</span></a></h1>' );
		echo( '</div>');
	} else echo( '<a href="'.$_SERVER['PHP_SELF'].'"><img class="img-responsive" src="img/top.png" /></a>' );
?>
</div>

<!-- Main block -->
<div class="container">

<!-- Navigation bar -->
<nav class="navbar navbar-default" role="navigation">
  <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
      <span class="sr-only">Navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="index.php"><?php echo($navbar_title); ?></a>
  </div>

  <!-- Collect the nav links, forms, and other content for toggling -->
  <div class="collapse navbar-collapse navbar-ex1-collapse">
    <ul class="nav navbar-nav">
      <li class="active"><a href="#"><?php echo( $navbar_active_title ); ?></a></li>
    </ul>
    <form class="navbar-form navbar-right" role="search" method="post">
      <div class="form-group">
		<input class="champ_recherche" type="hidden" name="search_field" value="<?php echo($search_field); ?>">
        <input type="text" class="form-control" placeholder="<?php echo($navbar_search) ?>" name="search_query">
      </div>
      <button type="submit" class="btn btn-default"><?php echo($navbar_go) ?></button>
    </form>
	<ul class="nav navbar-nav navbar-right">
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo($navbar_search_by . ' ' . $search_label); ?> <b class="caret"></b></a>
        <ul class="dropdown-menu">
			<?php 
				foreach ($search_fields as $value) {
					echo( '<li><a tabindex="0" href="#" name="'.$value.'">'.$field_labels[$value].'</a></li>');
				}
			?>
        </ul>
      </li>
	</ul>
    
  </div><!-- /.navbar-collapse -->
</nav>

<!-- Center block -->
<div class="row">

<!-- Movie list -->
<!--div class="col-8"-->
<div class="col-12 col-lg-8">


<?php if ($search_query!='') {
	if ($count!=0) {
		echo('<div class="alert alert-success alert-block">');
		echo($result_for_search .' <strong>' . $search_label . '</strong> '.$contains.' <strong>' . $search_query . '</strong>' );
		echo('</div>');
	} else {
		echo('<div class="alert alert-danger alert-block">');
		echo($no_result .' <strong>' . $search_label . '</strong> '.$contains.' <strong>' . $search_query . '</strong>' );
		echo('</div>');		
	}
}
?>

<?php if ($count!=0) { ?>
<?php if ($pagination) { ?>
<ul class="pager">	
<?php if ($page==1) echo('<li class="previous disabled"><a href="#">&larr; '.$previous_page.'</a></li>');
  		else echo('<li class="previous"><a href="?Page='.($page-1).'">&larr; '.$previous_page.'</a></li>');
?>
  <span class="text-info"><?php echo($page . "/" . ceil($total_record/$nb_record_per_page)); ?></span>
<?php if ($page>=ceil($total_record/$nb_record_per_page)) echo('<li class="next disabled"><a href="#">'.$next_page.' &rarr; </a></li>');
  		else echo('<li class="next"><a href="?Page='.($page+1).'">'.$next_page.' &rarr; </a></li>');
?>
</ul>
<?php } ?>

	<table class="table table-hover table-striped table-condensed">
		<thead>
		<th width="15"></th>
		<th>id</th>
		<th>Vignette</th>
		<th><a href=""></a><?php echo($field_labels['TitreVF']); ?></th>
		<th><?php echo($field_labels[$second_column]); ?></th></thead>	
	<?php } ?>
	<!--tr onmouseover="this.style.cursor='pointer';"><td>Puces</td><td>Titre</td><td>Genre</td></tr-->	
<?php



	while ((is_bool($response) !== true) && ($data = $response->fetch()))
	{
?>
		<tr style="vertical-align: middle;">
			<td style="vertical-align: middle;">
				<?php 
				if ($show_lent || $show_not_seen) {
		if ( ($show_lent) && ($show_not_seen) ) {
			if ( ($data['FilmVu'] == 'NON') && ($data['PretEnCours'] == 'OUI') )
				echo ( '<img src="img/dot_green_orange.png" alt="'.$movie_not_seen_and_lent.'" />' );
			elseif  ($data['FilmVu'] == 'NON')
				echo ( '<img src="img/dot_green.png" alt="'.$movie_not_seen.'" />' );
			elseif ($data['PretEnCours'] == 'OUI')
				echo ( '<img src="img/dot_orange.png" alt="'.$movie_lent.'" />' );
		} 
		elseif ( ($show_lent) && ($data['PretEnCours'] == 'OUI') )
			echo ( '<img src="img/dot_orange.png" alt="'.$movie_lent.'" />' );
		elseif ( ($show_not_seen) && ($data['FilmVu'] == 'NON') )
			echo ( '<img src="img/dot_green.png" alt="'.$movie_not_seen.'" />' );
			
		} ?>
			</td>
			<?php
				$image_name = sprintf("affiches/Filmotech_%05d.jpg", $data['ID']);
				if (!is_file($image_name))
					$image_name = "affiches/chat.jpg";
			?>
			<td style="vertical-align: middle;"><?php echo "#".$data['Reference']; ?></td>
			<td style="vertical-align: middle;"><img src="<?php echo $image_name; ?>"/></td>
			<td style="vertical-align: middle;"><a href="filmotech_detail.php?id=<?php echo $data['ID']; ?>"><?php echo $data['TitreVF']; ?></a></td>
			<td style="vertical-align: middle;"><?php echo column_format( $second_column, $data[$second_column] ) ; ?></td>
		</tr>
<?php
	}
	if (is_bool($response) !== true)
		$response->closeCursor();
?>			
	<?php if ($count!=0) { ?>
	</table>
<?php if ($pagination) { ?>
<ul class="pager">
<?php if ($page==1) echo('<li class="previous disabled"><a href="#">&larr; '.$previous_page.'</a></li>');
  		else echo('<li class="previous"><a href="?Page='.($page-1).'">&larr; '.$previous_page.'</a></li>');
?>
  <span class="text-info"><?php echo($page . "/" . ceil($total_record/$nb_record_per_page)); ?></span>
<?php if ($page>=ceil($total_record/$nb_record_per_page)) echo('<li class="next disabled"><a href="#">'.$next_page.' &rarr; </a></li>');
  		else echo('<li class="next"><a href="?Page='.($page+1).'">'.$next_page.' &rarr; </a></li>');
?>
</ul>
<?php } ?>

	<?php echo( '<p class="text-center"><small>' . $label_movie_count . '</small></p>' ); ?>
	<?php } ?>
	
<?php if ($show_update_date && $count!=0) echo( '<p class="text-center"><small>' . $last_update_label . '</small></p>' ); ?>
	
<!-- End of movie list -->		
</div>

<!-- Side bar -->		
<!--div class="col-4"-->
<div class="col-12 col-lg-4">


	<!-- Last added movies -->		
	<?php if ($show_latest) {
		echo( '<div class="alert alert-warning alert-block">');
		echo( '<h4>' . $latest_label . '</h4><br />' );
		echo( '<ul>' );
		$response = $db->query('SELECT * FROM '. $cfg->DB_TABLE .' ORDER BY EntreeDate DESC LIMIT ' . $max_latest );
		while ($data = $response->fetch()) 
		{
			echo( '<li><a href="filmotech_detail.php?id=' . $data['ID'] . '">' . $data['TitreVF'] . '</a></li>' );
		}
		$response->closeCursor();
		echo( '</ul>' );
		echo( '</div>' );
	}
	?>
	
	<!-- Favorites -->	
	<?php if ($show_favorites_index) { 
		echo( '<div class="alert alert-success alert-block">' );
		echo( '<h4>' . $favorites_label . '</h4><br />' );
		echo( '<ul>' );
		foreach ($favorites as $key => $value) { echo( '<li><a href="'.$value.'">'.$key.'</a></li>'); }
		echo( '</ul>' );
		echo( '</div>' );
	}
	?>
	
	<!-- Personal code #1 -->	
	<?php if ($show_custom_1_index) { 
		echo( '<div class="alert alert-info alert-block">' );
		echo( '<h4>' . $custom_label_1 . '</h4><br />' );
		echo( '<div>' . $custom_code_1 . '</div>' );
		echo( '</div>' );
	}
	?>
	
	<!-- Personal code #2 -->	
	<?php if ($show_custom_2_index) { 
		echo( '<div class="alert alert-info alert-block">' );
		echo( '<h4>' . $custom_label_2 . '</h4><br />' );
		echo( '<div>' . $custom_code_2 . '</div>' );
		echo( '</div>' );
	}
	?>

<!-- End of side bar -->		
</div>
  		
<!-- End of main block -->		
</div>

<!-- Footer -->
<div class="well well-small">
	<table width="100%">
		<tr>
			<td width="33%"><div class="muted"><?php echo($copyright); ?></div></td>
			<td width="33%"><div class="text-center muted"><a href="mailto:<?php echo($mail_address); ?>">
				<?php echo($mail_label); ?></a></div></td>
			<td width="33%"><div class="text-right muted"><?php echo($powered_by); ?>
			<a href="http://www.filmotech.fr">Filmotech</a></div></td>
		</tr>
	</table>
</div>

<!-- End of page -->
<!-- JavaScript plugins (requires jQuery) -->
<script src="http://code.jquery.com/jquery.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<!-- Enable responsive features in IE8 with Respond.js (https://github.com/scottjehl/Respond) -->
<script src="js/respond.min.js"></script>

	<script>
	$(".dropdown-menu li a").click(function(){
	var selText = $(this).text();
	var selName = $(this).attr("name");
	$(this).parents('.navbar').find('.champ_recherche').val(selName);
	$(this).parents('.navbar').find('.dropdown-toggle').html('Recherche par '+selText+' <span class="caret"></span>');
	});
	</script>


</body>
</html>