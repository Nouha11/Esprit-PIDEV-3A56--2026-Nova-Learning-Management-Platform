<?php

namespace App\Form\Admin;

use App\Entity\StudySession\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('courseName', TextType::class, [
                'label' => 'Course Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Introduction to PHP'],
                'empty_data' => '',
            ])
            
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'empty_data' => '',
            ])
            
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulty Level',
                'choices' => [
                    'Beginner' => 'BEGINNER',
                    'Intermediate' => 'INTERMEDIATE',
                    'Advanced' => 'ADVANCED'
                ],
                'attr' => ['class' => 'form-control']
            ])
            
            ->add('estimatedDuration', IntegerType::class, [
                'label' => 'Estimated Duration (minutes)',
                'attr' => ['class' => 'form-control', 'min' => 1],
                'empty_data' => '0',
            ])
            
            ->add('progress', IntegerType::class, [
                'label' => 'Progress (%)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0, 'max' => 100],
                'empty_data' => '0',
            ])
            
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Not Started' => 'NOT_STARTED',
                    'In Progress' => 'IN_PROGRESS',
                    'Completed' => 'COMPLETED'
                ],
                'attr' => ['class' => 'form-control']
            ])
            
            ->add('category', TextType::class, [
                'label' => 'Category',
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Programming'],
                'empty_data' => '',
            ])
            
            ->add('maxStudents', IntegerType::class, [
                'label' => 'Maximum Students',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1],
                'empty_data' => '0',
            ])
            
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Published',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
