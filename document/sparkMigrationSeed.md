# Commandes spark

## Creation d'un class Migration :
- php spark make:migration nom_migration

## Executer les fichiers migration :
- php spark migrate

## Annuler les modifications
- php spark migrate:rollback


### Les fonctions up() c'est ce qui se passe lors de l'execution et down() lors de l'annulation
- Exemple : 
```
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProduitsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nom' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'prix' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('produits');
    }

    public function down()
    {
        $this->forge->dropTable('produits', true);
    }
}
``` 
## Creation d'un class Seed :
- php spark make:seeder ProduitSeeder

## Execution :
- php spark db:seed ProduitSeeder

## Exemple de seed :
```
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProduitSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nom' => 'Stylo', 'prix' => '1500.00'],
            ['nom' => 'Cahier', 'prix' => '3000.00'],
            ['nom' => 'Sac', 'prix' => '25000.00'],
        ];

        $this->db->table('produits')->insertBatch($data);
    }
}

```

## Pour lancer plusieurs seed d'un cout, mettre dans un fichier DatabaseSeeder.php :
```
public function run()
{
    $this->call('ProduitSeeder');
    $this->call('AutreSeeder');
}
```

