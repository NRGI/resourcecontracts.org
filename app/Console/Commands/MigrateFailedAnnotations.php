<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Annotation\Page\Page;
use App\Nrgi\Services\Contract\MigrationService;
use Illuminate\Console\Command;

/**
 * Class MigrateFailedAnnotations
 * @package App\Console\Commands
 */
class MigrateFailedAnnotations extends Command
{
    /**
     * @var string
     */
    protected $name = 'nrgi:migrateFailedAnnotation';

    /**
     * @var string
     */
    protected $description = 'Migrates failed annotations for existing contracts';

    /**
     * @var
     */
    public $annotations;
    /**
     * @var MigrationService
     */
    protected $migration;

    /**
     * MigrateFailedAnnotations constructor.
     *
     * @param MigrationService $migration
     */
    public function __construct(MigrationService $migration)
    {
        parent::__construct();

        $this->migration = $migration;
    }

    /**
     * write brief description
     * @throws \Exception
     */
    public function fire()
    {
        $contract_id = 1567;
        $json        = json_decode(file_get_contents(public_path('34.json')));
        foreach ($json->annotations as $annotation) {
            $content  = $this->extractArticleReferenceAndText($annotation->content);
            $category = $this->mapCategory($annotation->title);

            if (empty($category)) {
                continue;
            }

            $ann      = [
                'text'        => $content['text'],
                'category'    => $category,
                'contract_id' => $contract_id,
                'status'      => 'draft',
            ];
            $a        = Annotation::create($ann);
            $ann_page = [
                'annotation_id'     => $a->id,
                'page_no'           => $annotation->page,
                'user_id'           => 1,
                'annotation'        => [
                    'shapes' => [
                        [
                            "type"     => "rect",
                            "geometry" => $this->migration->convertPoint(explode(',', $annotation->location->image)),
                        ],
                    ],
                ],
                'article_reference' => $content['article_reference'],
            ];
            Page::create($ann_page);
        }
    }

    /**
     * Get category key
     *
     * @param $category
     *
     * @return string
     * @throws \Exception
     */
    protected function mapCategory($category)
    {
        $array = explode('--', $category);
        if (isset($array[0])) {
            $category = $array[0];
        }
        $list = [
            "Nom du gisement/ champ de pétrole/ gas//Name and/or number of field, block or deposit"                                                                                                                                                                                                                                                                                                                                                                                                               => "name-of-field-block-deposit-or-site",
            "Type du titre associé au contrat (concession, bail, contrat de partage, contrat de service…)//Type of document / right (Concession, Lease, Production Sharing Agreement, Service Agreement, etc.)"                                                                                                                                                                                                                                                                                                   => "service-agreement-fee-to-developer-or-contractor",
            "Work and investment commitments"                                                                                                                                                                                                                                                                                                                                                                                                                                                                     => "work-and-investment-commitments",
            "State participation"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 => "state-participation",
            "Mining tax / royalty tax"                                                                                                                                                                                                                                                                                                                                                                                                                                                                            => "Royalties",
            "Production Share - \"Cost Oil\" features  (basis of calculation, limits on cost recovery - e.g. as % of revenue or production, capex uplift, etc.)"                                                                                                                                                                                                                                                                                                                                                  => "production-share-profit-oil-features-triggers-for-variations-in-split-irr-factor-production-etc",
            "Production Share - \"Profit Oil\" features  (triggers for variations in split - IRR, \"r\" factor, production, etc.)"                                                                                                                                                                                                                                                                                                                                                                                => "production-share-cost-oil-features-basis-of-calculation-limits-on-cost-recovery-eg-as-of-revenue-or-production-capex-uplift-etc",
            "Restrictions on transactions with affiliated parties"                                                                                                                                                                                                                                                                                                                                                                                                                                                => "restrictions-on-transactions-with-affiliated-parties",
            "Income tax: rate"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    => "income-tax-rate",
            "Stabilization clause"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                => "stabilization-clause",
            "Confidentiality"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     => "confidentiality",
            "Audit mechanisms - financial obligations"                                                                                                                                                                                                                                                                                                                                                                                                                                                            => "audit-mechanisms-financial-obligations",
            "Social, environmental and/or human rights impact assessments, as well as related management plans"                                                                                                                                                                                                                                                                                                                                                                                                   => "socialhuman-rights-impact-assessment-and-management-plan",
            "Right to take and/or use water within/near contract concession area (including fees, licenses, and permits required)"                                                                                                                                                                                                                                                                                                                                                                                => "Infrastructure - third party use",
            "Third party use of infrastructure"                                                                                                                                                                                                                                                                                                                                                                                                                                                                   => "use available water sources",
            "Construction and maintenance of infrastructure"                                                                                                                                                                                                                                                                                                                                                                                                                                                      => "Infrastructure",
            "The Domestic Supply Requirement is supplied from Government or Staatsolie entitlements and where the Domestic Supply Requirement cannot be met through these entitlements, Staatsolie may make written request to Kosmos for crude oil entitlements up to 1 year after the Date of Initial Commercial Production. Kosmos must respond to this request within 90 days. The excess entitlements are supplied on a pro rata basis (not exceeding 25%) to non-Staatosolie Suriname crude oil producers." => "Other - general",
            "Language"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            => "language",
            "Governing law in case of dispute"                                                                                                                                                                                                                                                                                                                                                                                                                                                                    => "governing-law",
            "Arbitration and dispute resolution"                                                                                                                                                                                                                                                                                                                                                                                                                                                                  => "arbitration-and-dispute-resolution",
            "Nom du gisement/ champ de petrole/ gas//Name and/or number of field, block or deposit"                                                                                                                                                                                                                                                                                                                                                                                                               => 'name-of-field-block-deposit-or-site',
            "Emplacement, longitude et latitude / terrestre vs marin (peu profond vs. profond) //Location, longitude and latitude /  Onshore vs Offshore (shallow vs. deep) "                                                                                                                                                                                                                                                                                                                                     => 'location',
            "Pays//Country"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       => 'country',
            "Nom de la société locale//Local company name"                                                                                                                                                                                                                                                                                                                                                                                                                                                        => '',
            "Type du titre associé au contrat (concession, bail, contrat de part\"Date de signature du contrat//Date of contract signature\"age, contrat de service…)//Type of document / right (Concession, Lease, Production Sharing Agreement, Service Agreement, etc.)"                                                                                                                                                                                                                                       => 'service-agreement-fee-to-developer-or-contractor',
            "Nom de la société signataire du contrat et composition des actionnaires mentionnés dans le document//Name of company executing the document and composition of the shareholders"                                                                                                                                                                                                                                                                                                                     => 'name-of-company-executing-document',
            "Année de signature du contrat//Year of contract signature"                                                                                                                                                                                                                                                                                                                                                                                                                                           => 'year-contract-signature',
            "Date de signature du contrat//Date of contract signature"                                                                                                                                                                                                                                                                                                                                                                                                                                            => 'date-contract-signature',
            "Agence de l'Etat, société nationale, ministère signataire du contrat//State agency, national company, ministry executing the document"                                                                                                                                                                                                                                                                                                                                                               => 'state-agency-national-company-or-ministry-executing-the-document',
            "Ressource(s) concernées (type de minéral, pétrole, gas, bois) ou les récoltes/denrées//Type of resources (mineral type, crude oil, gas, timber, etc.) OR specific crops planned (ex:  food crops, oil palm, etc.)"                                                                                                                                                                                                                                                                                   => 'resources',
            "Obligations de travaux, d'investissements//Work and investment commitments"                                                                                                                                                                                                                                                                                                                                                                                                                          => 'work-and-investment-commitments',
            "Durée//Term"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         => 'term',
            "Participation de l'Etat//State participation"                                                                                                                                                                                                                                                                                                                                                                                                                                                        => 'state-participation',
            "Taxe minière / redevance//Mining tax / royalty tax"                                                                                                                                                                                                                                                                                                                                                                                                                                                  => 'Royalties',
            "Partage de production - Eléments de \"Profit Oil\" (critères pour la modification du partage, - TRI, facteur \"r\", niveau de production, etc.)//Production Share - \"Profit Oil\" features  (triggers for variations in split - IRR, \"r\" factor, production, etc.)"                                                                                                                                                                                                                               => 'production-share-cost-oil-features-basis-of-calculation-limits-on-cost-recovery-eg-as-of-revenue-or-production-capex-uplift-etc',
            "Partage de production - Eléments de \"Cost Oil\" (base de calcul, limites sur le recouvrement des coûts, e.g. comme % des revenues ou de la production, crédit d'investissement, etc.)//Production Share - \"Cost Oil\" features  (basis of calculation, limits on cost recovery - e.g. as % of revenue or production, capex uplift, etc.)"                                                                                                                                                          => 'production-share-profit-oil-features-triggers-for-variations-in-split-irr-factor-production-etc',
            "Restrictions sur les transactions avec les parties liées//Restrictions on transactions with affiliated parties"                                                                                                                                                                                                                                                                                                                                                                                      => 'restrictions-on-transactions-with-affiliated-parties',
            "Impôt sur les bénéfices: taux//Income tax: rate"                                                                                                                                                                                                                                                                                                                                                                                                                                                     => 'income-tax-rate',
            "Clause de stabilisation//Stabilization clause"                                                                                                                                                                                                                                                                                                                                                                                                                                                       => 'stabilization-clause',
            "Autre - [Obligation d'approvisionnement domestique]//Other - [Domestic Supply Requirement]"                                                                                                                                                                                                                                                                                                                                                                                                          => 'Other - general',
            "Confidentialité//Confidentiality"                                                                                                                                                                                                                                                                                                                                                                                                                                                                    => 'confidentiality',
            "Modes d'audit à l'égard des obligations financières du détenteur du titre//Audit mechanisms - financial obligations"                                                                                                                                                                                                                                                                                                                                                                                 => 'audit-mechanisms-financial-obligations',
            "Droit de prendre ou d'utiliser l'eau dans la zone de concession (ou à coté) (y compris les tarifs, licences, et permis)//Right to take and/or use water within/near contract concession area (including fees, licenses, and permits required)"                                                                                                                                                                                                                                                       => 'Infrastructure - third party use',
            "Utilisation d’infrastructure par les tiers//Third party use of infrastructure"                                                                                                                                                                                                                                                                                                                                                                                                                       => 'Infrastructure - third party use',
            "Construction et entretien d'infrastructure//Construction and maintenance of infrastructure"                                                                                                                                                                                                                                                                                                                                                                                                          => 'Infrastructure',
            "Obligations liées a l'approvisionnement en biens et services locaux//Local procurement requirements"                                                                                                                                                                                                                                                                                                                                                                                                 => 'local-procurement',
            "Obligations liées à l'emploi du personnel local//Local employment requirements"                                                                                                                                                                                                                                                                                                                                                                                                                      => 'local-employment',
            "Autre - [Responsabilité sociale et Formation]//Other - [Social Responsibility and Training]"                                                                                                                                                                                                                                                                                                                                                                                                         => 'training',
            "Date d'octroi du permis d'exploitation ou concession//Date of issue of title/permit"                                                                                                                                                                                                                                                                                                                                                                                                                 => 'date-of-issue-of-titlepermit',
            "Langue//Language"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    => 'language',
            "Loi applicable en cas des différends//Governing law in case of dispute"                                                                                                                                                                                                                                                                                                                                                                                                                              => 'governing-law',
            "Arbitrage et règlement des différends//Arbitration and dispute resolution"                                                                                                                                                                                                                                                                                                                                                                                                                           => 'arbitration-and-dispute-resolution',
            "Etude d'impact social, environnemental ou des droits humains, et les plans de gestion des risques//Social, environmental and/or human rights impact assessments, as well as related management plans"                                                                                                                                                                                                                                                                                                => 'socialhuman-rights-impact-assessment-and-management-plan',
            "Droit de prendre ou d'utiliser l'eau dans la zone de concession (ou e cote) (y compris les tarifs, licences, et permis)//Right to take and/or use water within/near contract concession area (including fees, licenses, and permits required)"                                                                                                                                                                                                                                                       => 'Infrastructure - third party use',
            "Consultations communautaires requises//Requirements for community consultation "
            => "community-consultation",
            "Nom et/ou composition de la société du projet crée ou envisagée//Name and/or composition of the company created or anticipated"                                                                                                                                                                                                                                                                                                                                                                      => "name-andor-composition-of-the-company-created",
            "Autre aspects de l'impôt sur les bénéfices: (l’amortissement, déductibilité des frais financiers, report des pertes, cloisonnement)//Other income tax features (amortization, deductibility of expenses, loss carry forward, ring-fencing)"                                                                                                                                                                                                                                                          => "income-tax-other",
            "Autre - [Sites historiques ou archéologiques]//Other - [Archaeological and historical sites]"                                                                                                                                                                                                                                                                                                                                                                                                        => "sacred-cultural-or-historical-sites",
            "Année d'octroi du permis d'exploitation ou concession//Year of issue of title/permit"                                                                                                                                                                                                                                                                                                                                                                                                                => "year-of-issue-of-titlepermit",
        ];
        if (array_key_exists($category, $list)) {
            return $list[$category];
        }

        echo $category;
        throw new \Exception('Category mapping not found');
    }

    /**
     * Get Article Reference
     *
     * @param $comment
     *
     * @return array
     */
    function extractArticleReferenceAndText($comment)
    {
        $separator = '--';
        $text      = explode($separator, $comment);
        $section   = '';

        if (count($text) == 2) {
            $comment = $text[0];
            $section = isset($text[1]) ? trim($text[1]) : '';
        }

        if (count($text) > 2) {
            $section = trim(end($text));
            unset($text[count($text) - 1]);
            $comment = join($separator, $text);
        }

        return ['text' => $comment, 'article_reference' => $section];
    }
}
