<?php

namespace App\Form\StudySession;

use App\Entity\StudySession\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('courseName')
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('difficulty', ChoiceType::class, [
                'choices' => [
                    'Beginner' => 'BEGINNER',
                    'Intermediate' => 'INTERMEDIATE',
                    'Advanced' => 'ADVANCED'
                ]
            ])
            ->add('estimatedDuration', IntegerType::class)
            ->add('category')
            ->add('maxStudents', IntegerType::class, [
                'required' => false
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
