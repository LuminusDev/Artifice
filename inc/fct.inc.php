
<?php

/* Fonctions */
//Permet de traduire le texte balisé avec {t}
function templateEngine_translate($buffer)
{
	preg_match_all("#\{t\}(.+)\{/t\}#i", $buffer, $matches);

	for ($i = 0; $i < count($matches[0]); $i++) {
		$buffer = str_replace($matches[0][$i], _($matches[1][$i]), $buffer);
	}

    return $buffer;
}

function templateEngine_minification($buffer)
{
	//Minification
    $search = array(
        '/ {2,}/',
        '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'
    );

    $replace = array(
        ' ',
        ''
    );
    $buffer = preg_replace($search, $replace, $buffer);

    return $buffer;
}

function createToken($string)
{
	return hash('sha256','eEeg6eyu4F1'.$string.'r6sr4REge4');
}

function checkToken($string, $token)
{
	return createToken($string) === $token;
}

function randomName($taille) //Utilisée par TemplateEngine
{
	$string = "";
	$chaine = "abcdefghijklmnpqrstuvwxy";
	srand((double)microtime()*1000000);

	for ($i = 0; $i < $taille; $i++) {
		$string .= $chaine[rand()%strlen($chaine)];
	}

	return $string;
}
