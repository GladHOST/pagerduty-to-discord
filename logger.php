<?php

function logRequest($data)
{
    $fichier_json = 'requests.json';
    if (!file_exists($fichier_json)) {
        file_put_contents($fichier_json, '[]');
    }

    $requetes[] = [
        'methode' => $_SERVER['REQUEST_METHOD'],
        'donnees' => $data,
        'heure' => date('Y-m-d H:i:s'),
    ];

    file_put_contents($fichier_json, json_encode($requetes, JSON_PRETTY_PRINT));
}
