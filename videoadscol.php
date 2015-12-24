<?php
/*
Plugin Name: Video Ads Col
Plugin URI: http://www.eriksoft.co
Description: En este script podemos controlar el ctr en videos publicados en los post , personas que ingresan desde facebook y  twitter
Version: 1.0
Author: Erik Alexander Gonzalez Cardona
*/
include __DIR__.'/class/class_videoads.php';

$videoads = new class_videoads();

register_activation_hook(__FILE__ , 'videoads_activate');
register_deactivation_hook(__FILE__, 'videoads_deactivate');

function videoads_activate(){
    global $wpdb;
    $ntable = $wpdb->prefix . "videoadscol";
    $ntable_anuncios = $ntable."_ads";
    $wpdb->query("CREATE TABLE $ntable (`id` mediumint(9) NOT NULL auto_increment , `ctr` mediumint(2) NOT NULL ,`token` tinytext NOT NULL , `token1` tinytext NOT NULL , PRIMARY KEY (`id`)) " );
    $wpdb->query("CREATE TABLE $ntable_anuncios (`id` INTEGER NOT NULL auto_increment ,`anuncio` TEXT NOT NULL , PRIMARY KEY (`id`)) " );
    $wpdb->query("INSERT INTO $ntable (`id`,`ctr`,`token`,`token1`) VALUES (1,1,'Introduzca la llave','zOjEl6Cx9Du1mozaVWzqlyYKRh') ");
    $wpdb->query("INSERT INTO $ntable_anuncios (`anuncio`) VALUES('inserte su anuncio aqui')");
}

function videoads_deactivate(){
    global $wpdb;
    $ntable = $wpdb->prefix . "videoadscol";
    $ntable_anuncios = $ntable."_ads";
    $wpdb->query("DROP TABLE $ntable");
    $wpdb->query("DROP TABLE $ntable_anuncios");
}



add_shortcode('videoadscol','manejador_shortcode');

/**
 * Controla el ancho  y alto de la publicidad en px por defecto es 300 x 250
 * tambien maneja la opacidad pero por defecto es 0
 * la url es necesaria , sino pone la url muestra un mensaje
 * el id de la publicidad es opcional , sino se pone aparecera opcionalmente cualquiera de las que
 * se hayan ingresado al inicio
 * @param $attr
 * @param null $content
 * @return string
 */

function manejador_shortcode($attr , $content  = null){
    global $videoads;
    global $wpdb;
    $tabla = $wpdb->prefix."videoadscol";
    $tabla_ads = $tabla. "_ads";
    $a = shortcode_atts(array('url'=>"url",'id' => 'id' , "width"=>"300" , "height"=>"250" , "opacity"=>"0"),$attr);
    $id = $a["id"];
    $url = $a["url"];
    $ancho = $a["width"]."px";
    $alto = $a["height"]."px";
    $opacity = $a["opacity"];
    $url_ref = $_SERVER["HTTP_REFERER"];

    if($content != null || $url == "url"){
        return '<div style="background-color: yellow;"><h1>NO HAY NADA</h1><p>porfavor verifique que tiene escrito correctamente el shortcode [videoadscol id="id_anuncio" url="url_video"]</p></div>';
    }
    if($videoads->validar_token() == "2"){
        return '<div><script type="text/javascript">alert("Error en el servidor , porfavor contacte con el proveedor del plugin Video adscol. disculpe las molestias ");</script></div>';
    }else if($videoads->validar_token() == "0"){
        return '<div><script type="text/javascript">alert("Error la llave ingresada para el plugin Video adscol es invalida");</script></div>';
    }

    $anuncios = $wpdb->get_col("SELECT anuncio FROM $tabla_ads ;");

    if($id == "id" && count($anuncios) != 0){
        $index = rand( 0 ,(count($anuncios)-1));
        $anuncio = $anuncios[$index];
    }else {
        $anuncio = $wpdb->get_var("SELECT anuncio FROM $tabla_ads WHERE id=" . $id);
    }

    if($videoads->validar_token() != "0"){
        $l = $wpdb->get_var("SELECT ctr FROM $tabla WHERE id=1");
        $val = true;
        if($l != 0){
            if(strpos($url_ref,"facebook") || strpos($url_ref , "twitter")){
                $html = get_snippet_ads($anuncio,$url,$ancho,$alto,$opacity);
                $val = false;
            }
        }
        if($val){
            $html = "<iframe src=$url width='500' height='315' frameborder='0' allowfullscreen></iframe><div id='video-videoadscol'>".$anuncio."</div>";
        }

    }else{
        $html = '<script type="text/javascript">window.onload  = function(){ alert("Esta intentando usar el plugin video adscol sin activar la licencia <br/> , porfavor si  compro la licencia y no le funciona ponerse en contacto con el desarrollador");}</script>';
    }

    return $html;
}

function get_snippet_ads($ads,$url,$ancho ,$alto,$opacity){
    ob_start();
    ?>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js'></script>
    <script type="text/javascript" > $("document").ready(function(){
            if(Math.round(Math.random())==0){
                $("#video-videoadscol").css({"display":"none"});
            }
        })</script>
        <div id="video-videoadscol"><?php echo($ads)?></div>
        <iframe src=<?php echo($url);?> width="500" height="315" frameborder='0' allowfullscreen></iframe>
        <style>
            #video-videoadscol{
                position: absolute;
                z-index: 99999;
                width: <?php echo($ancho)?>;
                height:<?php echo($alto)?>;
                margin: 20px 0px 0px 160px;
                opacity: <?php echo($opacity)?>;
                background-color: blueviolet;
            }
        </style>
    <?php
    return ob_get_clean();
}

?>