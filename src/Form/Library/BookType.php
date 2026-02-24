<?php

namespace App\Form\Library;

use App\Entity\Library\Book;
use App\Entity\Library\Library;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Champ TITRE: Obligatoire, entre 3 et 255 caractères
            ->add('title', TextType::class, [
                'label' => 'Titre du livre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le titre du livre'],
                'empty_data' => '',
            ])
            
            // Champ DESCRIPTION: Optionnel, maximum 5000 caractères
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez la description du livre', 'rows' => '5'],
                'empty_data' => '',
            ])
            
            // Champ AUTEUR: Optionnel, entre 2 et 255 caractères si fourni
            ->add('author', TextType::class, [
                'label' => 'Nom de l\'auteur',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom de l\'auteur'],
                'empty_data' => '',
            ])
            
            // Champ FORMAT: Booléen (case à cocher) - Digital ou Physique
            ->add('isDigital', CheckboxType::class, [
                'label' => 'Livre numérique (PDF/eBook)',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            
            // Champ BIBLIOTHÈQUE: Sélection de la bibliothèque (pour livres physiques uniquement)
            ->add('libraries', EntityType::class, [
                'class' => Library::class,
                'choice_label' => 'name',
                'label' => 'Libraries (for physical books)',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'attr' => ['class' => 'form-select', 'size' => 5],
                'help' => 'Select the libraries where this physical book is available (hold Ctrl/Cmd to select multiple)'
            ])
            
            // Champ PRIX: Optionnel, doit être positif avec maximum 2 décimales
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'USD',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00'],
                'empty_data' => '0',
            ])
            
            // Champ DATE DE PUBLICATION: Optionnel, ne peut pas être dans le futur
            ->add('publishedAt', DateType::class, [
                'label' => 'Date de publication',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-control']
            ])
            
            // Champ IMAGE DE COUVERTURE: Fichier optionnel
            // Validation: Maximum 5MB, formats acceptés: JPEG, PNG, WebP
            ->add('coverImage', FileType::class, [
                'label' => 'Image de couverture',
                'required' => false,
                'mapped' => false, // Ce champ n'est pas directement mappé à l'entité
                'attr' => ['class' => 'form-control', 'accept' => 'image/*'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M', // Taille maximale: 5 mégaoctets
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, WebP)',
                    ])
                ]
            ])
            
            // Champ PDF: Fichier optionnel pour livres numériques
            // Validation: Maximum 50MB, format PDF uniquement
            ->add('pdfFile', FileType::class, [
                'label' => 'Fichier PDF (pour livres numériques)',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'accept' => '.pdf'],
                'help' => 'Upload a PDF file for digital books (max 50MB)',
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide',
                    ])
                ]
            ])
            
            // Bouton de soumission
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer le livre',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
