<?php

namespace App\Form;

use App\Entity\Link;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class LinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Nom du lien',
                    'required' => true,
                ]
            )
            ->add(
                'link',
                TextType::class,
                [
                    'label' => 'Lien',
                    'required' => true,
                ]
            )
            ->add(
                'description',
                TextareaType::class,
            )
            ->add(
                'visibility',
                CheckboxType::class,
                [
                    'label' => 'Rendre le lien visible',
                ]
            )
            ->add(
                'usable',
                CheckboxType::class,
                [
                    'label' => 'Rendre le lien utilisable',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Link::class,
        ]);
    }
}
