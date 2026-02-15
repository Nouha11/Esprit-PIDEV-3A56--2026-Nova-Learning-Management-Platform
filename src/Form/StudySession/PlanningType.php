<?php

namespace App\Form\StudySession;

use App\Entity\StudySession\Planning;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'empty_data' => '',
            ])
            ->add('scheduledDate', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('scheduledTime', TimeType::class, [
                'widget' => 'single_text'
            ])
            ->add('plannedDuration', IntegerType::class, [
                'empty_data' => '0',
            ])
            ->add('reminder', CheckboxType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planning::class,
        ]);
    }
}
