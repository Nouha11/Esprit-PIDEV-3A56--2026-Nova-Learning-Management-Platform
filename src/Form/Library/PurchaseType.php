<?php
namespace App\Form\Library;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bookId', HiddenType::class)
            ->add('method', ChoiceType::class, [
                'choices' => [
                    'Use Tokens' => 'tokens',
                    'Pay with Card' => 'card',
                ],
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Continue']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
