<?php include_once '../../secundario/brightcove-funciones.php';?>


<form method="post" action="">
AMF (Base64_encoded):<br/><textarea name="a" cols="100" rows="10"><?php echo isset($_POST["a"]) ? $_POST["a"] : ''?></textarea>
<br/><br/>
Texto plano:<br/><textarea name="b" cols="100" rows="10"><?php var_dump(isset($_POST["a"]) ? brightcove_decode(base64_decode($_POST["a"])) : '')?></textarea>
<br/>
<input type="submit" value="Calcular">
</form />
