<?php

namespace App\Form;

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
            ->add('title', null, [
                'empty_data' => '',
            ])
            ->add('content', null, [
                'empty_data' => '',
            ])
            // --- NEW: Link Field ---
            ->add('link', UrlType::class, [
                'required' => false, // Users don't HAVE to provide a link
                'label' => 'Attach a Link (Optional)',
                'attr' => [
                    'placeholder' => 'https://example.com...',
                ],
            ])
            // --- NEW: Image Upload Field ---
            ->add('imageFile', VichImageType::class, [
                'required' => false, // Users don't HAVE to upload an image
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