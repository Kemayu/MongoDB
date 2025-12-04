# use chopizza

## 1
db.Produit.find()

## 2
db.Produit.countDocuments()

## 3
db.Produit.find().sort({numero : -1}

## 4
db.Produit.find({libelle: "Margherita"})

## 5
db.Produit.find({categorie: "Boissons"})

## 6
db.Produit.find({}, {_id: 0, categorie: 1, numero: 1, libelle: 1})

## 7
db.Produit.find({}, {taille: 1, tarif: 1})

## 8
db.Produit.find({"tarifs.tarif":{$lt:8}})

## 9
db.Produit.find({"tarifs.taille":{$lt:8}})

## 10
db.Produit.insertOne([{
  _id: ObjectId('6543ac35a08f42f1cc02e810'),
  numero: 15,
  libelle: 'Pepperoni',
  description: 'Tomate, mozzarella, pepperoni',
  image: 'https://www.dominos.fr/ManagedAssets/FR/product/PZPE.png',
  categorie: 'Pizzas',
  tarifs: [
    {
      taille: 'grande',
      tarif: 12.99
    },
    {
      taille: 'normale',
      tarif: 9.99
    }
  ],
  recettes: [
    ObjectId('6543b503f0e418df120c79b2'),
    ObjectId('6543b503f0e418df120c79bb'),
    ObjectId('6543b503f0e418df120c79a5'),
    ObjectId('6543b503f0e418df120c79a6')
  ]
}])

## 11
db.Produit.aggregate([
  {
    $match: { numero: 1 }
  },
  {
    $lookup: {
      from: "Recette",
      localField: "recettes",
      foreignField: "_id",
      as: "details_recettes"
    }
  }
])