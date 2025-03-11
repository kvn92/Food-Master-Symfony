<?php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class CommentaireType extends AbstractType
{
    private HtmlSanitizerInterface $sanitizer;

    public function __construct(HtmlSanitizerInterface $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentaire', TextareaType::class, [
                'required' => true,
                'label' => 'Votre commentaire',
                'attr' => [
                    'class' => 'form-control shadow-sm',
                    'rows' => 3,
                    'placeholder' => 'Écrivez votre commentaire ici...',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ]);

        // ✅ Ajout d'un écouteur d'événement pour nettoyer le commentaire avant soumission
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (isset($data['commentaire'])) {
                $data['commentaire'] = $this->sanitizer->sanitize($data['commentaire']);
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'commentaire_item',
        ]);
    }
}
