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