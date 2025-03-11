<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Enum\NiveauEnum;
use App\Enum\RepasEnum;
use App\Enum\ViandeEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class RecetteType extends AbstractType
{
    private HtmlSanitizerInterface $sanitizer;

    public function __construct(HtmlSanitizerInterface $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'attr' => ['class' => 'form-control shadow-sm']
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo de la recette',
                'required' => true,
                'mapped' => false, 
                'attr' => ['class' => 'form-control shadow-sm'],
                'constraints' => [
                    new File([
                        'maxSize' => '10M', 
                        'maxSizeMessage' => 'Le fichier est trop volumineux (10 Mo max).',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg'], 
                        'mimeTypesMessage' => 'Seuls les fichiers JPEG, PNG et JPG sont autorisés.',
                    ])
                ]
            ])
            ->add('preparation', TextareaType::class, [
                'label' => 'Préparation',
                'required' => true,
                'attr' => ['class' => 'form-control shadow-sm', 'rows' => 5]
            ])
            ->add('ingredients', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'ingredient',
                'multiple' => true,
                'expanded' => true, // Pour afficher des cases à cocher
            ])
 
            ->add('niveau', ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => NiveauEnum::cases(),
                'choice_label' => fn(NiveauEnum $niveau) => $niveau->label(), 
                'choice_value' => fn(?NiveauEnum $niveau) => $niveau?->value,
                'placeholder' => 'Sélectionnez un niveau',
                'attr' => ['class' => 'form-select shadow-sm'],
            ])
            ->add('repas', ChoiceType::class, [
                'label' => 'Repas',
                'choices' => RepasEnum::cases(),
                'choice_label' => fn(RepasEnum $repas) => $repas->label(),
                'choice_value' => fn(?RepasEnum $repas) => $repas?->value, 
                //'expanded' => false, 
                //'multiple' => false,
                'placeholder' => 'Sélectionnez un type de repas',
                'attr' => ['class' => 'form-select shadow-sm'],
            ])
            ->add('viande', ChoiceType::class, [
                'label' => 'Viande',
                'choices' => ViandeEnum::cases(),
                'choice_label' => fn(ViandeEnum $viande) => $viande->label(), 
                'choice_value' => fn(?ViandeEnum $viande) => $viande?->value,
                'placeholder' => 'Sélectionnez une viande',
                'attr' => ['class' => 'form-select shadow-sm'],
            ])
            ->add('duree', TextType::class, [
                'label' => 'Durée',
                'required' => true,
                'attr' => ['class' => 'form-control shadow-sm']
            ]);

        // ✅ Afficher "isActive" seulement en mode édition
        if (!empty($options['is_edit'])) {
            $builder->add('isActive', ChoiceType::class, [
                'label' => 'Recette active ?',
                'choices' => [
                    'Actif' => true,
                    'Inactif' => false
                ],
                'expanded' => true, 
                'multiple' => false,
                'attr' => ['class' => 'form-check']
            ]);
        }

        // ✅ Ajout de la protection XSS avec `PRE_SUBMIT`
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            // Vérifie que les champs existent et applique le nettoyage
            if (isset($data['titre'])) {
                $data['titre'] = $this->sanitizer->sanitize($data['titre']);
            }
            if (isset($data['preparation'])) {
                $data['preparation'] = $this->sanitizer->sanitize($data['preparation']);
            }
            if (isset($data['duree'])) {
                $data['duree'] = $this->sanitizer->sanitize($data['duree']);
            }

            $event->setData($data);
        });

        // ✅ Bouton de soumission

    }
   
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
            'is_edit' => false, 
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'recette_item',
        ]);
    }
}
