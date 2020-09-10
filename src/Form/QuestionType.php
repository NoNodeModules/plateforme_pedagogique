<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'enonce',
                TextType::class,
                array(
                    'attr' => array(
                        'class' => 'enonceQuestionnaire',
                        'placeholder' => "Entrée l'intitulé de la question",
                    ),
                )
            )
            ->add(
                'score',
                NumberType::class,
                array(
                    'attr' => array(
                        'class' => 'score',
                    ),
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Question::class,
            ]
        );
    }
}