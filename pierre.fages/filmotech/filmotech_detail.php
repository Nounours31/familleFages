<!DOCTYPE html>
<?php

/* FILMOTECH Website 
	DETAIL page
	
	(c) 2013-2015 by Pascal PLUCHON
	http://www.filmotech.fr
*/

// Site parameters
require_once("include/params.inc.php");
require_once("include/config.inc.php");

// Get configuration
$cfg = new CONFIG();

// Connection to database
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
	die('Erreur : ' . $e->getMessage());
}

// Select Movie ID
if ((isset($_GET['id']))) $id=$_GET['id']; else $id=-1; 
$req = $db->prepare('SELECT * FROM ' . $cfg->DB_TABLE . ' WHERE ID = :id');
$req->execute(array('id' => $id));

// Formatting functions
function add_commas( $string ) {
	return str_replace("\r",", ",$string);	
}
function add_br( $string ) {
	return str_replace("\r","<br />",str_replace("\n","<br />",$string));
}

?>

<html>
<head>
	<title><?php echo($window_title); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<!-- Bootstrap -->
	<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="css/filmotech.css" rel="stylesheet" media="screen">
	<link href="css/bootstrap-glyphicons.css" rel="stylesheet">
</head>
<body>

<!-- Header -->
<div align="center">
<?php if ($show_title) {
		echo( '<div class="jumbotron">' );
		echo( '<h1><a href ="javascript:history.back();"><span class="text-info">'. $title_label .'</span></a></h1>' );
		echo( '</div>');
	} else echo( '<a href ="javascript:history.back();"><img class="img-responsive" src="img/top.png" /></a>' );
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
			<li><a href="javascript:history.back();"><?php echo( $navbar_active_title ); ?></a></li>
			<li class="active"><a href="#"><?php echo( $navbar_detail_title ); ?></a></li>
    </ul>
  </div><!-- /.navbar-collapse -->
</nav>


<!-- Update window title with movie title -->
<?php
	while ($data = $req->fetch()) {
	// Update window title
	echo( '<script>document.title += " - ' . $data['TitreVF'] . '";</script>' );
?>


<!-- Movie header for desktop/tablet -->
<div class="">
	<div class="row">
		<div class="col-lg-3"></div>
		<div class="col-12 col-lg-3">

			<ul class="media-list text-center">
				<li class="media">
				<div class="media-body text-center">
		<?php 
					$filename = sprintf('%s/Filmotech_%05d.jpg' , $cfg->POSTERS_DIRECTORY , $data['ID'] );
					if (file_exists($filename)) echo('<table width="100%"><tr align="center"><td><img class="img-responsive media-object" src="' . $filename . '" 
					alt="Affiche" /></td></tr></table>');
		?>
				</div>
			</li>
			</ul>
		</div>
		<div class="col-12 col-lg-4">
			<ul class="media-list text-center">
				<li class="media">
				<div class="media-body text-center">
					<br /><br />	
					<h4><?php echo $data['TitreVF']; ?></h4>
					<h4><span class="text-muted"><?php echo $data['TitreVO']; ?></span></h4>
					<h5><span class="text-warning"><?php echo $data['Genre']; ?></span></h5>
					<h5><span class=text-success><?php echo $data['Annee']; ?> - <?php echo $data['Duree']; ?> mn - 
					<?php echo $data['Pays']; ?></span></h5>
					<img src="img/note<?php echo $data['Note']; ?>.png" />
					<br /><br />
					<?php if (($data['BAChemin'] <> "") && ($data['BAType'] == "URL")) echo( '<a href="'.$data['BAChemin'].'" class="btn-fmt btn-mini"><span class="glyphicon glyphicon-film"></span> '.$show_trailer.'</a>' ); ?>
					<?php if (($data['MediaChemin'] <> "") && ($data['MediaType'] == "URL")) echo( '<a href="'.$data['MediaChemin'].'" class="btn-fmt"><span class="glyphicon glyphicon-file"></span> '.$show_media.'</a>' ); ?>
					
				</div>
				</li>
			</ul>
		</div>
		<div class="col-lg-2"></div>
	</div>
</div>

<!-- Movie header for phone -->
<!--
<div class="visible-sm">
	<div class="col-md-12 text-center">
	<div>
		<?php 
		$filename = sprintf('%s/Filmotech_%05d.jpg' , $cfg-> POSTERS_DIRECTORY , $data['ID'] );
		if (file_exists($filename)) echo('<img class="img-polaroid" src="' . $filename . '" alt="Affiche" />');
		?>
	</div>
 				<h4><?php echo $data['TitreVF']; ?></h4>
				<h4><span class="text-muted"><?php echo $data['TitreVO']; ?></span></h4>
				<h5><span class="text-warning"><?php echo $data['Genre']; ?></span></h5>
				<h5><span class=text-success><?php echo $data['Annee']; ?> - <?php echo $data['Duree']; ?> mn - 
					<?php echo $data['Pays']; ?></span></h5>
				<img src="img/note<?php echo $data['Note']; ?>.png" />
				<br /><br />
				<?php if (($data['BAChemin'] <> "") && ($data['BAType'] = "URL")) echo( '<a href="'.$data['BAChemin'].'" class="btn-fmt btn-mini"><span class="glyphicon glyphicon-film"></span> '.$show_trailer.'</a>' ); ?>
				<?php if (($data['MediaChemin'] <> "") && ($data['MediaType'] = "URL")) echo( '<a href="'.$data['MediaChemin'].'" class="btn-fmt"><span class="glyphicon glyphicon-file"></span> '.$show_media.'</a>' ); ?>
				<p></p>
	</div>
</div>
-->

<div> <!-- of row -->

<!-- Center block -->
<div class="row">

<!-- Movie details -->
<!--div class="span8"-->
<div class="col-12 col-lg-8">

	<p><span class="text-info"><strong><?php echo($field_labels['Realisateurs']); ?> : </strong></span>
	<span class="muted"><?php echo add_commas($data['Realisateurs']); ?></span></p>
	<p><span class="text-info"><strong><?php echo($field_labels['Acteurs']); ?> : </strong></span>
	<span class="muted"><?php echo add_commas($data['Acteurs']); ?></span></p>
	<p><span class="text-info"><strong><?php echo($field_labels['Synopsis']); ?> : </strong></span><br />
	<span class="muted"><?php echo add_br($data['Synopsis']); ?></span></p>
	<?php if ($show_features) { ?>
		<p><span class="text-info"><strong><?php echo($field_labels['Bonus']); ?> : </strong></span><br />
		<span class="muted"><?php echo add_br($data['Bonus']); ?></span></p>
	<?php } ?>
	<?php if ($show_comments) { ?>
		<p><span class="text-info"><strong><?php echo($field_labels['Commentaires']); ?> : </strong></span><br />
		<span class="muted"><?php echo add_br($data['Commentaires']); ?></span></p>
	<?php } ?>

</div>
	
<!-- Sidebar -->
<!--div class="span4"-->
<div class="col-12 col-lg-4">


	<?php if ($show_media_infos) {
		echo( '<div class="alert alert-warning alert-block">' );
		echo( '<h4>' . $media_informations . '</h4><br />' );
		echo( '<strong>' . $field_labels['Reference'] . ' : </strong>' . $data['Reference'] . '<br />' );
		echo( '<strong>' . $field_labels['Support'] . ' : </strong>' . $data['Support'] . '<br />' );
		echo( '<strong>' . $field_labels['Edition'] . ' : </strong>' . $data['Edition'] . '<br />' );
		echo( '<strong>' . $field_labels['Zone'] . ' : </strong>' . $data['Zone'] . '<br />' );
		echo( '<br />' );
		echo( '<strong>' . $field_labels['Langues'] . ' : </strong>' . $data['Langues'] . '<br />' );
		echo( '<strong>' . $field_labels['SousTitres'] . ' : </strong>' . $data['SousTitres'] . '<br />' );
		echo( '<strong>' . $field_labels['Audio'] . ' : </strong>' . $data['Audio'] . '<br />' );
		echo( '</div>' );
	}
	?>
	
	<!-- Favorites -->	
	<?php if ($show_favorites_detail) { 
		echo( '<div class="alert alert-success alert-block">' );
		echo( '<h4>' . $favorites_label . '</h4><br />' );
		echo( '<ul>' );
		foreach ($favorites as $key => $value) { echo( '<li><a href="'.$value.'">'.$key.'</a></li>'); }
		echo( '</ul>' );
		echo( '</div>' );
	}
	?>
	
	<!-- Personal code #1 -->	
	<?php if ($show_custom_1_detail) { 
		echo( '<div class="alert alert-info alert-block">' );
		echo( '<h4>' . $custom_label_1 . '</h4><br />' );
		echo( '<div>' . $custom_code_1 . '</div>' );
		echo( '</div>' );
	}
	?>
	
	<!-- Personal code #2 -->	
	<?php if ($show_custom_2_detail) { 
		echo( '<div class="alert alert-info alert-block">' );
		echo( '<h4>' . $custom_label_2 . '</h4><br />' );
		echo( '<div>' . $custom_code_2 . '</div>' );
		echo( '</div>' );
	}
	?>

<!-- End of sidebar -->
</div>

<!-- End of center block -->
</div>

<?php
	}
	$req->closeCursor(); // Termine le traitement de la requête
?>			

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

<!-- End of main block -->
</div>

<!-- End of page -->
<!-- JavaScript plugins (requires jQuery) -->
<script src="http://code.jquery.com/jquery.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<script src="js/collapse.js"></script>
<!-- Enable responsive features in IE8 with Respond.js (https://github.com/scottjehl/Respond) -->
<script src="js/respond.min.js"></script>

</body>
</html>