<?php
namespace App\Form\Admin;

use App\Entity\Gamification\Reward;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RewardFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'label' => 'Reward Name',
            'attr' => ['class' => 'form-control']
        ])

        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => false,
            'attr' => ['class' => 'form-control', 'rows' => 3]
        ])

        ->add('type', ChoiceType::class, [
            'label' => 'Reward Type',
            'choices' => [
            'Badge' => 'BADGE',
            'Achievement' => 'ACHIEVEMENT',
            'Bonus XP' => 'BONUS_XP',
            'Bonus Tokens' => 'BONUS_TOKENS',
            ],
            'attr' => ['class' => 'form-control']
        ])

        ->add('value', IntegerType::class, [
            'label' => 'Value (XP or Tokens)',
            'attr' => ['class' => 'form-control', 'min' => 0]
        ])

        ->add('requirement', TextareaType::class, [
            'label' => 'Requirement to Unlock',
            'required' => false,
            'attr' => ['class' => 'form-control', 'rows' => 2,
            'placeholder' => 'e.g., Complete 10 games']
        ])

        ->add('icon', TextType::class, [
            'label' => 'Icon URL',
            'required' => false,
            'attr' => ['class' => 'form-control']
        ])

        ->add('isActive', CheckboxType::class, [
            'label' => 'Is Active?',
            'required' => false,
        ]);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
            'data_class' => Reward::class,
            ]);
        }
}