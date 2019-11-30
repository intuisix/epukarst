<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Basin;
use App\Entity\System;
use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\Station;
use App\Entity\UserRole;
use App\Entity\Parameter;
use App\Entity\Instrument;
use App\Entity\Calibration;
use App\Entity\StationKind;
use App\Entity\Measurability;
use Faker\Factory as FakerFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $faker;
    private $passwordEncoder;
    private $manager;
    private $users;
    private $parameters;
    private $instruments;
    private $measurabilities;
    private $stationKinds;

    /**
     * Contruit le générateur de données.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
        $this->faker = FakerFactory::create("fr-BE");
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Génère les données.
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadAdminUsers($manager);
        $this->loadOtherUsers($manager);
        $this->loadPosts($manager);
        $this->loadParameters($manager);
        $this->loadInstruments($manager);
        $this->loadStationKinds($manager);
        $this->loadSystems($manager);
        $manager->flush();
    }

    /**
     * Retourne une description aléatoire, contenant un nombre aléatoire
     * de paragraphes formatés en HTML.
     *
     * @param integer $minParagraphCount
     * @param integer $maxParagraphCount
     * @return void
     */
    private function getFakeDescription(int $minParagraphCount, int $maxParagraphCount) {
        return '<p>' . join('<p></p>', $this->faker->paragraphs(mt_rand($minParagraphCount, $maxParagraphCount))) . '</p>';
    }

    /**
     * Retourne un commentaire aléatoire, contenant un nombre aléatoire de
     * paragraphes séparés un saut de ligne.
     *
     * @param integer $minParagraphCount
     * @param integer $maxParagraphCount
     * @return void
     */
    private function getFakeNote(int $minParagraphCount, int $maxParagraphCount) {
        return join(PHP_EOL, $this->faker->paragraphs(mt_rand($minParagraphCount, $maxParagraphCount)));
    }

    /**
     * Retourne un code aléatoire constitué de quelques lettres et chiffres.
     */
    private function getFakeCode() {
        return $this->faker->regexify('[A-Z0-9]{4}');
    }

    /**
     * Attribue un rôle à un utilisateur.
     */
    public function addUserRole(User $user, Role $role) {
        $userRole = new UserRole();
        $userRole
            ->setLinkedUser($user)
            ->setLinkedRole($role);
        $this->manager->persist($userRole);
    }

    /**
     * Génère des utilisateurs ayant le privilège d'administration.
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadAdminUsers(ObjectManager $manager) {
        /* Créer le rôle d'aministration */
        $role = new Role();
        $role->setRole('ROLE_ADMIN');
        $manager->persist($role);

        /* Créer le compte d'administration */
        $user = new User();
        $user
            ->setFirstName('Administrateur')
            ->setLastName('')
            ->setEmail('admin@epukarst.be')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'password'));
        $manager->persist($user);
        $this->users[] = $user;
        $this->addUserRole($user, $role);

        /* Créer un compte de contact, également administrateur */
        $user = new User();
        $user
            ->setFirstName('Contact')
            ->setLastName('CWEPSS')
            ->setEmail('contact@cwepss.org')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'password'));
        $manager->persist($user);
        $this->users[] = $user;
        $this->addUserRole($user, $role);
    }

    /**
     * Génère des utilisateurs n'ayant pas le privilège d'administration.
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadOtherUsers(ObjectManager $manager) {
        for ($i = 0; $i < 10; $i++) {
            $gender = $this->faker->randomElement(['male', 'female']);
            $picture = 'https://randomuser.me/api/portraits/' .
                ($gender == 'male' ? 'men/' : 'women/') .
                $this->faker->numberBetween(1, 99) . '.jpg';

            $user = new User();
            $user
                ->setFirstName($this->faker->firstName($gender))
                ->setLastName($this->faker->lastName())
                ->setEmail($this->faker->email())
                ->setPassword($this->passwordEncoder->encodePassword($user, 'password'))
                ->setPicture($picture);
            $manager->persist($user);
            $this->users[] = $user;
        }
    }

    /**
     * Génère des articles publiés sur le site.
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadPosts(ObjectManager $manager)
    {
        /* Menu "Projet" */

        $post = new Post();
        $post
            ->setTitle("Projet")
            ->setSummary("Découvrez les finalités et les modalités du projet")
            ->setContent("<p>Epu-Karst a pour but d'étudier la vulnérabilité des ressources en eaux souterraines dûe à la pollution par les nitrates&nbsp;:</p>")
            ->setHome(true)
            ->setTopMenu(true);
        $manager->persist($post);
        $projectMenu = $post;

        $post = new Post();
        $post
            ->setTitle("Trois constats et un projet")
            ->setParent($projectMenu)
            ->setContent(
"<ol>
<li>La majorité de l’eau potable en RW est prélevée dans des aquifères carbonatés.</li>
<li>Ces aquifères ont une bonne capacité de stockage (fissures interconnectées) mais
elle s’accompagne d’une haute vulnérabilité (diffusion rapide, vitesses de transfert...)</li>
<li>Fluctuation de la [NO3-] entre pertes et résurgences dans un système karstique:
quelles processus peuvent expliquer cette variation?</li>
</ol>
<p>
Le projet Epu-Karst vise à mieux caractériser ces processus à l’œuvre dans la &laquo;boîte noire&raquo; qu’est le milieu souterrain calcaire, en analysant les eaux à l’entrée, à la sortie et DANS le karst directement.
</p>");
        $manager->persist($post);

        $post = new Post();
        $post
            ->setTitle("Objectifs du projet")
            ->setParent($projectMenu)
            ->setContent(
"<p>Déterminer quels sont les variables déterminantes dans l’évolution de la concentration en Nitrates dans les eaux traversant un aquifère karstique&nbsp;:</p>
<ul>
<li>des investigations dans 5 systèmes karstiques en mobilisant un réseau de partenaires locaux</li>
<li>des prélèvements en surface et sous terre (grottes, puits, carrières souterraines...), dans différents types de milieux (& temps de transfert) :
<ul>
<li>Eaux de surface et point de pertes</li>
<li>zones non saturées (infiltration, gours et percolation),</li>
<li>rivières souterraines (circulation rapide et massive),</li>
<li>regards sur la nappe.</li>
</ul>
<li>application d’une analyse multivariée pour comparer la qualité des eaux entre ces systèmes (paramètres physico-chimiques, saisonnalité, débits...)</li>
<li>aboutir à des mesures spécifiques + recommandations favorables à la protection des eaux</li>
</ul>");
        $manager->persist($post);

        $post = new Post();
        $post
            ->setTitle("Méthodologies et analyses proposées")
            ->setParent($projectMenu)
            ->setContent(
"<p>Contrôler le nitrate entre entrée/sortie des systèmes karstiques&nbsp;:
<ul>
<li>Caractériser la fluctuation des concentrations en NO3 lors du trajet souterrain de l’eau.</li>
<li>Comparer les variables (physiques, chimiques et biologiques) pouvant influencer le cycle
de l’azote, avec les mesures in situ</li>
<li>Différencier dans ces fluctuations amont/aval et entre systèmes les effets liés à&nbsp;:
<ul>
<li>La dilution,</li>
<li>La fixation temporaire de l’azote dans le milieu souterrain,</li>
<li>La dénitrification pouvant se produire en milieu (an)aérobie (rôle des bacteries).</li>
</ul>
<li>Vérifier si le temps de séjour de l’eau sous terre constitue bien la variable déterminante dans la modification de concentration en Nitrate et comparer ce processus avec ceux qui se déroulent dans les sols</li>
<li>Confronter ces variations avec la saisonnalité, la fluctuation d’un bassin à l’autre...</li>
</p>");
        $manager->persist($post);

        $post = new Post();
        $post
            ->setTitle("Paramètres analysés")
            ->setParent($projectMenu)
            ->setContent(
"<p>Différents paramètres physico-chimiques sont analysés sur l'ensemble des sites de prélèvement&nbsp;:
<ul>
<li>Température,</li>
<li>Conductivité,</li>
<li>pH,</li>
<li>Oxygène dissous,</li>
<li>Potentiel rédox,</li>
<li><b>Dosage du nitrate</b>.</li>
</ul>
</p>
<p>Mesures de débit pour tenter de différencier les processus de dilution, de fixation et de dénitrification. Les mesures sont toutes réalisées in-situ.</p>");
        $manager->persist($post);

        $post = new Post();
        $post
            ->setTitle("Schéma d'échantillonnage")
            ->setParent($projectMenu)
            ->setContent(
"<div class=\"row\">
<div class=\"col\"><img src=\"/images/home/schema-d-echantillonnage.png\" width=\"100%\"></div>
<div class=\"col-3\">
<ul>
<li>C = chantoir (perte),</li>
<li>S1 = siphon émissif,</li>
<li>S2 = siphon absorbant,</li>
<li>G = gour,</li>
<li>Pe = percolation,</li>
<li>N = nappe,</li>
<li>E = exsurgence,</li>
<li>Pi = piézomètre.</li>
</ul>
</div>
</div>");
        $manager->persist($post);

        $post = new Post();
        $post
            ->setTitle("Choix des systèmes karstiques étudiés")
            ->setParent($projectMenu)
            ->setContent(
"<p>Critères de sélection appliqués pour sélectionner les ensemble karstiques Epukarst;nbsp;:</p>
<ul>
<li>Possibilités d’échantillonnage sur et sous terre,</li>
<li>Diversité des pressions anthropiques et agricoles,</li>
<li>Diversité géologique et karstique,</li>
<li>Bonne connaissance hydrogéologique préalable,</li>
<li>Périmètres vulnérables et non vulnérables du point de vue du nitrate,</li>
<li>Relais de terrain pour les actions de sensibilisation,</li>
<li>Partenaires de terrain mobilisables.</li>
</ul>");
        $manager->persist($post);

        /* Menu "Parternaires" */

        $post = new Post();
        $post
            ->setTitle("Partenaires")
            ->setSummary("Découvrez nos partenaires et nos intervenants")
            ->setContent("<p>Plusieurs partenaires contribuent à la réalisation de notre projet:<p>")
            ->setHome(true)
            ->setTopMenu(true);
        $manager->persist($post);
        $partnersMenu = $post;

        $post = new Post();
        $post
            ->setTitle("CWEPSS")
            ->setParent($partnersMenu)
            ->setContent(
"<p>La Commission Wallonne d’Etude et de protection des Sites Souterrains est une association de protection de l’environnement qui se consacre à l’étude et à la protection des sites karstiques et des eaux souterraines de Wallonie.</p>
<p>Ses rôles au sein du projet&nbsp;:</p>
<ul>
<li>Coordination générale du projet</li>
<li>Sélection et description des 5 systèmes karstiques</li>
<li>Supervision de l’interface d’encodage + cartographie</li>
<li>Coordination des équipes de terrain</li>
<li>Volet « sensibilisation » ; diffusion du projet + publication des résultats</li>
<li>Rapport final et synthèse des recommandations</li>
<li>Mise en place d’un suivi à long terme avec les partenaires</li>
<li>Gestion et aspects comptables / administratifs</li>
</ul>");
        $manager->persist($post);

        $post = new Post();
        $post
            ->setTitle("ISSeP")
            ->setParent($partnersMenu)
            ->setContent(
    "<p>L'Institut Scientifique de Service Public est une institution de surveillance de l’environnement et laboratoire de référence pour la Wallonie.</p>
<p>Ses rôles au sein du projet:</p>
<ul>
<li>conception et tests d’un kit d’analyse de terrain&nbsp;</li>
<li>coordination des analyses d’eau ;</li>
<li>conseils méthodologiques ;</li>
<li>participation à l’interprétation des mesures ;</li>
<li>valorisation du réseau de suivi des eaux souterraines.</li>
</ul>");
        $manager->persist($post);
                     
        $post = new Post();
        $post
            ->setTitle("Sanifox")
            ->setParent($partnersMenu)
            ->setContent(
    "<p>Sanifox est une PME wallonne spécialisée dans l’hydrogéologie appliquée et la dépollution des sols et des eaux souterraines par des méthodes in-situ.</p>
<p>Ses rôles au sein du projet&nbsp;:</p>
<ul>
<li>Description des processus épuratoires associés à l’infiltration d’eaux \"chargées\",</li>
<li>évaluation des activités à risque dans le bassin d’alimentation,</li>
<li>formation des intervenants aux méthodes d’échantillonnage,</li>
<li>recommandations d’améliorations,</li>
<li>extrapolation à d’autres bassins.</li>
</ul>");
        $manager->persist($post);
                                       
        $post = new Post();
        $post
            ->setTitle("Spéléologues et naturalistes")
            ->setParent($partnersMenu)
            ->setContent(
"<p>Le projet implique des associations et des volontaires ayant une sensibilité particulière pour la recherche et la protection du milieu souterrain.</p>
<p>Leurs rôles au sein du projet&nbsp;:</p>
<ul>
<li>participation à l’échantillonnage et à l’encodage des résultats,</li>
<li>récolte et mise en valeur de données sur le karst et son fonctionnement,</li>
<li>constitution d’un réseau de « sentinelles » karstiques,</li>
<li>poursuite des mesures après la fin du projet.</li>
</ul>");
        $manager->persist($post);

        $post = new Post();
        $post
            ->setTitle("Contrats de rivière")
            ->setParent($partnersMenu)
            ->setContent(
    "<p>Les Contrats de Rivière de la Haute Meuse, de l'Ourthe et de la Lesse, ainsi que les communes concernées ont une mission de sensibilisation et de concertation entre les usagers de l’eau d’un bassin, collaborant pour une meilleure qualité des cours d’eau.</p>
<p>Leurs rôles au sein du projet&nbsp;:</p>
<ul>
<li>&laquo;plateforme&raquo; de diffusion sur l’avancement du projet (Journées Wallonnes de l'Eau),</li>
<li>rencontre entre les usagers, sensibilisation du grand public,</li>
<li>contact avec les propriétaires & diffusion des résultats.</li>
</ul>");
        $manager->persist($post);

        /* Menu "Actualités" */

        $post = new Post();
        $post
            ->setTitle("Actualités")
            ->setSummary("Découvrez les actualités de notre projet")
            ->setContent("<p>Voici les dernières nouvelles du projet&nbsp;:</p>")
            ->setHome(true)
            ->setTopMenu(true);
        $manager->persist($post);
        $newsMenu = $post;
    }

    /**
     * Génère des paramètres mesurables durant l'étude.
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadParameters(ObjectManager $manager) {
        $parameter = new Parameter();
        $parameter
            ->setName("NO3")
            ->setTitle("Teneur en nitrates")
            ->setUnit("mg/l")
            ->setPhysicalMinimum(0)
            ->setPhysicalMaximum(100)
            ->setNormativeMinimum(5)
            ->setNormativeMaximum(15)
            ->setFavorite(true)
            ->setIntroduction("Ce paramètre exprime la concentration de nitrates dans l'eau")
            ->setDescription('<p>La source majeure des nitrates dans l\'eau souterraine provient de la fertilisation des champs par des engrais azotés, du rejet d\'eaux usées, et des rejets de l\'industrie.</p><p><a href="https://www.aquawal.be/fr/nitrate-et-eau-de-distribution.html?IDC=607">Plus d\'informations.</a></p>');
        $manager->persist($parameter);
        $this->parameters["NO3"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("NO2")
            ->setTitle("Teneur en nitrites")
            ->setUnit("mg/l")
            ->setPhysicalMinimum(0)
            ->setPhysicalMaximum(100)
            ->setNormativeMinimum(0)
            ->setNormativeMaximum(6)
            ->setFavorite(true)
            ->setIntroduction("Ce paramètre exprime la concentration de nitrites dans l'eau.")
            ->setDescription("<p>Description plus complète...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["NO2"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("pH")
            ->setTitle("Potentiel hydrogène")
            ->setUnit(null)
            ->setNormativeMinimum(5)
            ->setNormativeMaximum(8)
            ->setPhysicalMinimum(-1.1)
            ->setPhysicalMaximum(15)
            ->setFavorite(false)
            ->setIntroduction("Ce paramètre représente l'acidité de l'eau.")
            ->setDescription("<p>Résultat de la composition du sol ou de ce qu'il reçoit...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["pH"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("CaCO3")
            ->setTitle("Dureté carbonatée")
            ->setUnit("mg/l")
            ->setNormativeMinimum(0)
            ->setNormativeMaximum(100)
            ->setPhysicalMinimum(0)
            ->setPhysicalMaximum(100)
            ->setFavorite(true)
            ->setIntroduction("Ce paramètre est un indicateur de la minéralisation de l'eau")
            ->setDescription("<p>Aussi appelé <i>titre hygrométrique</i>, description plus complète...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["CaCO3"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("t°")
            ->setTitle("Température")
            ->setUnit("°C")
            ->setNormativeMinimum(null)
            ->setNormativeMaximum(null)
            ->setPhysicalMinimum(-273)
            ->setPhysicalMaximum(null)
            ->setFavorite(false)
            ->setIntroduction("Ce paramètre exprime la température de l'eau")
            ->setDescription("<p>Description plus complète...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["t°"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("O2")
            ->setTitle("Oxygène dissous")
            ->setUnit("mg/l")
            ->setNormativeMinimum(0)
            ->setNormativeMaximum(100)
            ->setPhysicalMinimum(0)
            ->setPhysicalMaximum(100)
            ->setFavorite(false)
            ->setIntroduction("Ce paramètre exprime la concentration d'oxyène dans l'eau.")
            ->setDescription("<p>Description plus complète...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["O2"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("Redox")
            ->setTitle("Potentiel rédox")
            ->setUnit(null)
            ->setNormativeMinimum(0)
            ->setNormativeMaximum(100)
            ->setPhysicalMinimum(0)
            ->setPhysicalMaximum(100)
            ->setFavorite(false)
            ->setIntroduction("Ce paramètre exprime le potentiel d'oxydoréduction de l'eau.")
            ->setDescription("<p>Description plus complète...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["Redox"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("Débit")
            ->setTitle("Débit")
            ->setUnit("l/s")
            ->setNormativeMinimum(0)
            ->setNormativeMaximum(1000)
            ->setPhysicalMinimum(0)
            ->setPhysicalMaximum(1000)
            ->setFavorite(false)
            ->setIntroduction("Ce paramètre exprime la quantité d'eau en circulation.")
            ->setDescription("<p>Description plus détaillée...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["Débit"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("sigma")
            ->setTitle("Conductivité électrique")
            ->setUnit("mS/m")
            ->setNormativeMinimum(0)
            ->setNormativeMaximum(1000)
            ->setPhysicalMinimum(0)
            ->setPhysicalMaximum(1000)
            ->setFavorite(false)
            ->setIntroduction("Ce paramètre caractérise l'aptitude de l'eau à laisser passer des charges électriques.")
            ->setDescription("<p>Description plus détaillée...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["sigma"] = $parameter;
    }

    /**
     * Génère des instruments de mesure servant à l'étude, et définit leur
     * capacité respective à mesurer les paramètres.
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadInstruments(ObjectManager $manager) {
        /* Table définissant les instruments que l'on souhaite générer ainsi
        que les paramètres que sont capables de mesurer */
        $template = [
            "Bandelette NO2" => [ "NO2" ],
            "Bandelette NO3" => [ "NO3" ],
            "Bandelette pH" => [ "pH" ],
            "Bandelette TH" => [ "CaCO3" ],
            "Gobelet doseur" => [ "Débit" ],
            "Echelle graduée" => [ "Débit" ],
            "Thermomètre" => [ "t°" ],
            "Débimètre" => [ "Débit" ],
            "Conductimètre" => [ "sigma" ],
            "Spectromètre n° 1" => [ "NO2", "NO3" ],
            "Spectromètre n° 2" => [ "NO2", "NO3", "CaCO3" ],
            "Spectromètre n° 3" => [ "CaCO3", "O2" ],
        ];

        foreach ($template as $name => $parameters) {
            /* Générer l'instrument */
            $instrument = new Instrument();
            $instrument
                ->setCode($this->getFakeCode())
                ->setName($name)
                ->setModel($this->faker->lastName() . " " . $this->faker->regexify('[A-Z0-9\-]{2,13}'))
                ->setSerialNumber($this->faker->regexify('[A-Z0-9\-]{5,15}'))
                ->setDescription($this->getFakeDescription(1, 3));
            $manager->persist($instrument);
            $this->instruments[] = $instrument;

            /* Générer les paramètres mesurables par l'instrument */
            foreach ($parameters as $name) {
                $parameter = $this->parameters[$name];
                $tolerance = ($parameter->getPhysicalMaximum() - $parameter->getPhysicalMinimum()) * 0.005;
                $measurability = new Measurability();
                $measurability
                    ->setParameter($parameter)
                    ->setInstrument($instrument)
                    ->setTolerance($tolerance)
                    ->setNotes($this->getFakeNote(0, 1));
                $manager->persist($measurability);
                $this->measurabilities[] = $measurability;
            }

            /* Générer des étalonnages */
            for ($i = 0; $i < mt_rand(0, 5); $i++) {
                $doneDate = $this->faker->dateTimeBetween('-2 years');
                $dueDate = (clone $doneDate)->modify('+1 year');
                $calibration = new Calibration();
                $calibration
                    ->setInstrument($instrument)
                    ->setDoneDate($doneDate)
                    ->setDueDate($dueDate)
                    ->setOperatorName($this->faker->lastName())
                    ->setNotes($this->getFakeNote(0, 1));
                $manager->persist($calibration);
            }
        }
    }

    /**
     * Génère des genres de stations.
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadStationKinds(ObjectManager $manager)
    {
        $names = [
            "Perte",
            "Chantoir",
            "Gour",
            "Stalactite",
            "Rivière",
            "Regard",
            "Siphon",
            "Résurgence",
        ];

        $this->stationKinds = [];
        foreach ($names as $name) {
            $stationKind = new StationKind();
            $stationKind->setName($name);
            $manager->persist($stationKind);
            $this->stationKinds[] = $stationKind;
        }
    }

    /**
     * Génère des mesures relatives à un relevé.
     *
     * @param ObjectManager $manager
     * @param Reading $reading
     * @return void
     */
    public function loadMeasures(ObjectManager $manager, Reading $reading) {
        for ($j = 0; $j < mt_rand(5, count($this->parameters)); $j++) {
            $measurability = $this->measurabilities[mt_rand(0, count($this->measurabilities) - 1)];
            $parameter = $measurability->getParameter();
            $value = round($parameter->getPhysicalMinimum() + (mt_rand(0, mt_getrandmax() - 1) * ($parameter->getPhysicalMaximum() - $parameter->getPhysicalMinimum())) / mt_getrandmax(), 1);
            $measure = new Measure();
            $measure
                ->setReading($reading)
                ->setMeasurability($measurability)
                ->setValue($value)
                ->setStable(mt_rand(0, 10) > 2)    /* 2 unstable out of 10 */
                ->setValid(mt_rand(0, 10) > 1)     /* 1 invalid out of 10 */
                ->setTolerance(null)
                ->setFieldDateTime($reading->getFieldDateTime())
                ->setEncodingDateTime($reading->getEncodingDateTime())
                ->setEncodingAuthor($reading->getEncodingAuthor());
            $manager->persist($measure);
        }
    }

    /**
     * Génère des relevés relatifs à une station.
     *
     * @param ObjectManager $manager
     * @param Station $station
     * @return void
     */
    private function loadReadings(ObjectManager $manager, Station $station) {
        for ($i = 0; $i < mt_rand(0, 15); $i++) {
            $fieldDateTime = $this->faker->dateTimeBetween('-9 months');
            $sleepingDays = mt_rand(0, 20);
            $reading = new Reading();
            $reading
                ->setStation($station)
                ->setCode($this->getFakeCode())
                ->setFieldDateTime($fieldDateTime)
                ->setEncodingAuthor($this->users[mt_rand(0, count($this->users) - 1)])
                ->setEncodingDateTime((clone $fieldDateTime)->modify("+$sleepingDays day"))
                ->setEncodingNotes($this->getFakeNote(1, 3));
            $manager->persist($reading);
            $this->loadMeasures($manager, $reading);
        }
    }

    /**
     * Génère des stations relatives à un bassin.
     *
     * @param ObjectManager $manager
     * @param Basin $basin
     * @return void
     */
    private function loadStations(ObjectManager $manager, Basin $basin) {
        for ($i = 0; $i < mt_rand(1, 10); $i++) {
            $kind = $this->stationKinds[mt_rand(0, count($this->stationKinds) - 1)];
            $station = new Station();
            $station
                ->setBasin($basin)
                ->setCode($this->getFakeCode())
                ->setName($kind->getName() . " " . $this->faker->firstName)
                ->setKind($kind)
                ->setDescription($this->getFakeDescription(1, 3));
            $this->loadReadings($manager, $station);
            $manager->persist($station);
        }
    }

    /**
     * Génère des bassins relatifs à un système.
     *
     * @param ObjectManager $manager
     * @param System $system
     * @return void
     */
    private function loadBasins(ObjectManager $manager, System $system) {
        for ($i = 0; $i < mt_rand(1, 5); $i++) {
            $basin = new Basin();
            $basin
                ->setSystem($system)
                ->setCode($this->getFakeCode())
                ->setName("Rivière " . $this->faker->lastName())
                ->setDescription($this->getFakeDescription(1, 5));
            $this->loadStations($manager, $basin);
            $manager->persist($basin);
        }
    }

    /**
     * Génère des systèmes.
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadSystems(ObjectManager $manager) {
        $system = new System();
        $system
            ->setCode("LESV")
            ->setName("Vallon de Lesve (Vilaine Source)")
            ->setIntroduction("Système souterrain du vallon de Lesve")
            ->setBasin("Burnot")
            ->setCommune("Profondeville")
            ->setWaterMass("M23")
            ->setDescription("<p>Ce vallon inclut l'abîme de Lesve et la résurgence à la Vilaine Source.</p>" . $this->getFakeDescription(1, 5));
        $this->loadBasins($manager, $system);
        $manager->persist($system);

        $system = new System();
        $system
            ->setCode("HOTT")
            ->setName("Système de Hotton")
            ->setBasin("Ourthe")
            ->setCommune("Hotton")
            ->setWaterMass("M23")
            ->setIntroduction("Système souterrain de Hotton")
            ->setDescription("<p>L'élément principal de ce système est la grotte des Mille-et-Une Nuits.</p>" . $this->getFakeDescription(1, 5));
        $this->loadBasins($manager, $system);
        $manager->persist($system);

        $system = new System();
        $system
            ->setCode("SPRI")
            ->setName("Noû Bleû (Synclinal de Sprimont)")
            ->setBasin("Ourthe")
            ->setCommune("Sprimont")
            ->setWaterMass("M21")
            ->setIntroduction("Système souterrain du synclinal de Sprimont")
            ->setDescription("<p>Description du système dont les éléments principaux sont la grotte du Noû Bleû et le lac Bleû.</p>" . $this->getFakeDescription(1, 5));
        $this->loadBasins($manager, $system);
        $manager->persist($system);

        $system = new System();
        $system
            ->setCode("CHANT")
            ->setName("Vallon des Chantoirs (Remouchamps)")
            ->setBasin("Amblève")
            ->setCommune("Aywaille")
            ->setWaterMass("M23")
            ->setIntroduction("Système du vallon des Chantoirs")
            ->setDescription("<p>Description du système.</p>" . $this->getFakeDescription(1, 5));
        $this->loadBasins($manager, $system);
        $manager->persist($system);
    
        $system = new System();
        $system
            ->setCode("FURF")
            ->setName("Lesse souterraine (Furfooz)")
            ->setBasin("Lesse")
            ->setCommune("Dinant")
            ->setWaterMass("M21")
            ->setIntroduction("Système souterrain de la basse-Lesse")
            ->setDescription("<p>Description du système dont un élément est la Galerie aux Sources.</p>" . $this->getFakeDescription(1, 5));
        $this->loadBasins($manager, $system);
        $manager->persist($system);
    }
}
