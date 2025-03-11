<?php

namespace App\Form;

use App\Entity\Ingredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class IngredientType extends AbstractType
{
    private HtmlSanitizerInterface $sanitizer;

    public function __construct(HtmlSanitizerInterface $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredient', TextType::class, [
                'label' => 'Nom de l\'ingrédient',
                'required' => true,
                'attr' => ['class' => 'form-control shadow-sm']
            ]);

        // ✅ Ajouter le champ isActive seulement en mode édition
        if (!empty($options['is_edit'])) {
            $builder->add('isActive', ChoiceType::class, [
                'label' => 'Ingrédient actif ?',
                'choices' => [
                    'Actif' => true,
                    'Inactif' => false
                ],
                'expanded' => true, // Affiche sous forme de boutons radio
                'multiple' => false,
                'attr' => ['class' => 'form-check']
            ]);
        }

        // ✅ Ajout de la protection XSS avec `PRE_SUBMIT`
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (isset($data['ingredient'])) {
                $data['ingredient'] = $this->sanitizer->sanitize($data['ingredient']);
            }

            $event->setData($data);
        });

    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
            'is_edit' => false,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'ingredient_item',
        ]);
    }
}
