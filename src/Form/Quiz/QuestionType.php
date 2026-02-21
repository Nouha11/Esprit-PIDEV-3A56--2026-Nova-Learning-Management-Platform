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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // required for the dropdown
use Vich\UploaderBundle\Form\Type\VichImageType;

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
                'invalid_message' => 'Please select a valid quiz',
            ])
            
            // 👇 Existing fields
            ->add('text', TextareaType::class, [
                'label' => 'Question Text',
                'required' => true,
                'attr' => [
                    'placeholder' => 'e.g., What is 2 + 2?',
                    'rows' => 4
                ],
                'invalid_message' => 'Please enter valid question text',
                'empty_data' => '', // Prevents null from being passed
            ])
            ->add('xpValue', IntegerType::class, [
                'label' => 'XP Reward',
                'required' => true,
                'invalid_message' => 'Please enter a valid XP value',
                'empty_data' => '0',
            ])
            ->add('difficulty', ChoiceType::class, [
                'choices'  => [
                    'Easy' => 'Easy',
                    'Medium' => 'Medium',
                    'Hard' => 'Hard',
                ],
                'required' => true,
                'placeholder' => 'Select difficulty...',
                'invalid_message' => 'Please select a valid difficulty',
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Question Image (Optional)',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Remove image',
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true,
                'help' => 'Upload an image to make the question more engaging (max 2MB)',
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