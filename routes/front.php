<?php
use Steampixel\Route;

Route::add('/', function () {
  echo file_get_contents(__DIR__.'/../views/home.html');
}, 'get');

Route::add('/register', function() {
  echo file_get_contents(__DIR__.'/../views/register.html');
}, 'get');

Route::add('/login', function() {
  echo file_get_contents(__DIR__.'/../views/login.html');
}, 'get');

Route::add('/calendar', function() {
  echo file_get_contents(__DIR__.'/../views/calendar.html');
}, 'get');

Route::add('/recap', function() {
  echo file_get_contents(__DIR__.'/../views/view-recap.html');
}, 'get');

Route::add('/recap/edit', function() {
  echo file_get_contents(__DIR__.'/../views/edit-recap.html');
}, 'get');

Route::add('/recap/list', function() {
  echo file_get_contents(__DIR__.'/../views/list-recaps.html');
}, 'get');

Route::add('/templates/hebdo.html', function() {
  header('Access-Control-Allow-Origin: *');
  $content = file_get_contents(__DIR__.'../views/recap_templates/hebdo.html');
}, 'get');