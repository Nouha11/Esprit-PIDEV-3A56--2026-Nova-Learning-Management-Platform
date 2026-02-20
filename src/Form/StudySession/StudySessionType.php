<?php

namespace App\Form\StudySession;

use App\Entity\StudySession\StudySession;
use App\Entity\StudySession\Tag;
use App\Entity\StudySession\Planning;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudySessionType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        
        $builder
            ->add('planning', EntityType::class, [
                'class' => Planning::class,
                'choice_label' => function(Planning $planning) {
                    return $planning->getCourse()->getName() . ' - ' . $planning->getScheduledAt()->format('Y-m-d H:i');
                },
                'required' => true,
                'placeholder' => 'Select a planning...',
                'query_builder' => function($repository) use ($user) {
                    return $repository->createQueryBuilder('p')
                        ->where('p.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('p.scheduledAt', 'DESC');
                },
            ])
            ->add('startedAt', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'html5' => true,
            ])
            ->add('duration', IntegerType::class, [
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'placeholder' => 'Duration in minutes'
                ],
            ])
            ->add('actualDuration', IntegerType::class, [
                'required' => false,
                'empty_data' => null,
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Actual duration in minutes'
                ],
            ])
            ->add('xpEarned', IntegerType::class, [
                'required' => false,
                'empty_data' => null,
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'XP earned'
                ],
            ])
            ->add('mood', ChoiceType::class, [
                'choices' => [
                    'Positive' => 'positive',
                    'Neutral' => 'neutral',
                    'Negative' => 'negative'
                ],
                'required' => false,
                'placeholder' => 'Select mood...',
                'empty_data' => null,
            ])
            ->add('energyLevel', ChoiceType::class, [
                'choices' => [
                    'Low' => 'low',
                    'Medium' => 'medium',
                    'High' => 'high'
                ],
                'required' => false,
                'placeholder' => 'Select energy level...',
                'empty_data' => null,
            ])
            ->add('breakDuration', IntegerType::class, [
                'required' => false,
                'empty_data' => null,
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Break duration in minutes'
                ],
            ])
            ->add('breakCount', IntegerType::class, [
                'required' => false,
                'empty_data' => null,
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Number of breaks'
                ],
            ])
            ->add('pomodoroCount', IntegerType::class, [
                'required' => false,
                'empty_data' => null,
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Number of pomodoros completed'
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'expanded' => false,
                'attr' => [
                    'class' => 'select2'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StudySession::class,
        ]);
    }
}
