<?php

include('include/database.php');
ini_set('auto_detect_line_endings', TRUE);

$action = 'lista';
if(isset($_GET['action'])){
    $action = $_GET['action'];
}

$id = 0;
if(isset($_REQUEST['id'])){
    $id = filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT);
}

switch($action){
    case 'lista':
        $strhtml = crea_lista();
        break;
    case 'dettaglio':
        $strhtml = visualizza_dettaglio();
        break;
    case 'form':
        $strhtml = crea_form();
        break;
    case 'salva':
        $strhtml = salva();
        break;
    case 'elimina':
        $strhtml = elimina();
        break;
    case 'export':
        $strhtml = export();
        break;
    case 'import':
        $strhtml = import();
        break;
    default:
        $strhtml = crea_lista();
        break;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <title>Utenti</title>
</head>
<body>
    <?php 
        if(isset($errore) && $errore ==1){
            echo $msgError;
        }
    ?>

    <div class="d-flex flex-column mx-auto">
        <?php echo $strhtml; ?>
    </div>


    <!--Script Bootstrap-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>


    <script>
        function elimina(id){
            
            var scelta = window.confirm('Sei sicuro di voler eliminare l\'utente?');
            if(scelta){
                location.href='?action=elimina&id='+id;
            }
           
        }
    </script>
</body>
</html>

<?php

/*FUNZIONI action*/
function crea_lista(){
    global $db, $id;
    $search = '%';
    if(isset($_GET['search'])){
        $search = '%'. $_GET['search'] .'%';
    }
    
    /*Navbar*/
    $strhtml = '<nav class="navbar navbar-dark bg-dark mb-3">';
    $strhtml .= '<div class="container-fluid">';
    $strhtml .= '<div>';
    $strhtml .= '<a href="?action=form" class="btn btn-success mx-2">Aggiungi nuovo utente</a>';
    $strhtml .= '<a href="?action=export" target="_blank" class="btn btn-success mx-2">Export CSV</a>';
    $strhtml .= '<a href="?action=import" class="btn btn-success mx-2">Import CSV</a>';
    $strhtml .= '</div>';
    $strhtml .= '<form class="col-md-4 d-flex">';
    $strhtml .= '<input class="form-control me-2" type="search" name="search" placeholder="Cerca utenti" value="'.str_replace('%','',$search).'">&nbsp;';
    $strhtml .= '<input type="submit" class="btn btn-success" value="Cerca">';
    $strhtml .=  '</form>';
    $strhtml .= '</div>';
    $strhtml .= '</nav>';

    /*Tabella*/
    $strhtml .= '<div class="col-md-10 mx-auto">';
    $strhtml .= '<table class="table table-striped">';
    $strhtml .= '<thead>';
    $strhtml .= '<tr>';
    $strhtml .= '<th>Nome</th>';
    $strhtml .= '<th>Cognome</th>';
    $strhtml .= '<th>Email</th>';
    $strhtml .= '<th>Tipologia</th>';
    $strhtml .= '</tr>';
    $strhtml .= '</thead>';
    $strhtml .= '<tbody>';
    $sql = 'SELECT utenti.id, utenti.nome, utenti.cognome, utenti.email, tipologia.nome AS tipologia FROM utenti LEFT JOIN tipologia ON tipologia.id = utenti.tipologia_id WHERE utenti.nome like :search OR utenti.cognome like :search';
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    $stmt->execute();
    if($stmt->rowCount() == 0) {
        $strhtml .= '<tr>';
        $strhtml .= '<td colspan="6">Nessun utente disponibile con i criteri di ricerca usati</td>';
        $strhtml .= '</tr>';
    } elseif($stmt->rowCount() == 1){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        header('location: ?action=dettaglio&id='.$row['id']);
    } else{
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $strhtml .= '<tr>';
            $strhtml .= '<td>'.$row['nome'].'</td>';
            $strhtml .= '<td>'.$row['cognome'].'</td>';
            $strhtml .= '<td>'.$row['email'].'</td>';
            $strhtml .= '<td>'.$row['tipologia'].'</td>';
            $strhtml .= '<td>';

            /*Dropdown actions*/
            $strhtml .= '<div class="dropdown">';
            $strhtml .= '<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
            $strhtml .= '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
            $strhtml .= '<a class="dropdown-item"href="?action=dettaglio&id='.$row['id'].'">Scopri di più</a>';
            $strhtml .= '<a class="dropdown-item" href="?action=form&id='.$row['id'].'">Modifica</a>';
            $strhtml .= '<a id="eliminaUtente" class="dropdown-item" href="javascript:elimina('.$row['id'].')">Elimina</a>';
            $strhtml .= '</div>';
            $strhtml .= '</div>';
            $strhtml .= '</td>';
        }
    }
    
    $strhtml .= '</tbody>';
    $strhtml .= '</table>';

    return ($strhtml);
}

function visualizza_dettaglio(){
    global $db, $id;
    $sql = 'SELECT utenti.* , tipologia.nome AS tipo, via_indirizzo.nome AS via, sesso.nome AS sex, abilitazione.nome AS abilitato FROM utenti INNER JOIN tipologia ON tipologia.id = utenti.tipologia_id INNER JOIN via_indirizzo ON via_indirizzo.id = utenti.via_indirizzo INNER JOIN sesso ON sesso.id = utenti.sesso INNER JOIN abilitazione ON abilitazione.id = utenti.abilitazione WHERE utenti.id = :id ORDER BY id DESC LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $strhtml = '<div class="mx-auto text-center">';
    $strhtml .= '<h3 class="m-3">Dettaglio utente</h3>';
    $strhtml .= '<p class="text-dark fs-2">'.$row['nome'].' '.$row['cognome'].'<p>';
    $strhtml .= '</div>';
    $strhtml .= '<div class="row d-flex justify-content-center mx-auto">';

    /*Card dettaglio utente*/
    $strhtml .= '<div class="col-md-8">';
    $strhtml .= '<div class="bg-light card shadow-sm">';
    $strhtml .= '<div class="row card-body">';
    $strhtml .= '<div class="fs-6 card-text col-md-6">';
    $strhtml .= '<p><strong>Email: </strong>'.$row['email'].'</p>';
    $strhtml .= '<p><strong>Password: </strong>'.$row['pass'].'</p>';
    $strhtml .= '<p><strong>Tipologia: </strong>'.$row['tipo'].'</p>';
    $strhtml .= '<p><strong>Indirizzo: </strong>'.$row['via'].' '.$row['indirizzo'].' '.$row['numero_indirizzo'].'</p>';
    $strhtml .= '<p><strong>Città: </strong>'.$row['city'].'</p>';
    $strhtml .= '<p><strong>Data di nascita: </strong>'.$row['data_nascita'].'</p>';
    $strhtml .= '<p><strong>Sesso: </strong>'.$row['sex'].'</p>';
    $strhtml .= '<p><strong>Abilitazione: </strong>'.$row['abilitato'].'</p>';
    $strhtml .= '</div>'; 
    $strhtml .= '<div class="col-md-6 d-flex align-items-center">';
    if($row['foto'] == ''){
        $strhtml .= '<img src="https://picsum.photos/400" class="card-img-right" alt="foto-personale" width="100%">';
    } else {
        $strhtml .= '<img src="upload/'.$row['foto'].'" class="card-img-right" alt="foto-personale" width="100%">';
    }
    
    $strhtml .= '</div>'; 
    $strhtml .= '</div>';
    $strhtml .= '</div>';
    $strhtml .= '</div>';
    /*Fine card*/

    $strhtml .= '</div>';
    $strhtml .= '</div>';

    return ($strhtml);
} 

function crea_form(){
    global $db, $id;
    include('include/form.php');
    $tabella = 'utenti';
    $form = new Form('info', 'info','?action=salva', 'post');

    $sql = 'SELECT * FROM '.$tabella.' WHERE id = :id';
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if($stmt->rowCount() == 0){ 
        $rowUtente['nome'] = '';
        $rowUtente['cognome'] = '';
        $rowUtente['email'] ='';
        $rowUtente['pass'] = '';
        $rowUtente['tipologia_id'] = '';
        $rowUtente['via_indirizzo'] = '';
        $rowUtente['indirizzo'] = '';
        $rowUtente['numero_indirizzo'] = '';
        $rowUtente['city'] = '';
        $rowUtente['data_nascita'] = '';
        $rowUtente['sesso'] = '';
        $rowUtente['abilitazione'] = '';
        $rowUtente['foto'] = '';
    } else{
        $rowUtente = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    $strhtml = '<div class="container mx-auto">';
    $strhtml .= '<h3 class="m-4 text-center">Compila il form per <br>aggiungere/modificare un utente</h3>';

    /*Inizio form*/
    $strhtml .= $form->creaForm();
    $strhtml .= '<div class="col-md-6 d-flex flex-column mx-auto">';
    $sql ='SELECT campo, tipo, label, select_tabella, select_chiave, select_testo FROM configurazione WHERE tabella = "'.$tabella.'"';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        switch($row['tipo']){
            case 'hidden':
              $strhtml .= $form-> creaHiddenInput($row['campo'], $id);
              break;
            case 'text':
            $strhtml .= $form->creaInput('mb-3 row', $row['label'], $row['tipo'], $row['campo'], $rowUtente[$row['campo']]);
              break;
            case 'email';
                $strhtml .= $form->creaInput('mb-3 row', $row['label'], $row['tipo'], $row['campo'], $rowUtente[$row['campo']]);
              break;
            case 'password';
                $strhtml .= $form->creaInput('mb-3 row', $row['label'], $row['tipo'], $row['campo'], $rowUtente[$row['campo']]);
            break;
            case 'select':
                $strhtml .= $form->creaSelect('form-group mb-3 row', $row['label'], $row['campo'], $db, $row['select_tabella'], $row['select_chiave'], $row['select_testo'], $rowUtente[$row['campo']]);
            break;
            case 'date';
                $strhtml .= $form->creaInput('mb-3 row', $row['label'], $row['tipo'], $row['campo'], $rowUtente[$row['campo']]);
            break;
            case 'file':
                if($id == 0){
                    $strhtml .= $form->creaInput('mb-3 row', $row['label'], $row['tipo'], $row['campo'], $rowUtente[$row['campo']]);
                }
                break;
            
        }
    }
    /*Button form*/
    $strhtml .= '<div class="d-grid gap-2 col-6 mx-auto mb-4">';
    $strhtml .= $form->creaButton('btn btn-outline-success', 'submit', 'Invia');
    $strhtml . '</div>';

    $strhtml .= '</div>';
    $strhtml .= $form->chiusuraForm();
    /*Fine form*/

    $strhtml .= '</div>';
    
    return ($strhtml);
}

function salva(){
    global $db, $id;
    /*Controlli e validazione*/
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    if(isset($_POST['via_indirizzo'])){
        $via_indirizzo = filter_var($_POST['via_indirizzo'],FILTER_SANITIZE_NUMBER_INT);
      }
      else{
        $via_indirizzo = 0;    
    }
    $indirizzo = trim($_POST['indirizzo']);
    $numero_indirizzo = trim($_POST['numero_indirizzo']);
    $city = trim($_POST['city']);
    $email = trim($_POST['email']);
    $pass = trim($_POST['pass']);
    $data_nascita = trim($_POST['data_nascita']);
    $picture = $_FILES['foto']['name']; 
    $filename = $_FILES['foto']['tmp_name'];
    if(isset($_POST['sesso'])){
        $sesso = filter_var($_POST['sesso'],FILTER_SANITIZE_NUMBER_INT);
      }
      else{
        $sesso = 0;    
    }
    if(isset($_POST['abilitazione'])){
        $abilitazione = filter_var($_POST['abilitazione'],FILTER_SANITIZE_NUMBER_INT);
      }
      else{
        $abilitazione = 0;    
    }
    if(isset($_POST['tipologia_id'])){
        $tipologia_id = filter_var($_POST['tipologia_id'],FILTER_SANITIZE_NUMBER_INT);
      }
      else{
        $tipologia_id = 0;    
    }

    $errore=0;
    $msgError= '<div class="alert alert-danger m-3">';

        
    $test = controlla_caratteri($nome);
    if (!$test) {
        $errore = 1;
        $msgError .= '<p>Errore: sono consentiti sono i caratteri a-z e A-Z e le cifre 0-9</p>';
    }
    
    if(controlla_lunghezza($nome, 3)){
        $errore = 1;
        $msgError.= '<p>Errore: la lunghezza del nome deve essere di almeno 2 caratteri!</p>';
    }
      
    if(controlla_lunghezza($cognome, 3)){
        $errore = 1;
        $msgError.= '<p>Errore: la lunghezza del cognome deve essere di almeno 2 caratteri!</p>';
    }

    $test = controlla_caratteri($cognome);
    if (!$test) {
        $errore = 1;
        $msgError .= '<p>Errore: sono consentiti sono i caratteri a-z e A-Z e le cifre 0-9</p>';
    }

    if(controlla_lunghezza($pass, 8)){
        $errore = 1;
        $msgError.= '<p>Errore: la lunghezza della password deve essere di almeno 8 caratteri!</p>';
    }

    $test = controlla_caratteri($pass);
    if(!$test){
        $errore = 1;
        $msgError .= '<p>Errore: sono consentiti sono i caratteri a-z e A-Z e le cifre 0-9</p>';
    }

    $test = controlla_caratteri($tipologia_id);
    if(!$test){
        $errore = 1;
        $msgError .= '<p>Errore: sono consentiti sono i caratteri a-z e A-Z e le cifre 0-9</p>';
    }

    $test = controlla_caratteri($indirizzo);
    if(!$test){
        $errore = 1;
        $msgError .= '<p>Errore: sono consentiti sono i caratteri a-z e A-Z e le cifre 0-9</p>';
    }

    $test = controlla_caratteri($via_indirizzo);
    if(!$test){
        $errore = 1;
        $msgError .= '<p>Errore: sono consentiti sono i caratteri a-z e A-Z e le cifre 0-9</p>';
    }

    $test = controlla_caratteri($numero_indirizzo);
    if (!$test) {
        $errore = 1;
        $msgError .= '<p>Errore: sono consentiti sono i caratteri a-z e A-Z e le cifre 0-9</p>';
    }


    $test = controlla_caratteri($abilitazione);
    if(!$test){
        $errore = 1;
        $msgError.= '<p>Errore nel campo "Abilitazione"</p>';
    }

    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errore =1;
        $msgError .= '<p>Errore nel campo "Email"</p>';
    }


    if(!validateDate($data_nascita)){
        $errore = 1;
        $msgError .= '<p>Errore nel campo "Data"</p>';
    }

    $picture = preg_replace("/[^a-z0-9\.]/","", strtolower($picture));

    $msgError .= '</div>';

    /*Fine controlli*/

    if($errore == 0){
        if($id == 0){
            $pass = password_hash($pass, PASSWORD_BCRYPT);
            $sql = 'INSERT INTO utenti(nome, cognome, via_indirizzo, indirizzo, numero_indirizzo, city, email, pass, data_nascita, sesso, abilitazione, tipologia_id) VALUES(:nome, :cognome, :via_indirizzo, :indirizzo, :numero_indirizzo, :city, :email, :pass, :data_nascita, :sesso, :abilitazione, :tipologia_id)';
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindParam(':cognome', $cognome, PDO::PARAM_STR);
            $stmt->bindParam(':via_indirizzo', $via_indirizzo, PDO::PARAM_STR);
            $stmt->bindParam(':indirizzo', $indirizzo, PDO::PARAM_STR);
            $stmt->bindParam(':numero_indirizzo', $numero_indirizzo, PDO::PARAM_STR);
            $stmt->bindParam(':city', $city, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
            $stmt->bindParam(':data_nascita', $data_nascita, PDO::PARAM_STR);
            $stmt->bindParam(':sesso', $sesso, PDO::PARAM_STR);
            $stmt->bindParam(':abilitazione', $abilitazione, PDO::PARAM_STR);
            $stmt->bindParam(':tipologia_id', $tipologia_id, PDO::PARAM_STR);
            $stmt->execute();

            /*UPLOAD FOTO*/
            $id = $db->lastInsertId();
            $uploadDir = $_SERVER['DOCUMENT_ROOT'].'/corso_epicode/prova_upload_utenti/upload/';
            if(!file_exists($uploadDir)){
                mkdir($uploadDir,'0777');
            }

            /*
            echo $picture.'<br>';
            echo $filename.'<br>';
            echo $uploadDir.$picture.'<br>';
            */
            if(is_uploaded_file($filename)){
                move_uploaded_file($filename, $uploadDir.$picture);
                
                //update della tabella
                $sql ='UPDATE utenti SET foto = :foto WHERE id=:id LIMIT 1';
                $result=$db->prepare($sql);
                $result->bindParam(':id', $id, PDO::PARAM_INT);
                $result->bindParam(':foto', $picture, PDO::PARAM_STR);
                $result->execute();
            }


            $strhtml = '<div class="alert alert-success d-grid gap-2 col-6 mx-auto text-center mt-3">Utente inserito con successo! <br><a class="alert-link" href="?acion=lista">Torna alla lista</a></div>';
        } else {
            $pass = password_hash($pass, PASSWORD_BCRYPT);
            $sql = 'UPDATE utenti SET nome = :nome, cognome = :cognome, via_indirizzo = :via_indirizzo, indirizzo = :indirizzo, numero_indirizzo = :numero_indirizzo, city = :city, email = :email, pass = :pass, data_nascita = :data_nascita, sesso = :sesso, abilitazione = :abilitazione, tipologia_id = :tipologia_id WHERE id = :id LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindParam(':cognome', $cognome, PDO::PARAM_STR);
            $stmt->bindParam(':via_indirizzo', $via_indirizzo, PDO::PARAM_STR);
            $stmt->bindParam(':indirizzo', $indirizzo, PDO::PARAM_STR);
            $stmt->bindParam(':numero_indirizzo', $numero_indirizzo, PDO::PARAM_STR);
            $stmt->bindParam(':city', $city, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
            $stmt->bindParam(':data_nascita', $data_nascita, PDO::PARAM_STR);
            $stmt->bindParam(':sesso', $sesso, PDO::PARAM_STR);
            $stmt->bindParam(':abilitazione', $abilitazione, PDO::PARAM_STR);
            $stmt->bindParam(':tipologia_id', $tipologia_id, PDO::PARAM_STR);
            $stmt->execute();

            $strhtml = '<div class="alert alert-success d-grid gap-2 col-6 mx-auto text-center mt-3">Utente modificato con successo! <br><a class="alert-link" href="?acion=lista">Torna alla lista</a></div>';
        }
    } else {
        $strhtml = $msgError;
    }

    return ($strhtml);
}

function elimina(){
    global $db, $id;
    $sql = 'DELETE FROM utenti WHERE id = :id LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header('location: esercizio_utenti.php');

    return ($strhtml);
}

function export(){
    global $db, $id;
    $sql = 'SELECT id, nome, cognome, city, email, pass, data_nascita FROM utenti';
    $stmt = $db->prepare($sql);

    $stmt->execute();
    $output = '';

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

        $output .= '"'.$row['id'].'" , ';
        $output .= '"'.$row['nome'].'" , ';
        $output .= '"'.$row['cognome'].'" , ';
        $output .= '"'.$row['city'].'" , ';
        $output .= '"'.$row['email'].'" , ';
        $output .= '"'.$row['pass'].'" , ';
        $output .= '"'.$row['data_nascita'].'" , ';
        $output .= "\n";
    }

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=utenti.csv");
    echo $output;
    exit();

}

function import(){
    global $db;
    $miofile ="utentiExcel.csv";
    $file = fopen($miofile, 'r');

    while(($data = fgetcsv($file)) !== FALSE){
        /*
        print_r($data);
        exit();
        */
        
        $nome = $data[0];
        $cognome = $data[1];
        $via_indirizzo = $data[2];
        $indirizzo = $data[3];
        $numero_indirizzo = $data[4];
        $city = $data[5];
        $email = $data[6];
        $passchiaro = random_string(10);
        $pass = password_hash($passchiaro, PASSWORD_BCRYPT);
        $data_nascita = $data[8];
        $sesso = $data[9];
        $abilitazione = $data[10];
        $tipologia_id = $data[11];
    

        $sql = 'INSERT INTO utenti(nome, cognome, via_indirizzo, indirizzo, numero_indirizzo, city, email, pass, data_nascita, sesso, abilitazione, tipologia_id) VALUES(:nome, :cognome, :via_indirizzo, :indirizzo, :numero_indirizzo, :city, :email, :pass, :data_nascita, :sesso, :abilitazione, :tipologia_id)';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':cognome', $cognome, PDO::PARAM_STR);
        $stmt->bindParam(':via_indirizzo', $via_indirizzo, PDO::PARAM_STR);
        $stmt->bindParam(':indirizzo', $indirizzo, PDO::PARAM_STR);
        $stmt->bindParam(':numero_indirizzo', $numero_indirizzo, PDO::PARAM_STR);
        $stmt->bindParam(':city', $city, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
        $stmt->bindParam(':data_nascita', $data_nascita, PDO::PARAM_STR);
        $stmt->bindParam(':sesso', $sesso, PDO::PARAM_STR);
        $stmt->bindParam(':abilitazione', $abilitazione, PDO::PARAM_STR);
        $stmt->bindParam(':tipologia_id', $tipologia_id, PDO::PARAM_STR);
        $stmt->execute();
        
    }

    fclose($file);
    header('location: esercizio_utenti.php');
}


/*FUNZIONI CONTROLLO CAMPI*/
function random_string($length){
    $string = "";
    $chars = "abcdefghijklmanopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $size = strlen($chars);
    for ($i = 0; $i < $length; $i++) {
        $string .= $chars[rand(0, $size - 1)];
    }
    return $string; 
}

function controlla_caratteri($str){
    $pattern = '/^[a-zA-Z0-9 ]{1,100}$/';
    $esito = preg_match($pattern, $str);
    return ($esito);
}

function controlla_lunghezza($str, $minlength){
    $prova = strlen(trim($str)) < $minlength;
    return $prova;
}

function validateDate($data_nascita, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $data_nascita);
    return $d && $d->format($format)===$data_nascita;
}

?>