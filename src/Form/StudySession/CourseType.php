<?php

namespace App\Form\StudySession;

use App\Entity\StudySession\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('courseName', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'invalid_message' => 'Please enter a valid course name',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('difficulty', ChoiceType::class, [
                'choices' => [
                    'Beginner' => 'BEGINNER',
                    'Intermediate' => 'INTERMEDIATE',
                    'Advanced' => 'ADVANCED'
                ],
                'required' => true,
                'placeholder' => 'Select difficulty...',
                'invalid_message' => 'Please select a valid difficulty',
            ])
            ->add('estimatedDuration', IntegerType::class, [
                'required' => true,
                'empty_data' => '0',
                'invalid_message' => 'Please enter a valid duration',
            ])
            ->add('category', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'invalid_message' => 'Please enter a valid category',
            ])
            ->add('maxStudents', IntegerType::class, [
                'required' => false,
                'invalid_message' => 'Please enter a valid number',
                'empty_data' => '0',
            ])
            ->add('isPublished');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}