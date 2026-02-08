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
                    'Credit Card' => 'credit_card',
                    'PayPal' => 'paypal',
                ],
                'expanded' => false,
                'label' => 'Payment Method',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('save', SubmitType::class, ['label' => 'Complete Purchase']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
