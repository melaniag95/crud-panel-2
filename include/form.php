<?php

class Form{
    private $id;
    private $name;
    private $action;
    private $method;

    public function __construct($a, $b, $c, $d){
        $this->id = $a;
        $this->name = $b;
        $this->action = $c;
        $this->method = $d;
    }

    //Form
    public function creaForm(){
        $strhtml = '<form id="'.$this->id.'" name="'.$this->name.'" action="'.$this->action.'" method="'.$this->method.'" enctype="multipart/form-data">';
        return $strhtml;
    }

    //Hidden input:
    public function creaHiddenInput($name,$value=''){
        $strhtml = '<input type ="hidden" name="'.$name.'" id="'.$name.'" class="form-control" value="'.$value.'" >';
        return $strhtml;
    }

    //Campi di input
    public function creaInput($cssClass, $label, $type, $name, $value='', $placeholder=''){
        $strhtml = '<div class="'.$cssClass.'">';
        $strhtml .= '<label for="'.$name.'">'.$label.'</label><br>';
        $strhtml .= '<input type ="'.$type.'" id="'.$name.'" name="'.$name.'" class="form-control" value="'.$value.'" placeholder="'.$placeholder.'">';
        $strhtml .= '</div>';
        return $strhtml;
    }


    //Textarea:
    public function creaTextArea($label, $name, $rows, $value=''){
        $strhtml = '<label>'.$label.'</label><br>';
        $strhtml .= '<textarea class="form-control" id="'.$name.'" name="'.$name.'" rows="'.$rows.'">'.$value.'</textarea>';
        return $strhtml;
    }


    //Select
    public function creaSelect($cssClass, $label, $name, $db, $nome_tabella, $chiave, $option_value, $value=''){
        $strhtml = '<div class="'.$cssClass.'">';
        $strhtml .= '<label>'.$label.'</label><br>';
        $strhtml .= '<select name="'.$name.'" class="form-control">';
        $sql = 'SELECT '.$chiave.', '.$option_value. ' FROM '.$nome_tabella;
        $result = $db->prepare($sql);
        $result->execute();
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            if($row[$chiave] == $value){
                $strhtml .= '<option value="'.$row[$chiave].'" selected>'.$row[$option_value].'</option>';
            } else{
                $strhtml .= '<option value="'.$row[$chiave].'">'.$row[$option_value].'</option>';
            }
            
        }
        $strhtml .= '</select>';
        $strhtml .= '</div>';

        return $strhtml;
    }
    


    //Button Submit
    public function creaButton($cssClass, $type, $value){
        $strhtml = '<button type="'.$type.'" class="'.$cssClass.'">'.$value.'</button>';
        return $strhtml;
    }

    //Chiusura form:
    public function chiusuraForm(){
        $strhtml ='</form>';
        return $strhtml;
    }

}