<?php

namespace App\Form;

use App\Entity\Forum\Post;
use App\Entity\Forum\Space;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('link')
            ->add('space', EntityType::class, [
                'class' => Space::class,
                'choice_label' => 'name',
            ])
            ->add('imageFile', VichImageType::class, ['required' => false])
            ->add('attachmentFile', VichFileType::class, ['required' => false])
            // --- NEW: TAGS INPUT (Unmapped because it sends a JSON string) ---
            ->add('tagsInput', TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control tagify-input',
                    'placeholder' => 'Add tags (e.g. math, exam, help)'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}