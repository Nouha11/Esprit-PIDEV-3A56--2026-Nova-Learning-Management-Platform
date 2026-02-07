<?php

namespace App\Form\Quiz;

use App\Entity\Quiz\Question;
use App\Form\Quiz\AnswerType; 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; // Native Dropdown

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', null, ['label' => 'Question Text'])
            ->add('xpValue', null, ['label' => 'XP Reward'])
            ->add('difficulty', ChoiceType::class, [ // This is the safe native dropdown
                'choices'  => [
                    'Easy' => 'Easy',
                    'Medium' => 'Medium',
                    'Hard' => 'Hard',
                ],
            ])
            ->add('choices', CollectionType::class, [
                'entry_type' => AnswerType::class, // <--- 2. Use AnswerType here!
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}