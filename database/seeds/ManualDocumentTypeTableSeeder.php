<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManualDocumentTypeTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('document_types')->insert(
            [
                [
                    'slug'       => 'Annexe',
                    'en'         => 'Annexe',
                    'fr'         => 'Annexe',
                    'ar'         => 'Annexe',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Arrêté',
                    'en'         => 'Arrêté',
                    'fr'         => 'Arrêté',
                    'ar'         => 'Arrêté',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Attestation de Mesure',
                    'en'         => 'Attestation de Mesure',
                    'fr'         => 'Attestation de Mesure',
                    'ar'         => 'Attestation de Mesure',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Clause sociale',
                    'en'         => 'Clause sociale',
                    'fr'         => 'Clause sociale',
                    'ar'         => 'Clause sociale',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'List of Attendees',
                    'en'         => 'List of Attendees',
                    'fr'         => 'List of Attendees',
                    'ar'         => 'List of Attendees',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Map Polygon',
                    'en'         => 'Map Polygon',
                    'fr'         => 'Map Polygon',
                    'ar'         => 'Map Polygon',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Minutes of Meeting',
                    'en'         => 'Minutes of Meeting',
                    'fr'         => 'Minutes of Meeting',
                    'ar'         => 'Minutes of Meeting',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Presidential Decree',
                    'en'         => 'Presidential Decree',
                    'fr'         => 'Presidential Decree',
                    'ar'         => 'Presidential Decree',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Annulation de permis',
                    'en'         => 'Annulation de permis',
                    'fr'         => 'Annulation de permis',
                    'ar'         => 'Annulation de permis',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Mise en demeure du PEA 171 de la SCAD',
                    'en'         => 'Mise en demeure du PEA 171 de la SCAD',
                    'fr'         => 'Mise en demeure du PEA 171 de la SCAD',
                    'ar'         => 'Mise en demeure du PEA 171 de la SCAD',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Mise en demeure suite au non-respect de l’avenant à la Convention Provisoire d’Aménagement-Exploitation',
                    'en'         => 'Mise en demeure suite au non-respect de l’avenant à la Convention Provisoire d’Aménagement-Exploitation',
                    'fr'         => 'Mise en demeure suite au non-respect de l’avenant à la Convention Provisoire d’Aménagement-Exploitation',
                    'ar'         => 'Mise en demeure suite au non-respect de l’avenant à la Convention Provisoire d’Aménagement-Exploitation',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Prorogation de la Convention Provisoire du PEA 190',
                    'en'         => 'Prorogation de la Convention Provisoire du PEA 190',
                    'fr'         => 'Prorogation de la Convention Provisoire du PEA 190',
                    'ar'         => 'Prorogation de la Convention Provisoire du PEA 190',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Publication Journal Officiel',
                    'en'         => 'Publication Journal Officiel',
                    'fr'         => 'Publication Journal Officiel',
                    'ar'         => 'Publication Journal Officiel',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],[
                    'slug'       => 'Ratification',
                    'en'         => 'Ratification',
                    'fr'         => 'Ratification',
                    'ar'         => 'Ratification',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ]
            ]
        );
    }
}
