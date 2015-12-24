<?php

/**
 * User: Erik Alexander Gonzalez Cardona
 * Date: 20/12/15
 * Time: 04:46 PM
 */
class class_videoads
{
    const TOKEN = "zOjEl6Cx9Du1mozaVWzqlyYKRh";
    const URL = "http://videoadscol.purorigor.com";

    function __construct() {
        add_action('admin_menu',array($this,'admin_menu'));
    }

    function admin_menu(){
        add_options_page("Video AdsCol" ,"Video adscol",'manage_options',basename(__FILE__), array($this,'settings_page'));
    }

    function settings_page(){
        global $wpdb;
        $tabla = $wpdb->prefix . "videoadscol";
        $tabla_ads = $tabla."_ads";
        $valor_chequear = "true";


            if(isset($_POST["token"]) && !empty($_POST["token"])){
                $_token = $_POST["token"];
                $_token1 = $_POST["token1"];

                if(isset($_POST["chequear"])){
                    $wpdb->query("UPDATE $tabla SET ctr=1 WHERE id=1 ;");
                }else{
                    $wpdb->query("UPDATE $tabla SET ctr=0 WHERE id=1 ;");
                }

                if($_token1 == $this::TOKEN){
                    $wpdb->query("UPDATE $tabla SET token='$_token' WHERE id=1 ;");

                }
            }

            $this->guardar_anuncio($wpdb,$tabla_ads);
            $this->eliminar_anuncio($wpdb,$tabla_ads);
            $this->agregar_anuncio($wpdb,$tabla_ads);

            $checked  = "";

            if( $wpdb->get_var("SELECT ctr FROM $tabla WHERE id=1") == 1){
                $checked  = "checked";
            }

            $holder_llave = $wpdb->get_var("SELECT token FROM $tabla WHERE id=1");
            $anuncios_id =   $wpdb->get_col("SELECT id FROM $tabla_ads");
            $anuncios_n =   $wpdb->get_col("SELECT anuncio FROM $tabla_ads");
            $html_ads = "";
            for($i = 0 ; $i < count($anuncios_id) ; $i++){
             $html_ads .= '<tr><td style="width:20%;" ><label> '.$anuncios_id[$i].' </label></td><td style="width:80%;" ><textarea name="anuncio[]" style="width:80%;" >'.$anuncios_n[$i].'</textarea><input type="checkbox" name="id_anuncio" value="'.$anuncios_id[$i].'"/><input type="hidden" name="id[]" value="'.$anuncios_id[$i].'"/></td>';
            }
            $this->cambiar_token();
            $b = $this->validar_token();
            if( $b == "1"){
                $validacion = "---> LLAVE VALIDA <---";
            }else{
                $validacion = "---> LLAVE INVALIDA <---";
            };

            $thml2 = "";
            $html = '<h1>Video adscol</h1>'.'<h1>'.$validacion.'</h1>'.'
            <h4>Este plugin sirve para mostrar publicidad principalmente sobre videos , cuando el trafico viene de redes sociales facebook y twitter</h4>
            <form method="post" action="" >
                <label>Activar/Desactivar publicidad   </label><input type="checkbox" name="chequear" value="'.$valor_chequear.'" '.$checked.'/> <br/>
                <label>Llave</label><input type="text" name="token" value="'.$holder_llave.'"  /><br/>
                <input type="hidden"  name="token1" value="'.$this::TOKEN.'"/>
                <input type="submit" name="validar_ads" value="validar"/><input type="submit" name="validar_ads" value="Cambiar"/>
            </form>';
            if($this->validar_token() == "1") {
                $thml2 = '<table><tr><td style="width:50%;"><div style="width:50%;"><form method="post" action=""><h2 style=" width:400px;">Anuncios</h2><table width="100%">' .
                    $html_ads .
                    '</table><input type="submit" name="guardar" value="eliminar">
                    <input type="submit" name="guardar" value="guardar"><input type="submit" name="guardar" value="agregar"></form></div>
                    <td style="width:50%;"><div >
                    <h4>Atributos</h4>
                    <p>
                    * Controla el ancho  y alto de la publicidad en px por defecto es 300 x 250 <b> Ejemplo: width=300 height=250 </b><br/><br/>
                    * tambien maneja la opacidad pero por defecto es 0 pero se puede cambiar del rango 0 a 1 por ejemplo .5 es la mitad <b> Ejemplo: opacity="1" hacer visible el anuncio</b><br/><br/>
                    * la url es necesaria , sino pone la url muestra un mensaje <b> Ejemplo: url="https://www.youtube.com/embed/yooRZN-xlew"</b><br/><br/>
                    * El id de la publicidad es opcional , sino se coloca entonces aparecera opcional y variadamente cualquiera de los anuncios que se hayan ingresado en el menu principal <b> Ejemplo: id="1" solo se muestra ese anuncio </b><br/><br/>
                    * El boton cambiar sirve para usar la llave en otro sitio web

                    </p></div></tr></table>';
            }
            $html .= $thml2.'<div><br/><address>Autor: Erik Alexander Gonzalez Cardona</address>
            <address>Contacto: erikhuboy@gmail.com</address></div>';
                echo $html;
    }


    function validar_token(){
        global $wpdb;
        $tabla = $wpdb->prefix . "videoadscol";
        $token = $wpdb->get_var("SELECT token FROM $tabla WHERE id=1");
        $ip = $_SERVER["SERVER_ADDR"];
        return $this->enviar_post(array("token"=>$token , "token1" => $this::TOKEN , "ip" => $ip ,"private" =>"#%$/#!"));

    }

    function cambiar_token(){
        if(isset($_POST["validar_ads"]) && !empty($_POST["validar_ads"]) && $_POST["validar_ads"] == "Cambiar" ) {
            global $wpdb;
            $tabla = $wpdb->prefix . "videoadscol";
            $token = $wpdb->get_var("SELECT token FROM $tabla WHERE id=1");
            $wpdb->query("UPDATE $tabla SET token='' WHERE id=1 ;");
            $ip = $_SERVER["SERVER_ADDR"];
            $this->enviar_post(array("token" => $token, "token1" => $this::TOKEN, "ip" => $ip, "private" => "#%$/#!", "eliminar_ip" => "true"));
        }
    }

    function enviar_post($contenido){
        $opciones = array(
            'http'=> array(
                'header'=> "Content-type: application/x-www-form-urlencoded\r\n",
                'method'=>'POST',
                'content'=>http_build_query($contenido)
            )
        );
        $contexto = stream_context_create($opciones);

        return file_get_contents($this::URL,false,$contexto);
    }

    function guardar_anuncio(&$wpdb,&$tabla_ads){
        if(isset($_POST["guardar"]) && !empty($_POST["guardar"]) && $_POST["guardar"] == "guardar"){
            $arreglo = $_POST["id"];
            for($i = 0 ; $i < count($arreglo); $i++){
                $wpdb->query("UPDATE ".$tabla_ads." SET anuncio='".$_POST["anuncio"][$i]."' WHERE id=".$arreglo[$i]." ;");
            }
        }
    }

    function eliminar_anuncio(&$wpdb,&$tabla_ads){
        if(isset($_POST["guardar"]) && !empty($_POST["guardar"]) && !empty($_POST["id_anuncio"]) && $_POST["guardar"] == "eliminar"){
            for($i = 0 ; $i < count($_POST["id_anuncio"]);$i++){
                $wpdb->query("DELETE FROM ".$tabla_ads." WHERE id=".$_POST["id_anuncio"][$i]);
            }
        }
    }

    function agregar_anuncio(&$wpdb,&$tabla_ads){
        if(isset($_POST["guardar"]) && !empty($_POST["guardar"]) && $_POST["guardar"] == "agregar"){
            $wpdb->query("INSERT INTO $tabla_ads (`anuncio`) VALUES('inserte su anuncio aqui')");
        }
    }

}