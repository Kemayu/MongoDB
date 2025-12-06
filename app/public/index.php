<?php

/**
 * Created by PhpStorm.
 * User: canals5
 * Date: 28/10/2019
 * Time: 16:16
 */

use MongoDB\Client;

require_once __DIR__ . "/../src/vendor/autoload.php";

$c = new Client("mongodb://mongo");
echo "connected to mongo <br>";

$db = $c->__get("chopizza");
$produit = $db->__get("Produit");
$cursor = $produit->find([], [
    'typeMap' => [
        'root' => 'array',
        'rument' => 'array',
        'array' => 'array'
    ]
]);
//1
echo "1) Liste des Produits : <br>";
foreach ($cursor as $p) {
    if (isset($p['numero']) && isset($p['categorie']) && isset($p['libelle'])) {
        echo "N° " . $p['numero'] . " | Catégorie : " . $p['categorie'] . " | Libelle : " . $p['libelle'] . " <br>";
    }
}

//2
$result = $db->Produit->find(["numero" => 6]);
echo "<br> 2) <br>";
foreach ($result as $r) {
    echo "Produit trouvé :" . $r['numero'] . " | Catégorie : " . $r['categorie'] . " | Libelle : " . $r['libelle'] . " | Description : " . $r['description'];
    echo " Tarifs : ";
    foreach ($r['tarifs'] as $tarif) {
        echo $tarif['taille'] . ": " . $tarif['tarif'] . "€ ";
    }
}

//3
$result = $db->Produit->find(["tarifs.taille" => "normale", "tarifs.tarif" => ['$lte' => 3.0]]);
echo "<br> 3)";
foreach ($result as $a) {
    echo "<br> Produit trouvé :" . $a['numero'] . " | Catégorie : " . $a['categorie'] . " | Libelle : " . $a['libelle'] . " | Description : " . $a['description'];
    echo " Tarifs : ";
    foreach ($r['tarifs'] as $tarif) {
        echo $tarif['taille'] . ": " . $tarif['tarif'] . "€ ";
    }
}
