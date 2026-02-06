<?php
namespace App\Form\Admin;

use App\Entity\Gamification\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
        'label' => 'Game Name',
        'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Memory Master']
        ])

        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'attr' => ['class' => 'form-control', 'rows' => 4]
        ])

        ->add('type', ChoiceType::class, [
            'label' => 'Game Type',
            'choices' => [
            'Puzzle' => 'PUZZLE',
            'Memory' => 'MEMORY',
            'Trivia' => 'TRIVIA',
            'Arcade' => 'ARCADE',
        ],
            'attr' => ['class' => 'form-control']
        ])

        ->add('difficulty', ChoiceType::class, [
            'label' => 'Difficulty',
            'choices' => [
            'Easy' => 'EASY',
            'Medium' => 'MEDIUM',
            'Hard' => 'HARD',
        ],
            'attr' => ['class' => 'form-control']
        ])

        ->add('tokenCost', IntegerType::class, [
            'label' => 'Token Cost (0 for free)',
            'attr' => ['class' => 'form-control', 'min' => 0]
        ])

        ->add('rewardTokens', IntegerType::class, [
            'label' => 'Reward Tokens',
            'attr' => ['class' => 'form-control', 'min' => 0]
        ])

        ->add('rewardXP', IntegerType::class, [
            'label' => 'Reward XP',
            'attr' => ['class' => 'form-control', 'min' => 0]
        ])
        
        ->add('isActive', CheckboxType::class, [
            'label' => 'Is Active?',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        'data_class' => Game::class,
    ]);
    }
}