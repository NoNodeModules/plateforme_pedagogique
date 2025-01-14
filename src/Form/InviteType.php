<?php

namespace App\Form;

use App\Entity\Invite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'email...',
                    ],
                    'purify_html' => true,
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'prenom...',
                    ],
                    'purify_html' => true,
                ]
                )
            ->add(
                'surname',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'nom...',
                    ],
                    'purify_html' => true,
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Envoyer',
                    'attr' => [
                        'class' => 'btn btn-primary',
                    ],
                ]
            )
        ;

        if ('ROLE_TEACHER' === $options['user']->getRoles()[0]) {
            $builder
                ->add(
                    'type',
                    ChoiceType::class,
                    [
                        'label' => false,
                        'choices' => [
                            'étudiant' => 'student',
                        ],
                    ]
                    )
                    ;
        } else {
            $builder
                ->add(
                        'type',
                        ChoiceType::class,
                        [
                            'label' => false,
                            'choices' => [
                                'formateur' => 'teacher',
                                'étudiant' => 'student',
                            ],
                        ]
                )
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Invite::class,
                'user' => null,
            ]
        );
    }
}
