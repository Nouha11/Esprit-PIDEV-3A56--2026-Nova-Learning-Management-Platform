<?php

namespace App\Form\Admin\Quiz;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'required' => false,
                'label' => 'Search',
                'attr' => [
                    'placeholder' => 'Search by title or description...',
                    'class' => 'form-control'
                ]
            ])
            ->add('minQuestions', IntegerType::class, [
                'required' => false,
                'label' => 'Min Questions',
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control'
                ]
            ])
            ->add('maxQuestions', IntegerType::class, [
                'required' => false,
                'label' => 'Max Questions',
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control'
                ]
            ])
            ->add('sortBy', ChoiceType::class, [
                'choices' => [
                    'Title' => 'title',
                    'ID' => 'id',
                    'Question Count' => 'questionCount'
                ],
                'data' => 'title',
                'label' => 'Sort By',
                'attr' => ['class' => 'form-select']
            ])
            ->add('sortOrder', ChoiceType::class, [
                'choices' => [
                    'Ascending' => 'ASC',
                    'Descending' => 'DESC'
                ],
                'data' => 'ASC',
                'label' => 'Sort Order',
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return ''; // Remove form name prefix for cleaner URLs
    }
}