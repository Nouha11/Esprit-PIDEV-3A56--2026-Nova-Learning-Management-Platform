<?php

namespace App\Form\Quiz;

use App\Entity\Quiz\Choice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerType extends AbstractType  // <--- CHANGED THIS NAME
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', null, [
                'label' => 'Answer Text',
                'attr' => ['placeholder' => 'Enter an answer...']
            ])
            ->add('isCorrect', CheckboxType::class, [
                'label' => 'Correct?',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Choice::class,
        ]);
    }
}