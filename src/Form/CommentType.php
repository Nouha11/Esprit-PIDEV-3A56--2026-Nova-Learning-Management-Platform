<?php

namespace App\Form;

use App\Entity\Forum\Comment;
use App\Entity\Forum\Post;
use App\Entity\users\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
       ->add('content', null, [
            'label' => 'Your Answer',
            'attr' => ['rows' => 5, 'placeholder' => 'Type your solution here...']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
