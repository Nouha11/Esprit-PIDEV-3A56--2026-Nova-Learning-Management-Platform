<?php

namespace App\Form;

use App\Entity\Forum\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', null, [
                'label' => 'Your Answer',
                'attr' => ['rows' => 5, 'placeholder' => 'Type your solution here...'],
                'empty_data' => '',
                // Remove default required attribute so HTML5 doesn't block image-only posts
                'required' => false, 
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true,
                'label' => 'Attach Image (Optional)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            // Add a custom callback constraint to the whole form
            'constraints' => [
                new Assert\Callback([$this, 'validateTextOrImage']),
            ],
        ]);
    }

    // This function runs when the main form is submitted
    public function validateTextOrImage(Comment $comment, ExecutionContextInterface $context): void
    {
        // Check if content is empty AND image is missing
        if (empty(trim($comment->getContent() ?? '')) && $comment->getImageFile() === null) {
            // Attach error to the 'content' field
            $context->buildViolation('Please write a reply or attach an image.')
                ->atPath('content')
                ->addViolation();
        }
    }
}