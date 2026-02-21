<?php
namespace App\Form\Admin;

use App\Entity\Gamification\Game;
use App\Entity\Gamification\Reward;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Memory Master'],
                'empty_data' => '',
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'empty_data' => '',
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

            ->add('category', ChoiceType::class, [
                'label' => 'Game Category',
                'choices' => [
                    'Full Game (with rewards)' => 'FULL_GAME',
                    'Mini Game (energy regeneration only)' => 'MINI_GAME',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'data-game-category' => 'true'
                ],
                'help' => 'Full games offer rewards and XP. Mini games only regenerate energy points.',
            ])

            ->add('tokenCost', IntegerType::class, [
                'label' => 'Token Cost (0 for free)',
                'attr' => ['class' => 'form-control', 'min' => 0],
                'empty_data' => '0',
            ])

            ->add('energyPoints', IntegerType::class, [
                'label' => 'Energy Points (for mini games)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'data-energy-field' => 'true'
                ],
                'required' => false,
                'help' => 'Amount of energy points this mini game regenerates. Only applicable for mini games.',
            ])

            ->add('rewardTokens', IntegerType::class, [
                'label' => 'Reward Tokens',
                'attr' => ['class' => 'form-control', 'min' => 0],
                'empty_data' => '0',
            ])

            ->add('rewardXP', IntegerType::class, [
                'label' => 'Reward XP',
                'attr' => ['class' => 'form-control', 'min' => 0],
                'empty_data' => '0',
            ])
            
            ->add('isActive', CheckboxType::class, [
                'label' => 'Is Active?',
                'required' => false,
            ])

            // Add rewards relationship field (excluding level milestones)
            ->add('rewards', EntityType::class, [
                'class' => Reward::class,
                'choice_label' => function(Reward $reward) {
                    $type = str_replace('_', ' ', $reward->getType());
                    $value = in_array($reward->getType(), ['BONUS_XP', 'BONUS_TOKENS']) 
                        ? ' (' . $reward->getValue() . ')' 
                        : '';
                    return $reward->getName() . ' [' . $type . $value . ']';
                },
                'multiple' => true,
                'expanded' => true, // Creates checkboxes instead of a select
                'required' => false,
                'label' => 'Special Rewards',
                'help' => 'Select rewards that players can earn by completing this game (Level milestones are awarded automatically and cannot be assigned to specific games)',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->where('r.isActive = :active')
                        ->andWhere('r.type != :levelMilestone')
                        ->setParameter('active', true)
                        ->setParameter('levelMilestone', 'LEVEL_MILESTONE')
                        ->orderBy('r.type', 'ASC')
                        ->addOrderBy('r.name', 'ASC');
                },
                'choice_attr' => function(Reward $reward) {
                    $class = 'form-check-input';
                    if (!$reward->isActive()) {
                        $class .= ' text-muted';
                    }
                    return ['class' => $class];
                },
                'attr' => ['class' => 'rewards-list']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}