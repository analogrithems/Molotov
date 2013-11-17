<?php


include_once('bootstrap.php');

$crawler = new \Media\Controllers\Crawler();

$crawler->scanRepos();
