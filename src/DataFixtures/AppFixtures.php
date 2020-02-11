<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Basin;
use App\Entity\System;
use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\Station;
use App\Entity\UserRole;
use App\Entity\Parameter;
use App\Entity\Instrument;
use App\Entity\SystemRole;
use App\Entity\Calibration;
use App\Entity\StationKind;
use App\Entity\Measurability;
use App\Entity\SystemParameter;
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
     * Retourne un code aléatoire constitué de quatre lettres et chiffres.
     */
    private function getFakeCode() {
        return $this->faker->regexify('[A-Z0-9]{4}');
    }

    /**
     * Génère des utilisateurs ayant le privilège d'administration.
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadAdminUsers(ObjectManager $manager) {
        /* Créer le compte d'administration */
        $user = new User();
        $user
            ->setFirstName($_ENV['MAILER_NAME'])
            ->setLastName('')
            ->setEmail($_ENV['MAILER_EMAIL'])
            ->setPassword($this->passwordEncoder->encodePassword($user, 'password'))
            ->setMainRole('ROLE_SUPER_ADMIN');
        $manager->persist($user);
        $this->users[] = $user;

        /* */
        $systemRole = new SystemRole();
        $systemRole->setUserAccount($user);
        $systemRole->setRole('SYSTEM_MANAGER');
        $manager->persist($systemRole);
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
                ->setPicture($picture)
                ->setMainRole('ROLE_USER');
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
        /* Article de tête de la page d'accueil */
        $post = new Post();
        $post
            ->setTitle("Accueil")
            ->setSummary("")
            ->setContent($this->getFakeDescription(1, 5))
            ->setHome(true)
            ->setTopMenu(false)
            ->setPublishFromDate(new \DateTime('now'));
        $manager->persist($post);
        $projectMenu = $post;

        /* Menu "Projet" */
        $post = new Post();
        $post
            ->setTitle("Projet")
            ->setSummary("Découvrez les finalités et les modalités du projet")
            ->setContent($this->getFakeDescription(1, 5))
            ->setHome(true)
            ->setTopMenu(true)
            ->setPublishFromDate(new \DateTime('now'));
        $manager->persist($post);
        $projectMenu = $post;

        /* Articles du menu "Projet" */
        for ($i = 1; $i < 5; $i++) {
            $post = new Post();
            $post
                ->setTitle($this->faker->text(mt_rand(5, 50)))
                ->setParent($projectMenu)
                ->setContent($this->getFakeDescription(1, 10))
                ->setPublishFromDate(new \DateTime('now'));
            $manager->persist($post);
        }

        /* Menu "Parternaires" */
        $post = new Post();
        $post
            ->setTitle("Partenaires")
            ->setSummary("Découvrez nos partenaires et nos intervenants")
            ->setContent($this->getFakeDescription(1, 5))
            ->setHome(true)
            ->setTopMenu(true)
            ->setPublishFromDate(new \DateTime('now'));
        $manager->persist($post);
        $partnersMenu = $post;

        /* Articles du menu "Partenaires" */
        for ($i = 1; $i < 7; $i++) {
            $post = new Post();
            $post
                ->setTitle($this->faker->text(mt_rand(5, 50)))
                ->setParent($partnersMenu)
                ->setContent($this->getFakeDescription(1, 10))
                ->setPublishFromDate(new \DateTime('now'));
            $manager->persist($post);
        }

        /* Menu "Actualités" */
        $post = new Post();
        $post
            ->setTitle("Actualités")
            ->setSummary("Découvrez les actualités de notre projet")
            ->setContent($this->getFakeDescription(1, 10))
            ->setHome(true)
            ->setTopMenu(true)
            ->setPublishFromDate(null);
        $manager->persist($post);
        $newsMenu = $post;

        /* Laisser le menu "Actualités" vide et non publié */
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
            ->setPhysicalMinimum(-273)
            ->setNormativeMinimum(0)
            ->setNormativeMaximum(30)
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
                ->setName($this->faker->lastName())
                ->setDescription($this->getFakeDescription(5, 100));
            $this->loadStations($manager, $basin);
            $manager->persist($basin);
        }
    }

    /**
     * Génère des paramètres pour les systèmes.
     *
     * @param ObjectManager $manager
     * @param System $system
     * @return void
     */
    private function loadSystemParameters(ObjectManager $manager, System $system)
    {
        foreach ($this->measurabilities as $measurability) {
            $systemParameter = new SystemParameter();
            $systemParameter
                ->setInstrumentParameter($measurability)
                ->setNotes($this->getFakeNote(0, 50));
            $system->addParameter($systemParameter);
            $manager->persist($systemParameter);
        }
    }

    /**
     * Génère des systèmes.
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function loadSystems(ObjectManager $manager) {
        for ($i = 0; $i < 10; $i++) {
            $system = new System();
            $system
                ->setCode($this->getFakeCode())
                ->setName($this->faker->lastName())
                ->setIntroduction($this->faker->sentence(mt_rand(5, 20)))
                ->setBasin($this->faker->lastName())
                ->setCommune($this->faker->city())
                ->setDescription($this->getFakeDescription(1, 5));
            $this->loadBasins($manager, $system);
            $this->loadSystemParameters($manager, $system);
            $manager->persist($system);
        }
    }
}
