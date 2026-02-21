<?php

namespace App\Form\Admin\gamification;

use App\Entity\Gamification\Reward;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class LevelMilestoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Milestone Name',
                'attr' => [
                    'placeholder' => 'e.g., Level 5 Champion',
                    'class' => 'form-control'
                ],
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Describe this milestone achievement...',
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'empty_data' => '',
            ])
            ->add('requiredLevel', IntegerType::class, [
                'label' => 'Required Level',
                'attr' => [
                    'placeholder' => 'Level needed to earn this milestone',
                    'class' => 'form-control'
                ],
                'help' => 'Students will automatically receive this reward when they reach this level',
                'empty_data' => '0',
            ])
            ->add('value', IntegerType::class, [
                'label' => 'Token Reward',
                'attr' => [
                    'placeholder' => 'Number of tokens to award',
                    'class' => 'form-control'
                ],
                'help' => 'How many tokens the student receives for reaching this level',
                'empty_data' => '0',
            ])
            ->add('icon', FileType::class, [
                'label' => 'Icon Image',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, GIF, or WebP)',
                    ])
                ],
                'help' => 'Upload an icon image (max 2MB)'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Only active milestones can be earned'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reward::class,
        ]);
    }
}
