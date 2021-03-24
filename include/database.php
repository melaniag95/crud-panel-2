<?php

//connessione al database utilizzando PDO:
try{
    $hostname = 'localhost';
    $dbname = 'corso';
    $user = 'root';
    $pwd = '';

    //apriamo la connessione al database:
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $user, $pwd);

    //possiamo anche impostare degli attributi relativi alla connessione:
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //ci restituisce eventuali errori nel dialogo con il database
} catch (PDOException $e) {
    echo 'Errore: ' .$e->getMessage();
    die(); //l'esecuzione del codice php termina
}

?>