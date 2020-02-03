<?php

namespace App\Form;

use App\Entity\User;
use App\Form\SystemRoleType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Formulaire permettant l'introduction des données d'un compte d'utilisateur.
 */
class UserType extends AbstractType
{
    /**
     * Construit le formulaire.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* Construire le contenu de la liste déroulante des rôles */
        $roles = [
            "Utilisateur" => 'ROLE_USER',
            "Administrateur" => 'ROLE_ADMIN',
        ];
        if ($options['superAdmin'] === true) {
            /* Cooptation possible d'un autre super-utilisateur */
            $roles["Super-Administrateur"] = 'ROLE_SUPER_ADMIN';
        }

        /* Construire le formulaire */
        $builder
            ->add('firstName', TextType::class, [
                'label' => "Prénom",
                'required' => false,
                'attr' => [
                    'placeholder' => "Prénom",
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => "Nom",
                'required' => false,
                'attr' => [
                    'placeholder' => "Nom",
                ],
            ])
            ->add('organization', TextType::class, [
                'label' => "Organisation",
                'required' => false,
                'attr' => [
                    'placeholder' => "Société, organisme ou club",
                ],
            ])
            ->add('email', TextType::class, [
                'label' => "E-mail",
                'required' => true,
                'attr' => [
                    'placeholder' => "nom.prenom@exemple.org",
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => "Téléphone",
                'required' => false,
                'attr' => [
                    'placeholder' => "+32 999 99.99.99",
                ],
            ])
            ->add('displayName', TextType::class, [
                'label' => "Pseudonyme",
                'required' => false,
                'attr' => [
                    'placeholder' => "Peut être complété automatiquement",
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'required' => false,
                'attr' => [
                    'placeholder' => "Brève présentation de l'utilisateur (facultatif)",
                ],
            ])
            ->add('picture', UrlType::class, [
                'label' => "Avatar",
                'required' => false,
                'attr' => [
                    'placeholder' => "https://www.exemple.org/photo",
                ],
            ])
            ->add('systemRoles', CollectionType::class, [
                'label' => "Rôles sur les systèmes",
                'entry_type' => SystemRoleType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('mainRole', ChoiceType::class, [
                'label' => "Rôle principal",
                'choices' => $roles,
                'required' => true,
            ])
        ;
    }

    /**
     * Finit la vue du formulaire.
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @return void
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /* Ordonner les rôles par nom de système */
        usort(
            $view->children['systemRoles']->children,
            function ($a, $b) {
                $aSystem = $a->vars['data']->getSystem();
                $bSystem = $b->vars['data']->getSystem();
                return
                    (($aSystem === null) ? null : $aSystem->getName()) <=>
                    (($bSystem === null) ? null : $bSystem->getName());
            }
        );
    }

    /**
     * Configure les options du formulaire.
     *
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => User::class,
                'superAdmin' => false,
            ])
            ->setAllowedTypes('superAdmin', 'boolean');
    }
}
