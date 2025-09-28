<?php
function firebaseSignUp($email, $password, $apiKey) {
    $url = "https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=" . $apiKey;

    $data = [
        "email" => $email,
        "password" => $password,
        "returnSecureToken" => true
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function firebaseLogin($email, $password, $apiKey) {
    $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=" . $apiKey;

    $data = [
        "email" => $email,
        "password" => $password,
        "returnSecureToken" => true
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
