<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\Basin;
use App\Entity\System;
use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\Station;
use App\Entity\Parameter;
use App\Entity\Instrument;
use App\Entity\Measurability;
use Faker\Factory as FakerFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $faker;
    private $passwordEncoder;
    private $users;
    private $parameters;
    private $instruments;
    private $measurabilities;

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
        $this->loadAdminUsers($manager);
        $this->loadOtherUsers($manager);
        $this->loadParameters($manager);
        $this->loadInstruments($manager);
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

        /* Créer un deuxième compte d'administration */
        $user = new User();
        $user
            ->setFirstName('Georges')
            ->setLastName('Michel')
            ->setEmail('contact@cwepss.org')
            ->setPassword($this->passwordEncoder->encodePassword($user, 'password'));
        $manager->persist($user);
        $this->users[] = $user;
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
            ->setFavorite(false)
            ->setIntroduction("Ce paramètre exprime la concentration de nitrites dans l'eau.")
            ->setDescription("<p>Description plus complète...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["NO2"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("pH")
            ->setTitle("Potentiel hydrogène")
            ->setUnit("")
            ->setNormativeMinimum(5)
            ->setNormativeMaximum(8)
            ->setPhysicalMinimum(-1.1)
            ->setPhysicalMaximum(15)
            ->setFavorite(true)
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
            ->setFavorite(false)
            ->setIntroduction("Ce paramètre est un indicateur de la minéralisation de l'eau")
            ->setDescription("<p>Aussi appelé <i>titre hygrométrique</i>, description plus complète...</p>" . $this->getFakeDescription(1, 3));
        $manager->persist($parameter);
        $this->parameters["CaCO3"] = $parameter;

        $parameter = new Parameter();
        $parameter
            ->setName("t°")
            ->setTitle("Température")
            ->setUnit("°C")
            ->setNormativeMinimum(-50)
            ->setNormativeMaximum(50)
            ->setPhysicalMinimum(-100)
            ->setPhysicalMaximum(100)
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
            ->setUnit("")
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
            "Spectromètre n° 3" => [ "NO2", "NO3", "CaCO3", "O2" ],
        ];

        foreach ($template as $name => $parameters) {
            $instrument = new Instrument();
            $instrument
                ->setCode($this->getFakeCode())
                ->setName($name)
                ->setModel($this->faker->lastName() . " " . $this->faker->regexify('[A-Z0-9\-]{2,13}'))
                ->setSerialNumber($this->faker->regexify('[A-Z0-9\-]{5,15}'))
                ->setDescription($this->getFakeDescription(1, 3));
            $manager->persist($instrument);
            $this->instruments[] = $instrument;

            foreach ($parameters as $name) {
                $parameter = $this->parameters[$name];
                $tolerance = ($parameter->getPhysicalMaximum() - $parameter->getPhysicalMinimum()) * 0.005;
                $measurability = new Measurability();
                $measurability
                    ->setParameter($parameter)
                    ->setInstrument($instrument)
                    ->setTolerance($tolerance);
                $manager->persist($measurability);
                $this->measurabilities[] = $measurability;
            }
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
        for ($j = 0; $j < mt_rand(4, count($this->parameters)); $j++) {
            $measurability = $this->measurabilities[mt_rand(0, count($this->measurabilities) - 1)];
            $parameter = $measurability->getParameter();
            $value = $parameter->getPhysicalMinimum() + (mt_rand(0, mt_getrandmax() - 1) * ($parameter->getPhysicalMaximum() - $parameter->getPhysicalMinimum())) / mt_getrandmax();
            $measure = new Measure();
            $measure
                ->setReading($reading)
                ->setMeasurability($measurability)
                ->setValue($value)
                ->setStabilized(true)
                ->setTolerance($measurability->getTolerance())
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
        $namings = [
            "Perte",
            "Chantoir",
            "Gour",
            "Stalactite",
            "Rivière",
            "Regard",
            "Siphon",
            "Résurgence",
        ];

        for ($i = 0; $i < mt_rand(1, 10); $i++) {
            $kind = $namings[mt_rand(0, count($namings) - 1)];
            $station = new Station();
            $station
                ->setBasin($basin)
                ->setCode($this->getFakeCode())
                ->setName($kind . " " . $this->faker->firstName)
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
            ->setNumber("533-010")
            ->setWaterMass("M23")
            ->setDescription("<p>Ce vallon inclut l'abîme de Lesve et la résurgence à la Vilaine Source.</p>" . $this->getFakeDescription(1, 5))
            ->setPicture("lesve.jpg");
        $this->loadBasins($manager, $system);
        $manager->persist($system);

        $system = new System();
        $system
            ->setCode("HOTT")
            ->setName("Système de Hotton")
            ->setBasin("Ourthe")
            ->setCommune("Hotton")
            ->setNumber("555-005")
            ->setWaterMass("M23")
            ->setIntroduction("Système souterrain de Hotton")
            ->setDescription("<p>L'élément principal de ce système est la grotte des Mille-et-Une Nuits.</p>" . $this->getFakeDescription(1, 5))
            ->setPicture("hotton.jpg");
        $this->loadBasins($manager, $system);
        $manager->persist($system);

        $system = new System();
        $system
            ->setCode("SPRI")
            ->setName("Noû Bleû (Synclinal de Sprimont)")
            ->setBasin("Ourthe")
            ->setCommune("Sprimont")
            ->setNumber("492-200")
            ->setWaterMass("M21")
            ->setIntroduction("Système souterrain du synclinal de Sprimont")
            ->setDescription("<p>Description du système dont les éléments principaux sont la grotte du Noû Bleû et le lac Bleû.</p>" . $this->getFakeDescription(1, 5))
            ->setPicture("nou-bleu.jpg");
        $this->loadBasins($manager, $system);
        $manager->persist($system);

        $system = new System();
        $system
            ->setCode("CHANT")
            ->setName("Vallon des Chantoirs (Remouchamps)")
            ->setBasin("Amblève")
            ->setCommune("Aywaille")
            ->setNumber("493-074")
            ->setWaterMass("M23")
            ->setIntroduction("Système du vallon des Chantoirs")
            ->setDescription("<p>Description du système.</p>" . $this->getFakeDescription(1, 5))
            ->setPicture("vallon-des-chantoirs.jpg");
        $this->loadBasins($manager, $system);
        $manager->persist($system);
    
        $system = new System();
        $system
            ->setCode("FURF")
            ->setName("Lesse souterraine (Furfooz)")
            ->setBasin("Lesse")
            ->setCommune("Dinant")
            ->setNumber("538-252")
            ->setWaterMass("M21")
            ->setIntroduction("Système souterrain de la basse-Lesse")
            ->setDescription("<p>Description du système dont un élément est la Galerie aux Sources.</p>" . $this->getFakeDescription(1, 5))
            ->setPicture("galerie-aux-sources.jpg");
        $this->loadBasins($manager, $system);
        $manager->persist($system);
    }
}
