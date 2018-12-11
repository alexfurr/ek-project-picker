<?php
$projectID = get_the_ID();
// Tell the footer this should be full screen

$fullScreen = true;
$siteURL = get_site_url();

get_header(); ?> 

<main id="content">
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<header class="header">


<br/>
<a href="<?php echo $siteURL; ?>/project-list/"><i class="fas fa-chevron-circle-left"></i> Back to Project List</a>

<h1 class="entry-title"><?php the_title(); ?></h1> 
</header>
<div class="entry-content">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php

$supervisorTitle = get_post_meta( $projectID, 'supervisorTitle', true );
$projectDept = get_post_meta( $projectID, 'projectDept', true );
$projectSection = get_post_meta( $projectID, 'projectSection', true );
$supervisorName= get_post_meta( $projectID, 'supervisorName', true );
$supervisorEmail= get_post_meta( $projectID, 'supervisorEmail', true );



$supervisorTitleArray = explode(',', $supervisorTitle);
$supervisorNameArray = explode(',', $supervisorName);


$supervisorNameStr = '';
$i=0;
foreach($supervisorNameArray as $thisName)
{
	$thisTitle = $supervisorTitleArray[$i];
	$supervisorNameStr.=$thisTitle.' '.$thisName.' ,';
	$i++;
}
// Remove the Last Comman
$supervisorNameStr = substr($supervisorNameStr, 0, -1);


echo '<div class="ek_project_wrapper">';
echo '<div class="project_info">';
echo 'Supervisor(s) : <a href="mailto:'.$supervisorEmail.'">'.$supervisorNameStr.'</a><br/>';
echo 'Department : '.$projectDept.'<br/>';
echo 'Section : '.$projectSection.'<br/>';  

the_content();

echo '</div>';
echo '<div class="project_side">';

echo '<div id="imperialBasketDiv">';
$basketStr = ek_pp_draw::drawBasketWidget($projectTypeID);
echo $basketStr;
echo '</div>';

echo '</div>';
echo '</div>';

?>
<?php endwhile; endif; ?>
</div>
</article>
</main>
<?php get_footer(); ?>