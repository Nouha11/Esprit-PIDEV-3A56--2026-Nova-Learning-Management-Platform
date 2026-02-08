<?php

namespace App\Form\Quiz;

use App\Entity\Quiz\Question;
use App\Entity\Quiz; //  Quiz Entity
use App\Form\Quiz\AnswerType; 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // required for the dropdown

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //  NEW: Select which Quiz this question belongs to
            ->add('quiz', EntityType::class, [
                'class' => Quiz::class,
                'choice_label' => 'title', // Displays the Quiz Title in the dropdown
                'placeholder' => 'Select a Quiz...',
                'label' => 'Assign to Quiz',
                'required' => true,
            ])
            
            // 👇 Existing fields
            ->add('text', null, [
                'label' => 'Question Text',
                'attr' => ['placeholder' => 'e.g., What is 2 + 2?']
            ])
            ->add('xpValue', null, [
                'label' => 'XP Reward'
            ])
            ->add('difficulty', ChoiceType::class, [
                'choices'  => [
                    'Easy' => 'Easy',
                    'Medium' => 'Medium',
                    'Hard' => 'Hard',
                ],
            ])
            ->add('choices', CollectionType::class, [
                'entry_type' => AnswerType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Answers (Check the correct one)',
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