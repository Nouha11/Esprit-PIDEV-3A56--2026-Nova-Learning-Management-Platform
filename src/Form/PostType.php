<?php

namespace App\Form;

use App\Entity\Forum\Space; // <-- NEW
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use App\Entity\Forum\Post;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // --- NEW: Space Selection Dropdown ---
            ->add('space', EntityType::class, [
                'class' => Space::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a Study Space...',
                'required' => true,
                'label' => 'Study Space',
                'attr' => ['class' => 'form-select mb-3']
            ])
            ->add('title', null, [
                'empty_data' => '',
            ])
            ->add('content', null, [
                'empty_data' => '',
            ])
            ->add('link', UrlType::class, [
                'required' => false,
                'label' => 'Attach a Link (Optional)',
                'attr' => ['placeholder' => 'https://example.com...'],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Remove Image',
                'download_uri' => false,
                'image_uri' => true,
                'label' => 'Upload an Image (Optional)',
                'asset_helper' => true,
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