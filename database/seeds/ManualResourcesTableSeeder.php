<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManualResourcesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('resources')->insert(
            [
                [
                    'slug'                  => 'wood',
                    'en'                    => 'wood',
                    'fr'                    => 'wood',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Sweet potatoes',
                    'en'                    => 'Sweet potatoes',
                    'fr'                    => 'Sweet potatoes',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Rivers',
                    'en'                    => 'Rivers',
                    'fr'                    => 'Rivers',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Resin tapping',
                    'en'                    => 'Resin tapping',
                    'fr'                    => 'Resin tapping',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Poultry',
                    'en'                    => 'Poultry',
                    'fr'                    => 'Poultry',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Pine (Tree)',
                    'en'                    => 'Pine (Tree)',
                    'fr'                    => 'Pine (Tree)',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Peat soils',
                    'en'                    => 'Peat soils',
                    'fr'                    => 'Peat soils',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Nut crops',
                    'en'                    => 'Nut Crops',
                    'fr'                    => 'Nut Crops',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Mango',
                    'en'                    => 'Mango',
                    'fr'                    => 'Mango',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Livestock',
                    'en'                    => 'Livestock',
                    'fr'                    => 'Livestock',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Le bois d\'œuvre',
                    'en'                    => 'Le bois d\'œuvre',
                    'fr'                    => 'Le bois d\'œuvre',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Land',
                    'en'                    => 'Land',
                    'fr'                    => 'Land',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Grapes',
                    'en'                    => 'Grapes',
                    'fr'                    => 'Grapes',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Fruit Trees',
                    'en'                    => 'Fruit Trees',
                    'fr'                    => 'Fruit Trees',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Forests',
                    'en'                    => 'Forests',
                    'fr'                    => 'Forests',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Cranberries',
                    'en'                    => 'Cranberries',
                    'fr'                    => 'Cranberries',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Cattle',
                    'en'                    => 'Cattle',
                    'fr'                    => 'Cattle',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Cassava',
                    'en'                    => 'Cassava',
                    'fr'                    => 'Cassava',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Carbon credits',
                    'en'                    => 'Carbon credits',
                    'fr'                    => 'Carbon credits',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Bertholethia excellsa',
                    'en'                    => 'Bertholethia excellsa',
                    'fr'                    => 'Bertholethia excellsa',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Bamboo',
                    'en'                    => 'Bamboo',
                    'fr'                    => 'Bamboo',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Avocado',
                    'en'                    => 'Avocado',
                    'fr'                    => 'Avocado',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Asparagus',
                    'en'                    => 'Asparagus',
                    'fr'                    => 'Asparagus',
                    'ar'                    => NULL ,
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'other minerals',
                    'en'                    => 'Other Minerals',
                    'fr'                    => 'Autres Minéraux',
                    'ar'                    => 'المعادن الأخرى',
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Methane',
                    'en'                    => 'Methane',
                    'fr'                    => 'Methane',
                    'ar'                    => 'Methane',
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Quartz',
                    'en'                    => 'Quartz',
                    'fr'                    => 'Quartz',
                    'ar'                    => 'Quartz',
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'cuivre',
                    'en'                    => 'Cuivre',
                    'fr'                    => 'Cuivre',
                    'ar'                    => 'Cuivre',
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'or',
                    'en'                    => 'Or',
                    'fr'                    => 'Or',
                    'ar'                    => 'Or',
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Bois',
                    'en'                    => 'Bois',
                    'fr'                    => 'Bois',
                    'ar'                    => 'Bois',
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Forest Management Unit (FMU)',
                    'en'                    => 'Forest Management Unit (FMU)',
                    'fr'                    => 'Forest Management Unit (FMU)',
                    'ar'                    => 'Forest Management Unit (FMU)',
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ],[
                    'slug'                  => 'Forest',
                    'en'                    => 'Forest',
                    'fr'                    => 'Forest',
                    'ar'                    => 'Forest',
                    'created_at'            => new DateTime,
                    'updated_at'            => new DateTime,
                ]
            ]
        );
    }
}