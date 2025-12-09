<?php
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
    foreach ($a['tarifs'] as $tarif) {
        echo $tarif['taille'] . ": " . $tarif['tarif'] . "€ ";
    }
}

//4
$result = $db->Produit->find(["recettes" => ['$size' => 4]]);
echo "<br> 4) Produits avec 4 recettes :<br>";
foreach ($result as $produit) {
    echo "N° " . $produit['numero'] . " | Libelle : " . $produit['libelle'] . " | Nombre de recettes : " . count($produit['recettes']) . "<br>";
}

//5
echo "<br> 5) Recette du Produit numéro 6 : <br> ";
$result = $db->Produit->find(["numero" => 6]);
foreach ($result as $recette) {
    $r = $db->Recette->find(["_id" => ['$in' => $recette["recettes"]]]);
    foreach ($r as $rece) {
        echo "<br> Nom de la recette : " .  $rece["nom"] . " | Difficulte : " . $rece["difficulte"];
    }
}

//6
echo "<br> 6) <br>";
function getProduit(int $numero, string $taille)
{
    $c = new Client("mongodb://mongo");
    $db = $c->__get("chopizza");
    $result = $db->Produit->find(["numero" => $numero]);
    foreach ($result as $p) {
        foreach ($p['tarifs'] as $tarifs) {
            if($tarifs['taille'] == $taille){
                $tarif = $tarifs['tarif'];
            }
        }
        $produit = [
            'numero' =>  $p["numero"],
            'libelle' => $p['libelle'],
            'categorie' => $p['categorie'],
            'tarif' => $tarif,
            'taille' => $taille
        ];
    }
    return $produit;
}
$produit = getProduit(6, "grande");
echo json_encode($produit);
