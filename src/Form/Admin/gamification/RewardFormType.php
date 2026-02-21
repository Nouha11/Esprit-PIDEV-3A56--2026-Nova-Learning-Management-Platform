<?php
namespace App\Form\Admin;

use App\Entity\Gamification\Game;
use App\Entity\Gamification\Reward;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RewardFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'label' => 'Reward Name',
            'attr' => ['class' => 'form-control'],
            'empty_data' => '',
        ])

        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => false,
            'attr' => ['class' => 'form-control', 'rows' => 3],
            'empty_data' => '',
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
            'attr' => ['class' => 'form-control', 'min' => 0],
            'empty_data' => '0',
        ])

        ->add('requirement', TextareaType::class, [
            'label' => 'Requirement to Unlock',
            'required' => false,
            'attr' => ['class' => 'form-control', 'rows' => 2,
            'placeholder' => 'e.g., Complete 10 games'],
            'empty_data' => '',
        ])

        ->add('iconFile', FileType::class, [
            'label' => 'Icon Image (PNG, JPG, SVG)',
            'mapped' => false,
            'required' => false,
            'attr' => ['class' => 'form-control', 'accept' => 'image/*'],
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                        'image/jpg',
                        'image/svg+xml',
                        'image/webp',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image file (PNG, JPG, SVG, WEBP)',
                ])
            ],
            'help' => 'Current icon: ' . ($options['data']->getIcon() ?? 'None'),
        ])

        ->add('games', EntityType::class, [
            'class' => Game::class,
            'choice_label' => function(Game $game) {
                return $game->getName() . ' (' . $game->getType() . ' - ' . $game->getDifficulty() . ')';
            },
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'by_reference' => false,
            'label' => 'Associated Games (Full Games Only)',
            'help' => 'Check the full games that should offer this reward. Mini games only regenerate energy and cannot offer rewards.',
            'query_builder' => function($repository) {
                return $repository->createQueryBuilder('g')
                    ->where('g.category = :category')
                    ->setParameter('category', 'FULL_GAME')
                    ->orderBy('g.name', 'ASC');
            },
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