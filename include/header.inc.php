<?php
/**
 * @author Viet Dung Tran
 *
 * created on 13.02.2012
 */

function htmlHeader($titel,$mainNav = array(),$benutzer='Gast',$link=null){
	
	if(empty($link)){
		$link='login.php';
	}else{
		$link = 'mitglied.php?beid='.$link;
	}
	
	printf('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
			<meta http-equiv="content-type" content="text/html; charset=utf-8">
			<title>%s</title>
			<link rel="stylesheet" type="text/css" href="css/style.css" >
			</head>
			<body>
				<div id="wrapper">
					<div id="header">
						<div id="logo">
							<ul id="smallnav">
								<li><a href="index.php">Home</a></li>
								<li><a href="http://dbs.cs.uni-duesseldorf.de/lehre/veranst.php?veranst=43">DBS</a></li>
							</ul>
							<h1>DBS Abschlussprojekt</h1>
							<p class="login">Hallo : <a href="%s">%s</a></p>
						</div><!-- END #logo -->
						
						<div id="banner">
							<img alt="dbs Abschlussprojekt" src="css/banner.jpg" >
						</div><!-- END #banner -->
							
						<div id="navBar">
							<ul id="mainNav">
								<li><a href="index.php">Home</a></li>
	',$titel,$link,$benutzer);
	if($mainNav){
		foreach ($mainNav as $key => $value) {
			echo "<li><a href=\"{$key}\">{$value}</a></li>\n";
		}
	}
	
	printf('
				</ul>
			
			</div><!-- END #narBar -->
		</div> <!-- END #header -->
		<div id="content">
			<div id="sidebar">
	');
	
}




?>

