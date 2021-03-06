<?php

class Aragontv extends cadena{

function calcula(){

/*
"clip":{"url":"mp4:/_archivos/videos/web/4334/4334.mp4"}
"netConnectionUrl":"rtmp://alacarta.aragontelevision.es/vod"
"playlist":[{"url":"mp4:/_archivos/videos/web/4334/4334.mp4"}]

http://alacarta.aragontelevision.es/_archivos/videos/web/4334/4334.mp4
*/

$imagen='http://www.'.DOMINIO.'/canales/aragontv.png';

$obtenido=array('enlaces' => array());

//un solo video
if(enString($this->web_descargada,'flowplayer(')){
	dbug('simple');
	$titulo=entre1y2($this->web_descargada,'<div class="apartado"><h2>','</h2>');
	$titulo=limpiaTitulo($titulo);
	
	if(stringContains($titulo, array('</', 'Server:'))){
		dbug('titulo fallido, usando <title>');
		$titulo=entre1y2($this->web_descargada,'<title>','</');
		$titulo=limpiaTitulo($titulo);
	}
	dbug('titulo='.$titulo);
	
	$obtenido['enlaces'][] = $this->SacarVideo($this->web_descargada, $titulo);
}

//muchos videos
elseif(enString($this->web_descargada,'list-not-even')){
	dbug('multi');

	$p=strpos($this->web_descargada,'<div class="apartado">');
	$titulo=entre1y2_a($this->web_descargada,$p,'<h2>','</h2>');
	
	//en la pagina principal y otras el titulo estará mal, por lo que poner uno genérico
	if(enString($titulo,'<'))
		$titulo='Aragon TV';
	
	$titulo=limpiaTitulo($titulo);
	dbug('titulo='.$titulo);


	$videos=substr_count($this->web_descargada,'<span>Ver video</span>');
	dbug('total videos='.$videos);

	$last=0;
	for($i=0;$i<$videos;$i++){
		$last=strposF($this->web_descargada,'<div id="idv',$last);
		$url='http://alacarta.aragontelevision.es/ajax/ajax.php?id='.entre1y2_a($this->web_descargada,$last,'_','"');

		//encontrar ya el titulo del vídeo
		$f=strpos($this->web_descargada,'fecha',$last);
		$parte=substr($this->web_descargada,$last,$f-$last);
		$p=strrpos($parte,'<a');
		$nombre=entre1y2_a($parte,$p,'title="','"');

		$extracto=CargaWebCurl($url);

		$obtenido['enlaces'][] = $this->SacarVideoPorId($extracto,$nombre);
	}
}

$obtenido['titulo']=$titulo;
$obtenido['imagen']=$imagen;

finalCadena($obtenido, false);
}


function SacarVideo(&$entrada, $nombre){
	//url:'mp4%3A%2Fweb%2F4311%2F4311.mp4',

	$retfull = strtr($entrada,array(' '=>''));
	
	$url = urldecode(entre1y2($retfull,"url:'","'"));
	dbug($url);
	
	$rtmpbase = urldecode(entre1y2($retfull,"netConnectionUrl:'","'"));
	dbug_($rtmpbase);

	// http://alacarta.aragontelevision.es/_archivos/videos'.$url;

	$videos=array(
		'url'       => 'rtmp://aragontvvodfs.fplive.net/aragontvvod'.$url,
		'rtmpdump'  => '-r "'.$rtmpbase.'" -y "'.$url.'" -o "'.generaNombreWindowsValido($nombre).'.mp4"',
		'tipo'      => 'rtmpConcreto',
		'extension' => 'mp4'
	);

	return $videos;
}


function SacarVideoPorId(&$entrada,$nombre=''){
	//titulo
	if($nombre===''){
		//<div class="apartado"><h2>ARAGÓN NOTICIAS 1 - 05/05/2012 14:00</h2></div> 
		$nombre=entre1y2($entrada,'<h1>','<');
		dbug('nombre. Obtenido en la web ID='.$nombre);
	}else{
		dbug('nombre. Obtenido en la web padre='.$nombre);
	}
	
	$res = $this->SacarVideo($entrada,$nombre);
	$res['titulo'] = $nombre;
	return $res;
}

}
