<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('document_types')->insert(
            [
                [
                    'slug'       => 'Company-Company Contract',
                    'en'         => 'Company-Company Contract',
                    'fr'         => 'Contrat Privé',
                    'ar'         => 'عقد خاص',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Company-State Contract',
                    'en'         => 'Company-State Contract',
                    'fr'         => 'Contrat Public',
                    'ar'         => 'عقد عام',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Company-State Model Contract',
                    'en'         => 'Company-State Model Contract',
                    'fr'         => 'Contrat Public Modèle de Contrat',
                    'ar'         => 'عقد عام نموذج عقد',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Contract',
                    'en'         => 'Contract',
                    'fr'         => 'Contrat',
                    'ar'         => 'عقد',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Document Summary',
                    'en'         => 'Document Summary',
                    'fr'         => 'Résumé',
                    'ar'         => 'ملخص',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Environmental and Social Document',
                    'en'         => 'Environmental and Social Document',
                    'fr'         => 'Document Social et/ou Environmental',
                    'ar'         => 'وثيقة ذات طابع بيئي و اجتماعي',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Feasiblity Study',
                    'en'         => 'Feasiblity Study',
                    'fr'         => 'Étude de faisabilité',
                    'ar'         => 'دراسة الجدوى',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Financial information',
                    'en'         => 'Financial information',
                    'fr'         => 'Information financière',
                    'ar'         => 'معلومات مالية',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Forest Management Plan',
                    'en'         => 'Forest Management Plan',
                    'fr'         => 'Plan de gestion forestière',
                    'ar'         => 'خطة إدارة الغابات',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Model Contract',
                    'en'         => 'Model Contract',
                    'fr'         => 'Contrat-type',
                    'ar'         => 'نموذج عقد',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Notice',
                    'en'         => 'Notice',
                    'fr'         => 'Préavis',
                    'ar'         => 'إشعار',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Other',
                    'en'         => 'Other',
                    'fr'         => 'Autres',
                    'ar'         => 'آخر',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Project Plan',
                    'en'         => 'Project Plan',
                    'fr'         => 'Plan de projet',
                    'ar'         => 'مخطط المشروع',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Social Agreement',
                    'en'         => 'Social Agreement',
                    'fr'         => 'Accord social',
                    'ar'         => 'اتفاق اجتماعي',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Social Impact Assessment',
                    'en'         => 'Social Impact Assessment',
                    'fr'         => 'Étude d\'impact sur la société',
                    'ar'         => 'تقييم الأثر الاجتماعي',
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ],
                [
                    'slug'       => 'Decree',
                    'en'         => 'Decree',
                    'fr'         => 'Décret',
                    'ar'         => null,
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime,
                ]
            ]
        );
    }
}