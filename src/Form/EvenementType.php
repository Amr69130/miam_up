<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Restaurant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // on met ici une variable pour éviter de répéter les classes Tailwind communes
        $inputStyle = 'w-full mt-1 px-4 py-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block transition duration-200 outline-none';

        $builder
            ->add('titre', TextType::class, [
                'label' => 'Nom de l\'événement',
                'attr' => [
                    'placeholder' => 'Ex: Soirée Tapas',
                    'class' => $inputStyle
                ]
            ])
            ->add('restaurant', EntityType::class, [
                'class' => Restaurant::class,
                'choice_label' => 'nomRestaurant',
                'label' => 'Dans quel établissement ?',
                'attr' => ['class' => $inputStyle]
            ])
            ->add('description_evenement', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 4,
                    'class' => $inputStyle,
                    'placeholder' => 'Détaillez le programme...'
                ]
            ])
            ->add('date_evenement', DateTimeType::class, [
                'label' => 'Début',
                'widget' => 'single_text',
                'attr' => ['class' => $inputStyle]
            ])
            ->add('date_fin', DateTimeType::class, [
                'label' => 'Fin',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => $inputStyle]
            ])
            ->add('nombre_places', IntegerType::class, [
                'label' => 'Places disponibles (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => $inputStyle,
                    'min' => 1
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
