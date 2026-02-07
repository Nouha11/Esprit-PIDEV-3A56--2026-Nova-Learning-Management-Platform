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
            ->add('libraryId', IntegerType::class, ['label' => 'Library ID'])
            ->add('startAt', DateTimeType::class, ['widget' => 'single_text'])
            ->add('endAt', DateTimeType::class, ['widget' => 'single_text'])
            ->add('save', SubmitType::class, ['label' => 'Request Loan']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
