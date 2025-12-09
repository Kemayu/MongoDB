<?php

use MongoDB\Client;

require_once __DIR__ . '/../src/vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$client = new Client('mongodb://mongo:27017');
$db = $client->selectDatabase('ChoPizza');
$produitsCol = $db->selectCollection('Produit');

$categories = $produitsCol->distinct('categorie');
sort($categories);
$categoryOptions = array_values($categories);

$sizeOptions = $produitsCol->distinct('tarifs.taille');
sort($sizeOptions);
$sizeOptions = array_values($sizeOptions);

$activeCategory = $_GET['categorie'] ?? 'Toutes';
$status = $_GET['status'] ?? null;
$formError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libelle = trim($_POST['libelle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categorieInput = $_POST['categorie'] ?? '';
    $tarifTailles = $_POST['tarif_taille'] ?? [];
    $tarifPrix = $_POST['tarif_prix'] ?? [];
    $tarifs = [];

    foreach ($tarifTailles as $idx => $taille) {
        $taille = trim((string) $taille);
        $prixField = $tarifPrix[$idx] ?? '';
        if ($taille === '' || $prixField === '') {
            continue;
        }
        $prixValue = filter_var($prixField, FILTER_VALIDATE_FLOAT);
        if ($prixValue === false) {
            continue;
        }
        $tarifs[] = [
            'taille' => $taille,
            'tarif' => $prixValue
        ];
    }

    if ($libelle === '' || $description === '' || $categorieInput === '' || empty($tarifs)) {
        $formError = "Merci de remplir toutes les données et au moins un tarif valide.";
    } else {
        $lastProduct = $produitsCol->findOne([
            'sort' => ['numero' => -1],
            'projection' => ['numero' => 1]
        ]);
        $nextNumero = (($lastProduct['numero'] ?? 0) + 1);
        $produitsCol->insertOne([
            'numero' => $nextNumero,
            'categorie' => $categorieInput,
            'libelle' => $libelle,
            'description' => $description,
            'tarifs' => $tarifs,
            'recettes' => []
        ]);
        header('Location: ?categorie=' . urlencode($categorieInput) . '&status=added');
        exit;
    }
}

$queryFilter = $activeCategory === 'Toutes' ? [] : ['categorie' => $activeCategory];
$produits = $produitsCol->find($queryFilter, [
    'sort' => ['numero' => 1],
    'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']
]);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue Chopizza</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .layout { display: flex; min-height: 100vh; }
        .sidebar { width: 220px; background: #222; color: #fff; padding: 20px; }
        .sidebar h2 { font-size: 18px; margin-top: 0; }
        .sidebar a { display: block; color: #fff; padding: 6px 10px; text-decoration: none; margin-bottom: 4px; border-radius: 4px; }
        .sidebar a.active, .sidebar a:hover { background: #ff6b3f; }
        .content { flex: 1; padding: 30px; }
        .flash { padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; }
        .flash.success { background: #d1f5d3; color: #116611; }
        .flash.error { background: #fdd8d8; color: #661111; }
        .products { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
        .card { background: #fff; border-radius: 8px; padding: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin: 0 0 8px; }
        .card ul { padding-left: 18px; margin: 8px 0 0; }
        .form-block { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 30px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; font-weight: bold; margin-bottom: 4px; }
        input[type="text"], textarea, select, input[type="number"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        textarea { resize: vertical; min-height: 60px; }
        .tarif-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; align-items: end; margin-bottom: 10px; }
        button { background: #ff6b3f; border: none; color: #fff; padding: 10px 18px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #ff845f; }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h2>Catégories</h2>
        <a href="?categorie=Toutes" class="<?= $activeCategory === 'Toutes' ? 'active' : '' ?>">Toutes</a>
        <?php foreach ($categoryOptions as $cat): ?>
            <a href="?categorie=<?= urlencode($cat) ?>" class="<?= $activeCategory === $cat ? 'active' : '' ?>">
                <?= htmlspecialchars($cat, ENT_QUOTES) ?>
            </a>
        <?php endforeach; ?>
    </aside>
    <main class="content">
        <h1>Catalogue Chopizza</h1>
        <?php if ($status === 'added'): ?>
            <div class="flash success">Produit ajouté avec succès.</div>
        <?php endif; ?>
        <?php if ($formError): ?>
            <div class="flash error"><?= htmlspecialchars($formError, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <div class="products">
            <?php foreach ($produits as $prod): ?>
                <article class="card">
                    <h3>#<?= htmlspecialchars((string) ($prod['numero'] ?? '?'), ENT_QUOTES) ?> · <?= htmlspecialchars((string) ($prod['libelle'] ?? 'Sans libellé'), ENT_QUOTES) ?></h3>
                    <p><strong>Catégorie :</strong> <?= htmlspecialchars((string) ($prod['categorie'] ?? 'n/c'), ENT_QUOTES) ?></p>
                    <p><?= nl2br(htmlspecialchars((string) ($prod['description'] ?? 'Pas de description.'), ENT_QUOTES)) ?></p>
                    <?php if (!empty($prod['tarifs'])): ?>
                        <ul>
                            <?php foreach ($prod['tarifs'] as $tarif): ?>
                                <li><?= htmlspecialchars((string) ($tarif['taille'] ?? 'n/c'), ENT_QUOTES) ?> : <?= htmlspecialchars((string) ($tarif['tarif'] ?? '?'), ENT_QUOTES) ?> €</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <section class="form-block">
            <h2>Ajouter un produit</h2>
            <form method="post">
                <div class="form-group">
                    <label for="libelle">Libellé</label>
                    <input type="text" name="libelle" id="libelle" value="<?= htmlspecialchars($_POST['libelle'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" required><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="categorie">Catégorie</label>
                    <select name="categorie" id="categorie" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($categoryOptions as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>" <?= (($_POST['categorie'] ?? $activeCategory) === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p>Tarifs (sélectionner une taille puis un tarif) :</p>
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="tarif-row">
                        <select name="tarif_taille[]">
                            <option value="">Taille</option>
                            <?php foreach ($sizeOptions as $taille): ?>
                                <option value="<?= htmlspecialchars($taille, ENT_QUOTES) ?>" <?= (($_POST['tarif_taille'][$i] ?? '') === $taille) ? 'selected' : '' ?>><?= htmlspecialchars($taille, ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="tarif_prix[]" step="0.1" min="0" placeholder="Tarif €" value="<?= htmlspecialchars($_POST['tarif_prix'][$i] ?? '', ENT_QUOTES) ?>">
                    </div>
                <?php endfor; ?>
                <button type="submit">Créer le produit</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
