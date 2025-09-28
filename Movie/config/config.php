<?php
// Firebase Config
$FIREBASE_API_KEY = "AIzaSyDTjvrL4M2qZSilwgwSuAbhZ8qvLzp9g2M";
define('TMDB_API_KEY', 'a3a1abc3bf552838ff7dbaeaa92c3984'); // Put your TMDb API key here
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p/w500');


// MySQL Config
$DB_HOST = "127.0.0.1:3307";   // or "localhost:3307"
$DB_USER = "root"; 
$DB_PASS = "";      
$DB_NAME = "movie";

// Database Connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
