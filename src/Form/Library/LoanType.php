<?php
namespace App\Form\Library;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Library\Loan;

class LoanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bookId', HiddenType::class)
            ->add('libraryId', HiddenType::class) // Changed to hidden since it's pre-selected
            ->add('startAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Start Date & Time',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Return Date & Time',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, ['label' => 'Submit Loan Request']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
