<?php

namespace App\Form\Admin;

use App\Entity\StudySession\Planning;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningStatusFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Planning Status',
                'choices' => [
                    'Scheduled' => Planning::STATUS_SCHEDULED,
                    'Completed' => Planning::STATUS_COMPLETED,
                    'Missed' => Planning::STATUS_MISSED,
                    'Cancelled' => Planning::STATUS_CANCELLED
                ],
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planning::class,
        ]);
    }
}
