<?php

namespace App\Form\StudySession;

use App\Entity\StudySession\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Tag name cannot be empty',
                    ]),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Tag name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'maxlength' => 50,
                    'placeholder' => 'Enter tag name'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
}
