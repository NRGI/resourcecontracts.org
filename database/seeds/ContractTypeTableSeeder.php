<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractTypeTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('contract_types')->insert(
            [
                [
                    'slug'        => 'Agricultural Development Agreement',
                    'en'          => 'Agricultural Development Agreement',
                    'fr'          => 'Accord de développement agricole',
                    'ar'          => 'اتفاقية التنمية الزراعية',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ],  
                [
                    'slug'        => 'Asset Sale and Purchase Agreement',
                    'en'          => 'Asset Sale and Purchase Agreement',
                    'fr'          => 'Accord d\'achat et de vente d\'actifs',
                    'ar'          => 'بيع و شراء الأصول',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ],  
                [
                    'slug'        => 'Autre',
                    'en'          => 'Autre',
                    'fr'          => 'Autres',
                    'ar'          => 'آخر',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Bail Foncier',
                    'en'          => 'Bail Foncier',
                    'fr'          => 'Bail Foncier',
                    'ar'          => 'ايجار العقارات',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Mining Contract',
                    'en'          => 'Mining Contract',
                    'fr'          => 'contrat minier',
                    'ar'          => null,
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Concession Agreement',
                    'en'          => 'Concession Agreement',
                    'fr'          => 'Accord de concession',
                    'ar'          => 'اتفاقية امتياز',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Contract Amendment',
                    'en'          => 'Contract Amendment',
                    'fr'          => 'Avenant au contrat',
                    'ar'          => 'تعديل العقد',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Contract Annex',
                    'en'          => 'Contract Annex',
                    'fr'          => 'Annexe au contrat',
                    'ar'          => 'ملحق العقد',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Contrat de Concession Agricole',
                    'en'          => 'Contrat de Concession Agricole',
                    'fr'          => 'Contrat de Concession Agricole',
                    'ar'          => 'عقد امتياز زراعي',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Contrat de Concession Forestière',
                    'en'          => 'Contrat de Concession Forestière',
                    'fr'          => 'Contrat de Concession Forestière',
                    'ar'          => 'عقد امتيازغابي',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Establishment Convention',
                    'en'          => 'Establishment Convention',
                    'fr'          => 'Convention d\'établissement',
                    'ar'          => 'اتفاقية تأسيس',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Exploitation Permit/License',
                    'en'          => 'Exploitation Permit/License',
                    'fr'          => 'Permis/Licence d\'exploitation',
                    'ar'          => 'رخصة / اذن استغلال',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Exploration Permit/License',
                    'en'          => 'Exploration Permit/License',
                    'fr'          => 'Permis/Licence d\'exploration',
                    'ar'          => 'رخصة / اذن استكشاف',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Forest Management Contract',
                    'en'          => 'Forest Management Contract',
                    'fr'          => 'Contrat de gestion forestière',
                    'ar'          => 'عقد إدارة الغابات',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Forest Management Plan',
                    'en'          => 'Forest Management Plan',
                    'fr'          => 'Plan de gestion forestière',
                    'ar'          => 'خطة إدارة الغابات',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Investment Incentive Contract',
                    'en'          => 'Investment Incentive Contract',
                    'fr'          => 'Contrat d\'incitation aux investissements',
                    'ar'          => 'عقد تحفيز على الاستثمار',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Investment Promotion Agreement',
                    'en'          => 'Investment Promotion Agreement',
                    'fr'          => 'Accord de promotion de l\'investissement',
                    'ar'          => 'اتفاقية تشجيع الاستثمار',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Joint Venture Agreement',
                    'en'          => 'Joint Venture Agreement',
                    'fr'          => 'Accord de coentreprise',
                    'ar'          => 'اتفاق المشروع المشترك',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Land Lease Agreement',
                    'en'          => 'Land Lease Agreement',
                    'fr'          => 'Accord de bail foncier',
                    'ar'          => 'اتفاقية تأجير أرض',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Memorandum of Understanding',
                    'en'          => 'Memorandum of Understanding',
                    'fr'          => 'Mémorandum d\'entente',
                    'ar'          => 'مذكرة تفاهم',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Other',
                    'en'          => 'Other',
                    'fr'          => 'Autres',
                    'ar'          => 'آخر',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Production or Profit Sharing Agreement',
                    'en'          => 'Production or Profit Sharing Agreement',
                    'fr'          => 'Accord de production ou d\'intéressement aux bénéfices',
                    'ar'          => 'اتفاقية مشاركة بالانتاج او الأرباح',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Protocole d\'Accord',
                    'en'          => 'Protocole d\'Accord',
                    'fr'          => 'Protocole d\'accord',
                    'ar'          => 'بروتوكول اتفاق',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Service Contract',
                    'en'          => 'Service Contract',
                    'fr'          => 'Contrat de service',
                    'ar'          => 'عقد  تقديم خدمة',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Stabilization Agreements',
                    'en'          => 'Stabilization Agreements',
                    'fr'          => 'Accord de stabilisation',
                    'ar'          => 'اتفاقيات لتحقيق الاستقرار',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Sub Contract',
                    'en'          => 'Sub Contract',
                    'fr'          => 'Sous contrat',
                    'ar'          => 'عقد من الباطن',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Sub-lease',
                    'en'          => 'Sub-lease',
                    'fr'          => 'Sous location',
                    'ar'          => 'تأجير من الباطن',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Timber Sale Contract',
                    'en'          => 'Timber Sale Contract',
                    'fr'          => 'Contrat de vente de bois',
                    'ar'          => 'عقد بيع الأخشاب',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ], 
                [
                    'slug'        => 'Translated Contract',
                    'en'          => 'Translated Contract',
                    'fr'          => 'Contrat traduit',
                    'ar'          => 'عقد مترجم',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ],
                [
                    'slug'        => 'Cahier des Charges',
                    'en'          => 'Cahier des Charges',
                    'fr'          => 'Cahier des Charges',
                    'ar'          => 'كراس شروط',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ],
                [
                    'slug'        => 'Infrastructure Agreement',
                    'en'          => 'Infrastructure Agreement',
                    'fr'          => 'Infrastructure Agreement',
                    'ar'          => 'Infrastructure Agreement',
                    'created_at'  => new DateTime,
                    'updated_at'  => new DateTime,
                ]
            ]
        );
    }
}
