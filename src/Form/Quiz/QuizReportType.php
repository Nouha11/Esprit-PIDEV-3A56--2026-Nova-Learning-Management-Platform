<?php

namespace App\Form\Quiz;

use App\Entity\Quiz\QuizReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'choices' => [
                    'Incorrect Information' => 'Incorrect Information',
                    'Inappropriate Content' => 'Inappropriate Content',
                    'Duplicate Quiz' => 'Duplicate Quiz',
                    'Poor Quality' => 'Poor Quality',
                    'Other' => 'Other',
                ],
                'placeholder' => 'Select a reason...',
                'label' => 'Reason for Report',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Additional Details',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Please provide more details about the issue...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizReport::class,
        ]);
    }
}
